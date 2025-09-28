<?php 
include '../database/config.php';

if (isset($_POST['register']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
   
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
   $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
   $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);

   $stmt = $conn->prepare("INSERT INTO client (firstname, middlename, lastname) VALUES (?, ?, ?)");
   $stmt->bind_param("sss", $first_name, $middle_name, $last_name);

   if ($stmt->execute()) {

        $lastid = $stmt->insert_id;
       
       header("Location: assessment.php?id=$lastid");
       exit();
   } else {
       echo "Error: " . $sql . "<br>" . mysqli_error($conn);
   }

    $stmt->close();
    $conn->close();
}
?>