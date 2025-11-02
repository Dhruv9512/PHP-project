<?php
session_start();
include 'db.php';
require __DIR__ . '/vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Generates a full HTML email template.
 *
 * @param array $data An associative array with keys like:
 * 'username' => The user's name.
 * 'subject'  => The email subject (used in <title>).
 * 'body'     => The main HTML content for the email.
 * 'cta_text' => (Optional) Text for a button.
 * 'cta_url'  => (Optional) URL for the button.
 */
function getEmailTemplate(array $data): string {
    $username = htmlspecialchars($data['username'] ?? 'User');
    $subject = htmlspecialchars($data['subject'] ?? 'A message from Event Portal');
    $body = $data['body'] ?? '<p>This is a default message.</p>';
    $cta_text = htmlspecialchars($data['cta_text'] ?? '');
    $cta_url = htmlspecialchars($data['cta_url'] ?? '#');

    $buttonHtml = '';
    if (!empty($cta_text)) {
        $buttonHtml = '
            <tr>
                <td style="padding: 20px 0 30px 0; text-align: center;">
                    <a href="' . $cta_url . '" style="background-color: #6B21A8; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                        ' . $cta_text . '
                    </a>
                </td>
            </tr>
        ';
    }

    return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $subject . '</title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; }
        .content { padding: 30px; }
        .header { background-color: #6B21A8; color: #ffffff; padding: 20px; text-align: center; }
        .footer { background-color: #f4f4f4; color: #777; padding: 20px; text-align: center; font-size: 12px; }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif;">
    <table class="container" border="0" cellpadding="0" cellspacing="0" width="100%" style="width: 100%; max-width: 600px; margin: 0 auto;">
        <!-- Header -->
        <tr>
            <td class="header" style="background-color: #6B21A8; color: #ffffff; padding: 20px; text-align: center;">
                <h1 style="margin: 0; font-size: 24px;">Event Portal</h1>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td class="content" style="padding: 30px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="font-size: 16px; line-height: 1.6;">
                            <p style="margin-bottom: 20px;">Hi ' . $username . ',</p>
                            ' . $body . '
                        </td>
                    </tr>
                    ' . $buttonHtml . '
                </table>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td class="footer" style="background-color: #f4f4f4; color: #777; padding: 20px; text-align: center; font-size: 12px;">
                <p style="margin: 5px 0;">&copy; ' . date('Y') . ' Event Portal. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>';
}


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
                        
                        // --- Create the content for the template ---
                        $emailBody = '
                            <p>Thank you for signing up for Event Portal. Please use the code below to verify your email address.</p>
                            <p style="text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 3px; background-color: #f4f4f4; padding: 15px; border-radius: 5px; margin: 20px 0;">
                                ' . $otp . '
                            </p>
                            <p>This code will expire in 5 minutes.</p>
                        ';
                        
                        $templateData = [
                            'username' => $username,
                            'subject'  => 'Your OTP for Email Verification',
                            'body'     => $emailBody,
                        ];

                        // --- Set the mail body using the template function ---
                        $mail->Body = getEmailTemplate($templateData); // This line will no longer cause an error

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
