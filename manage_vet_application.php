<?php
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = $_POST['application_id'];
    $status = $_POST['status'];
    $rejection_reason = $_POST['rejection_reason'] ?: null;
    
    // Update application status
    $stmt = $conn->prepare("UPDATE veterinarian_applications SET status = ?, rejection_reason = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssii", $status, $rejection_reason, $_SESSION['user_id'], $application_id);
    $stmt->execute();
    
    // If approved, update user role and create veterinarian profile
    if ($status === 'approved') {
        $stmt = $conn->prepare("SELECT user_id, license_number, clinic_name, qualifications, specializations, years_experience, education, certifications FROM veterinarian_applications WHERE id = ?");
        $stmt->bind_param("i", $application_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $application = $result->fetch_assoc();
        
        if ($application) {
            $user_id = $application['user_id'];
            
            // Update user role
            $stmt = $conn->prepare("UPDATE users SET role = 'veterinarian' WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Create veterinarian profile
            $stmt = $conn->prepare("INSERT INTO veterinarian_profiles (user_id, license_number, clinic_name, specializations, years_experience, education, certifications, is_accepting_patients) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("isssiss", 
                $user_id, 
                $application['license_number'], 
                $application['clinic_name'], 
                $application['specializations'], 
                $application['years_experience'], 
                $application['education'], 
                $application['certifications']
            );
            $stmt->execute();
        }
    }
    
    $_SESSION['success_message'] = "Application " . ($status === 'approved' ? 'approved' : 'updated') . " successfully!";
    header("Location: manage_vet_application.php");
    exit;
}

// Get application details if ID is provided
$application = null;
$application_id = $_GET['id'] ?? null;

if ($application_id) {
    $stmt = $conn->prepare("
        SELECT va.*, u.first_name, u.last_name, u.email, u.phone, 
               reviewer.first_name as reviewer_first_name, reviewer.last_name as reviewer_last_name
        FROM veterinarian_applications va 
        JOIN users u ON va.user_id = u.id 
        LEFT JOIN users reviewer ON va.reviewed_by = reviewer.id 
        WHERE va.id = ?
    ");
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $application = $result->fetch_assoc();
    
    if (!$application) {
        $_SESSION['error_message'] = "Application not found.";
        header("Location: admin_dashboard.php");
        exit;
    }
}

// Get all applications for listing
$stmt = $conn->query("
    SELECT va.*, u.first_name, u.last_name, u.email 
    FROM veterinarian_applications va 
    JOIN users u ON va.user_id = u.id 
    ORDER BY va.created_at DESC
");
$applications = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Veterinarian Applications - Pet Health Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .status-badge {
            @apply px-3 py-1 rounded-full text-xs font-medium;
        }
        .status-pending { @apply bg-yellow-100 text-yellow-800; }
        .status-under_review { @apply bg-blue-100 text-blue-800; }
        .status-approved { @apply bg-green-100 text-green-800; }
        .status-rejected { @apply bg-red-100 text-red-800; }
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
                    <span class="text-sm text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                    <a href="admin_dashboard.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">← Back to Dashboard</a>
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

        <?php if ($application): ?>
            <!-- Detailed Application View -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-500 to-green-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold text-white">Veterinarian Application Review</h1>
                        <span class="status-badge status-<?php echo $application['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $application['status'])); ?>
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Applicant Information -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Applicant Information
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Full Name</label>
                                    <p class="text-gray-800"><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Email</label>
                                    <p class="text-gray-800"><?php echo htmlspecialchars($application['email']); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Phone</label>
                                    <p class="text-gray-800"><?php echo htmlspecialchars($application['phone'] ?: 'Not provided'); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Application Date</label>
                                    <p class="text-gray-800"><?php echo date('F j, Y g:i A', strtotime($application['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Professional Information
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">License Number</label>
                                    <p class="text-gray-800"><?php echo htmlspecialchars($application['license_number']); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Clinic Name</label>
                                    <p class="text-gray-800"><?php echo htmlspecialchars($application['clinic_name'] ?: 'Not provided'); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Years of Experience</label>
                                    <p class="text-gray-800"><?php echo htmlspecialchars($application['years_experience'] ?: 'Not provided'); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Specializations</label>
                                    <p class="text-gray-800"><?php echo htmlspecialchars($application['specializations'] ?: 'Not provided'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Qualifications and Education -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Qualifications</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($application['qualifications']); ?></p>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Education</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($application['education'] ?: 'Not provided'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Certifications -->
                    <?php if ($application['certifications']): ?>
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Certifications</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($application['certifications']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Review Status -->
                    <?php if ($application['reviewed_at']): ?>
                    <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-blue-800 mb-2">Review Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-blue-600">Reviewed By</label>
                                <p class="text-blue-800"><?php echo htmlspecialchars($application['reviewer_first_name'] . ' ' . $application['reviewer_last_name']); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-blue-600">Review Date</label>
                                <p class="text-blue-800"><?php echo date('F j, Y g:i A', strtotime($application['reviewed_at'])); ?></p>
                            </div>
                        </div>
                        <?php if ($application['rejection_reason']): ?>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-blue-600">Rejection Reason</label>
                            <p class="text-blue-800"><?php echo htmlspecialchars($application['rejection_reason']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Action Form -->
                    <?php if ($application['status'] === 'pending' || $application['status'] === 'under_review'): ?>
                    <form method="POST" class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                        <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                        
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Review Decision</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Decision</label>
                                <select name="status" id="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                    <option value="">Select Decision</option>
                                    <option value="under_review">Under Review</option>
                                    <option value="approved">Approve</option>
                                    <option value="rejected">Reject</option>
                                </select>
                            </div>
                            
                            <div id="rejection-reason-container" style="display: none;">
                                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                                <textarea name="rejection_reason" id="rejection_reason" rows="3" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Please provide a reason for rejection..."></textarea>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end space-x-4 mt-6">
                            <a href="admin_dashboard.php" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-green-500 text-white rounded-lg hover:from-blue-600 hover:to-green-600 transition-all duration-200 font-medium">
                                Submit Decision
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Applications List -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-500 to-green-500 px-6 py-4">
                    <h1 class="text-2xl font-bold text-white">Veterinarian Applications</h1>
                </div>

                <div class="p-6">
                    <?php if (empty($applications)): ?>
                        <div class="text-center py-12">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Applications Found</h3>
                            <p class="text-gray-500">There are currently no veterinarian applications to review.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($applications as $app): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($app['email']); ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($app['license_number']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo date('M j, Y', strtotime($app['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="status-badge status-<?php echo $app['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $app['status'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="?id=<?php echo $app['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Show/hide rejection reason field based on status selection
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status');
            const rejectionContainer = document.getElementById('rejection-reason-container');
            const rejectionTextarea = document.getElementById('rejection_reason');
            
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    if (this.value === 'rejected') {
                        rejectionContainer.style.display = 'block';
                        rejectionTextarea.required = true;
                    } else {
                        rejectionContainer.style.display = 'none';
                        rejectionTextarea.required = false;
                        rejectionTextarea.value = '';
                    }
                });
            }
        });
    </script>
</body>
</html>