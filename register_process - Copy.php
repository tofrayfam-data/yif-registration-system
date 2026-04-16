<?php
// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "yif_db";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get and trim form values
    $fullname       = trim($_POST['fullname'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $mobile         = trim($_POST['mobile'] ?? '');
    $marital        = trim($_POST['marital'] ?? '');
    $occupation     = trim($_POST['occupation'] ?? '');
    $contribution   = trim($_POST['contribution'] ?? '');
    $password       = $_POST['password'] ?? '';
    $confirmpassword= $_POST['confirmpassword'] ?? '';

    // Password confirmation
    if ($password !== $confirmpassword) {
        echo json_encode([
            "status" => "error",
            "field" => "password",
            "message" => "Passwords do not match"
        ]);
        exit();
    }

    // Check for duplicate email using prepared statement
    $check = $conn->prepare("SELECT id FROM members WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode([
            "status" => "error",
            "field" => "email",
            "message" => "Email already registered"
        ]);
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert member into database
    $stmt = $conn->prepare("INSERT INTO members (fullname, email, mobile, marital, occupation, password, contribution) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $fullname, $email, $mobile, $marital, $occupation, $hashed_password, $contribution);

    if ($stmt->execute()) {

        // Send welcome email
        $to      = $email;
        $subject = "Welcome to Yoruba Indigenes Foundation";
        $message = "Dear $fullname,\n\nYour registration has been received successfully.\n\nRegards,\nYIF Team";
        $headers = "From: noreply@yifworldwide.org\r\n";

        @mail($to, $subject, $message, $headers);

        // Return success JSON
        echo json_encode([
            "status" => "success",
            "message" => "Registration successful!"
        ]);

    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Registration failed. Please try again."
        ]);
    }

    $stmt->close();
    $check->close();
}

$conn->close();
?>