<?php
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pet_owner') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$pet_id = $_GET['pet_id'] ?? 0;

// Verify pet ownership
$pet_result = $conn->query("SELECT * FROM pets WHERE id = $pet_id AND owner_id = $user_id");
if (!$pet_result || $pet_result->num_rows === 0) {
    header("Location: my_pets.php");
    exit;
}
$pet = $pet_result->fetch_assoc();

// Get health records
$health_records_query = "
    SELECT hr.*, CONCAT(u.first_name, ' ', u.last_name) as vet_name, u.email as vet_email
    FROM health_records hr
    LEFT JOIN users u ON hr.veterinarian_id = u.id
    WHERE hr.pet_id = $pet_id
    ORDER BY hr.record_date DESC, hr.created_at DESC
";
$health_records = $conn->query($health_records_query)->fetch_all(MYSQLI_ASSOC);

// Get active medications
$medications_query = "
    SELECT * FROM medications 
    WHERE pet_id = $pet_id AND is_active = 1
    ORDER BY start_date DESC
";
$medications = $conn->query($medications_query)->fetch_all(MYSQLI_ASSOC);

// Get vaccination records
$vaccinations_query = "
    SELECT v.*, CONCAT(u.first_name, ' ', u.last_name) as vet_name
    FROM vaccinations v
    LEFT JOIN users u ON v.veterinarian_id = u.id
    WHERE v.pet_id = $pet_id
    ORDER BY v.administered_date DESC
";
$vaccinations = $conn->query($vaccinations_query)->fetch_all(MYSQLI_ASSOC);

// Get upcoming reminders
$reminders_query = "
    SELECT * FROM reminders 
    WHERE pet_id = $pet_id AND reminder_date >= CURDATE() AND is_completed = 0
    ORDER BY reminder_date ASC
    LIMIT 5
";
$reminders = $conn->query($reminders_query)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pet['name']); ?>'s Health Records - Pet Health Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/tailwind.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'ui-sans-serif', 'system-ui'],
                    }
                }
            }
        }
    </script>
    <script src="js/main.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include 'includes/header.php'; ?>

    <div class="container py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <nav class="text-sm text-gray-500 mb-2">
                        <a href="my_pets.php" class="hover:text-blue-600">My Pets</a> 
                        <span class="mx-2">></span> 
                        <span><?php echo htmlspecialchars($pet['name']); ?></span>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($pet['name']); ?>'s Health Records</h1>
                    <p class="text-gray-600"><?php echo htmlspecialchars($pet['species']); ?> • <?php echo htmlspecialchars($pet['breed'] ?? 'Mixed breed'); ?></p>
                </div>
                <div class="flex space-x-3">
                    <a href="pet_profile.php?pet_id=<?php echo $pet_id; ?>" class="btn btn-outline">
                        View Profile
                    </a>
                    <a href="schedule_appointment.php?pet_id=<?php echo $pet_id; ?>" class="btn btn-primary">
                        Schedule Appointment
                    </a>
                </div>
            </div>
        </div>

        <!-- Pet Quick Info & Alerts -->
        <div class="grid lg:grid-cols-3 gap-6 mb-8">
            <!-- Pet Info Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-800">Pet Information</h3>
                </div>
                <div class="card-body">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Weight:</span>
                            <span class="font-medium"><?php echo $pet['weight'] ? $pet['weight'] . ' lbs' : 'Not recorded'; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Age:</span>
                            <span class="font-medium">
                                <?php 
                                if ($pet['dob']) {
                                    $birth = new DateTime($pet['dob']);
                                    $now = new DateTime();
                                    $age = $birth->diff($now);
                                    echo $age->y . ' years, ' . $age->m . ' months';
                                } else {
                                    echo 'Unknown';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Microchip:</span>
                            <span class="font-medium"><?php echo $pet['microchip_id'] ?: 'Not chipped'; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Health Alerts -->
            <?php if ($pet['allergies'] || $pet['special_needs'] || !empty($medications)): ?>
                <div class="card border-l-4 border-l-yellow-400">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-yellow-800 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Health Alerts
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-3">
                            <?php if ($pet['allergies']): ?>
                                <div class="bg-red-50 p-3 rounded-lg">
                                    <strong class="text-red-800">Allergies:</strong>
                                    <p class="text-red-700 mt-1"><?php echo htmlspecialchars($pet['allergies']); ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if ($pet['special_needs']): ?>
                                <div class="bg-blue-50 p-3 rounded-lg">
                                    <strong class="text-blue-800">Special Needs:</strong>
                                    <p class="text-blue-700 mt-1"><?php echo htmlspecialchars($pet['special_needs']); ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($medications)): ?>
                                <div class="bg-green-50 p-3 rounded-lg">
                                    <strong class="text-green-800">Current Medications:</strong>
                                    <div class="mt-1 space-y-1">
                                        <?php foreach (array_slice($medications, 0, 3) as $med): ?>
                                            <p class="text-green-700 text-sm"><?php echo htmlspecialchars($med['medication_name']); ?> - <?php echo htmlspecialchars($med['dosage']); ?></p>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Upcoming Reminders -->
            <?php if (!empty($reminders)): ?>
                <div class="card border-l-4 border-l-blue-400">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-blue-800">Upcoming Care</h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-3">
                            <?php foreach ($reminders as $reminder): ?>
                                <div class="flex items-center justify-between p-2 bg-blue-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-blue-800"><?php echo ucfirst($reminder['reminder_type']); ?></p>
                                        <p class="text-sm text-blue-600"><?php echo htmlspecialchars($reminder['title']); ?></p>
                                    </div>
                                    <span class="text-xs text-blue-600"><?php echo date('M j', strtotime($reminder['reminder_date'])); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Main Content Tabs -->
        <div class="card">
            <div class="card-header">
                <div class="flex space-x-6 border-b border-gray-200 -mb-px">
                    <button class="tab-button active" data-tab="health-records">Health Records</button>
                    <button class="tab-button" data-tab="medications">Medications</button>
                    <button class="tab-button" data-tab="vaccinations">Vaccinations</button>
                </div>
            </div>
            
            <!-- Health Records Tab -->
            <div id="health-records" class="tab-content active">
                <div class="card-body">
                    <?php if (empty($health_records)): ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">No health records yet</h3>
                            <p class="text-gray-500 mb-4">Health records will appear here after vet visits</p>
                            <a href="schedule_appointment.php?pet_id=<?php echo $pet_id; ?>" class="btn btn-primary">Schedule Appointment</a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($health_records as $record): ?>
                                <div class="border rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <h4 class="font-semibold text-gray-800"><?php echo ucfirst(str_replace('_', ' ', $record['record_type'])); ?></h4>
                                            <p class="text-sm text-gray-600">
                                                <?php echo date('M j, Y', strtotime($record['record_date'])); ?>
                                                <?php if ($record['vet_name']): ?>
                                                    • Dr. <?php echo htmlspecialchars($record['vet_name']); ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-full text-xs">
                                            <?php echo ucfirst($record['record_type']); ?>
                                        </span>
                                    </div>
                                    
                                    <?php if ($record['description']): ?>
                                        <div class="mb-3">
                                            <strong class="text-gray-700">Description:</strong>
                                            <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($record['description']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($record['diagnosis']): ?>
                                        <div class="mb-3">
                                            <strong class="text-gray-700">Diagnosis:</strong>
                                            <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($record['diagnosis']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($record['treatment']): ?>
                                        <div class="mb-3">
                                            <strong class="text-gray-700">Treatment:</strong>
                                            <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($record['treatment']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($record['medications_prescribed']): ?>
                                        <div class="mb-3 bg-green-50 p-3 rounded-lg">
                                            <strong class="text-green-800">Medications Prescribed:</strong>
                                            <p class="text-green-700 mt-1"><?php echo htmlspecialchars($record['medications_prescribed']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($record['diagnosis']): ?>
                                        <div class="bg-blue-50 p-3 rounded-lg">
                                            <strong class="text-blue-800">Diagnosis:</strong>
                                            <p class="text-blue-700 mt-1"><?php echo htmlspecialchars($record['diagnosis']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($record['follow_up_date']): ?>
                                        <div class="mt-3 text-sm text-amber-600">
                                            <strong>Follow-up scheduled:</strong> <?php echo date('M j, Y', strtotime($record['follow_up_date'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Medications Tab -->
            <div id="medications" class="tab-content">
                <div class="card-body">
                    <?php if (empty($medications)): ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">No active medications</h3>
                            <p class="text-gray-500">Current medications will appear here</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($medications as $med): ?>
                                <div class="border rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($med['medication_name']); ?></h4>
                                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($med['dosage']); ?> • <?php echo htmlspecialchars($med['frequency']); ?></p>
                                        </div>
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">Active</span>
                                    </div>
                                    
                                    <?php if ($med['purpose']): ?>
                                        <div class="mb-3">
                                            <strong class="text-gray-700">Purpose:</strong>
                                            <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($med['purpose']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($med['instructions']): ?>
                                        <div class="mb-3 bg-blue-50 p-3 rounded-lg">
                                            <strong class="text-blue-800">Instructions:</strong>
                                            <p class="text-blue-700 mt-1"><?php echo htmlspecialchars($med['instructions']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="flex justify-between text-sm text-gray-500">
                                        <span>Started: <?php echo date('M j, Y', strtotime($med['start_date'])); ?></span>
                                        <?php if ($med['end_date']): ?>
                                            <span>Until: <?php echo date('M j, Y', strtotime($med['end_date'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Vaccinations Tab -->
            <div id="vaccinations" class="tab-content">
                <div class="card-body">
                    <?php if (empty($vaccinations)): ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.871 4A17.926 17.926 0 003 12c0 2.874.673 5.59 1.871 8m14.13 0a17.926 17.926 0 001.87-8c0-2.874-.673-5.59-1.87-8M9 9h1.246a1 1 0 01.961.725l1.586 5.55a1 1 0 00.961.725H15m1-7h-.08a2 2 0 00-1.519.698L9.6 15.302A2 2 0 018.08 16H8"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">No vaccination records</h3>
                            <p class="text-gray-500">Vaccination history will appear here</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($vaccinations as $vacc): ?>
                                <div class="border rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($vacc['vaccine_name']); ?></h4>
                                            <p class="text-sm text-gray-600">
                                                <?php echo date('M j, Y', strtotime($vacc['administered_date'])); ?>
                                                <?php if ($vacc['vet_name']): ?>
                                                    • Dr. <?php echo htmlspecialchars($vacc['vet_name']); ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <?php if ($vacc['next_due_date'] && strtotime($vacc['next_due_date']) > time()): ?>
                                            <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs">
                                                Next: <?php echo date('M j, Y', strtotime($vacc['next_due_date'])); ?>
                                            </span>
                                        <?php elseif ($vacc['next_due_date']): ?>
                                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs">
                                                Overdue
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($vacc['batch_number']): ?>
                                        <p class="text-sm text-gray-500">Batch: <?php echo htmlspecialchars($vacc['batch_number']); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($vacc['manufacturer']): ?>
                                        <p class="text-sm text-gray-500">Manufacturer: <?php echo htmlspecialchars($vacc['manufacturer']); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetTab = button.getAttribute('data-tab');
                    
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked button and corresponding content
                    button.classList.add('active');
                    document.getElementById(targetTab).classList.add('active');
                });
            });
        });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
