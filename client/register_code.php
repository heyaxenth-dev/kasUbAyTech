<?php
/**
 * Registration Handler
 * 
 * Handles student registration and redirects to disclosure page
 */

include '../database/config.php';

if (isset($_POST['register']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $middle_name = $conn->real_escape_string($_POST['middle_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);

    // Insert new client
    $stmt = $conn->prepare("INSERT INTO client (firstname, middlename, lastname) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $first_name, $middle_name, $last_name);

    if ($stmt->execute()) {
        $lastid = $stmt->insert_id;
        $stmt->close();
        
        // Redirect to disclosure page
        header("Location: disclosure.php?id=$lastid");
        exit();
    } else {
        $stmt->close();
        die("Error: Failed to register. Please try again.");
    }
} else {
    // Invalid request
    header("Location: ../register.php");
    exit();
}

$conn->close();
?>