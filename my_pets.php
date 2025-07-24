<?php
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pet_owner') {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// Get user information
$user_result = $conn->query("SELECT *, CONCAT(first_name, ' ', last_name) as full_name FROM users WHERE id = $user_id");
$user = $user_result->fetch_assoc();

// Get all pets with health summary
$pets_query = "
    SELECT p.*, 
           COUNT(DISTINCT hr.id) as health_records_count,
           COUNT(DISTINCT a.id) as appointments_count,
           COUNT(DISTINCT CASE WHEN a.appointment_date >= CURDATE() AND a.status = 'confirmed' THEN a.id END) as upcoming_appointments,
           MAX(hr.record_date) as last_health_record,
           MAX(a.appointment_date) as last_appointment
    FROM pets p 
    LEFT JOIN health_records hr ON p.id = hr.pet_id 
    LEFT JOIN appointments a ON p.id = a.pet_id 
    WHERE p.owner_id = $user_id 
    GROUP BY p.id
    ORDER BY p.name
";
$pets_result = $conn->query($pets_query);
$pets = $pets_result->fetch_all(MYSQLI_ASSOC);

// Get recent health alerts/medications for each pet
$health_alerts = [];
// Health alerts functionality can be re-implemented when medication tracking is added

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Pets - Pet Health Tracker</title>
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
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include 'includes/header.php'; ?>

    <div class="container py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">My Pets</h1>
                    <p class="text-gray-600">Manage and view all your pets' information</p>
                </div>
                <a href="add_pet.php" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Pet
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid md:grid-cols-4 gap-4 mb-8">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="stat-title">Total Pets</div>
                        <div class="stat-value"><?php echo count($pets); ?></div>
                    </div>
                    <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="stat-title">Dogs</div>
                        <div class="stat-value"><?php echo count(array_filter($pets, function($p) { return strtolower($p['species']) === 'dog'; })); ?></div>
                    </div>
                    <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="stat-title">Cats</div>
                        <div class="stat-value"><?php echo count(array_filter($pets, function($p) { return strtolower($p['species']) === 'cat'; })); ?></div>
                    </div>
                    <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="stat-title">Upcoming Visits</div>
                        <div class="stat-value"><?php echo array_sum(array_column($pets, 'upcoming_appointments')); ?></div>
                    </div>
                    <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pets Grid -->
        <?php if (empty($pets)): ?>
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="mx-auto w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <h3 class="text-xl font-medium text-gray-700 mb-2">No pets registered yet</h3>
                    <p class="text-gray-500 mb-6">Add your first pet to start tracking their health</p>
                    <a href="add_pet.php" class="btn btn-primary">Add Your First Pet</a>
                </div>
            </div>
        <?php else: ?>
            <div class="grid lg:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php foreach ($pets as $pet): ?>
                    <div class="card hover:shadow-lg transition-shadow">
                        <div class="card-body">
                            <!-- Pet Header -->
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full flex items-center justify-center mr-4">
                                    <span class="text-white font-bold text-lg"><?php echo strtoupper(substr($pet['name'], 0, 1)); ?></span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($pet['name']); ?></h3>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($pet['species']); ?> • <?php echo htmlspecialchars($pet['breed'] ?? 'Mixed'); ?></p>
                                </div>
                            </div>

                            <!-- Pet Info -->
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
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
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Weight:</span>
                                    <span class="font-medium"><?php echo $pet['weight'] ? $pet['weight'] . ' lbs' : 'Not recorded'; ?></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Microchip:</span>
                                    <span class="font-medium"><?php echo $pet['microchip_id'] ?: 'Not chipped'; ?></span>
                                </div>
                            </div>

                            <!-- Health Summary -->
                            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Health Summary</h4>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <span class="text-gray-600">Health Records:</span>
                                        <span class="font-medium ml-1"><?php echo $pet['health_records_count']; ?></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Appointments:</span>
                                        <span class="font-medium ml-1"><?php echo $pet['appointments_count']; ?></span>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-gray-600">Last Visit:</span>
                                        <span class="font-medium ml-1">
                                            <?php echo $pet['last_appointment'] ? date('M j, Y', strtotime($pet['last_appointment'])) : 'Never'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Health Alerts -->
                            <?php if (!empty($health_alerts[$pet['id']]) || $pet['allergies'] || $pet['special_needs']): ?>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                                    <h4 class="text-sm font-semibold text-yellow-800 mb-2 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        Health Alerts
                                    </h4>
                                    <div class="space-y-1 text-xs text-yellow-700">
                                        <?php if ($pet['allergies']): ?>
                                            <div><strong>Allergies:</strong> <?php echo htmlspecialchars($pet['allergies']); ?></div>
                                        <?php endif; ?>
                                        <?php if ($pet['special_needs']): ?>
                                            <div><strong>Special Needs:</strong> <?php echo htmlspecialchars($pet['special_needs']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($health_alerts[$pet['id']])): ?>
                                            <?php foreach (array_slice($health_alerts[$pet['id']], 0, 2) as $alert): ?>
                                                <div><strong>Active Medication:</strong> <?php echo htmlspecialchars($alert['medication_name']); ?></div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="grid grid-cols-3 gap-2">
                                <a href="pet_profile.php?id=<?php echo $pet['id']; ?>" 
                                   class="btn btn-outline text-xs py-2 px-3 text-center">
                                    <svg class="w-3 h-3 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Profile
                                </a>
                                <a href="view_health_records.php?pet_id=<?php echo $pet['id']; ?>" 
                                   class="btn btn-outline text-xs py-2 px-3 text-center">
                                    <svg class="w-3 h-3 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Health
                                </a>
                                <a href="schedule_appointment.php?pet_id=<?php echo $pet['id']; ?>" 
                                   class="btn btn-secondary text-xs py-2 px-3 text-center">
                                    <svg class="w-3 h-3 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Book
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
