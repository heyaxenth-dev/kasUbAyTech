<?php
/**
 * Database Configuration - CAT-lite Version
 * 
 * Provides database connection for the exam system
 * Uses mysqli object-oriented interface
 * 
 * Updated to use the new CAT-lite database structure:
 * - Database: kasubaytech_catlite_db
 * - Supports course_tag and category separation
 */

$host = "localhost";
// Use CAT-lite database for the new adaptive algorithm
$db = "kasubaytech_catlite_db";
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