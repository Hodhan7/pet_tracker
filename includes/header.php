<?php
// Get current page for active navigation
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? null;
$user_name = $_SESSION['user_name'] ?? 'User';
?>

<header class="bg-white shadow-lg border-b border-gray-200 sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <!-- Main Navigation -->
        <div class="flex items-center justify-between h-16">
            <!-- Logo and Brand -->
            <div class="flex items-center space-x-4">
                <a href="index.php" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-green-500 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Pet Health Tracker</h1>
                        <p class="text-xs text-gray-500 hidden sm:block">Your Pet's Health Companion</p>
                    </div>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center space-x-8">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <!-- Guest Navigation -->
                    <a href="index.php" class="nav-link <?php echo $current_page === 'index.php' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?> transition-colors duration-200">
                        Home
                    </a>
                    <a href="about.php" class="nav-link <?php echo $current_page === 'about.php' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?> transition-colors duration-200">
                        About Us
                    </a>
                    <a href="features.php" class="nav-link <?php echo $current_page === 'features.php' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?> transition-colors duration-200">
                        Features
                    </a>
                    <a href="pricing.php" class="nav-link <?php echo $current_page === 'pricing.php' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?> transition-colors duration-200">
                        Pricing
                    </a>
                    <a href="contact.php" class="nav-link <?php echo $current_page === 'contact.php' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?> transition-colors duration-200">
                        Contact
                    </a>
                    <a href="faq.php" class="nav-link <?php echo $current_page === 'faq.php' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?> transition-colors duration-200">
                        FAQ
                    </a>
                <?php else: ?>
                    <!-- Authenticated Navigation -->
                    <?php if ($user_role === 'pet_owner'): ?>
                        <a href="owner_dashboard.php" class="nav-link <?php echo $current_page === 'owner_dashboard.php' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?> transition-colors duration-200">
                            Dashboard
                        </a>
                        <a href="my_pets.php" class="nav-link <?php echo $current_page === 'my_pets.php' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?> transition-colors duration-200">
                            My Pets
                        </a>
                        <a href="schedule_appointment.php" class="nav-link <?php echo $current_page === 'schedule_appointment.php' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?> transition-colors duration-200">
                            Appointments
                        </a>
                    <?php elseif ($user_role === 'veterinarian'): ?>
                        <a href="vet_dashboard.php" class="nav-link <?php echo $current_page === 'vet_dashboard.php' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?> transition-colors duration-200">
                            Dashboard
                        </a>
                        <a href="manage_appointments.php" class="nav-link <?php echo $current_page === 'manage_appointments.php' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?> transition-colors duration-200">
                            Appointments
                        </a>
                        <a href="patients.php" class="nav-link <?php echo $current_page === 'patients.php' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?> transition-colors duration-200">
                            Patients
                        </a>
                    <?php elseif ($user_role === 'admin'): ?>
                        <a href="admin_dashboard.php" class="nav-link <?php echo $current_page === 'admin_dashboard.php' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?> transition-colors duration-200">
                            Dashboard
                        </a>
                        <a href="manage_vet_application.php" class="nav-link <?php echo $current_page === 'manage_vet_application.php' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?> transition-colors duration-200">
                            Vet Applications
                        </a>
                        <a href="manage_users.php" class="nav-link <?php echo $current_page === 'manage_users.php' ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'; ?> transition-colors duration-200">
                            Manage Users
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>

            <!-- User Menu / Auth Buttons -->
            <div class="flex items-center space-x-4">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <!-- Guest Actions -->
                    <a href="login.php" class="text-gray-600 hover:text-blue-600 transition-colors duration-200 font-medium">
                        Sign In
                    </a>
                    <a href="register.php" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 font-medium shadow-md hover:shadow-lg">
                        Get Started
                    </a>
                <?php else: ?>
                    <!-- User Dropdown -->
                    <div class="relative" id="userDropdown">
                        <button class="flex items-center space-x-3 text-gray-700 hover:text-gray-900 transition-colors duration-200" id="userDropdownButton">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-green-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-semibold"><?php echo strtoupper(substr($user_name, 0, 1)); ?></span>
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-medium"><?php echo htmlspecialchars($user_name); ?></p>
                                <p class="text-xs text-gray-500 capitalize"><?php echo str_replace('_', ' ', $user_role); ?></p>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50 hidden" id="userDropdownMenu">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Profile
                            </a>
                            <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Settings
                            </a>
                            <hr class="my-2">
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Sign Out
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Mobile Menu Button -->
                <button class="md:hidden text-gray-600 hover:text-gray-900 transition-colors duration-200" id="mobileMenuButton">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="md:hidden hidden" id="mobileMenu">
            <div class="py-4 border-t border-gray-200">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="index.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Home</a>
                    <a href="about.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">About Us</a>
                    <a href="features.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Features</a>
                    <a href="pricing.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Pricing</a>
                    <a href="contact.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Contact</a>
                    <a href="faq.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">FAQ</a>
                    <div class="pt-4 border-t border-gray-200 mt-4">
                        <a href="login.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Sign In</a>
                        <a href="register.php" class="block py-2 text-blue-600 font-medium">Get Started</a>
                    </div>
                <?php else: ?>
                    <?php if ($user_role === 'pet_owner'): ?>
                        <a href="owner_dashboard.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Dashboard</a>
                        <a href="add_pet.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">My Pets</a>
                        <a href="schedule_appointment.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Appointments</a>
                        <a href="add_health_record.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Health Records</a>
                    <?php elseif ($user_role === 'veterinarian'): ?>
                        <a href="vet_dashboard.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Dashboard</a>
                        <a href="manage_appointments.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Appointments</a>
                        <a href="patients.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Patients</a>
                    <?php elseif ($user_role === 'admin'): ?>
                        <a href="admin_dashboard.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Dashboard</a>
                        <a href="manage_vet_application.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Vet Applications</a>
                        <a href="manage_users.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Manage Users</a>
                    <?php endif; ?>
                    <div class="pt-4 border-t border-gray-200 mt-4">
                        <a href="profile.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Profile</a>
                        <a href="settings.php" class="block py-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">Settings</a>
                        <a href="logout.php" class="block py-2 text-red-600 hover:text-red-700 transition-colors duration-200">Sign Out</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<script>
// User dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const userDropdownButton = document.getElementById('userDropdownButton');
    const userDropdownMenu = document.getElementById('userDropdownMenu');
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileMenu = document.getElementById('mobileMenu');

    // User dropdown toggle
    if (userDropdownButton && userDropdownMenu) {
        userDropdownButton.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdownMenu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            userDropdownMenu.classList.add('hidden');
        });
    }

    // Mobile menu toggle
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
});
</script>
