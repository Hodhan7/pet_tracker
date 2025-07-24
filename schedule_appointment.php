<?php
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pet_owner') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$selected_pet_id = $_GET['pet_id'] ?? null;

// Get user's pets
$pets_query = "SELECT * FROM pets WHERE owner_id = ? ORDER BY name";
$pets_stmt = $conn->prepare($pets_query);
$pets_stmt->bind_param("i", $user_id);
$pets_stmt->execute();
$pets = $pets_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get veterinarians
$vets = $conn->query("SELECT id, first_name, last_name, email FROM users WHERE role = 'veterinarian' ORDER BY first_name, last_name")->fetch_all(MYSQLI_ASSOC);

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = $_POST['pet_id'];
    $veterinarian_id = $_POST['veterinarian_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_type = $_POST['appointment_type'] ?? 'checkup';
    $purpose = $_POST['purpose'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Validate that the pet belongs to the owner
    $pet_check = $conn->prepare("SELECT id FROM pets WHERE id = ? AND owner_id = ?");
    $pet_check->bind_param("ii", $pet_id, $user_id);
    $pet_check->execute();
    $pet_result = $pet_check->get_result();
    
    if ($pet_result->num_rows > 0) {
        $stmt = $conn->prepare("INSERT INTO appointments (pet_id, veterinarian_id, owner_id, appointment_date, appointment_type, purpose, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iiissss", $pet_id, $veterinarian_id, $user_id, $appointment_date, $appointment_type, $purpose, $notes);
        
        if ($stmt->execute()) {
            $success_message = "Appointment scheduled successfully!";
        } else {
            $error_message = "Error scheduling appointment. Please try again.";
        }
    } else {
        $error_message = "Invalid pet selection.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Appointment - Pet Health Tracker</title>
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
        <div class="max-w-3xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <nav class="text-sm text-gray-500 mb-2">
                    <a href="owner_dashboard.php" class="hover:text-blue-600">Dashboard</a> 
                    <span class="mx-2">></span> 
                    <span>Schedule Appointment</span>
                </nav>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Schedule Appointment</h1>
                <p class="text-gray-600">Book a veterinary appointment for your pet</p>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success_message): ?>
                <div class="card border-l-4 border-l-green-400 mb-6">
                    <div class="card-body">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <p class="text-green-800"><?php echo htmlspecialchars($success_message); ?></p>
                        </div>
                        <div class="mt-4">
                            <a href="owner_dashboard.php" class="btn btn-primary">Return to Dashboard</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="card border-l-4 border-l-red-400 mb-6">
                    <div class="card-body">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-red-800"><?php echo htmlspecialchars($error_message); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Check if user has pets -->
            <?php if (empty($pets)): ?>
                <div class="card">
                    <div class="card-body text-center py-12">
                        <svg class="mx-auto w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <h3 class="text-xl font-medium text-gray-700 mb-2">No pets registered</h3>
                        <p class="text-gray-500 mb-6">You need to add a pet before scheduling an appointment</p>
                        <a href="add_pet.php" class="btn btn-primary">Add Your First Pet</a>
                    </div>
                </div>
            <?php else: ?>
            <!-- Schedule Form -->
            <div class="card">
                <div class="card-header">
                    <h2 class="text-xl font-bold text-gray-800">Appointment Details</h2>
                </div>
                <div class="card-body">
                    <form action="schedule_appointment.php" method="POST" class="space-y-6">
                        <!-- Pet Selection -->
                        <div>
                            <label for="pet_id" class="block text-sm font-medium text-gray-700 mb-2">Select Pet *</label>
                            <select id="pet_id" name="pet_id" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="">Choose a pet</option>
                                <?php foreach ($pets as $pet): ?>
                                    <option value="<?php echo $pet['id']; ?>" <?php echo ($selected_pet_id == $pet['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pet['name']); ?> - <?php echo htmlspecialchars($pet['species']); ?> (<?php echo htmlspecialchars($pet['breed'] ?? 'Mixed'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Veterinarian Selection -->
                        <div>
                            <label for="veterinarian_id" class="block text-sm font-medium text-gray-700 mb-2">Select Veterinarian *</label>
                            <select id="veterinarian_id" name="veterinarian_id" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="">Choose a veterinarian</option>
                                <?php foreach ($vets as $vet): ?>
                                    <option value="<?php echo $vet['id']; ?>">
                                        Dr. <?php echo htmlspecialchars($vet['first_name'] . ' ' . $vet['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Date Selection -->
                            <div>
                                <label for="appointment_date" class="block text-sm font-medium text-gray-700 mb-2">Appointment Date *</label>
                                <input type="date" id="appointment_date" name="appointment_date" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <!-- Time Selection -->
                            <div>
                                <label for="appointment_time" class="block text-sm font-medium text-gray-700 mb-2">Preferred Time *</label>
                                <select id="appointment_time" name="appointment_time" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                    <option value="">Select time</option>
                                    <option value="09:00">9:00 AM</option>
                                    <option value="09:30">9:30 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="10:30">10:30 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="11:30">11:30 AM</option>
                                    <option value="13:00">1:00 PM</option>
                                    <option value="13:30">1:30 PM</option>
                                    <option value="14:00">2:00 PM</option>
                                    <option value="14:30">2:30 PM</option>
                                    <option value="15:00">3:00 PM</option>
                                    <option value="15:30">3:30 PM</option>
                                    <option value="16:00">4:00 PM</option>
                                    <option value="16:30">4:30 PM</option>
                                </select>
                            </div>
                        </div>

                        <!-- Appointment Type -->
                        <div>
                            <label for="appointment_type" class="block text-sm font-medium text-gray-700 mb-2">Appointment Type</label>
                            <select id="appointment_type" name="appointment_type" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="checkup">General Checkup</option>
                                <option value="vaccination">Vaccination</option>
                                <option value="emergency">Emergency</option>
                                <option value="surgery">Surgery Consultation</option>
                                <option value="consultation">Consultation</option>
                                <option value="follow_up">Follow-up</option>
                            </select>
                        </div>

                        <!-- Purpose of Visit -->
                        <div>
                            <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">Purpose of Visit</label>
                            <input type="text" id="purpose" name="purpose" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                   placeholder="Brief description of the visit purpose">
                        </div>

                        <!-- Additional Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                            <textarea id="notes" name="notes" rows="4" 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                      placeholder="Please describe your pet's symptoms or any additional information for the veterinarian"></textarea>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <a href="owner_dashboard.php" class="btn btn-outline">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Schedule Appointment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Information Card -->
            <div class="card border-l-4 border-l-blue-400 mt-8">
                <div class="card-body">
                    <h3 class="text-lg font-semibold text-blue-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Appointment Information
                    </h3>
                    <ul class="text-sm text-blue-700 space-y-2">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            <span>Appointments are available Monday to Friday, 9:00 AM to 5:00 PM</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            <span>Emergency appointments may be available outside regular hours</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            <span>Please arrive 15 minutes early for your appointment</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            <span>Cancellations must be made at least 24 hours in advance</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            <span>Bring your pet's vaccination records and any previous medical documents</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Combine date and time into datetime format for submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const date = document.getElementById('appointment_date').value;
            const time = document.getElementById('appointment_time').value;
            
            if (date && time) {
                const datetime = date + ' ' + time + ':00';
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'appointment_date';
                hiddenInput.value = datetime;
                this.appendChild(hiddenInput);
                
                // Remove the original inputs from form submission
                document.getElementById('appointment_date').disabled = true;
                document.getElementById('appointment_time').disabled = true;
            }
        });

        // Auto-select pet if coming from a specific pet page
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const petId = urlParams.get('pet_id');
            if (petId) {
                const petSelect = document.getElementById('pet_id');
                petSelect.value = petId;
            }
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>