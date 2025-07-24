<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$success_message = '';
$error_message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip_code = trim($_POST['zip_code']);
    $bio = trim($_POST['bio']);
    
    // Check if email is already taken by another user
    $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $email_check->bind_param("si", $email, $user_id);
    $email_check->execute();
    $email_result = $email_check->get_result();
    
    if ($email_result->num_rows > 0) {
        $error_message = "This email is already in use by another account.";
    } else {
        // Update user profile
        $update_query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, city = ?, state = ?, zip_code = ?, bio = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sssssssssi", $first_name, $last_name, $email, $phone, $address, $city, $state, $zip_code, $bio, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            $_SESSION['user_email'] = $email;
            $success_message = "Profile updated successfully!";
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
    }
}

// Handle veterinarian profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_vet_profile']) && $user_role === 'veterinarian') {
    $license_number = trim($_POST['license_number']);
    $clinic_name = trim($_POST['clinic_name']);
    $clinic_address = trim($_POST['clinic_address']);
    $clinic_phone = trim($_POST['clinic_phone']);
    $specializations = $_POST['specializations'] ? json_encode($_POST['specializations']) : '[]';
    $years_experience = intval($_POST['years_experience']);
    $education = trim($_POST['education']);
    $certifications = trim($_POST['certifications']);
    $consultation_fee = floatval($_POST['consultation_fee']);
    $is_accepting_patients = isset($_POST['is_accepting_patients']) ? 1 : 0;
    
    // Working hours
    $working_hours = [
        'monday' => $_POST['monday_hours'] ?? '',
        'tuesday' => $_POST['tuesday_hours'] ?? '',
        'wednesday' => $_POST['wednesday_hours'] ?? '',
        'thursday' => $_POST['thursday_hours'] ?? '',
        'friday' => $_POST['friday_hours'] ?? '',
        'saturday' => $_POST['saturday_hours'] ?? '',
        'sunday' => $_POST['sunday_hours'] ?? ''
    ];
    $working_hours_json = json_encode($working_hours);
    
    $vet_update_query = "UPDATE veterinarian_profiles SET 
        license_number = ?, clinic_name = ?, clinic_address = ?, clinic_phone = ?, 
        specializations = ?, years_experience = ?, education = ?, certifications = ?, 
        consultation_fee = ?, working_hours = ?, is_accepting_patients = ? 
        WHERE user_id = ?";
    
    $vet_stmt = $conn->prepare($vet_update_query);
    $vet_stmt->bind_param("sssssissdsii", 
        $license_number, $clinic_name, $clinic_address, $clinic_phone, 
        $specializations, $years_experience, $education, $certifications, 
        $consultation_fee, $working_hours_json, $is_accepting_patients, $user_id
    );
    
    if ($vet_stmt->execute()) {
        $success_message = "Veterinarian profile updated successfully!";
    } else {
        $error_message = "Error updating veterinarian profile: " . $conn->error;
    }
}

// Get user data
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Get veterinarian profile if applicable
$vet_profile = null;
if ($user_role === 'veterinarian') {
    $vet_query = "SELECT * FROM veterinarian_profiles WHERE user_id = ?";
    $vet_stmt = $conn->prepare($vet_query);
    $vet_stmt->bind_param("i", $user_id);
    $vet_stmt->execute();
    $vet_result = $vet_stmt->get_result();
    $vet_profile = $vet_result->fetch_assoc();
}

// Get user statistics
$stats = [];
if ($user_role === 'pet_owner') {
    $pets_count = $conn->prepare("SELECT COUNT(*) as count FROM pets WHERE owner_id = ?");
    $pets_count->bind_param("i", $user_id);
    $pets_count->execute();
    $stats['pets'] = $pets_count->get_result()->fetch_assoc()['count'];
    
    $appointments_count = $conn->prepare("SELECT COUNT(*) as count FROM appointments a JOIN pets p ON a.pet_id = p.id WHERE p.owner_id = ?");
    $appointments_count->bind_param("i", $user_id);
    $appointments_count->execute();
    $stats['appointments'] = $appointments_count->get_result()->fetch_assoc()['count'];
} elseif ($user_role === 'veterinarian') {
    $patients_count = $conn->prepare("SELECT COUNT(DISTINCT p.id) as count FROM pets p JOIN health_records hr ON p.id = hr.pet_id WHERE hr.veterinarian_id = ?");
    $patients_count->bind_param("i", $user_id);
    $patients_count->execute();
    $stats['patients'] = $patients_count->get_result()->fetch_assoc()['count'];
    
    $records_count = $conn->prepare("SELECT COUNT(*) as count FROM health_records WHERE veterinarian_id = ?");
    $records_count->bind_param("i", $user_id);
    $records_count->execute();
    $stats['records'] = $records_count->get_result()->fetch_assoc()['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Pet Health Tracker</title>
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
<body class="bg-gray-50 font-sans">
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">My Profile</h1>
            <p class="text-gray-600">Manage your account information and preferences</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success_message): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-center">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 flex items-center">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-4 gap-8">
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <!-- Profile Photo -->
                    <div class="text-center mb-6">
                        <div class="w-24 h-24 bg-gradient-to-r from-blue-500 to-green-500 rounded-full mx-auto flex items-center justify-center mb-4">
                            <span class="text-white text-2xl font-bold">
                                <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                            </span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                        <p class="text-gray-500 capitalize"><?php echo str_replace('_', ' ', $user['role']); ?></p>
                        <p class="text-sm text-gray-400 mt-1">Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                    </div>

                    <!-- Quick Stats -->
                    <div class="space-y-4">
                        <?php if ($user_role === 'pet_owner'): ?>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Pets</span>
                                <span class="font-semibold text-blue-600"><?php echo $stats['pets']; ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Appointments</span>
                                <span class="font-semibold text-green-600"><?php echo $stats['appointments']; ?></span>
                            </div>
                        <?php elseif ($user_role === 'veterinarian'): ?>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Patients</span>
                                <span class="font-semibold text-blue-600"><?php echo $stats['patients']; ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Records</span>
                                <span class="font-semibold text-green-600"><?php echo $stats['records']; ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Last Login</span>
                            <span class="text-sm text-gray-500"><?php echo $user['last_login'] ? date('M j', strtotime($user['last_login'])) : 'Never'; ?></span>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <a href="settings.php" class="flex items-center text-gray-600 hover:text-blue-600 transition-colors duration-200 mb-3">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Account Settings
                        </a>
                        <?php if ($user_role === 'pet_owner'): ?>
                            <a href="owner_dashboard.php" class="flex items-center text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-6 4h4"></path>
                                </svg>
                                Dashboard
                            </a>
                        <?php elseif ($user_role === 'veterinarian'): ?>
                            <a href="vet_dashboard.php" class="flex items-center text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-6 4h4"></path>
                                </svg>
                                Dashboard
                            </a>
                        <?php elseif ($user_role === 'admin'): ?>
                            <a href="admin_dashboard.php" class="flex items-center text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-6 4h4"></path>
                                </svg>
                                Admin Dashboard
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-8">
                <!-- Personal Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Personal Information</h2>
                        <p class="text-gray-600 text-sm mt-1">Update your personal details and contact information</p>
                    </div>
                    
                    <form method="POST" class="p-6">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="grid md:grid-cols-3 gap-6 mt-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                <input type="text" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">State</label>
                                <input type="text" name="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ZIP Code</label>
                                <input type="text" name="zip_code" value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                            <textarea name="bio" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Tell us a little about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Veterinarian Profile (Only for Veterinarians) -->
                <?php if ($user_role === 'veterinarian' && $vet_profile): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Professional Information</h2>
                        <p class="text-gray-600 text-sm mt-1">Manage your veterinary practice details</p>
                    </div>
                    
                    <form method="POST" class="p-6">
                        <input type="hidden" name="update_vet_profile" value="1">
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">License Number</label>
                                <input type="text" name="license_number" value="<?php echo htmlspecialchars($vet_profile['license_number'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Years of Experience</label>
                                <input type="number" name="years_experience" value="<?php echo htmlspecialchars($vet_profile['years_experience'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Clinic Name</label>
                            <input type="text" name="clinic_name" value="<?php echo htmlspecialchars($vet_profile['clinic_name'] ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Clinic Address</label>
                            <input type="text" name="clinic_address" value="<?php echo htmlspecialchars($vet_profile['clinic_address'] ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="grid md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Clinic Phone</label>
                                <input type="tel" name="clinic_phone" value="<?php echo htmlspecialchars($vet_profile['clinic_phone'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Consultation Fee ($)</label>
                                <input type="number" step="0.01" name="consultation_fee" value="<?php echo htmlspecialchars($vet_profile['consultation_fee'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Specializations</label>
                            <div class="grid md:grid-cols-3 gap-4">
                                <?php 
                                $all_specializations = ['Internal Medicine', 'Surgery', 'Dermatology', 'Cardiology', 'Oncology', 'Orthopedics', 'Dentistry', 'Emergency Medicine', 'Exotic Animals'];
                                $current_specializations = $vet_profile['specializations'] ? json_decode($vet_profile['specializations'], true) : [];
                                foreach ($all_specializations as $spec): 
                                ?>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="specializations[]" value="<?php echo htmlspecialchars($spec); ?>" 
                                               <?php echo in_array($spec, $current_specializations) ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700"><?php echo htmlspecialchars($spec); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Education</label>
                            <textarea name="education" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Your educational background..."><?php echo htmlspecialchars($vet_profile['education'] ?? ''); ?></textarea>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Certifications</label>
                            <textarea name="certifications" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Professional certifications and awards..."><?php echo htmlspecialchars($vet_profile['certifications'] ?? ''); ?></textarea>
                        </div>

                        <!-- Working Hours -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-4">Working Hours</label>
                            <?php 
                            $working_hours = $vet_profile['working_hours'] ? json_decode($vet_profile['working_hours'], true) : [];
                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            $day_labels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            ?>
                            <div class="grid md:grid-cols-2 gap-4">
                                <?php for ($i = 0; $i < count($days); $i++): ?>
                                    <div class="flex items-center space-x-3">
                                        <span class="w-20 text-sm text-gray-700"><?php echo $day_labels[$i]; ?>:</span>
                                        <input type="text" name="<?php echo $days[$i]; ?>_hours" 
                                               value="<?php echo htmlspecialchars($working_hours[$days[$i]] ?? ''); ?>" 
                                               placeholder="e.g., 9:00 AM - 5:00 PM"
                                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_accepting_patients" <?php echo ($vet_profile['is_accepting_patients'] ?? 0) ? 'checked' : ''; ?>
                                       class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Currently accepting new patients</span>
                            </label>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Update Professional Profile
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
