<!DOCTYPE html>
<html>
<head>
    <title>Quick Vet Login Test</title>
</head>
<body>
    <h1>Quick Login for Veterinarian Testing</h1>
    
    <?php
    session_start();
    require 'db.php';
    
    if ($_POST['login'] ?? false) {
        $_SESSION['user_id'] = 2; // Dr. Smith
        $_SESSION['role'] = 'veterinarian';
        $_SESSION['first_name'] = 'Dr. Smith';
        $_SESSION['user_name'] = 'Dr. Smith Johnson';
        
        echo "<p>Logged in as veterinarian successfully!</p>";
        echo "<p><a href='vet_dashboard.php'>Go to Vet Dashboard</a></p>";
        echo "<p><a href='vet_pet_details.php?pet_id=1'>View Pet Details (Buddy)</a></p>";
    } else {
        echo '<form method="post">
                <button type="submit" name="login" value="1">Login as Veterinarian</button>
              </form>';
    }
    ?>
</body>
</html>
