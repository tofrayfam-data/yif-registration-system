<?php
$conn = new mysqli("localhost", "your_username", "your_password", "your_database_name");

$token = $_POST['token'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if ($password !== $confirm_password) {
    die("<span style='color:red;'>Passwords do not match.</span>");
}

// Check token
$stmt = $conn->prepare("SELECT email FROM members WHERE reset_token=? AND reset_token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("<span style='color:red;'>Invalid or expired reset link.</span>");
}

// Update password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$update = $conn->prepare("UPDATE members 
    SET password=?, reset_token=NULL, reset_token_expiry=NULL 
    WHERE reset_token=?");

$update->bind_param("ss", $hashed_password, $token);

if ($update->execute()) {
    echo "<span style='color:green;'>Password reset successful. <a href='signin.html'>Login</a></span>";
} else {
    echo "<span style='color:red;'>Password reset failed.</span>";
}

$update->close();
$conn->close();
?>