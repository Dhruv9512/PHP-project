<?php
session_start();
include 'db.php';
include 'razorpay_config.php';
require __DIR__ . '/vendor/autoload.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError; // Correct 'use' statement

$success = false;
$registration_id = 0; // For certificate
$user_id = $_SESSION['user_id'] ?? null; // Get the logged-in user

// 1. Get data from Razorpay POST
$razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
$razorpay_signature = $_POST['razorpay_signature'] ?? '';

// 2. Get data from our Session
$razorpay_order_id = $_SESSION['razorpay_order_id'] ?? '';
$sub_event_id = $_SESSION['sub_event_id'] ?? 0;
$name = $_SESSION['name'] ?? '';
$email = $_SESSION['email'] ?? '';
$phone = $_SESSION['phone'] ?? '';
$price = 0;

$error = "Payment failed. Please try again.";

if (empty($razorpay_payment_id) || empty($razorpay_signature) || empty($razorpay_order_id)) {
    $error = "Payment details are missing.";
} elseif (!$user_id) { // Check if user is logged in
    $error = "Your session expired. Payment was taken but registration failed. Please contact support.";
} else {
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

    try {
        // 3. --- CRITICAL: Verify the Signature ---
        $attributes = [
            'razorpay_order_id' => $razorpay_order_id,
            'razorpay_payment_id' => $razorpay_payment_id,
            'razorpay_signature' => $razorpay_signature
        ];
        $api->utility->verifyPaymentSignature($attributes);
        
        // 4. If signature is OK, save to DB
        $success = true;

        // Fetch event name and price for display
        $stmt = $conn->prepare("SELECT name, price FROM sub_events WHERE id = ?");
        $stmt->bind_param("i", $sub_event_id);
        $stmt->execute();
        $sub_event = $stmt->get_result()->fetch_assoc();
        $sub_event_name = $sub_event['name'];
        $price = $sub_event['price'];
        $stmt->close();
        
        // 5. --- SAVE REGISTRATION (with user_id) ---
        $stmt_insert = $conn->prepare("INSERT INTO registrations (sub_event_id, user_id, name, email, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("iisss", $sub_event_id, $user_id, $name, $email, $phone);
        $stmt_insert->execute();
        $registration_id = $conn->insert_id; // Get ID for certificate
        $stmt_insert->close();

    } catch(SignatureVerificationError $e) { // This class is now correctly imported
        $success = false;
        $error = 'Razorpay Error: ' . $e->getMessage();
    }
}

// 6. Clear session data
unset($_SESSION['razorpay_order_id'], $_SESSION['sub_event_id'], $_SESSION['name'], $_SESSION['email'], $_SESSION['phone']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 p-6">
    <div class="max-w-xl mx-auto bg-white p-10 rounded-lg shadow-md text-center">
        <?php if ($success): ?>
            <h1 class="text-3xl font-bold mb-4 text-green-600">Payment Successful!</h1>
            <h2 class="text-2xl font-semibold mb-4">Thank you for registering.</h2>
            
            <p class="mb-2"><strong>Event:</strong> <?php echo htmlspecialchars($sub_event_name); ?></p>
            <p class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
            <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p class="mb-4 font-bold"><strong>Amount Paid:</strong> <?php echo '₹' . number_format($price); ?></p>
            
            <p class="text-gray-600 mb-6">You will receive a confirmation email shortly.</p>

            <?php if ($registration_id > 0): ?>
                <p class="mt-6 font-semibold">Download your affiliation certificate:</p>
                <a href="generate_certificate.php?id=<?php echo $registration_id; ?>" 
                   target="_blank" 
                   class="inline-block mt-2 bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Download Certificate
                </a>
            <?php endif; ?>

        <?php else: ?>
            <h1 class="text-3xl font-bold mb-4 text-red-600">Payment Failed</h1>
            <p class="text-gray-700 mb-6"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <a href="index.php" class="block mt-6 text-purple-600 hover:underline">
            Back to All Events
        </a>
    </div>
</body>
</html>