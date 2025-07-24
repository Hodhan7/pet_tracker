<?php
session_start();
$conn = new mysqli('localhost', 'root', 'stoicdavi', 'pet_health_tracker');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>