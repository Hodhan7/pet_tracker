<?php
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pet_owner') {
    header("Location: login.php");
    exit;
}
$pet_id = $_GET['pet_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $record_type = $_POST['record_type'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $stmt = $conn->prepare("INSERT INTO health_records (pet_id, record_type, description, date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $pet_id, $record_type, $description, $date);
    $stmt->execute();
    header("Location: owner_dashboard.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Health Record - Pet Health Tracker</title>
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
        <div class="max-w-2xl mx-auto">
            <!-- Page Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Add Health Record</h1>
                        <p class="text-gray-600">Document your pet's health information and medical history</p>
                    </div>
                </div>
            </div>

            <!-- Health Record Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <form action="add_health_record.php?pet_id=<?php echo $pet_id; ?>" method="POST" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="record_type" class="block text-sm font-medium text-gray-700 mb-2">Record Type *</label>
                            <select id="record_type" name="record_type" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="">Select record type</option>
                                <option value="Vaccination">Vaccination</option>
                                <option value="Medical Examination">Medical Examination</option>
                                <option value="Surgery">Surgery</option>
                                <option value="Medication">Medication</option>
                                <option value="Allergy">Allergy</option>
                                <option value="Injury">Injury</option>
                                <option value="Dental Care">Dental Care</option>
                                <option value="Weight Check">Weight Check</option>
                                <option value="Blood Test">Blood Test</option>
                                <option value="X-Ray">X-Ray</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                            <input type="date" id="date" name="date" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                   max="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="md:col-span-2">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                            <input type="text" id="title" name="title" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                   placeholder="Brief title for this record (e.g., Annual Vaccination, Dental Cleaning)">
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                            <textarea id="description" name="description" rows="6" 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                      placeholder="Detailed description of the health record, including symptoms, diagnosis, treatment, medications, etc." required></textarea>
                        </div>

                        <div>
                            <label for="veterinarian" class="block text-sm font-medium text-gray-700 mb-2">Veterinarian</label>
                            <input type="text" id="veterinarian" name="veterinarian" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                   placeholder="Dr. Smith">
                        </div>

                        <div>
                            <label for="cost" class="block text-sm font-medium text-gray-700 mb-2">Cost ($)</label>
                            <input type="number" id="cost" name="cost" step="0.01" min="0" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                   placeholder="0.00">
                        </div>

                        <div class="md:col-span-2">
                            <label for="medications" class="block text-sm font-medium text-gray-700 mb-2">Medications Prescribed</label>
                            <textarea id="medications" name="medications" rows="3" 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                      placeholder="List any medications prescribed, including dosage and frequency"></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label for="follow_up" class="block text-sm font-medium text-gray-700 mb-2">Follow-up Required</label>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="follow_up" value="yes" class="text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Yes</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="follow_up" value="no" class="text-blue-600 focus:ring-blue-500" checked>
                                    <span class="ml-2 text-sm text-gray-700">No</span>
                                </label>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="follow_up_date" class="block text-sm font-medium text-gray-700 mb-2">Follow-up Date (if applicable)</label>
                            <input type="date" id="follow_up_date" name="follow_up_date" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <a href="owner_dashboard.php" 
                           class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200 font-medium">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-8 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 font-medium shadow-sm">
                            Add Health Record
                        </button>
                    </div>
                </form>
            </div>

            <!-- Help Section -->
            <div class="mt-8 bg-amber-50 border border-amber-200 rounded-lg p-6">
                <div class="flex items-start space-x-3">
                    <svg class="w-6 h-6 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-amber-800 mb-1">Tips for health records</h3>
                        <ul class="text-sm text-amber-700 space-y-1">
                            <li>• Include as much detail as possible for future reference</li>
                            <li>• Keep track of vaccination dates to maintain immunity schedules</li>
                            <li>• Record any adverse reactions or allergies</li>
                            <li>• Note behavioral changes or symptoms leading to the visit</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>