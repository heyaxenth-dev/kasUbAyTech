<?php 
// connection to database
$host = "localhost";
$db = "kasubaytech_db";
$root = "root";
$password = "";

$conn = mysqli_connect($host, $root, $password, $db);

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
} 
?>