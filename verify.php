<?php
session_start();
include 'db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');
    $user_id = $_SESSION['pending_user_id'] ?? null;

    if (!$user_id) {
        $error = "Session expired. Please sign up again.";
    } elseif (empty($otp)) {
        $error = "OTP is required.";
    } else {
        $stmt = $conn->prepare("SELECT otp_hash, expires_at FROM email_verifications WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if (new DateTime() > new DateTime($row['expires_at'])) {
                $error = "OTP expired. Please request a new one.";
            } elseif (password_verify($otp, $row['otp_hash'])) {
                // Remove OTP
                $conn->prepare("DELETE FROM email_verifications WHERE user_id = $user_id")->execute();

                // Mark as verified
                $update = $conn->prepare("UPDATE users SET verified = 1 WHERE id = ?");
                $update->bind_param("i", $user_id);
                $update->execute();
                $update->close();

                // Fetch username
                $stmtUser = $conn->prepare("SELECT username FROM users WHERE id = ?");
                $stmtUser->bind_param("i", $user_id);
                $stmtUser->execute();
                $resultUser = $stmtUser->get_result();
                $userData = $resultUser->fetch_assoc();
                $stmtUser->close();

                // Set session for logged in user
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $userData['username'];
                $_SESSION['logged_in'] = true;

                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid OTP.";
            }
        } else {
            $error = "No OTP found. Please sign up again.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-4">OTP Verification</h2>
        <?php if ($success): ?>
            <div class="bg-green-100 p-3 rounded mb-4"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="bg-red-100 p-3 rounded mb-4"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" class="space-y-3">
            <input type="text" name="otp" placeholder="Enter OTP" class="w-full p-2 border rounded" required>
            <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded">Verify</button>
        </form>
    </div>
</body>
</html>