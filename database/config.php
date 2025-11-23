<?php
/**
 * Database Configuration
 * 
 * Provides database connection for the exam system
 * Uses mysqli object-oriented interface
 */

$host = "localhost";
$db = "kasubaytech_db";
$root = "root";
$password = "";

// Create connection using object-oriented mysqli
$conn = new mysqli($host, $root, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for proper Unicode support
$conn->set_charset("utf8mb4");
?>