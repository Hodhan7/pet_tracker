<?php
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Features - Pet Health Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-green-50 min-h-screen font-sans">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-12">
        <!-- Hero Section -->
        <div class="text-center mb-16">
            <h1 class="text-5xl font-bold text-gray-800 mb-6">Powerful Features for Pet Care</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Everything you need to manage your pet's health in one comprehensive platform
            </p>
        </div>

        <!-- Main Features Grid -->
        <div class="grid lg:grid-cols-3 md:grid-cols-2 gap-8 mb-16">
            <!-- Health Records -->
            <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition-shadow duration-300">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Complete Health Records</h3>
                <p class="text-gray-600 mb-4">Comprehensive medical history tracking including checkups, vaccinations, illnesses, surgeries, and medications.</p>
                <ul class="text-sm text-gray-500 space-y-2">
                    <li>• Digital health records</li>
                    <li>• Vaccination tracking</li>
                    <li>• Medical history timeline</li>
                    <li>• Treatment notes</li>
                </ul>
            </div>

            <!-- Appointment Scheduling -->
            <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition-shadow duration-300">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Smart Scheduling</h3>
                <p class="text-gray-600 mb-4">Easy online appointment booking with your preferred veterinarians and automated reminders.</p>
                <ul class="text-sm text-gray-500 space-y-2">
                    <li>• Online booking</li>
                    <li>• Automated reminders</li>
                    <li>• Multiple appointment types</li>
                    <li>• Vet availability tracking</li>
                </ul>
            </div>

            <!-- Veterinarian Network -->
            <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition-shadow duration-300">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Veterinarian Network</h3>
                <p class="text-gray-600 mb-4">Connect with licensed veterinarians and access professional care from trusted practitioners.</p>
                <ul class="text-sm text-gray-500 space-y-2">
                    <li>• Licensed professionals</li>
                    <li>• Specialization matching</li>
                    <li>• Practice information</li>
                    <li>• Reviews and ratings</li>
                </ul>
            </div>

            <!-- Medication Tracking -->
            <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition-shadow duration-300">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Medication Management</h3>
                <p class="text-gray-600 mb-4">Track current medications, dosages, and schedules with automated reminders for administration.</p>
                <ul class="text-sm text-gray-500 space-y-2">
                    <li>• Dosage tracking</li>
                    <li>• Administration reminders</li>
                    <li>• Side effect monitoring</li>
                    <li>• Prescription history</li>
                </ul>
            </div>

            <!-- Weight & Vitals -->
            <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition-shadow duration-300">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Weight & Vitals Tracking</h3>
                <p class="text-gray-600 mb-4">Monitor your pet's weight, temperature, and other vital statistics over time with visual charts.</p>
                <ul class="text-sm text-gray-500 space-y-2">
                    <li>• Weight monitoring</li>
                    <li>• Temperature tracking</li>
                    <li>• Growth charts</li>
                    <li>• Health trends</li>
                </ul>
            </div>

            <!-- Reminders & Alerts -->
            <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition-shadow duration-300">
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17h.01M7 21h4a2 2 0 002-2v-4a2 2 0 00-2-2H7a2 2 0 00-2 2v4a2 2 0 002 2zM7 9h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v2a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Smart Reminders</h3>
                <p class="text-gray-600 mb-4">Never miss important dates with automated reminders for vaccinations, checkups, and medications.</p>
                <ul class="text-sm text-gray-500 space-y-2">
                    <li>• Vaccination due dates</li>
                    <li>• Checkup reminders</li>
                    <li>• Medication alerts</li>
                    <li>• Custom notifications</li>
                </ul>
            </div>
        </div>

        <!-- For Veterinarians Section -->
        <div class="bg-gradient-to-r from-blue-600 to-green-600 rounded-2xl shadow-xl p-12 text-white mb-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold mb-4">Features for Veterinarians</h2>
                <p class="text-xl opacity-90">Professional tools to manage your practice efficiently</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m2 0h2a2 2 0 002-2V7a2 2 0 00-2-2h-2m-2 0V3m0 2v2m0 4v2m2-6h.01M21 21v-7a2 2 0 00-2-2h-4a2 2 0 00-2 2v7h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Patient Management</h3>
                    <p class="opacity-90">Comprehensive patient records, search, and filtering capabilities</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Quick Record Entry</h3>
                    <p class="opacity-90">Fast health record creation with multiple access points and templates</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13v-1a4 4 0 014-4 4 4 0 014 4v1m0 0l-4 4m4-4l4 4m-8-8V9a4 4 0 114 4v4m-4-4l-4 4m4-4l4-4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Practice Analytics</h3>
                    <p class="opacity-90">Insights into patient trends, appointment patterns, and practice growth</p>
                </div>
            </div>
        </div>

        <!-- Security & Privacy -->
        <div class="bg-white rounded-2xl shadow-xl p-12 mb-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Security & Privacy</h2>
                <p class="text-xl text-gray-600">Your pet's health data is protected with enterprise-grade security</p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">Data Encryption</h3>
                    <p class="text-gray-600 text-sm">All data encrypted in transit and at rest</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">HIPAA Compliant</h3>
                    <p class="text-gray-600 text-sm">Healthcare data protection standards</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">Access Control</h3>
                    <p class="text-gray-600 text-sm">Role-based permissions and access logs</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">Regular Backups</h3>
                    <p class="text-gray-600 text-sm">Automated backups and data recovery</p>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Ready to Experience These Features?</h2>
            <p class="text-xl text-gray-600 mb-8">Join thousands of pet families using Pet Health Tracker</p>
            <div class="space-x-4">
                <a href="register.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200">
                    Start Free Trial
                </a>
                <a href="pricing.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold border-2 border-blue-600 hover:bg-blue-50 transition-colors duration-200">
                    View Pricing
                </a>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
