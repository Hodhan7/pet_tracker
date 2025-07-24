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

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current password hash
    $user_query = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $user_query->bind_param("i", $user_id);
    $user_query->execute();
    $user_result = $user_query->get_result();
    $user_data = $user_result->fetch_assoc();
    
    if (!password_verify($current_password, $user_data['password'])) {
        $error_message = "Current password is incorrect.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "New password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_query->bind_param("si", $hashed_password, $user_id);
        
        if ($update_query->execute()) {
            $success_message = "Password changed successfully!";
        } else {
            $error_message = "Error changing password. Please try again.";
        }
    }
}

// Handle notification preferences
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notifications'])) {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
    $appointment_reminders = isset($_POST['appointment_reminders']) ? 1 : 0;
    $vaccination_reminders = isset($_POST['vaccination_reminders']) ? 1 : 0;
    $health_updates = isset($_POST['health_updates']) ? 1 : 0;
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    $notification_prefs = json_encode([
        'email_notifications' => $email_notifications,
        'sms_notifications' => $sms_notifications,
        'appointment_reminders' => $appointment_reminders,
        'vaccination_reminders' => $vaccination_reminders,
        'health_updates' => $health_updates,
        'newsletter' => $newsletter
    ]);
    
    $update_query = $conn->prepare("UPDATE users SET notification_preferences = ? WHERE id = ?");
    $update_query->bind_param("si", $notification_prefs, $user_id);
    
    if ($update_query->execute()) {
        $success_message = "Notification preferences updated successfully!";
    } else {
        $error_message = "Error updating notification preferences.";
    }
}

// Handle privacy settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_privacy'])) {
    $profile_visibility = $_POST['profile_visibility'];
    $data_sharing = isset($_POST['data_sharing']) ? 1 : 0;
    $analytics = isset($_POST['analytics']) ? 1 : 0;
    
    $privacy_settings = json_encode([
        'profile_visibility' => $profile_visibility,
        'data_sharing' => $data_sharing,
        'analytics' => $analytics
    ]);
    
    $update_query = $conn->prepare("UPDATE users SET privacy_settings = ? WHERE id = ?");
    $update_query->bind_param("si", $privacy_settings, $user_id);
    
    if ($update_query->execute()) {
        $success_message = "Privacy settings updated successfully!";
    } else {
        $error_message = "Error updating privacy settings.";
    }
}

// Handle account deletion request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_deletion'])) {
    $deletion_reason = trim($_POST['deletion_reason']);
    $confirm_deletion = $_POST['confirm_deletion'];
    
    if ($confirm_deletion !== 'DELETE') {
        $error_message = "Please type 'DELETE' to confirm account deletion.";
    } else {
        // Instead of immediately deleting, mark for deletion and notify admin
        $update_query = $conn->prepare("UPDATE users SET deletion_requested = 1, deletion_reason = ?, deletion_requested_at = NOW() WHERE id = ?");
        $update_query->bind_param("si", $deletion_reason, $user_id);
        
        if ($update_query->execute()) {
            $success_message = "Account deletion requested. You will receive confirmation within 24 hours.";
        } else {
            $error_message = "Error processing deletion request.";
        }
    }
}

// Get user data with settings
$user_query = "SELECT *, COALESCE(notification_preferences, '{}') as notification_preferences, COALESCE(privacy_settings, '{}') as privacy_settings FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Parse JSON settings
$notification_prefs = json_decode($user['notification_preferences'], true) ?: [];
$privacy_settings = json_decode($user['privacy_settings'], true) ?: [];

// Default values
$notification_defaults = [
    'email_notifications' => 1,
    'sms_notifications' => 0,
    'appointment_reminders' => 1,
    'vaccination_reminders' => 1,
    'health_updates' => 1,
    'newsletter' => 0
];

$privacy_defaults = [
    'profile_visibility' => 'private',
    'data_sharing' => 0,
    'analytics' => 1
];

$notification_prefs = array_merge($notification_defaults, $notification_prefs);
$privacy_settings = array_merge($privacy_defaults, $privacy_settings);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Pet Health Tracker</title>
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
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Account Settings</h1>
            <p class="text-gray-600">Manage your account preferences, security, and privacy settings</p>
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
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <nav class="space-y-2">
                        <a href="#security" class="flex items-center px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Security
                        </a>
                        <a href="#notifications" class="flex items-center px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 5v13a2 2 0 002 2h8.5l5.5-5.5V7a2 2 0 00-2-2H6a2 2 0 00-2 2z"></path>
                            </svg>
                            Notifications
                        </a>
                        <a href="#privacy" class="flex items-center px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Privacy
                        </a>
                        <a href="#account" class="flex items-center px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Account
                        </a>
                    </nav>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <a href="profile.php" class="flex items-center text-blue-600 hover:text-blue-700 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-8">
                <!-- Security Settings -->
                <div id="security" class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Security Settings</h2>
                        <p class="text-gray-600 text-sm mt-1">Manage your password and account security</p>
                    </div>
                    
                    <form method="POST" class="p-6">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                <input type="password" name="current_password" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                <input type="password" name="new_password" required minlength="6"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-sm text-gray-500 mt-1">Must be at least 6 characters long</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                <input type="password" name="confirm_password" required minlength="6"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Change Password
                            </button>
                        </div>
                    </form>

                    <!-- Recent Login Activity -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Account Information</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p><strong>Last Login:</strong> <?php echo $user['last_login'] ? date('M j, Y \a\t g:i A', strtotime($user['last_login'])) : 'Never'; ?></p>
                            <p><strong>Account Created:</strong> <?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                            <p><strong>Account Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Notification Preferences -->
                <div id="notifications" class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Notification Preferences</h2>
                        <p class="text-gray-600 text-sm mt-1">Choose how you want to be notified</p>
                    </div>
                    
                    <form method="POST" class="p-6">
                        <input type="hidden" name="update_notifications" value="1">
                        
                        <div class="space-y-6">
                            <!-- Communication Preferences -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Communication Methods</h3>
                                <div class="space-y-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="email_notifications" <?php echo $notification_prefs['email_notifications'] ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">Email Notifications</span>
                                            <span class="block text-sm text-gray-500">Receive notifications via email</span>
                                        </span>
                                    </label>
                                    
                                    <label class="flex items-center">
                                        <input type="checkbox" name="sms_notifications" <?php echo $notification_prefs['sms_notifications'] ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">SMS Notifications</span>
                                            <span class="block text-sm text-gray-500">Receive important alerts via text message</span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Notification Types -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Notification Types</h3>
                                <div class="space-y-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="appointment_reminders" <?php echo $notification_prefs['appointment_reminders'] ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">Appointment Reminders</span>
                                            <span class="block text-sm text-gray-500">Get reminded about upcoming appointments</span>
                                        </span>
                                    </label>
                                    
                                    <?php if ($user_role === 'pet_owner'): ?>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="vaccination_reminders" <?php echo $notification_prefs['vaccination_reminders'] ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">Vaccination Reminders</span>
                                            <span class="block text-sm text-gray-500">Be notified when your pet's vaccinations are due</span>
                                        </span>
                                    </label>
                                    <?php endif; ?>
                                    
                                    <label class="flex items-center">
                                        <input type="checkbox" name="health_updates" <?php echo $notification_prefs['health_updates'] ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">Health Updates</span>
                                            <span class="block text-sm text-gray-500">Receive important health-related notifications</span>
                                        </span>
                                    </label>
                                    
                                    <label class="flex items-center">
                                        <input type="checkbox" name="newsletter" <?php echo $notification_prefs['newsletter'] ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">Newsletter</span>
                                            <span class="block text-sm text-gray-500">Stay updated with pet care tips and platform news</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Preferences
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Privacy Settings -->
                <div id="privacy" class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Privacy Settings</h2>
                        <p class="text-gray-600 text-sm mt-1">Control your privacy and data sharing preferences</p>
                    </div>
                    
                    <form method="POST" class="p-6">
                        <input type="hidden" name="update_privacy" value="1">
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Profile Visibility</label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="profile_visibility" value="public" <?php echo $privacy_settings['profile_visibility'] === 'public' ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <span class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">Public</span>
                                            <span class="block text-sm text-gray-500">Your profile is visible to other users</span>
                                        </span>
                                    </label>
                                    
                                    <label class="flex items-center">
                                        <input type="radio" name="profile_visibility" value="private" <?php echo $privacy_settings['profile_visibility'] === 'private' ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <span class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">Private</span>
                                            <span class="block text-sm text-gray-500">Your profile is only visible to healthcare providers</span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="data_sharing" <?php echo $privacy_settings['data_sharing'] ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-gray-900">Data Sharing for Research</span>
                                        <span class="block text-sm text-gray-500">Allow anonymized data to be used for veterinary research (helps improve pet healthcare)</span>
                                    </span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" name="analytics" <?php echo $privacy_settings['analytics'] ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-gray-900">Usage Analytics</span>
                                        <span class="block text-sm text-gray-500">Help us improve the platform by sharing anonymous usage data</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Privacy Settings
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Account Management -->
                <div id="account" class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Account Management</h2>
                        <p class="text-gray-600 text-sm mt-1">Manage your account and data</p>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Export Data -->
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">Export Your Data</h3>
                                <p class="text-sm text-gray-500 mt-1">Download a copy of your account data and pet health records</p>
                            </div>
                            <button class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors duration-200 text-sm">
                                Export Data
                            </button>
                        </div>

                        <!-- Two-Factor Authentication -->
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">Two-Factor Authentication</h3>
                                <p class="text-sm text-gray-500 mt-1">Add an extra layer of security to your account</p>
                            </div>
                            <button class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-sm" disabled>
                                Coming Soon
                            </button>
                        </div>

                        <!-- Delete Account -->
                        <div class="border border-red-200 rounded-lg bg-red-50">
                            <div class="p-4">
                                <h3 class="text-sm font-medium text-red-900">Delete Account</h3>
                                <p class="text-sm text-red-700 mt-1">Permanently delete your account and all associated data</p>
                                
                                <form method="POST" class="mt-4" onsubmit="return confirm('Are you absolutely sure? This action cannot be undone.')">
                                    <input type="hidden" name="request_deletion" value="1">
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-red-900 mb-2">Reason for deletion (optional)</label>
                                            <textarea name="deletion_reason" rows="2" 
                                                      class="w-full px-3 py-2 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                                      placeholder="Help us improve by telling us why you're leaving..."></textarea>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-red-900 mb-2">
                                                Type "DELETE" to confirm
                                            </label>
                                            <input type="text" name="confirm_deletion" required
                                                   class="w-full px-3 py-2 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                                   placeholder="DELETE">
                                        </div>
                                    </div>

                                    <button type="submit" class="mt-4 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors duration-200 text-sm">
                                        Request Account Deletion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Password confirmation validation
        const newPassword = document.querySelector('input[name="new_password"]');
        const confirmPassword = document.querySelector('input[name="confirm_password"]');
        
        if (newPassword && confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (this.value !== newPassword.value) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
