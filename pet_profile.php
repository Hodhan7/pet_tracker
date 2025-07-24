<?php
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pet_owner') {
    header("Location: login.php");
    exit;
}

$pet_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Get pet information
$pet_result = $conn->query("SELECT * FROM pets WHERE id = $pet_id AND owner_id = $user_id");
if ($pet_result->num_rows === 0) {
    header("Location: owner_dashboard.php");
    exit;
}
$pet = $pet_result->fetch_assoc();

// Get health records
$health_records = $conn->query("
    SELECT * FROM health_records 
    WHERE pet_id = $pet_id 
    ORDER BY record_date DESC
")->fetch_all(MYSQLI_ASSOC);

// Get appointments
$appointments = $conn->query("
    SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as vet_name 
    FROM appointments a 
    LEFT JOIN users u ON a.veterinarian_id = u.id 
    WHERE a.pet_id = $pet_id 
    ORDER BY a.appointment_date DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pet['name']); ?> - Pet Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Pet Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                        <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($pet['name']); ?></h1>
                            <p class="text-gray-600"><?php echo htmlspecialchars($pet['breed']); ?> • <?php echo htmlspecialchars($pet['species']); ?></p>
                            <p class="text-sm text-gray-500">
                                Born: <?php echo date('M j, Y', strtotime($pet['dob'])); ?> 
                                (<?php echo floor((time() - strtotime($pet['dob'])) / 31556926); ?> years old)
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="schedule_appointment.php?pet_id=<?php echo $pet['id']; ?>" 
                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            Schedule Appointment
                        </a>
                        <a href="view_health_records.php?pet_id=<?php echo $pet['id']; ?>" 
                           class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                            View Health Records
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Left Column - Pet Info & Quick Stats -->
                <div class="space-y-6">
                    <!-- Pet Information Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Pet Information</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Species:</span>
                                <span class="font-medium"><?php echo htmlspecialchars($pet['species']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Breed:</span>
                                <span class="font-medium"><?php echo htmlspecialchars($pet['breed']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date of Birth:</span>
                                <span class="font-medium"><?php echo date('M j, Y', strtotime($pet['dob'])); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Age:</span>
                                <span class="font-medium"><?php echo floor((time() - strtotime($pet['dob'])) / 31556926); ?> years</span>
                            </div>
                            <?php if (isset($pet['weight']) && $pet['weight']): ?>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Weight:</span>
                                <span class="font-medium"><?php echo htmlspecialchars($pet['weight']); ?> kg</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Stats</h2>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-gray-700">Health Records</span>
                                </div>
                                <span class="font-semibold text-gray-800"><?php echo count($health_records); ?></span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-gray-700">Appointments</span>
                                </div>
                                <span class="font-semibold text-gray-800"><?php echo count($appointments); ?></span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-gray-700">Last Visit</span>
                                </div>
                                <span class="font-semibold text-gray-800">
                                    <?php 
                                    $lastRecord = $health_records[0] ?? null;
                                    echo $lastRecord ? date('M j', strtotime($lastRecord['record_date'])) : 'None';
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Health Records & Appointments -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Recent Health Records -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-800">Health Records</h2>
                                <a href="add_health_record.php?pet_id=<?php echo $pet['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-700 text-sm font-medium">Add Record</a>
                            </div>
                        </div>
                        
                        <div class="divide-y divide-gray-200">
                            <?php if (empty($health_records)): ?>
                            <div class="p-6 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No health records</h3>
                                <p class="mt-1 text-sm text-gray-500">Start by adding your pet's first health record.</p>
                            </div>
                            <?php else: ?>
                                <?php foreach (array_slice($health_records, 0, 5) as $record): ?>
                                <div class="p-6">
                                    <div class="flex items-start space-x-4">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($record['record_type']); ?></h3>
                                                <span class="text-sm text-gray-500"><?php echo date('M j, Y', strtotime($record['record_date'])); ?></span>
                                            </div>
                                            <p class="mt-1 text-sm text-gray-600"><?php echo htmlspecialchars($record['description']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($health_records) > 5): ?>
                                <div class="p-6 bg-gray-50 text-center">
                                    <span class="text-sm text-gray-600">And <?php echo count($health_records) - 5; ?> more records...</span>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Appointments -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-800">Appointments</h2>
                                <a href="schedule_appointment.php?pet_id=<?php echo $pet['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-700 text-sm font-medium">Schedule New</a>
                            </div>
                        </div>
                        
                        <div class="divide-y divide-gray-200">
                            <?php if (empty($appointments)): ?>
                            <div class="p-6 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No appointments</h3>
                                <p class="mt-1 text-sm text-gray-500">Schedule your pet's first appointment.</p>
                            </div>
                            <?php else: ?>
                                <?php foreach (array_slice($appointments, 0, 5) as $appointment): ?>
                                <div class="p-6">
                                    <div class="flex items-start space-x-4">
                                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($appointment['vet_name'] ?? 'Veterinarian'); ?>
                                                </h3>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    <?php 
                                                    echo match($appointment['status']) {
                                                        'confirmed' => 'bg-green-100 text-green-800',
                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                        'cancelled' => 'bg-red-100 text-red-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    };
                                                    ?>">
                                                    <?php echo ucfirst($appointment['status']); ?>
                                                </span>
                                            </div>
                                            <p class="mt-1 text-sm text-gray-600">
                                                <?php echo date('M j, Y g:i A', strtotime($appointment['appointment_date'])); ?>
                                            </p>
                                            <?php if (isset($appointment['purpose']) && $appointment['purpose']): ?>
                                                <p class="mt-1 text-sm text-gray-500"><?php echo htmlspecialchars($appointment['purpose']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($appointments) > 5): ?>
                                <div class="p-6 bg-gray-50 text-center">
                                    <span class="text-sm text-gray-600">And <?php echo count($appointments) - 5; ?> more appointments...</span>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
