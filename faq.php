<?php
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Pet Health Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-green-50 min-h-screen font-sans">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-12">
        <!-- Hero Section -->
        <div class="text-center mb-16">
            <h1 class="text-5xl font-bold text-gray-800 mb-6">Frequently Asked Questions</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Find answers to common questions about Pet Health Tracker and how to make the most of our platform.
            </p>
        </div>

        <!-- Search Bar -->
        <div class="max-w-2xl mx-auto mb-12">
            <div class="relative">
                <input type="text" 
                       placeholder="Search for answers..." 
                       class="w-full px-6 py-4 text-lg border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-transparent pl-12">
                <svg class="w-6 h-6 text-gray-400 absolute left-4 top-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- FAQ Categories -->
        <div class="grid md:grid-cols-4 gap-6 mb-12">
            <button class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow duration-300">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-800">Getting Started</h3>
            </button>
            
            <button class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow duration-300">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-800">Health Records</h3>
            </button>
            
            <button class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow duration-300">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-800">Appointments</h3>
            </button>
            
            <button class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow duration-300">
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-800">Billing</h3>
            </button>
        </div>

        <!-- FAQ Sections -->
        <div class="space-y-8">
            <!-- Getting Started -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    Getting Started
                </h2>
                
                <div class="space-y-4" x-data="{ openFaq: null }">
                    <div class="border border-gray-200 rounded-lg">
                        <button @click="openFaq = openFaq === 1 ? null : 1" 
                                class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                            <span class="font-medium text-gray-800">How do I create an account?</span>
                            <svg :class="openFaq === 1 ? 'rotate-180' : ''" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openFaq === 1" x-collapse class="px-6 pb-4">
                            <p class="text-gray-600">Click the "Register" button in the top navigation, fill out your information, and choose whether you're a pet owner or veterinarian. You'll receive a confirmation email to activate your account.</p>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg">
                        <button @click="openFaq = openFaq === 2 ? null : 2" 
                                class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                            <span class="font-medium text-gray-800">How do I add my first pet?</span>
                            <svg :class="openFaq === 2 ? 'rotate-180' : ''" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openFaq === 2" x-collapse class="px-6 pb-4">
                            <p class="text-gray-600">After logging in, go to your dashboard and click "Add New Pet". Fill out your pet's basic information including name, species, breed, age, and any medical information you have available.</p>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg">
                        <button @click="openFaq = openFaq === 3 ? null : 3" 
                                class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                            <span class="font-medium text-gray-800">Is Pet Health Tracker free to use?</span>
                            <svg :class="openFaq === 3 ? 'rotate-180' : ''" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openFaq === 3" x-collapse class="px-6 pb-4">
                            <p class="text-gray-600">We offer a free trial period. After that, we have affordable monthly and annual plans starting at $9/month. Check our pricing page for current rates and features.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Health Records -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    Health Records
                </h2>
                
                <div class="space-y-4" x-data="{ openFaq: null }">
                    <div class="border border-gray-200 rounded-lg">
                        <button @click="openFaq = openFaq === 4 ? null : 4" 
                                class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                            <span class="font-medium text-gray-800">How do I add health records for my pet?</span>
                            <svg :class="openFaq === 4 ? 'rotate-180' : ''" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openFaq === 4" x-collapse class="px-6 pb-4">
                            <p class="text-gray-600">Go to your pet's profile and click "Add Health Record". You can add various types including checkups, vaccinations, illnesses, surgeries, and medications. Include details like date, veterinarian, diagnosis, and treatment.</p>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg">
                        <button @click="openFaq = openFaq === 5 ? null : 5" 
                                class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                            <span class="font-medium text-gray-800">Can veterinarians add records to my pet's profile?</span>
                            <svg :class="openFaq === 5 ? 'rotate-180' : ''" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openFaq === 5" x-collapse class="px-6 pb-4">
                            <p class="text-gray-600">Yes! Licensed veterinarians can add health records directly to your pet's profile after appointments. You'll receive notifications when new records are added and can review them in your dashboard.</p>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg">
                        <button @click="openFaq = openFaq === 6 ? null : 6" 
                                class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                            <span class="font-medium text-gray-8000">How do I upload documents or photos?</span>
                            <svg :class="openFaq === 6 ? 'rotate-180' : ''" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openFaq === 6" x-collapse class="px-6 pb-4">
                            <p class="text-gray-600">When adding or editing health records, you can attach photos, lab results, or other documents. Supported formats include JPG, PNG, PDF. Maximum file size is 10MB per upload.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointments -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    Appointments
                </h2>
                
                <div class="space-y-4" x-data="{ openFaq: null }">
                    <div class="border border-gray-200 rounded-lg">
                        <button @click="openFaq = openFaq === 7 ? null : 7" 
                                class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                            <span class="font-medium text-gray-800">How do I schedule an appointment?</span>
                            <svg :class="openFaq === 7 ? 'rotate-180' : ''" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openFaq === 7" x-collapse class="px-6 pb-4">
                            <p class="text-gray-600">Click "Schedule Appointment" from your dashboard or pet profile. Choose your preferred veterinarian, select an available time slot, specify the appointment type, and provide any additional notes about your pet's condition.</p>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg">
                        <button @click="openFaq = openFaq === 8 ? null : 8" 
                                class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                            <span class="font-medium text-gray-800">Can I cancel or reschedule appointments?</span>
                            <svg :class="openFaq === 8 ? 'rotate-180' : ''" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openFaq === 8" x-collapse class="px-6 pb-4">
                            <p class="text-gray-600">Yes, you can cancel or reschedule appointments up to 24 hours before the scheduled time. Go to your appointments page, find the appointment, and click the appropriate action. Please note cancellation policies may vary by veterinary practice.</p>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg">
                        <button @click="openFaq = openFaq === 9 ? null : 9" 
                                class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                            <span class="font-medium text-gray-800">Will I receive appointment reminders?</span>
                            <svg :class="openFaq === 9 ? 'rotate-180' : ''" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openFaq === 9" x-collapse class="px-6 pb-4">
                            <p class="text-gray-600">Yes! We'll send you email and app notifications 24 hours and 2 hours before your appointment. You can customize reminder preferences in your account settings.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing & Account -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    Billing & Account
                </h2>
                
                <div class="space-y-4" x-data="{ openFaq: null }">
                    <div class="border border-gray-200 rounded-lg">
                        <button @click="openFaq = openFaq === 10 ? null : 10" 
                                class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                            <span class="font-medium text-gray-800">How can I update my payment information?</span>
                            <svg :class="openFaq === 10 ? 'rotate-180' : ''" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openFaq === 10" x-collapse class="px-6 pb-4">
                            <p class="text-gray-600">Go to Account Settings > Billing, then click "Update Payment Method". You can add new cards, change your primary payment method, or update billing addresses securely.</p>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg">
                        <button @click="openFaq = openFaq === 11 ? null : 11" 
                                class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                            <span class="font-medium text-gray-800">Can I export my pet's data?</span>
                            <svg :class="openFaq === 11 ? 'rotate-180' : ''" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openFaq === 11" x-collapse class="px-6 pb-4">
                            <p class="text-gray-600">Yes! You can export your pet's complete health records as a PDF report from their profile page. This is useful for vet visits, travel, or keeping offline backups.</p>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg">
                        <button @click="openFaq = openFaq === 12 ? null : 12" 
                                class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                            <span class="font-medium text-gray-800">How do I delete my account?</span>
                            <svg :class="openFaq === 12 ? 'rotate-180' : ''" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openFaq === 12" x-collapse class="px-6 pb-4">
                            <p class="text-gray-600">Go to Account Settings > Privacy & Security > Delete Account. We'll ask you to confirm and provide feedback. Note that this action is permanent and cannot be undone. We recommend exporting your data first.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Still Have Questions -->
        <div class="bg-gradient-to-r from-blue-600 to-green-600 rounded-2xl shadow-xl p-12 text-white text-center mt-16">
            <h2 class="text-3xl font-bold mb-4">Still Have Questions?</h2>
            <p class="text-xl mb-8 opacity-90">Our support team is here to help you get the most out of Pet Health Tracker</p>
            <div class="space-x-4">
                <a href="contact.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors duration-200">
                    Contact Support
                </a>
                <button class="bg-white bg-opacity-20 text-white px-8 py-3 rounded-lg font-semibold hover:bg-opacity-30 transition-colors duration-200">
                    Live Chat
                </button>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
