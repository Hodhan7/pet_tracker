<?php
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'veterinarian') {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$status_filter = isset($_GET['status_filter']) && $_GET['status_filter'] !== '' ? $_GET['status_filter'] : null;
$query = "SELECT a.*, p.name AS pet_name, p.id as pet_id, u.first_name as owner_first_name, u.last_name as owner_last_name FROM appointments a JOIN pets p ON a.pet_id = p.id JOIN users u ON p.owner_id = u.id WHERE a.veterinarian_id = $user_id";
if ($status_filter) {
    $safe_status = $conn->real_escape_string($status_filter);
    $query .= " AND a.status = '$safe_status'";
}
$query .= " ORDER BY a.appointment_date DESC";
$result = $conn->query($query);
$appointments = $result->fetch_all(MYSQLI_ASSOC);

// Get pets this veterinarian has seen (unique pets from appointments and health records)
$pets_query = "
    SELECT DISTINCT p.id, p.name, p.species, p.breed, 
           u.first_name as owner_first_name, u.last_name as owner_last_name,
           MAX(a.appointment_date) as last_appointment,
           COUNT(DISTINCT a.id) as appointment_count,
           COUNT(DISTINCT hr.id) as health_record_count
    FROM pets p 
    JOIN users u ON p.owner_id = u.id
    LEFT JOIN appointments a ON p.id = a.pet_id AND a.veterinarian_id = $user_id
    LEFT JOIN health_records hr ON p.id = hr.pet_id AND hr.veterinarian_id = $user_id
    WHERE (a.id IS NOT NULL OR hr.id IS NOT NULL)
    GROUP BY p.id
    ORDER BY MAX(a.appointment_date) DESC, p.name
";
$pets_result = $conn->query($pets_query);
$vet_pets = $pets_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veterinarian Dashboard - Pet Health Tracker</title>
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
    <script src="js/main.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Page Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Veterinarian Dashboard</h1>
                        <p class="text-gray-600">Manage your appointments and patient care</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                            Veterinarian
                        </div>
                        <div class="relative">
                            <button onclick="toggleQuickActions()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Quick Actions
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="quickActionsMenu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                <div class="py-2">
                                    <div class="px-4 py-2 text-xs text-gray-500 uppercase tracking-wider font-medium">Add Health Records</div>
                                    <?php if (!empty($vet_pets)): ?>
                                        <?php foreach (array_slice($vet_pets, 0, 5) as $pet): ?>
                                            <a href="vet_pet_details.php?pet_id=<?php echo $pet['id']; ?>&add_record=1" 
                                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <div class="w-6 h-6 bg-gradient-to-r from-green-500 to-blue-500 rounded-full flex items-center justify-center mr-3">
                                                    <span class="text-white font-bold text-xs"><?php echo strtoupper(substr($pet['name'], 0, 1)); ?></span>
                                                </div>
                                                <?php echo htmlspecialchars($pet['name']); ?>
                                                <span class="ml-auto text-xs text-gray-500"><?php echo htmlspecialchars($pet['species']); ?></span>
                                            </a>
                                        <?php endforeach; ?>
                                        <?php if (count($vet_pets) > 5): ?>
                                            <div class="border-t border-gray-100 mt-1 pt-1">
                                                <div class="px-4 py-2 text-xs text-gray-500">And <?php echo count($vet_pets) - 5; ?> more patients...</div>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="px-4 py-2 text-sm text-gray-500">No patients yet</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Total Appointments</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo count($appointments); ?></p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Pending</p>
                            <p class="text-2xl font-bold text-yellow-600">
                                <?php echo count(array_filter($appointments, fn($app) => $app['status'] === 'pending')); ?>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Confirmed</p>
                            <p class="text-2xl font-bold text-green-600">
                                <?php echo count(array_filter($appointments, fn($app) => $app['status'] === 'confirmed')); ?>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Today's Appointments</p>
                            <p class="text-2xl font-bold text-purple-600">
                                <?php echo count(array_filter($appointments, fn($app) => date('Y-m-d', strtotime($app['appointment_date'])) === date('Y-m-d'))); ?>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Summary -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Recent Activity</h2>
                    <span class="text-sm text-gray-500">Last 7 days</span>
                </div>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-blue-600 font-medium">Health Records Added</p>
                                <p class="text-2xl font-bold text-blue-800">
                                    <?php 
                                    // Count health records added by this vet in last 7 days
                                    $recent_records_query = "SELECT COUNT(*) as count FROM health_records WHERE veterinarian_id = $user_id AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                                    $recent_records_result = $conn->query($recent_records_query);
                                    $recent_records = $recent_records_result->fetch_assoc();
                                    echo $recent_records['count'];
                                    ?>
                                </p>
                            </div>
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-green-600 font-medium">Appointments This Week</p>
                                <p class="text-2xl font-bold text-green-800">
                                    <?php 
                                    // Count appointments for this week
                                    $week_appointments = count(array_filter($appointments, function($app) {
                                        $appointment_date = strtotime($app['appointment_date']);
                                        $week_start = strtotime('monday this week');
                                        $week_end = strtotime('sunday this week') + 86400; // Add 24 hours to include Sunday
                                        return $appointment_date >= $week_start && $appointment_date < $week_end;
                                    }));
                                    echo $week_appointments;
                                    ?>
                                </p>
                            </div>
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-purple-600 font-medium">Active Patients</p>
                                <p class="text-2xl font-bold text-purple-800"><?php echo count($vet_pets); ?></p>
                            </div>
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointments Management -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-800">Appointments</h2>
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <input type="text" id="appointmentSearch" placeholder="Search appointments..." 
                                       class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <form method="GET" id="statusFilterForm">
                                <select name="status_filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" onchange="document.getElementById('statusFilterForm').submit();">
                                    <option value="" <?php echo empty($_GET['status_filter']) ? 'selected' : ''; ?>>All Status</option>
                                    <option value="pending" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="completed" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-3 px-6 text-xs font-medium text-gray-500 uppercase tracking-wider">Pet & Owner</th>
                                <th class="text-left py-3 px-6 text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="text-left py-3 px-6 text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="text-left py-3 px-6 text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                                <th class="text-left py-3 px-6 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($appointments as $appointment): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-4 px-6">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($appointment['pet_name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Owner: <?php echo htmlspecialchars($appointment['owner_first_name'] . ' ' . $appointment['owner_last_name']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="text-sm text-gray-900">
                                        <?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <?php 
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'confirmed' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-blue-100 text-blue-800',
                                        'cancelled' => 'bg-red-100 text-red-800'
                                    ];
                                    $statusColor = $statusColors[$appointment['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-500">
                                    <?php echo htmlspecialchars($appointment['purpose'] ?? 'General Checkup'); ?>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-2">
                                        <a href="vet_pet_details.php?pet_id=<?php echo $appointment['pet_id']; ?>" 
                                           class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View Pet
                                        </a>
                                        
                                        <?php if ($appointment['status'] === 'confirmed' || $appointment['status'] === 'pending'): ?>
                                        <a href="vet_pet_details.php?pet_id=<?php echo $appointment['pet_id']; ?>&add_record=1" 
                                           class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Add Record
                                        </a>
                                        <?php endif; ?>
                                        
                                        <div class="flex items-center space-x-1">
                                            <form action="manage_appointments.php" method="POST" class="flex items-center space-x-1" onsubmit="return confirmStatusChange(this)">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                <select name="status" class="border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                        onchange="this.form.submit()" <?php echo $appointment['status'] === 'completed' ? 'disabled' : ''; ?>>
                                                    <option value="pending" <?php echo $appointment['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="confirmed" <?php echo $appointment['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                    <option value="completed" <?php echo $appointment['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                    <option value="cancelled" <?php echo $appointment['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (empty($appointments)): ?>
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No appointments</h3>
                    <p class="mt-1 text-sm text-gray-500">No appointments have been scheduled yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- My Patients Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mt-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">My Patients</h2>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" id="patientSearch" placeholder="Search patients..." 
                                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <span class="text-sm text-gray-500"><?php echo count($vet_pets); ?> total patients</span>
                    </div>
                </div>
            </div>
            
            <?php if (empty($vet_pets)): ?>
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No patients yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Patients will appear here after you see them for appointments or add health records.</p>
                </div>
            <?php else: ?>
                <div class="p-6">
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($vet_pets as $pet): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-blue-500 rounded-full flex items-center justify-center">
                                            <span class="text-white font-bold text-sm"><?php echo strtoupper(substr($pet['name'], 0, 1)); ?></span>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($pet['name']); ?></h3>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($pet['species']); ?> • <?php echo htmlspecialchars($pet['breed'] ?? 'Mixed'); ?></p>
                                        </div>
                                    </div>
                                    <?php if ($pet['health_record_count'] > 0): ?>
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                            Active Patient
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">
                                            New Patient
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Owner:</span>
                                        <span class="font-medium"><?php echo htmlspecialchars($pet['owner_first_name'] . ' ' . $pet['owner_last_name']); ?></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Last Visit:</span>
                                        <span class="font-medium <?php echo $pet['last_appointment'] ? 'text-gray-900' : 'text-gray-400'; ?>">
                                            <?php echo $pet['last_appointment'] ? date('M j, Y', strtotime($pet['last_appointment'])) : 'Never'; ?>
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Health Records:</span>
                                        <span class="font-medium"><?php echo $pet['health_record_count']; ?></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Appointments:</span>
                                        <span class="font-medium"><?php echo $pet['appointment_count']; ?></span>
                                    </div>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <a href="vet_pet_details.php?pet_id=<?php echo $pet['id']; ?>" 
                                       class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View Details
                                    </a>
                                    <a href="vet_pet_details.php?pet_id=<?php echo $pet['id']; ?>&add_record=1" 
                                       class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-center px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Add Record
                                    </a>
                                    <button onclick="showScheduleModal(<?php echo $pet['id']; ?>, '<?php echo htmlspecialchars($pet['name']); ?>')"
                                            class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Schedule
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Floating Quick Add Button -->
    <div class="fixed bottom-6 right-6 z-40">
        <div class="relative">
            <button onclick="toggleFloatingMenu()" class="bg-blue-600 hover:bg-blue-700 text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-all duration-200 hover:scale-110">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
            
            <!-- Floating Menu -->
            <div id="floatingMenu" class="hidden absolute bottom-16 right-0 w-64 bg-white rounded-lg shadow-xl border border-gray-200">
                <div class="p-3">
                    <div class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-2">Quick Add Health Record</div>
                    <?php if (!empty($vet_pets)): ?>
                        <div class="max-h-48 overflow-y-auto space-y-1">
                            <?php foreach ($vet_pets as $pet): ?>
                                <a href="vet_pet_details.php?pet_id=<?php echo $pet['id']; ?>&add_record=1" 
                                   class="flex items-center p-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                    <div class="w-6 h-6 bg-gradient-to-r from-green-500 to-blue-500 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-white font-bold text-xs"><?php echo strtoupper(substr($pet['name'], 0, 1)); ?></span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium"><?php echo htmlspecialchars($pet['name']); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($pet['species']); ?> • <?php echo htmlspecialchars($pet['owner_first_name'] . ' ' . $pet['owner_last_name']); ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-gray-500 text-center py-4">No patients available</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Schedule Modal (for scheduling from pets section) -->
    <div id="quickScheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Schedule Appointment</h3>
                    <p class="text-sm text-gray-600" id="selectedPetName"></p>
                </div>
                <form action="vet_pet_details.php" method="GET" class="p-6">
                    <input type="hidden" name="pet_id" id="selectedPetId">
                    <input type="hidden" name="schedule" value="1">
                    
                    <div class="text-center">
                        <p class="text-gray-600 mb-4">This will take you to the pet's details page where you can schedule the appointment.</p>
                        <div class="flex items-center justify-end space-x-4">
                            <button type="button" onclick="hideQuickScheduleModal()" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Go to Pet Details
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

        <?php include 'includes/footer.php'; ?>

    <script>
        function showScheduleModal(petId, petName) {
            document.getElementById('selectedPetId').value = petId;
            document.getElementById('selectedPetName').textContent = 'Pet: ' + petName;
            document.getElementById('quickScheduleModal').classList.remove('hidden');
        }

        function hideQuickScheduleModal() {
            document.getElementById('quickScheduleModal').classList.add('hidden');
        }

        // Quick Actions Menu
        function toggleQuickActions() {
            const menu = document.getElementById('quickActionsMenu');
            menu.classList.toggle('hidden');
        }

        // Close quick actions menu when clicking outside
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('quickActionsMenu');
            const button = e.target.closest('button');
            
            if (!button || !button.onclick || button.onclick.toString().indexOf('toggleQuickActions') === -1) {
                if (!menu.contains(e.target)) {
                    menu.classList.add('hidden');
                }
            }
        });

        // Floating Menu Functions
        function toggleFloatingMenu() {
            const menu = document.getElementById('floatingMenu');
            menu.classList.toggle('hidden');
        }

        // Close floating menu when clicking outside
        document.addEventListener('click', function(e) {
            const floatingMenu = document.getElementById('floatingMenu');
            const floatingButton = e.target.closest('button');
            
            if (!floatingButton || !floatingButton.onclick || floatingButton.onclick.toString().indexOf('toggleFloatingMenu') === -1) {
                if (!floatingMenu.contains(e.target)) {
                    floatingMenu.classList.add('hidden');
                }
            }
        });

        // Close modal when clicking outside
        document.getElementById('quickScheduleModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideQuickScheduleModal();
            }
        });

        // Confirm status changes
        function confirmStatusChange(form) {
            const statusSelect = form.querySelector('select[name="status"]');
            const newStatus = statusSelect.value;
            const currentStatus = statusSelect.querySelector('option[selected]')?.value || '';
            
            if (newStatus === 'completed' && currentStatus !== 'completed') {
                return confirm('Mark this appointment as completed? This action cannot be undone.');
            }
            
            if (newStatus === 'cancelled' && currentStatus !== 'cancelled') {
                return confirm('Cancel this appointment? The pet owner will be notified.');
            }
            
            return true;
        }

        // Auto-refresh page every 5 minutes to keep appointment data current
        setTimeout(() => {
            window.location.reload();
        }, 300000); // 5 minutes

        // Search functionality for appointments
        document.getElementById('appointmentSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const appointmentRows = document.querySelectorAll('tbody tr');
            
            appointmentRows.forEach(row => {
                const petName = row.querySelector('td:first-child .text-sm.font-medium')?.textContent.toLowerCase() || '';
                const ownerName = row.querySelector('td:first-child .text-sm.text-gray-500')?.textContent.toLowerCase() || '';
                const purpose = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || '';
                
                const isVisible = petName.includes(searchTerm) || 
                                ownerName.includes(searchTerm) || 
                                purpose.includes(searchTerm);
                
                row.style.display = isVisible ? '' : 'none';
            });
        });

        // Search functionality for patients
        document.getElementById('patientSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const patientCards = document.querySelectorAll('.grid.md\\:grid-cols-2 > div');
            
            patientCards.forEach(card => {
                const petName = card.querySelector('h3')?.textContent.toLowerCase() || '';
                const petInfo = card.querySelector('.text-xs.text-gray-500')?.textContent.toLowerCase() || '';
                const ownerName = card.querySelector('.space-y-2 .font-medium')?.textContent.toLowerCase() || '';
                
                const isVisible = petName.includes(searchTerm) || 
                                petInfo.includes(searchTerm) || 
                                ownerName.includes(searchTerm);
                
                card.style.display = isVisible ? '' : 'none';
            });
        });

        // Show success message if status was updated
        <?php if (isset($_GET['status_updated']) && $_GET['status_updated'] === 'success'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.createElement('div');
            successAlert.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg z-50';
            successAlert.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Appointment status updated successfully!
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-green-600 hover:text-green-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
            document.body.appendChild(successAlert);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (successAlert.parentElement) {
                    successAlert.remove();
                }
            }, 5000);
        });
        <?php endif; ?>
    </script>
</body>
</html>