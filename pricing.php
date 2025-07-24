<?php
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing - Pet Health Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-green-50 min-h-screen font-sans">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-12">
        <!-- Hero Section -->
        <div class="text-center mb-16">
            <h1 class="text-5xl font-bold text-gray-800 mb-6">Simple, Transparent Pricing</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Choose the perfect plan for your pet family. No hidden fees, no surprises.
            </p>
        </div>

        <!-- Pricing Toggle -->
        <div class="flex justify-center mb-12">
            <div class="bg-white rounded-lg p-1 shadow-lg" x-data="{ billing: 'monthly' }">
                <div class="flex">
                    <button @click="billing = 'monthly'" 
                            :class="billing === 'monthly' ? 'bg-blue-600 text-white' : 'text-gray-500'"
                            class="px-6 py-2 rounded-md font-medium transition-all duration-200">
                        Monthly
                    </button>
                    <button @click="billing = 'annual'" 
                            :class="billing === 'annual' ? 'bg-blue-600 text-white' : 'text-gray-500'"
                            class="px-6 py-2 rounded-md font-medium transition-all duration-200">
                        Annual <span class="text-green-500 text-xs">(Save 20%)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Pricing Cards -->
        <div class="grid lg:grid-cols-3 gap-8 mb-16">
            <!-- Basic Plan -->
            <div class="bg-white rounded-2xl shadow-lg p-8 border-2 border-gray-100 hover:border-blue-200 transition-all duration-300">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Basic</h3>
                    <p class="text-gray-500 mb-4">Perfect for single pet families</p>
                    <div class="text-4xl font-bold text-gray-800 mb-2">
                        $9<span class="text-lg text-gray-500">/month</span>
                    </div>
                    <p class="text-sm text-gray-500">or $86/year (save $22)</p>
                </div>
                
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">1 Pet Profile</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Health Records Tracking</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Vaccination Reminders</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Appointment Scheduling</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Basic Support</span>
                    </li>
                </ul>
                
                <button class="w-full bg-gray-100 text-gray-800 py-3 rounded-lg font-semibold hover:bg-gray-200 transition-colors duration-200">
                    Get Started
                </button>
            </div>

            <!-- Premium Plan (Featured) -->
            <div class="bg-white rounded-2xl shadow-xl p-8 border-2 border-blue-500 relative transform scale-105">
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <span class="bg-blue-500 text-white px-6 py-2 rounded-full text-sm font-semibold">Most Popular</span>
                </div>
                
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Premium</h3>
                    <p class="text-gray-500 mb-4">Ideal for multi-pet families</p>
                    <div class="text-4xl font-bold text-gray-800 mb-2">
                        $19<span class="text-lg text-gray-500">/month</span>
                    </div>
                    <p class="text-sm text-gray-500">or $182/year (save $46)</p>
                </div>
                
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Up to 5 Pet Profiles</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Complete Health Records</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Smart Reminders & Alerts</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Weight & Vitals Tracking</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Medication Management</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Photo Storage (50 photos)</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Priority Support</span>
                    </li>
                </ul>
                
                <button class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200">
                    Start Free Trial
                </button>
            </div>

            <!-- Professional Plan -->
            <div class="bg-white rounded-2xl shadow-lg p-8 border-2 border-gray-100 hover:border-purple-200 transition-all duration-300">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Professional</h3>
                    <p class="text-gray-500 mb-4">For veterinarians and clinics</p>
                    <div class="text-4xl font-bold text-gray-800 mb-2">
                        $49<span class="text-lg text-gray-500">/month</span>
                    </div>
                    <p class="text-sm text-gray-500">or $470/year (save $118)</p>
                </div>
                
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Unlimited Patient Records</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Practice Management Tools</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Advanced Analytics</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Multi-user Access</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Custom Branding</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">API Access</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">24/7 Premium Support</span>
                    </li>
                </ul>
                
                <button class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition-colors duration-200">
                    Contact Sales
                </button>
            </div>
        </div>

        <!-- Enterprise Section -->
        <div class="bg-gray-900 rounded-2xl shadow-xl p-12 text-white text-center mb-16">
            <h2 class="text-3xl font-bold mb-4">Enterprise Solutions</h2>
            <p class="text-xl text-gray-300 mb-8">
                Custom solutions for large veterinary chains, universities, and organizations
            </p>
            <div class="grid md:grid-cols-3 gap-8 mb-8">
                <div>
                    <h3 class="font-bold mb-2">Custom Integration</h3>
                    <p class="text-gray-300 text-sm">Integrate with existing practice management systems</p>
                </div>
                <div>
                    <h3 class="font-bold mb-2">White Label Solutions</h3>
                    <p class="text-gray-300 text-sm">Branded platform with your organization's identity</p>
                </div>
                <div>
                    <h3 class="font-bold mb-2">Dedicated Support</h3>
                    <p class="text-gray-300 text-sm">Dedicated account manager and technical support</p>
                </div>
            </div>
            <button class="bg-white text-gray-900 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors duration-200">
                Schedule a Demo
            </button>
        </div>

        <!-- FAQ Section -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-800 text-center mb-12">Frequently Asked Questions</h2>
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-800 mb-3">Is there a free trial?</h3>
                    <p class="text-gray-600">Yes! We offer a 14-day free trial for all Premium plans. No credit card required to start.</p>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-800 mb-3">Can I change plans anytime?</h3>
                    <p class="text-gray-600">Absolutely! You can upgrade, downgrade, or cancel your plan at any time from your account settings.</p>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-800 mb-3">What payment methods do you accept?</h3>
                    <p class="text-gray-600">We accept all major credit cards, PayPal, and offer annual billing discounts.</p>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-800 mb-3">Is my data secure?</h3>
                    <p class="text-gray-600">Yes! We use enterprise-grade encryption and are HIPAA compliant to protect your pet's health information.</p>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Ready to Get Started?</h2>
            <p class="text-xl text-gray-600 mb-8">Join thousands of pet families who trust Pet Health Tracker</p>
            <div class="space-x-4">
                <a href="register.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200">
                    Start Free Trial
                </a>
                <a href="contact.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold border-2 border-blue-600 hover:bg-blue-50 transition-colors duration-200">
                    Contact Sales
                </a>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
