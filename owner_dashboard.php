<?php
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pet_owner') {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// Get user information
$user_result = $conn->query("SELECT *, CONCAT(first_name, ' ', last_name) as full_name FROM users WHERE id = $user_id");
if (!$user_result) {
    die("Error fetching user data: " . $conn->error);
}
$user = $user_result->fetch_assoc();

// Get pets
$pets_result = $conn->query("SELECT * FROM pets WHERE owner_id = $user_id");
if (!$pets_result) {
    die("Error fetching pets data: " . $conn->error);
}
$pets = $pets_result->fetch_all(MYSQLI_ASSOC);

// Get recent appointments
$appointments_result = $conn->query("
    SELECT a.*, p.name as pet_name, CONCAT(u.first_name, ' ', u.last_name) as vet_name 
    FROM appointments a 
    JOIN pets p ON a.pet_id = p.id 
    LEFT JOIN users u ON a.veterinarian_id = u.id 
    WHERE p.owner_id = $user_id 
    ORDER BY a.appointment_date DESC 
    LIMIT 5
");
if (!$appointments_result) {
    die("Error fetching appointments data: " . $conn->error);
}
$recent_appointments = $appointments_result->fetch_all(MYSQLI_ASSOC);

// Get stats
$total_pets = count($pets);
$upcoming_appointments = $conn->query("
    SELECT COUNT(*) as count 
    FROM appointments a 
    JOIN pets p ON a.pet_id = p.id 
    WHERE p.owner_id = $user_id 
    AND a.appointment_date >= CURDATE() 
    AND a.status = 'confirmed'
")->fetch_assoc()['count'];

$health_records_count = $conn->query("
    SELECT COUNT(*) as count 
    FROM health_records hr 
    JOIN pets p ON hr.pet_id = p.id 
    WHERE p.owner_id = $user_id
")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Owner Dashboard - Pet Health Tracker</title>
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
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Pet Owner Dashboard</h1>
            <p class="text-gray-600">Manage your pets' health and appointments</p>
        </div>

        <!-- Statistics Cards -->
        <div class="dashboard-stats mb-8">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="stat-title">Total Pets</div>
                        <div class="stat-value"><?php echo $total_pets; ?></div>
                    </div>
                    <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="stat-title">Upcoming Appointments</div>
                        <div class="stat-value"><?php echo $upcoming_appointments; ?></div>
                    </div>
                    <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="stat-title">Health Records</div>
                        <div class="stat-value"><?php echo $health_records_count; ?></div>
                    </div>
                    <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- My Pets Section -->
            <div class="card">
                <div class="card-header">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-800">My Pets</h2>
                        <a href="add_pet.php" class="btn btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Pet
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($pets)): ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">No pets registered yet</h3>
                            <p class="text-gray-500 mb-4">Add your first pet to get started with health tracking</p>
                            <a href="add_pet.php" class="btn btn-primary">Add Your First Pet</a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($pets as $pet): ?>
                                <div class="pet-card p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-blue-500 rounded-full flex items-center justify-center">
                                                <span class="text-white font-bold"><?php echo strtoupper(substr($pet['name'], 0, 1)); ?></span>
                                            </div>
                                            <div>
                                                <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($pet['name']); ?></h3>
                                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($pet['species']); ?> • <?php echo htmlspecialchars($pet['breed'] ?? 'Mixed breed'); ?></p>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="pet_profile.php?pet_id=<?php echo $pet['id']; ?>" 
                                               class="text-sm text-blue-600 hover:text-blue-500" title="View Pet Profile">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <a href="view_health_records.php?pet_id=<?php echo $pet['id']; ?>" 
                                               class="text-sm text-purple-600 hover:text-purple-500" title="View Health Records">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </a>
                                            <a href="schedule_appointment.php?pet_id=<?php echo $pet['id']; ?>" 
                                               class="text-sm text-green-600 hover:text-green-500" title="Schedule Appointment">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Appointments Section -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-xl font-bold text-gray-800">Recent Appointments</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_appointments)): ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">No appointments yet</h3>
                            <p class="text-gray-500">Schedule your first appointment with a veterinarian</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($recent_appointments as $appointment): ?>
                                <div class="border-l-4 border-blue-400 pl-4 py-2">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium text-gray-800"><?php echo htmlspecialchars($appointment['pet_name']); ?></h4>
                                            <p class="text-sm text-gray-600">
                                                <?php echo date('M j, Y g:i A', strtotime($appointment['appointment_date'])); ?>
                                                <?php if ($appointment['vet_name']): ?>
                                                    • Dr. <?php echo htmlspecialchars($appointment['vet_name']); ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <span class="appointment-status status-<?php echo $appointment['status']; ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="manage_appointments.php" class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                        View all appointments →
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
            <div class="grid md:grid-cols-3 gap-4">
                <a href="add_pet.php" class="card hover:shadow-lg transition-shadow">
                    <div class="card-body text-center">
                        <svg class="mx-auto w-8 h-8 text-blue-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <h3 class="font-medium text-gray-800">Add New Pet</h3>
                        <p class="text-sm text-gray-600 mt-1">Register a new pet</p>
                    </div>
                </a>
                
                <a href="my_pets.php" class="card hover:shadow-lg transition-shadow">
                    <div class="card-body text-center">
                        <svg class="mx-auto w-8 h-8 text-purple-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <h3 class="font-medium text-gray-800">View All Pets</h3>
                        <p class="text-sm text-gray-600 mt-1">Manage your pets</p>
                    </div>
                </a>
                
                <a href="schedule_appointment.php" class="card hover:shadow-lg transition-shadow">
                    <div class="card-body text-center">
                        <svg class="mx-auto w-8 h-8 text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="font-medium text-gray-800">Schedule Appointment</h3>
                        <p class="text-sm text-gray-600 mt-1">Book a vet visit</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>