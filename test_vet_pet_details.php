<?php
// Test vet_pet_details.php
session_start();

// Set up session for veterinarian
$_SESSION['user_id'] = 2; // Dr. Smith
$_SESSION['role'] = 'veterinarian';
$_SESSION['first_name'] = 'Dr. Smith';

// Set GET parameter
$_GET['pet_id'] = 1; // First pet

echo "Testing vet_pet_details.php with pet ID: 1\n";

try {
    // Capture output and errors
    ob_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    include 'vet_pet_details.php';
    
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "Page rendered successfully!\n";
    echo "Output length: " . strlen($output) . " characters\n";
    
    // Check for errors
    if (strpos($output, 'Fatal error') !== false) {
        echo "FATAL ERROR found!\n";
        $start = strpos($output, 'Fatal error');
        echo substr($output, $start, 200) . "\n";
    }
    if (strpos($output, 'Warning') !== false) {
        echo "WARNING found!\n";
    }
    if (strpos($output, 'Notice') !== false) {
        echo "NOTICE found!\n";
    }
    
    // Check if pet name is found
    if (strpos($output, 'Buddy') !== false) {
        echo "Pet 'Buddy' found in output - good!\n";
    } else {
        echo "Pet 'Buddy' NOT found in output - issue!\n";
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
