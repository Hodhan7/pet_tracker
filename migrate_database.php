<?php
/**
 * Database Migration Script for User Settings
 * Adds support for user preferences and settings
 */

require 'db.php';

echo "<h2>Pet Health Tracker - Database Migration</h2>";
echo "<p>Adding user settings and preferences support...</p>";

// Check if migration is needed
$check_query = "SHOW COLUMNS FROM users LIKE 'notification_preferences'";
$result = $conn->query($check_query);

if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Database already has user settings fields.</p>";
} else {
    echo "<p>Adding new fields to users table...</p>";
    
    try {
        // Add bio field if not exists
        $conn->query("ALTER TABLE users ADD COLUMN bio TEXT AFTER zip_code");
        echo "<p style='color: green;'>✓ Added bio field</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            echo "<p style='color: red;'>Error adding bio field: " . $e->getMessage() . "</p>";
        }
    }

    try {
        // Add notification_preferences field
        $conn->query("ALTER TABLE users ADD COLUMN notification_preferences JSON AFTER bio");
        echo "<p style='color: green;'>✓ Added notification_preferences field</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            echo "<p style='color: red;'>Error adding notification_preferences field: " . $e->getMessage() . "</p>";
        }
    }

    try {
        // Add privacy_settings field
        $conn->query("ALTER TABLE users ADD COLUMN privacy_settings JSON AFTER notification_preferences");
        echo "<p style='color: green;'>✓ Added privacy_settings field</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            echo "<p style='color: red;'>Error adding privacy_settings field: " . $e->getMessage() . "</p>";
        }
    }

    try {
        // Add deletion fields
        $conn->query("ALTER TABLE users ADD COLUMN deletion_requested BOOLEAN DEFAULT FALSE AFTER privacy_settings");
        $conn->query("ALTER TABLE users ADD COLUMN deletion_reason TEXT AFTER deletion_requested");
        $conn->query("ALTER TABLE users ADD COLUMN deletion_requested_at TIMESTAMP NULL AFTER deletion_reason");
        echo "<p style='color: green;'>✓ Added account deletion fields</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            echo "<p style='color: red;'>Error adding deletion fields: " . $e->getMessage() . "</p>";
        }
    }
}

// Set default preferences for existing users
echo "<p>Setting default preferences for existing users...</p>";

$default_notifications = json_encode([
    'email_notifications' => true,
    'sms_notifications' => false,
    'appointment_reminders' => true,
    'vaccination_reminders' => true,
    'health_updates' => true,
    'newsletter' => false
]);

$default_privacy = json_encode([
    'profile_visibility' => 'private',
    'data_sharing' => false,
    'analytics' => true
]);

$update_query = "UPDATE users SET 
    notification_preferences = COALESCE(notification_preferences, ?),
    privacy_settings = COALESCE(privacy_settings, ?)
    WHERE notification_preferences IS NULL OR privacy_settings IS NULL";

$stmt = $conn->prepare($update_query);
$stmt->bind_param("ss", $default_notifications, $default_privacy);

if ($stmt->execute()) {
    $affected_rows = $stmt->affected_rows;
    echo "<p style='color: green;'>✓ Updated $affected_rows users with default preferences</p>";
} else {
    echo "<p style='color: red;'>Error setting default preferences: " . $conn->error . "</p>";
}

// Add indexes for better performance
echo "<p>Adding database indexes for better performance...</p>";

$indexes = [
    "CREATE INDEX IF NOT EXISTS idx_users_deletion_requested ON users(deletion_requested)",
    "CREATE INDEX IF NOT EXISTS idx_users_last_login ON users(last_login)",
    "CREATE INDEX IF NOT EXISTS idx_users_role_active ON users(role, is_active)"
];

foreach ($indexes as $index_query) {
    try {
        $conn->query($index_query);
        echo "<p style='color: green;'>✓ Added database index</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>Index may already exist: " . $e->getMessage() . "</p>";
    }
}

// Show final table structure
echo "<h3>Final Users Table Structure:</h3>";
$columns_result = $conn->query("SHOW COLUMNS FROM users");
echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
echo "<tr><th style='padding: 5px; background: #f0f0f0;'>Field</th><th style='padding: 5px; background: #f0f0f0;'>Type</th><th style='padding: 5px; background: #f0f0f0;'>Null</th><th style='padding: 5px; background: #f0f0f0;'>Default</th></tr>";

while ($column = $columns_result->fetch_assoc()) {
    echo "<tr>";
    echo "<td style='padding: 5px;'>" . htmlspecialchars($column['Field']) . "</td>";
    echo "<td style='padding: 5px;'>" . htmlspecialchars($column['Type']) . "</td>";
    echo "<td style='padding: 5px;'>" . htmlspecialchars($column['Null']) . "</td>";
    echo "<td style='padding: 5px;'>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<p style='color: green; font-weight: bold;'>✓ Migration completed successfully!</p>";
echo "<p><a href='profile.php'>Go to Profile Page</a> | <a href='settings.php'>Go to Settings Page</a></p>";

$conn->close();
?>
