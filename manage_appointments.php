<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Handle veterinarian actions
if ($user_role === 'veterinarian' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';
    $cancellation_reason = $_POST['cancellation_reason'] ?? null;
    
    // Update appointment
    $stmt = $conn->prepare("UPDATE appointments SET status = ?, notes = ?, cancellation_reason = ?, updated_at = NOW() WHERE id = ? AND veterinarian_id = ?");
    $stmt->bind_param("sssii", $status, $notes, $cancellation_reason, $appointment_id, $user_id);
    $stmt->execute();
    
    $_SESSION['success_message'] = "Appointment updated successfully!";
    header("Location: manage_appointments.php");
    exit;
}

// Handle pet owner cancellations
if ($user_role === 'pet_owner' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment'])) {
    $appointment_id = $_POST['appointment_id'];
    $cancellation_reason = $_POST['cancellation_reason'] ?? 'Cancelled by owner';
    
    // Verify appointment belongs to owner and can be cancelled
    $check_stmt = $conn->prepare("SELECT id FROM appointments a JOIN pets p ON a.pet_id = p.id WHERE a.id = ? AND p.owner_id = ? AND a.appointment_date > NOW() AND a.status NOT IN ('completed', 'cancelled')");
    $check_stmt->bind_param("ii", $appointment_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled', cancellation_reason = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $cancellation_reason, $appointment_id);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Appointment cancelled successfully!";
    } else {
        $_SESSION['error_message'] = "Unable to cancel this appointment.";
    }
    
    header("Location: manage_appointments.php");
    exit;
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query conditions based on user role
$where_conditions = [];
$params = [];
$param_types = "";

if ($user_role === 'veterinarian') {
    $where_conditions[] = "a.veterinarian_id = ?";
    $params[] = $user_id;
    $param_types .= "i";
} elseif ($user_role === 'pet_owner') {
    $where_conditions[] = "p.owner_id = ?";
    $params[] = $user_id;
    $param_types .= "i";
}

if ($status_filter !== 'all') {
    $where_conditions[] = "a.status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}

if ($date_filter === 'today') {
    $where_conditions[] = "DATE(a.appointment_date) = CURDATE()";
} elseif ($date_filter === 'tomorrow') {
    $where_conditions[] = "DATE(a.appointment_date) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
} elseif ($date_filter === 'week') {
    $where_conditions[] = "a.appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
} elseif ($date_filter === 'past') {
    $where_conditions[] = "a.appointment_date < CURDATE()";
}

if ($search) {
    if ($user_role === 'veterinarian') {
        $where_conditions[] = "(p.name LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR a.purpose LIKE ?)";
    } else {
        $where_conditions[] = "(p.name LIKE ? OR CONCAT(vet.first_name, ' ', vet.last_name) LIKE ? OR a.purpose LIKE ?)";
    }
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= "sss";
}

$where_clause = implode(" AND ", $where_conditions);

// Get appointments with related data - different queries for different roles
if ($user_role === 'veterinarian') {
    $query = "
        SELECT a.*, 
               p.name as pet_name, p.species, p.breed, p.photo_url,
               u.first_name as owner_first_name, u.last_name as owner_last_name, u.email as owner_email, u.phone as owner_phone,
               CASE 
                   WHEN a.appointment_date < NOW() AND a.status = 'confirmed' THEN 'overdue'
                   ELSE a.status
               END as display_status
        FROM appointments a
        JOIN pets p ON a.pet_id = p.id
        JOIN users u ON a.owner_id = u.id
        WHERE $where_clause
        ORDER BY a.appointment_date ASC
    ";
} else {
    $query = "
        SELECT a.*, 
               p.name as pet_name, p.species, p.breed, p.photo_url,
               vet.first_name as vet_first_name, vet.last_name as vet_last_name, vet.email as vet_email,
               CASE 
                   WHEN a.appointment_date < NOW() AND a.status = 'confirmed' THEN 'overdue'
                   ELSE a.status
               END as display_status
        FROM appointments a
        JOIN pets p ON a.pet_id = p.id
        LEFT JOIN users vet ON a.veterinarian_id = vet.id
        WHERE $where_clause
        ORDER BY a.appointment_date DESC
    ";
}

$stmt = $conn->prepare($query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get appointment statistics - different queries for different roles
if ($user_role === 'veterinarian') {
    $stats_query = "
        SELECT 
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress
        FROM appointments 
        WHERE veterinarian_id = ?
    ";
    $stmt = $conn->prepare($stats_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
} else {
    $stats_query = "
        SELECT 
            SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN a.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN a.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress
        FROM appointments a
        JOIN pets p ON a.pet_id = p.id
        WHERE p.owner_id = ?
    ";
    $stmt = $conn->prepare($stats_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
}
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get detailed appointment if ID is provided
$appointment_detail = null;
$appointment_id = $_GET['id'] ?? null;

if ($appointment_id) {
    if ($user_role === 'veterinarian') {
        $detail_query = "
            SELECT a.*, 
                   p.name as pet_name, p.species, p.breed, p.dob, p.weight, p.color, p.allergies, p.medications, p.photo_url,
                   u.first_name as owner_first_name, u.last_name as owner_last_name, u.email as owner_email, u.phone as owner_phone, u.address as owner_address
            FROM appointments a
            JOIN pets p ON a.pet_id = p.id
            JOIN users u ON a.owner_id = u.id
            WHERE a.id = ? AND a.veterinarian_id = ?
        ";
        $stmt = $conn->prepare($detail_query);
        $stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
    } else {
        $detail_query = "
            SELECT a.*, 
                   p.name as pet_name, p.species, p.breed, p.dob, p.weight, p.color, p.allergies, p.medications, p.photo_url,
                   vet.first_name as vet_first_name, vet.last_name as vet_last_name, vet.email as vet_email
            FROM appointments a
            JOIN pets p ON a.pet_id = p.id
            LEFT JOIN users vet ON a.veterinarian_id = vet.id
            WHERE a.id = ? AND p.owner_id = ?
        ";
        $stmt = $conn->prepare($detail_query);
        $stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
    }
    
    $stmt->execute();
    $appointment_detail = $stmt->get_result()->fetch_assoc();
    
    if (!$appointment_detail) {
        $_SESSION['error_message'] = "Appointment not found.";
        header("Location: manage_appointments.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - Pet Health Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .status-badge {
            @apply px-3 py-1 rounded-full text-xs font-medium;
        }
        .status-pending { @apply bg-yellow-100 text-yellow-800; }
        .status-confirmed { @apply bg-blue-100 text-blue-800; }
        .status-in_progress { @apply bg-purple-100 text-purple-800; }
        .status-completed { @apply bg-green-100 text-green-800; }
        .status-cancelled { @apply bg-red-100 text-red-800; }
        .status-no_show { @apply bg-gray-100 text-gray-800; }
        .status-overdue { @apply bg-red-100 text-red-800; }
        .appointment-card:hover { @apply shadow-lg transform -translate-y-1; }
        .appointment-card { @apply transition-all duration-200; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-800">Pet Health Tracker</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Dr. <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
                    <a href="vet_dashboard.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">← Dashboard</a>
                    <a href="logout.php" class="text-red-600 hover:text-red-700 text-sm font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-center">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 flex items-center">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <?php if ($appointment_detail): ?>
            <!-- Detailed Appointment View -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden mb-8">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-500 to-green-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold text-white">Appointment Details</h1>
                        <span class="status-badge status-<?php echo $appointment_detail['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $appointment_detail['status'])); ?>
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Appointment Information -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h3z"></path>
                                </svg>
                                Appointment Information
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Date & Time</label>
                                    <p class="text-gray-800 font-medium"><?php echo date('F j, Y g:i A', strtotime($appointment_detail['appointment_date'])); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Duration</label>
                                    <p class="text-gray-800"><?php echo $appointment_detail['duration_minutes']; ?> minutes</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Type</label>
                                    <p class="text-gray-800"><?php echo ucfirst(str_replace('_', ' ', $appointment_detail['appointment_type'])); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Purpose</label>
                                    <p class="text-gray-800"><?php echo htmlspecialchars($appointment_detail['purpose'] ?: 'Not specified'); ?></p>
                                </div>
                                <?php if ($appointment_detail['cost']): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Cost</label>
                                    <p class="text-gray-800">$<?php echo number_format($appointment_detail['cost'], 2); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <?php if ($user_role === 'veterinarian'): ?>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Pet Owner Information
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Owner Name</label>
                                    <p class="text-gray-800"><?php echo htmlspecialchars($appointment_detail['owner_first_name'] . ' ' . $appointment_detail['owner_last_name']); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Email</label>
                                    <p class="text-gray-800"><?php echo htmlspecialchars($appointment_detail['owner_email']); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Phone</label>
                                    <p class="text-gray-800"><?php echo htmlspecialchars($appointment_detail['owner_phone'] ?: 'Not provided'); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Address</label>
                                    <p class="text-gray-800"><?php echo htmlspecialchars($appointment_detail['owner_address'] ?: 'Not provided'); ?></p>
                                </div>
                            </div>
                            <?php else: ?>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                Veterinarian Information
                            </h3>
                            <div class="space-y-3">
                                <?php if (isset($appointment_detail['vet_first_name']) && $appointment_detail['vet_first_name']): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Veterinarian</label>
                                    <p class="text-gray-800">Dr. <?php echo htmlspecialchars($appointment_detail['vet_first_name'] . ' ' . $appointment_detail['vet_last_name']); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Email</label>
                                    <p class="text-gray-800"><?php echo htmlspecialchars($appointment_detail['vet_email']); ?></p>
                                </div>
                                <?php else: ?>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <p class="text-yellow-800">Veterinarian will be assigned before your appointment.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Pet Information -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                        <h3 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            Pet Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-blue-600">Name</label>
                                <p class="text-blue-800 font-medium"><?php echo htmlspecialchars($appointment_detail['pet_name']); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-blue-600">Species & Breed</label>
                                <p class="text-blue-800"><?php echo htmlspecialchars($appointment_detail['species'] . ' - ' . $appointment_detail['breed']); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-blue-600">Age</label>
                                <p class="text-blue-800">
                                    <?php 
                                    if ($appointment_detail['dob']) {
                                        $dob = new DateTime($appointment_detail['dob']);
                                        $now = new DateTime();
                                        $age = $now->diff($dob);
                                        echo $age->y . ' years, ' . $age->m . ' months';
                                    } else {
                                        echo 'Not provided';
                                    }
                                    ?>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-blue-600">Weight</label>
                                <p class="text-blue-800"><?php echo $appointment_detail['weight'] ? $appointment_detail['weight'] . ' kg' : 'Not recorded'; ?></p>
                            </div>
                        </div>
                        <?php if ($appointment_detail['allergies'] || $appointment_detail['medications']): ?>
                        <div class="mt-4 pt-4 border-t border-blue-200">
                            <?php if ($appointment_detail['allergies']): ?>
                            <div class="mb-2">
                                <label class="block text-sm font-medium text-blue-600">Allergies</label>
                                <p class="text-blue-800"><?php echo htmlspecialchars($appointment_detail['allergies']); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if ($appointment_detail['medications']): ?>
                            <div>
                                <label class="block text-sm font-medium text-blue-600">Current Medications</label>
                                <p class="text-blue-800"><?php echo htmlspecialchars($appointment_detail['medications']); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Notes -->
                    <?php if ($appointment_detail['notes']): ?>
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Notes</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($appointment_detail['notes']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Cancellation Reason -->
                    <?php if ($appointment_detail['cancellation_reason']): ?>
                    <div class="mb-8 bg-red-50 border border-red-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-red-800 mb-2">Cancellation Reason</h3>
                        <p class="text-red-700"><?php echo htmlspecialchars($appointment_detail['cancellation_reason']); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Action Form -->
                    <?php if ($user_role === 'veterinarian' && in_array($appointment_detail['status'], ['pending', 'confirmed'])): ?>
                    <form method="POST" class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                        <input type="hidden" name="appointment_id" value="<?php echo $appointment_detail['id']; ?>">
                        
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Update Appointment</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" id="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                    <option value="">Select Status</option>
                                    <option value="confirmed" <?php echo $appointment_detail['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirm</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Complete</option>
                                    <option value="cancelled">Cancel</option>
                                    <option value="no_show">No Show</option>
                                </select>
                            </div>
                            
                            <div id="cancellation-reason-container" style="display: none;">
                                <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-2">Cancellation Reason</label>
                                <select name="cancellation_reason" id="cancellation_reason" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Select Reason</option>
                                    <option value="Pet illness">Pet illness</option>
                                    <option value="Owner emergency">Owner emergency</option>
                                    <option value="Weather conditions">Weather conditions</option>
                                    <option value="Veterinarian emergency">Veterinarian emergency</option>
                                    <option value="Scheduling conflict">Scheduling conflict</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" id="notes" rows="4" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Add any notes about this appointment..."><?php echo htmlspecialchars($appointment_detail['notes']); ?></textarea>
                        </div>
                        
                        <div class="flex items-center justify-end space-x-4 mt-6">
                            <a href="manage_appointments.php" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                Back to List
                            </a>
                            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-green-500 text-white rounded-lg hover:from-blue-600 hover:to-green-600 transition-all duration-200 font-medium">
                                Update Appointment
                            </button>
                        </div>
                    </form>
                    <?php elseif ($user_role === 'pet_owner' && in_array($appointment_detail['status'], ['pending', 'confirmed'])): ?>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Appointment Actions</h3>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="appointment_id" value="<?php echo $appointment_detail['id']; ?>">
                            <input type="hidden" name="status" value="cancelled">
                            <input type="hidden" name="cancellation_reason" value="Owner request">
                            <div class="flex items-center justify-end space-x-4">
                                <a href="manage_appointments.php" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    Back to List
                                </a>
                                <button type="submit" class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all duration-200 font-medium"
                                        onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                    Cancel Appointment
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-end">
                            <a href="manage_appointments.php" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                Back to List
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Appointments List -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-500 to-green-500 px-6 py-4">
                    <h1 class="text-2xl font-bold text-white">
                        <?php echo $user_role === 'veterinarian' ? 'Manage Appointments' : 'My Appointments'; ?>
                    </h1>
                </div>

                <!-- Statistics Cards -->
                <div class="p-6 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-yellow-600">Pending</p>
                                    <p class="text-2xl font-bold text-yellow-800"><?php echo $stats['pending']; ?></p>
                                </div>
                                <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-blue-600">Confirmed</p>
                                    <p class="text-2xl font-bold text-blue-800"><?php echo $stats['confirmed']; ?></p>
                                </div>
                                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-green-600">Completed</p>
                                    <p class="text-2xl font-bold text-green-800"><?php echo $stats['completed']; ?></p>
                                </div>
                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-purple-600">Today</p>
                                    <p class="text-2xl font-bold text-purple-800"><?php echo $stats['today']; ?></p>
                                </div>
                                <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h3z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" class="flex flex-wrap items-center gap-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="filter-status" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <select name="date" id="filter-date" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                <option value="all" <?php echo $date_filter === 'all' ? 'selected' : ''; ?>>All Dates</option>
                                <option value="today" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Today</option>
                                <option value="tomorrow" <?php echo $date_filter === 'tomorrow' ? 'selected' : ''; ?>>Tomorrow</option>
                                <option value="week" <?php echo $date_filter === 'week' ? 'selected' : ''; ?>>Next 7 Days</option>
                                <option value="past" <?php echo $date_filter === 'past' ? 'selected' : ''; ?>>Past Appointments</option>
                            </select>
                        </div>
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Pet name, owner, purpose..." 
                                   class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200 text-sm">
                                Filter
                            </button>
                            <a href="manage_appointments.php" class="ml-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200 text-sm">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Appointments List -->
                <div class="p-6">
                    <?php if (empty($appointments)): ?>
                        <div class="text-center py-12">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h3z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Appointments Found</h3>
                            <p class="text-gray-500">No appointments match your current filters.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($appointments as $apt): ?>
                            <div class="appointment-card bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg cursor-pointer" 
                                 onclick="window.location.href='?id=<?php echo $apt['id']; ?>'">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-green-500 rounded-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($apt['pet_name']); ?></h3>
                                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($apt['species'] . ' - ' . $apt['breed']); ?></p>
                                            <?php if ($user_role === 'veterinarian'): ?>
                                                <p class="text-sm text-gray-600">Owner: <?php echo htmlspecialchars($apt['owner_first_name'] . ' ' . $apt['owner_last_name']); ?></p>
                                            <?php else: ?>
                                                <?php if (isset($apt['vet_first_name']) && $apt['vet_first_name']): ?>
                                                    <p class="text-sm text-gray-600">Veterinarian: Dr. <?php echo htmlspecialchars($apt['vet_first_name'] . ' ' . $apt['vet_last_name']); ?></p>
                                                <?php else: ?>
                                                    <p class="text-sm text-yellow-600">Veterinarian: To be assigned</p>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-semibold text-gray-900"><?php echo date('M j, Y', strtotime($apt['appointment_date'])); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo date('g:i A', strtotime($apt['appointment_date'])); ?></p>
                                        <span class="status-badge status-<?php echo $apt['display_status']; ?> mt-2 inline-block">
                                            <?php echo ucfirst(str_replace('_', ' ', $apt['display_status'])); ?>
                                        </span>
                                        <?php if ($user_role === 'pet_owner' && in_array($apt['status'], ['pending', 'confirmed'])): ?>
                                            <div class="mt-2">
                                                <form method="POST" style="display: inline;" onclick="event.stopPropagation();">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $apt['id']; ?>">
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <input type="hidden" name="cancellation_reason" value="Owner request">
                                                    <button type="submit" class="text-xs px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition-colors"
                                                            onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                                        Cancel
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($apt['purpose']): ?>
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <p class="text-sm text-gray-600"><strong>Purpose:</strong> <?php echo htmlspecialchars($apt['purpose']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Show/hide cancellation reason field based on status selection
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status');
            const cancellationContainer = document.getElementById('cancellation-reason-container');
            const cancellationSelect = document.getElementById('cancellation_reason');
            
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    if (this.value === 'cancelled') {
                        cancellationContainer.style.display = 'block';
                        cancellationSelect.required = true;
                    } else {
                        cancellationContainer.style.display = 'none';
                        cancellationSelect.required = false;
                        cancellationSelect.value = '';
                    }
                });
            }
        });
    </script>
</body>
</html>