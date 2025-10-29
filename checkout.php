<?php
session_start();
include 'db.php';
include 'razorpay_config.php';
require __DIR__ . '/vendor/autoload.php';

use Razorpay\Api\Api;

// 1. Get Form Data
$sub_event_id = $_POST['sub_event_id'] ?? 0;
$name = $_POST['name'] ?? 'Guest';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';

if (!$sub_event_id || !$email) {
    die("Missing event ID or email.");
}

// 2. Fetch Event Details from DB
$stmt = $conn->prepare("SELECT name, price FROM sub_events WHERE id = ?");
$stmt->bind_param("i", $sub_event_id);
$stmt->execute();
$result = $stmt->get_result();
$sub_event = $result->fetch_assoc();
$stmt->close();

if (!$sub_event || $sub_event['price'] <= 0) {
    die("Invalid event or event is free.");
}

$price_in_paise = $sub_event['price'] * 100; // e.g., ₹500 -> 50000 paise
$event_name = $sub_event['name'];

// 3. Create Razorpay API client
$api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

// 4. Create Razorpay Order
try {
    $orderData = [
        'receipt'         => 'rcpt_' . time(),
        'amount'          => $price_in_paise, // amount in paisa
        'currency'        => 'INR',
        'payment_capture' => 1 // Auto-capture payment
    ];
    $razorpayOrder = $api->order->create($orderData);
    $razorpayOrderId = $razorpayOrder['id'];

    // 5. Store data in session to use in verify_payment.php
    $_SESSION['razorpay_order_id'] = $razorpayOrderId;
    $_SESSION['sub_event_id'] = $sub_event_id;
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['phone'] = $phone;
    $_SESSION['user_id'] = $_SESSION['user_id'] ?? null; // Store user_id for verification

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proceeding to Payment...</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body class="bg-gray-100">
    <div style="font-family: sans-serif; text-align: center; margin-top: 100px;">
        <h1 style="font-size: 24px;">Please wait, redirecting to payment...</h1>
        <p>Do not refresh or press back.</p>
    </div>

    <script>
    var options = {
        "key": "<?php echo RAZORPAY_KEY_ID; ?>",
        "amount": "<?php echo $price_in_paise; ?>",
        "currency": "INR",
        "name": "<?php echo htmlspecialchars($event_name); ?>",
        "description": "Event Registration",
        "order_id": "<?php echo $razorpayOrderId; ?>",
        
        "handler": function (response){
            document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
            document.getElementById('razorpay_signature').value = response.razorpay_signature;
            document.getElementById('payment-form').submit();
        },
        "prefill": {
            "name": "<?php echo htmlspecialchars($name); ?>",
            "email": "<?php echo htmlspecialchars($email); ?>",
            "contact": "<?php echo htmlspecialchars($phone); ?>"
        },
        "theme": {
            "color": "#6B21A8" // Purple
        },
        "modal": {
            "ondismiss": function(){
                window.location.href = 'cancel.php'; 
            }
        },

        // --- HIDE ALL METHODS EXCEPT UPI ---
        "method": {
            "upi": true,      // Show UPI
            "card": false,    // Hide Card
            "netbanking": false, // Hide Netbanking
            "wallet": false,  // Hide Wallets
            "upi_intent": true // Prioritize GPay/PhonePe
        },

        // --- NEW CONFIG: HIDE EMI & PAYLATER ---
        "config": {
          "display": {
            "hide": [
              { "method": "paylater" },
              { "method": "emi" }
            ],
            "preferences": {
              "show_email": false, // Don't ask for email on UPI page
              "show_contact": false // Don't ask for contact on UPI page
            }
          }
        }
        // ------------------------------------------
    };
    var rzp = new Razorpay(options);
    
    // 4. Automatically open the payment popup
    rzp.open();
    </script>

    <form id="payment-form" action="verify_payment.php" method="POST">
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
        <input type="hidden" name="razorpay_signature"  id="razorpay_signature">
    </form>

</body>
</html>