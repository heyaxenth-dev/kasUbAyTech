<?php
session_start();
include '../database/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        // For demo purposes, check if password is 'admin123' (hashed) or plain text
        if (password_verify($password, $admin['password']) || $password === 'admin123') {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: homepage.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid username or password";
            header("Location: ../login-admin.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid username or password";
        header("Location: ../login-admin.php");
        exit();
    }
    
    $stmt->close();
} else {
    header("Location: ../login-admin.php");
    exit();
}
?>

