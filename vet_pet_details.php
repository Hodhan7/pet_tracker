<?php
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'veterinarian') {
    header("Location: login.php");
    exit;
}

$pet_id = $_GET['pet_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Get pet information with owner details
$pet_query = "
    SELECT p.*, u.first_name as owner_first_name, u.last_name as owner_last_name, 
           u.email as owner_email, u.phone as owner_phone, u.address as owner_address
    FROM pets p 
    JOIN users u ON p.owner_id = u.id 
    WHERE p.id = ?
";
$pet_stmt = $conn->prepare($pet_query);
$pet_stmt->bind_param("i", $pet_id);
$pet_stmt->execute();
$pet_result = $pet_stmt->get_result();

if ($pet_result->num_rows === 0) {
    header("Location: vet_dashboard.php");
    exit;
}
$pet = $pet_result->fetch_assoc();

// Get complete health history
$health_records_query = "
    SELECT hr.*, CONCAT(v.first_name, ' ', v.last_name) as vet_name
    FROM health_records hr
    LEFT JOIN users v ON hr.veterinarian_id = v.id
    WHERE hr.pet_id = ?
    ORDER BY hr.record_date DESC, hr.created_at DESC
";
$hr_stmt = $conn->prepare($health_records_query);
$hr_stmt->bind_param("i", $pet_id);
$hr_stmt->execute();
$health_records = $hr_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get vaccination history
$vaccinations_query = "
    SELECT v.*, CONCAT(u.first_name, ' ', u.last_name) as vet_name
    FROM vaccinations v
    LEFT JOIN users u ON v.veterinarian_id = u.id
    WHERE v.pet_id = ?
    ORDER BY v.administered_date DESC
";
$vacc_stmt = $conn->prepare($vaccinations_query);
$vacc_stmt->bind_param("i", $pet_id);
$vacc_stmt->execute();
$vaccinations = $vacc_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get active medications
$medications_query = "
    SELECT m.*, CONCAT(u.first_name, ' ', u.last_name) as prescribed_by_name
    FROM medications m
    LEFT JOIN users u ON m.prescribed_by = u.id
    WHERE m.pet_id = ? AND m.is_active = 1
    ORDER BY m.start_date DESC
";
$med_stmt = $conn->prepare($medications_query);
$med_stmt->bind_param("i", $pet_id);
$med_stmt->execute();
$medications = $med_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get appointment history
$appointments_query = "
    SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as vet_name
    FROM appointments a
    LEFT JOIN users u ON a.veterinarian_id = u.id
    WHERE a.pet_id = ?
    ORDER BY a.appointment_date DESC
";
$apt_stmt = $conn->prepare($appointments_query);
$apt_stmt->bind_param("i", $pet_id);
$apt_stmt->execute();
$appointments = $apt_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle appointment scheduling
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_appointment'])) {
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $appointment_type = $_POST['appointment_type'];
    $purpose = $_POST['purpose'];
    $duration = $_POST['duration'] ?? 30;
    
    // Combine date and time
    $full_datetime = $appointment_date . ' ' . $appointment_time;
    
    $insert_query = "
        INSERT INTO appointments (pet_id, owner_id, veterinarian_id, appointment_date, appointment_type, purpose, duration_minutes, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed')
    ";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iiisssi", $pet_id, $pet['owner_id'], $user_id, $full_datetime, $appointment_type, $purpose, $duration);
    
    if ($insert_stmt->execute()) {
        $success_message = "Appointment scheduled successfully!";
        // Refresh appointments data
        $apt_stmt->execute();
        $appointments = $apt_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $error_message = "Error scheduling appointment: " . $conn->error;
    }
}

// Handle health record submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_health_record'])) {
    $record_date = $_POST['record_date'];
    $record_type = $_POST['record_type'];
    $description = $_POST['description'];
    $diagnosis = $_POST['diagnosis'] ?? '';
    $treatment = $_POST['treatment'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $weight = !empty($_POST['weight']) ? (float)$_POST['weight'] : null;
    $temperature = !empty($_POST['temperature']) ? (float)$_POST['temperature'] : null;
    $follow_up_date = !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : null;
    
    // Generate a title based on record type and date
    $title = ucfirst(str_replace('_', ' ', $record_type)) . ' - ' . date('M j, Y', strtotime($record_date));
    
    // Set follow_up_required based on whether follow_up_date is provided
    $follow_up_required = !empty($follow_up_date) ? 1 : 0;
    
    $insert_hr_query = "
        INSERT INTO health_records (pet_id, veterinarian_id, record_date, record_type, title, description, diagnosis, treatment, follow_up_required, follow_up_date, weight_at_visit, temperature, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";
    $insert_hr_stmt = $conn->prepare($insert_hr_query);
    
    if (!$insert_hr_stmt) {
        $error_message = "Error preparing statement: " . $conn->error;
    } else {
        $insert_hr_stmt->bind_param("iisssssissdd", $pet_id, $user_id, $record_date, $record_type, $title, $description, $diagnosis, $treatment, $follow_up_required, $follow_up_date, $weight, $temperature);
        
        if ($insert_hr_stmt->execute()) {
            $success_message = "Health record added successfully!";
            // Refresh health records data
            $hr_stmt->execute();
            $health_records = $hr_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $error_message = "Error adding health record: " . $insert_hr_stmt->error . " (MySQL Error: " . $conn->error . ")";
        }
        $insert_hr_stmt->close();
    }
}

// Handle vaccination record submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vaccination'])) {
    $vaccine_name = $_POST['vaccine_name'];
    $administered_date = $_POST['administered_date'];
    $batch_number = $_POST['batch_number'] ?? '';
    $manufacturer = $_POST['manufacturer'] ?? '';
    $next_due_date = $_POST['next_due_date'] ?? null;
    $notes = $_POST['vaccination_notes'] ?? '';
    
    $insert_vacc_query = "
        INSERT INTO vaccinations (pet_id, veterinarian_id, vaccine_name, administered_date, batch_number, manufacturer, next_due_date, notes, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";
    $insert_vacc_stmt = $conn->prepare($insert_vacc_query);
    $insert_vacc_stmt->bind_param("iissssss", $pet_id, $user_id, $vaccine_name, $administered_date, $batch_number, $manufacturer, $next_due_date, $notes);
    
    if ($insert_vacc_stmt->execute()) {
        $success_message = "Vaccination record added successfully!";
        // Refresh vaccination data
        $vacc_stmt->execute();
        $vaccinations = $vacc_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $error_message = "Error adding vaccination record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pet['name']); ?> - Veterinary Records</title>
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

    <div class="container mx-auto px-4 py-8">
        <!-- Success/Error Messages -->
        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Pet Header -->
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($pet['name']); ?></h1>
                        <p class="text-gray-600"><?php echo htmlspecialchars($pet['species']); ?> • <?php echo htmlspecialchars($pet['breed'] ?? 'Mixed'); ?></p>
                        <p class="text-sm text-gray-500">
                            Owner: <?php echo htmlspecialchars($pet['owner_first_name'] . ' ' . $pet['owner_last_name']); ?>
                            • <?php echo htmlspecialchars($pet['owner_email']); ?>
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="scheduleAppointmentBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        Schedule Next Appointment
                    </button>
                    <button onclick="openHealthRecordModal()" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                        Add Health Record
                    </button>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-4 gap-8">
            <!-- Left Column - Pet Info & Owner Details -->
            <div class="space-y-6">
                <!-- Pet Information -->
                <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Pet Information</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Species:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($pet['species']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Breed:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($pet['breed'] ?? 'Mixed'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Gender:</span>
                            <span class="font-medium"><?php echo ucfirst($pet['gender']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date of Birth:</span>
                            <span class="font-medium">
                                <?php 
                                if ($pet['dob']) {
                                    echo date('M j, Y', strtotime($pet['dob']));
                                } else {
                                    echo 'Unknown';
                                }
                                ?>
                            </span>
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
                        <?php if ($pet['weight']): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Weight:</span>
                            <span class="font-medium"><?php echo $pet['weight']; ?> kg</span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Microchip:</span>
                            <span class="font-medium"><?php echo $pet['microchip_id'] ?: 'Not chipped'; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Spayed/Neutered:</span>
                            <span class="font-medium"><?php echo $pet['is_spayed_neutered'] ? 'Yes' : 'No'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Owner Information -->
                <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Owner Information</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Name:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($pet['owner_first_name'] . ' ' . $pet['owner_last_name']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Email:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($pet['owner_email']); ?></span>
                        </div>
                        <?php if ($pet['owner_phone']): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phone:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($pet['owner_phone']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($pet['owner_address']): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Address:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($pet['owner_address']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Health Alerts -->
                <?php if ($pet['allergies'] || $pet['special_needs'] || $pet['medications']): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-yellow-800 mb-2 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        Health Alerts
                    </h3>
                    <div class="space-y-2 text-xs text-yellow-700">
                        <?php if ($pet['allergies']): ?>
                            <div><strong>Allergies:</strong> <?php echo htmlspecialchars($pet['allergies']); ?></div>
                        <?php endif; ?>
                        <?php if ($pet['special_needs']): ?>
                            <div><strong>Special Needs:</strong> <?php echo htmlspecialchars($pet['special_needs']); ?></div>
                        <?php endif; ?>
                        <?php if ($pet['medications']): ?>
                            <div><strong>Current Medications:</strong> <?php echo htmlspecialchars($pet['medications']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Main Content - Tabbed Interface -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-lg shadow-lg border border-gray-200">
                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-200">
                        <nav class="flex space-x-8 px-6" aria-label="Tabs">
                            <button onclick="showTab('health-records')" id="tab-health-records" class="tab-button active py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600">
                                Health Records
                            </button>
                            <button onclick="showTab('vaccinations')" id="tab-vaccinations" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Vaccinations
                            </button>
                            <button onclick="showTab('medications')" id="tab-medications" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Medications
                            </button>
                            <button onclick="showTab('appointments')" id="tab-appointments" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Appointments
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Health Records Tab -->
                        <div id="content-health-records" class="tab-content">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-800">Health Records</h3>
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm text-gray-500"><?php echo count($health_records); ?> records</span>
                                    <button onclick="openHealthRecordModal()" class="btn btn-primary text-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Add Health Record
                                    </button>
                                </div>
                            </div>
                            
                            <?php if (empty($health_records)): ?>
                                <div class="text-center py-8">
                                    <p class="text-gray-500">No health records found for this pet.</p>
                                    <a href="add_health_record.php?pet_id=<?php echo $pet['id']; ?>" 
                                       class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        Add First Record
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($health_records as $record): ?>
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-3">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($record['title']); ?></h4>
                                                        <p class="text-sm text-gray-600"><?php echo date('M j, Y', strtotime($record['record_date'])); ?></p>
                                                    </div>
                                                </div>
                                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-full text-xs">
                                                    <?php echo ucfirst(str_replace('_', ' ', $record['record_type'])); ?>
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
                                            
                                            <?php if ($record['weight_at_visit'] || $record['temperature']): ?>
                                                <div class="mb-3 bg-blue-50 p-3 rounded-lg">
                                                    <strong class="text-blue-800">Vital Signs:</strong>
                                                    <div class="grid grid-cols-2 gap-2 mt-1 text-sm">
                                                        <?php if ($record['weight_at_visit']): ?>
                                                            <div class="text-blue-700">Weight: <?php echo $record['weight_at_visit']; ?> lbs</div>
                                                        <?php endif; ?>
                                                        <?php if ($record['temperature']): ?>
                                                            <div class="text-blue-700">Temperature: <?php echo $record['temperature']; ?>°F</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($record['medications_prescribed']): ?>
                                                <div class="mb-3 bg-green-50 p-3 rounded-lg">
                                                    <strong class="text-green-800">Medications Prescribed:</strong>
                                                    <p class="text-green-700 mt-1"><?php echo htmlspecialchars($record['medications_prescribed']); ?></p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($record['follow_up_required'] && $record['follow_up_date']): ?>
                                                <div class="bg-yellow-50 p-3 rounded-lg">
                                                    <strong class="text-yellow-800">Follow-up Required:</strong>
                                                    <p class="text-yellow-700 mt-1"><?php echo date('M j, Y', strtotime($record['follow_up_date'])); ?></p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($record['vet_name']): ?>
                                                <div class="mt-3 pt-3 border-t border-gray-100 text-sm text-gray-500">
                                                    Treated by: Dr. <?php echo htmlspecialchars($record['vet_name']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Vaccinations Tab -->
                        <div id="content-vaccinations" class="tab-content hidden">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-800">Vaccination History</h3>
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm text-gray-500"><?php echo count($vaccinations); ?> vaccinations</span>
                                    <button onclick="openVaccinationModal()" class="btn btn-primary text-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Add Vaccination
                                    </button>
                                </div>
                            </div>
                            
                            <?php if (empty($vaccinations)): ?>
                                <div class="text-center py-8">
                                    <p class="text-gray-500">No vaccination records found.</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($vaccinations as $vacc): ?>
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($vacc['vaccine_name']); ?></h4>
                                                    <p class="text-sm text-gray-600">
                                                        <?php echo date('M j, Y', strtotime($vacc['administered_date'])); ?>
                                                        <?php if ($vacc['vet_name']): ?>
                                                            • Dr. <?php echo htmlspecialchars($vacc['vet_name']); ?>
                                                        <?php endif; ?>
                                                    </p>
                                                    <?php if ($vacc['vaccine_type']): ?>
                                                        <p class="text-xs text-gray-500">Type: <?php echo htmlspecialchars($vacc['vaccine_type']); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($vacc['next_due_date'] && strtotime($vacc['next_due_date']) > time()): ?>
                                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs">
                                                        Next: <?php echo date('M j, Y', strtotime($vacc['next_due_date'])); ?>
                                                    </span>
                                                <?php elseif ($vacc['next_due_date'] && strtotime($vacc['next_due_date']) <= time()): ?>
                                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs">
                                                        Due: <?php echo date('M j, Y', strtotime($vacc['next_due_date'])); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Medications Tab -->
                        <div id="content-medications" class="tab-content hidden">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-800">Active Medications</h3>
                                <span class="text-sm text-gray-500"><?php echo count($medications); ?> active</span>
                            </div>
                            
                            <?php if (empty($medications)): ?>
                                <div class="text-center py-8">
                                    <p class="text-gray-500">No active medications.</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($medications as $med): ?>
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($med['name']); ?></h4>
                                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">Active</span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <span class="text-gray-600">Dosage:</span>
                                                    <span class="font-medium ml-2"><?php echo htmlspecialchars($med['dosage'] ?? 'Not specified'); ?></span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-600">Frequency:</span>
                                                    <span class="font-medium ml-2"><?php echo htmlspecialchars($med['frequency'] ?? 'Not specified'); ?></span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-600">Start Date:</span>
                                                    <span class="font-medium ml-2"><?php echo date('M j, Y', strtotime($med['start_date'])); ?></span>
                                                </div>
                                                <?php if ($med['end_date']): ?>
                                                <div>
                                                    <span class="text-gray-600">End Date:</span>
                                                    <span class="font-medium ml-2"><?php echo date('M j, Y', strtotime($med['end_date'])); ?></span>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($med['purpose']): ?>
                                                <div class="mt-2 text-sm">
                                                    <span class="text-gray-600">Purpose:</span>
                                                    <span class="font-medium ml-2"><?php echo htmlspecialchars($med['purpose']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($med['prescribed_by_name']): ?>
                                                <div class="mt-2 pt-2 border-t border-gray-100 text-xs text-gray-500">
                                                    Prescribed by: Dr. <?php echo htmlspecialchars($med['prescribed_by_name']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Appointments Tab -->
                        <div id="content-appointments" class="tab-content hidden">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-800">Appointment History</h3>
                                <span class="text-sm text-gray-500"><?php echo count($appointments); ?> appointments</span>
                            </div>
                            
                            <?php if (empty($appointments)): ?>
                                <div class="text-center py-8">
                                    <p class="text-gray-500">No appointment history found.</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($appointments as $apt): ?>
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <div>
                                                    <h4 class="font-semibold text-gray-800"><?php echo ucfirst(str_replace('_', ' ', $apt['appointment_type'])); ?></h4>
                                                    <p class="text-sm text-gray-600"><?php echo date('M j, Y g:i A', strtotime($apt['appointment_date'])); ?></p>
                                                </div>
                                                <span class="status-badge status-<?php echo $apt['status']; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $apt['status'])); ?>
                                                </span>
                                            </div>
                                            <?php if ($apt['purpose']): ?>
                                                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($apt['purpose']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($apt['vet_name']): ?>
                                                <p class="text-xs text-gray-500">Veterinarian: Dr. <?php echo htmlspecialchars($apt['vet_name']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Appointment Modal -->
    <div id="scheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Schedule Next Appointment</h3>
                </div>
                <form method="POST" class="p-6">
                    <input type="hidden" name="schedule_appointment" value="1">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="appointment_date" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                            <input type="date" name="appointment_date" id="appointment_date" required
                                   min="<?php echo date('Y-m-d'); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="appointment_time" class="block text-sm font-medium text-gray-700 mb-2">Time</label>
                            <input type="time" name="appointment_time" id="appointment_time" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="appointment_type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                            <select name="appointment_type" id="appointment_type" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="checkup">Regular Checkup</option>
                                <option value="follow_up">Follow-up</option>
                                <option value="vaccination">Vaccination</option>
                                <option value="emergency">Emergency</option>
                                <option value="surgery">Surgery</option>
                                <option value="consultation">Consultation</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                            <select name="duration" id="duration"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="15">15 minutes</option>
                                <option value="30" selected>30 minutes</option>
                                <option value="45">45 minutes</option>
                                <option value="60">1 hour</option>
                                <option value="90">1.5 hours</option>
                                <option value="120">2 hours</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">Purpose</label>
                            <textarea name="purpose" id="purpose" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Brief description of the appointment purpose..."></textarea>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-4 mt-6">
                        <button type="button" onclick="hideScheduleModal()" 
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Schedule Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function showTab(tabName) {
            // Hide all tab content
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.add('hidden'));
            
            // Remove active class from all tab buttons
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(button => {
                button.classList.remove('active', 'border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected tab content
            document.getElementById(`content-${tabName}`).classList.remove('hidden');
            
            // Add active class to selected tab button
            const activeButton = document.getElementById(`tab-${tabName}`);
            activeButton.classList.add('active', 'border-blue-500', 'text-blue-600');
            activeButton.classList.remove('border-transparent', 'text-gray-500');
        }

        function showScheduleModal() {
            document.getElementById('scheduleModal').classList.remove('hidden');
        }

        function hideScheduleModal() {
            document.getElementById('scheduleModal').classList.add('hidden');
        }

        // Attach event listener for Schedule Next Appointment button
        document.addEventListener('DOMContentLoaded', function() {
            var scheduleBtn = document.getElementById('scheduleAppointmentBtn');
            if (scheduleBtn) {
                scheduleBtn.addEventListener('click', showScheduleModal);
            }
        });

        // Close modal when clicking outside
        document.getElementById('scheduleModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideScheduleModal();
            }
        });

        // Health Record Modal Functions
        function openHealthRecordModal() {
            document.getElementById('healthRecordModal').classList.remove('hidden');
        }

        function closeHealthRecordModal() {
            document.getElementById('healthRecordModal').classList.add('hidden');
        }

        // Auto-open health record modal if add_record parameter is present
        <?php if (isset($_GET['add_record']) && $_GET['add_record'] == '1'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            openHealthRecordModal();
        });
        <?php endif; ?>

        // Vaccination Modal Functions
        function openVaccinationModal() {
            document.getElementById('vaccinationModal').classList.remove('hidden');
        }

        function closeVaccinationModal() {
            document.getElementById('vaccinationModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        document.getElementById('healthRecordModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeHealthRecordModal();
            }
        });

        document.getElementById('vaccinationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeVaccinationModal();
            }
        });
    </script>

    <!-- Health Record Modal -->
    <div id="healthRecordModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between pb-3">
                    <h3 class="text-lg font-bold text-gray-900">Add Health Record</h3>
                    <button onclick="closeHealthRecordModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="add_health_record" value="1">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="record_date" class="block text-sm font-medium text-gray-700 mb-1">Record Date</label>
                            <input type="date" id="record_date" name="record_date" value="<?php echo date('Y-m-d'); ?>" 
                                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        
                        <div>
                            <label for="record_type" class="block text-sm font-medium text-gray-700 mb-1">Record Type</label>
                            <select id="record_type" name="record_type" 
                                    class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">Select Type</option>
                                <option value="checkup">Regular Checkup</option>
                                <option value="illness">Illness</option>
                                <option value="injury">Injury</option>
                                <option value="surgery">Surgery</option>
                                <option value="emergency">Emergency</option>
                                <option value="consultation">Consultation</option>
                                <option value="follow_up">Follow-up</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="3" 
                                  class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                                  placeholder="Brief description of the visit/condition" required></textarea>
                    </div>
                    
                    <div>
                        <label for="diagnosis" class="block text-sm font-medium text-gray-700 mb-1">Diagnosis</label>
                        <textarea id="diagnosis" name="diagnosis" rows="2" 
                                  class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                                  placeholder="Medical diagnosis (optional)"></textarea>
                    </div>
                    
                    <div>
                        <label for="treatment" class="block text-sm font-medium text-gray-700 mb-1">Treatment</label>
                        <textarea id="treatment" name="treatment" rows="2" 
                                  class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                                  placeholder="Treatment provided (optional)"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Weight (lbs)</label>
                            <input type="number" id="weight" name="weight" step="0.1" 
                                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="0.0">
                        </div>
                        
                        <div>
                            <label for="temperature" class="block text-sm font-medium text-gray-700 mb-1">Temperature (°F)</label>
                            <input type="number" id="temperature" name="temperature" step="0.1" 
                                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="0.0">
                        </div>
                        
                        <div>
                            <label for="heart_rate" class="block text-sm font-medium text-gray-700 mb-1">Heart Rate (bpm)</label>
                            <input type="number" id="heart_rate" name="heart_rate" 
                                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="0">
                        </div>
                    </div>
                    
                    <div>
                        <label for="follow_up_date" class="block text-sm font-medium text-gray-700 mb-1">Follow-up Date (optional)</label>
                        <input type="date" id="follow_up_date" name="follow_up_date" 
                               class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                        <textarea id="notes" name="notes" rows="2" 
                                  class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                                  placeholder="Any additional notes or observations"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeHealthRecordModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                            Add Health Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Vaccination Modal -->
    <div id="vaccinationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between pb-3">
                    <h3 class="text-lg font-bold text-gray-900">Add Vaccination Record</h3>
                    <button onclick="closeVaccinationModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="add_vaccination" value="1">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="vaccine_name" class="block text-sm font-medium text-gray-700 mb-1">Vaccine Name</label>
                            <select id="vaccine_name" name="vaccine_name" 
                                    class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">Select Vaccine</option>
                                <option value="Rabies">Rabies</option>
                                <option value="DHPP">DHPP (Distemper, Hepatitis, Parvovirus, Parainfluenza)</option>
                                <option value="Bordetella">Bordetella</option>
                                <option value="Lyme Disease">Lyme Disease</option>
                                <option value="FVRCP">FVRCP (Feline Viral Rhinotracheitis, Calicivirus, Panleukopenia)</option>
                                <option value="FeLV">FeLV (Feline Leukemia)</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="administered_date" class="block text-sm font-medium text-gray-700 mb-1">Administered Date</label>
                            <input type="date" id="administered_date" name="administered_date" value="<?php echo date('Y-m-d'); ?>" 
                                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-1">Batch Number</label>
                            <input type="text" id="batch_number" name="batch_number" 
                                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="Vaccine batch number">
                        </div>
                        
                        <div>
                            <label for="manufacturer" class="block text-sm font-medium text-gray-700 mb-1">Manufacturer</label>
                            <input type="text" id="manufacturer" name="manufacturer" 
                                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="Vaccine manufacturer">
                        </div>
                    </div>
                    
                    <div>
                        <label for="next_due_date" class="block text-sm font-medium text-gray-700 mb-1">Next Due Date</label>
                        <input type="date" id="next_due_date" name="next_due_date" 
                               class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="vaccination_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea id="vaccination_notes" name="vaccination_notes" rows="3" 
                                  class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                                  placeholder="Any additional notes about the vaccination"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeVaccinationModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                            Add Vaccination
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
