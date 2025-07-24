<?php
require 'db.php';

// Check if user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    // Redirect based on user role
    if ($_SESSION['role'] === 'pet_owner') {
        header("Location: owner_dashboard.php");
    } elseif ($_SESSION['role'] === 'veterinarian') {
        header("Location: vet_dashboard.php");
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Health Tracker - Your Pet's Health Companion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'ui-sans-serif', 'system-ui'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% { transform: translate3d(0,0,0); }
            40%, 43% { transform: translate3d(0, -8px, 0); }
            70% { transform: translate3d(0, -4px, 0); }
            90% { transform: translate3d(0, -2px, 0); }
        }
        .animate-fadeIn { animation: fadeIn 0.8s ease-in-out; }
        .animate-slideUp { animation: slideUp 0.6s ease-out; }
        .animate-pulse-custom { animation: pulse 2s infinite; }
        .animate-bounce-custom { animation: bounce 2s infinite; }
        
        .bg-gradient-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #374151 0%, #6b7280 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .btn-hover:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
    </style>
    <script src="js/main.js"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-green-50 min-h-screen">
    <?php include 'includes/header.php'; ?>
    
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25px 25px, #3b82f6 2px, transparent 0), radial-gradient(circle at 75px 75px, #22c55e 2px, transparent 0); background-size: 100px 100px;"></div>
    </div>
    
    <div class="relative min-h-screen flex items-center justify-center py-12 px-4 font-sans">
        <div class="w-full max-w-lg mx-auto">
            <!-- Main Card -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-2xl border-0 animate-fadeIn transition-all duration-300 card-hover">
                <div class="p-8 text-center">
                    <!-- Enhanced Logo/Icon -->
                    <div class="relative mx-auto w-20 h-20 mb-8">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-green-500 rounded-full animate-pulse-custom opacity-75"></div>
                        <div class="relative w-full h-full bg-gradient-to-r from-blue-500 to-green-500 rounded-full flex items-center justify-center shadow-lg">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <!-- Floating elements -->
                        <div class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-bounce-custom"></div>
                        <div class="absolute -bottom-1 -left-2 w-3 h-3 bg-pink-400 rounded-full animate-pulse-custom"></div>
                    </div>
                    
                    <h1 class="text-4xl font-bold text-gradient mb-4">
                        Welcome to Pet Health Tracker
                    </h1>
                    <p class="text-gray-600 mb-8 leading-relaxed text-lg">
                        Your comprehensive companion for managing your pet's health, scheduling veterinary appointments, and keeping track of medical records with ease.
                    </p>
                    
                    <!-- Feature highlights -->
                    <div class="grid grid-cols-3 gap-4 mb-8">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2 transition-transform hover:scale-110">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-600 font-medium">Health Tracking</p>
                        </div>
                        <div class="text-center">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-2 transition-transform hover:scale-110">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-600 font-medium">Appointments</p>
                        </div>
                        <div class="text-center">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-2 transition-transform hover:scale-110">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-600 font-medium">Records</p>
                        </div>
                    </div>
                
                    <!-- Action Buttons -->
                    <div class="space-y-4 mb-8">
                        <a href="login.php" class="inline-block w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-4 px-6 rounded-lg text-lg font-semibold transition-all duration-200 btn-hover shadow-lg hover:shadow-xl">
                            <div class="flex items-center justify-center gap-3">
                                <div class="w-5 h-5 bg-white/20 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                </div>
                                Sign In to Your Account
                            </div>
                        </a>
                        <a href="register.php" class="inline-block w-full border-2 border-blue-500 text-blue-500 py-4 px-6 rounded-lg text-lg font-semibold transition-all duration-200 btn-hover hover:bg-gradient-to-r hover:from-blue-500 hover:to-green-500 hover:text-white hover:border-transparent">
                            <div class="flex items-center justify-center gap-3">
                                <div class="w-5 h-5 border border-current rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                </div>
                                Create New Account
                            </div>
                        </a>
                    </div>
                    
                    <!-- Trust Indicators -->
                    <div class="border-t border-gray-200 pt-8">
                        <p class="text-sm text-gray-500 mb-6 font-medium">Trusted by pet owners and veterinarians worldwide</p>
                        <div class="grid grid-cols-3 gap-4 text-xs">
                            <div class="flex flex-col items-center space-y-2">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center transition-transform hover:scale-110">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-600 font-medium text-center">Secure & Private</span>
                            </div>
                            <div class="flex flex-col items-center space-y-2">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center transition-transform hover:scale-110">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V8z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-600 font-medium text-center">Easy to Use</span>
                            </div>
                            <div class="flex flex-col items-center space-y-2">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center transition-transform hover:scale-110">
                                    <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-600 font-medium text-center">24/7 Access</span>
                            </div>
                        </div>
                        
                        <!-- Stats -->
                        <div class="mt-8 pt-6 border-t border-gray-100">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <div class="text-2xl font-bold text-blue-600">1,000+</div>
                                    <div class="text-xs text-gray-500">Happy Pets</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-green-600">50+</div>
                                    <div class="text-xs text-gray-500">Veterinarians</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-purple-600">24/7</div>
                                    <div class="text-xs text-gray-500">Support</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Features Section -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 transition-all duration-300 card-hover">
                    <div class="p-4 text-center">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-blue-500 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-800 mb-2">For Pet Owners</h3>
                        <p class="text-sm text-gray-600">Track your pet's health, schedule appointments, and maintain medical records.</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 transition-all duration-300 card-hover">
                    <div class="p-4 text-center">
                        <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-green-500 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-800 mb-2">For Veterinarians</h3>
                        <p class="text-sm text-gray-600">Manage appointments, access patient records, and provide better care.</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 transition-all duration-300 card-hover">
                    <div class="p-4 text-center">
                        <div class="w-10 h-10 bg-gradient-to-r from-purple-400 to-purple-500 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-800 mb-2">Secure Platform</h3>
                        <p class="text-sm text-gray-600">HIPAA-compliant security ensuring your pet's data is always protected.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>