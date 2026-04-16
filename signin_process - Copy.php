<?php
session_start();

// Connect to DB
$conn = new mysqli("localhost", "your_username", "your_password", "your_database_name");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form inputs
$email = $_POST['email'];
$password = $_POST['password'];

// Prepare statement
$stmt = $conn->prepare("SELECT id, fullname, password, is_admin FROM members WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $fullname, $hashed_password, $is_admin);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['email'] = $email;
        $_SESSION['fullname'] = $fullname;
        $_SESSION['is_admin'] = $is_admin;

        // Update last login time
        $update = $conn->prepare("UPDATE members SET last_login = NOW() WHERE id = ?");
        $update->bind_param("i", $id);
        $update->execute();
        $update->close();

        header("Location: dashboard.php");
        exit();
    } else {
        echo "<span style='color:red;'>Incorrect password!</span>";
    }
} else {
    echo "<span style='color:red;'>User not found!</span>";
}

$stmt->close();
$conn->close();
?>