<?php
require_once 'db.php';

// Test data for health record insertion
$test_data = [
    'pet_id' => 1, // Buddy
    'veterinarian_id' => 2, // Dr. Smith
    'record_date' => '2024-12-30',
    'record_type' => 'checkup',
    'title' => 'Test Health Record',
    'description' => 'This is a test health record to verify database insertion',
    'diagnosis' => 'Test diagnosis',
    'treatment' => 'Test treatment',
    'follow_up_required' => 1,
    'follow_up_date' => '2025-01-15',
    'weight' => 29.0,
    'temperature' => 38.5
];

echo "Testing health record insertion...\n";

$insert_hr_query = "
    INSERT INTO health_records (pet_id, veterinarian_id, record_date, record_type, title, description, diagnosis, treatment, follow_up_required, follow_up_date, weight_at_visit, temperature, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
";

$insert_hr_stmt = $conn->prepare($insert_hr_query);

if (!$insert_hr_stmt) {
    echo "Error preparing statement: " . $conn->error . "\n";
    exit(1);
}

echo "Binding parameters...\n";
// 12 parameters: pet_id(i), veterinarian_id(i), record_date(s), record_type(s), title(s), description(s), diagnosis(s), treatment(s), follow_up_required(i), follow_up_date(s), weight_at_visit(d), temperature(d)
$result = $insert_hr_stmt->bind_param(
    "iisssssissdd", 
    $test_data['pet_id'], 
    $test_data['veterinarian_id'], 
    $test_data['record_date'], 
    $test_data['record_type'], 
    $test_data['title'], 
    $test_data['description'], 
    $test_data['diagnosis'], 
    $test_data['treatment'], 
    $test_data['follow_up_required'], 
    $test_data['follow_up_date'], 
    $test_data['weight'], 
    $test_data['temperature']
);

if (!$result) {
    echo "Error binding parameters: " . $insert_hr_stmt->error . "\n";
    exit(1);
}

echo "Executing query...\n";
if ($insert_hr_stmt->execute()) {
    $new_id = $conn->insert_id;
    echo "SUCCESS: Health record added with ID: $new_id\n";
    
    // Verify the record was inserted
    $verify_query = "SELECT * FROM health_records WHERE id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param("i", $new_id);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    $record = $result->fetch_assoc();
    
    echo "Verification:\n";
    print_r($record);
    
} else {
    echo "ERROR: Failed to add health record: " . $insert_hr_stmt->error . "\n";
    echo "MySQL Error: " . $conn->error . "\n";
}

$insert_hr_stmt->close();
$conn->close();
?>
