<?php
$conn = new mysqli("localhost", "your_username", "your_password", "your_database_name");

if (!isset($_GET['token'])) {
    die("NO TOKEN PROVIDED");
}

$token = $_GET['token'];

$stmt = $conn->prepare("SELECT email FROM members WHERE reset_token=? AND reset_token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Invalid or expired reset link.");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>
</head>
<body>

<h2>Reset Your Password</h2>

<form method="POST" action="reset_password_process.php">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    
    <input type="password" name="password" placeholder="New Password" required><br><br>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required><br><br>

    <button type="submit">Reset Password</button>
</form>

</body>
</html>