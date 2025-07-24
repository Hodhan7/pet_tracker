<?php
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Pet Health Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-green-50 min-h-screen font-sans">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-12">
        <!-- Hero Section -->
        <div class="text-center mb-16">
            <h1 class="text-5xl font-bold text-gray-800 mb-6">About Pet Health Tracker</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                We're passionate about making pet healthcare accessible, organized, and stress-free for pet owners and veterinarians alike.
            </p>
        </div>

        <!-- Mission Section -->
        <div class="grid md:grid-cols-2 gap-12 items-center mb-16">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Our Mission</h2>
                <p class="text-gray-600 text-lg leading-relaxed mb-6">
                    At Pet Health Tracker, we believe every pet deserves the best possible healthcare. Our mission is to bridge the gap between pet owners and veterinary professionals through innovative technology that makes health management simple, comprehensive, and effective.
                </p>
                <p class="text-gray-600 text-lg leading-relaxed">
                    We're committed to helping you keep track of your pet's health journey, from routine checkups to emergency care, ensuring no important detail is ever lost.
                </p>
            </div>
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <div class="text-center">
                    <div class="w-24 h-24 bg-gradient-to-r from-blue-500 to-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Trusted by Pet Families</h3>
                    <p class="text-gray-600">Helping pets live healthier, happier lives through better healthcare management.</p>
                </div>
            </div>
        </div>

        <!-- Values Section -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-800 text-center mb-12">Our Values</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Reliability</h3>
                    <p class="text-gray-600">Your pet's health data is secure, accurate, and always accessible when you need it most.</p>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Innovation</h3>
                    <p class="text-gray-600">We continuously improve our platform with the latest technology to serve pets and their families better.</p>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Community</h3>
                    <p class="text-gray-600">Building stronger relationships between pet owners, veterinarians, and the broader pet care community.</p>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-800 text-center mb-12">Our Story</h2>
            <div class="bg-white rounded-2xl shadow-xl p-12">
                <div class="max-w-4xl mx-auto text-center">
                    <p class="text-lg text-gray-600 leading-relaxed mb-8">
                        Pet Health Tracker was born from a simple idea: pet healthcare should be as organized and accessible as human healthcare. Founded by a team of pet lovers, veterinarians, and technology enthusiasts, we recognized the challenges pet owners face in managing their furry family members' health records.
                    </p>
                    <p class="text-lg text-gray-600 leading-relaxed mb-8">
                        Too often, important vaccination records were lost, appointment histories were scattered across different veterinary offices, and critical health information wasn't readily available during emergencies. We knew there had to be a better way.
                    </p>
                    <p class="text-lg text-gray-600 leading-relaxed">
                        Today, Pet Health Tracker serves thousands of pet families and veterinary practices, providing a comprehensive platform that keeps all health information organized, accessible, and secure. We're proud to be part of your pet's health journey.
                    </p>
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="mb-16">
            <div class="bg-gradient-to-r from-blue-600 to-green-600 rounded-2xl shadow-xl p-12 text-white">
                <h2 class="text-3xl font-bold text-center mb-12">Making a Difference</h2>
                <div class="grid md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-4xl font-bold mb-2">10,000+</div>
                        <div class="text-lg opacity-90">Happy Pets</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold mb-2">500+</div>
                        <div class="text-lg opacity-90">Veterinarians</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold mb-2">50,000+</div>
                        <div class="text-lg opacity-90">Health Records</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold mb-2">99.9%</div>
                        <div class="text-lg opacity-90">Uptime</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact CTA -->
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Ready to Get Started?</h2>
            <p class="text-xl text-gray-600 mb-8">Join thousands of pet families who trust Pet Health Tracker with their pets' health.</p>
            <div class="space-x-4">
                <a href="register.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200">
                    Get Started Free
                </a>
                <a href="contact.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold border-2 border-blue-600 hover:bg-blue-50 transition-colors duration-200">
                    Contact Us
                </a>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
