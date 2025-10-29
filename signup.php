<?php
session_start();
include 'db.php';
require __DIR__ . '/vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check if username or email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, verified) VALUES (?, ?, ?, 0)");
            if ($stmt) {
                $stmt->bind_param("sss", $username, $email, $hashedPassword);
                if ($stmt->execute()) {
                    $userId = $stmt->insert_id;

                    // Save pending ID in session
                    $_SESSION['pending_user_id'] = $userId;

                    // Generate OTP
                    $otp = rand(100000, 999999);
                    $otpHash = password_hash($otp, PASSWORD_BCRYPT);
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

                    // Insert OTP into verification table
                    $otpStmt = $conn->prepare("INSERT INTO email_verifications (user_id, otp_hash, expires_at) VALUES (?, ?, ?)");
                    $otpStmt->bind_param("iss", $userId, $otpHash, $expiresAt);
                    $otpStmt->execute();
                    $otpStmt->close();

                    // Send OTP email
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'PixelClasses3@gmail.com';
                        $mail->Password   = 'xupb ogrp wesd eaxu'; // App password
                        $mail->SMTPSecure = 'tls';
                        $mail->Port       = 587;

                        $mail->setFrom('PixelClasses3@gmail.com', 'Event Portal');
                        $mail->addAddress($email, $username);
                        $mail->isHTML(true);
                        $mail->Subject = 'Your OTP for Email Verification';
                        $mail->Body    = "Hi $username,<br>Your OTP is: <b>$otp</b><br>Expires in 5 minutes.";

                        $mail->send();

                        // Redirect to OTP verification
                        header("Location: verify.php");
                        exit;
                    } catch (Exception $e) {
                        $error = "Account created, but OTP email could not be sent. Error: {$mail->ErrorInfo}";
                    }

                } else {
                    $error = "Error: " . $stmt->error;
                }
            } else {
                $error = "Database error: " . $conn->error;
            }
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-4">User Sign Up</h2>
        <?php if ($success): ?>
            <div class="bg-green-100 p-3 rounded mb-4"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="bg-red-100 p-3 rounded mb-4"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" class="space-y-3">
            <input type="text" name="username" placeholder="Username" class="w-full p-2 border rounded" required>
            <input type="email" name="email" placeholder="Email" class="w-full p-2 border rounded" required>
            <input type="password" name="password" placeholder="Password" class="w-full p-2 border rounded" required>
            <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded">Sign Up</button>
        </form>
    </div>
</body>
</html>