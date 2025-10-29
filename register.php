<?php
session_start();
include 'db.php';

$sub_event_id = 0;
$sub_event_name = 'Unknown Event';
$name = '';
$email = '';
$phone = '';
$price = 0.00;
$registration_id = 0; // For certificate
$user_id = $_SESSION['user_id'] ?? null; // Get the logged-in user

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user_id) {
    $sub_event_id = $_POST['sub_event_id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $price = $_POST['price'] ?? 0.00;

    // 1. Get sub-event name
    $stmt = $conn->prepare("SELECT name FROM sub_events WHERE id = ?");
    $stmt->bind_param("i", $sub_event_id);
    $stmt->execute();
    $sub_event = $stmt->get_result()->fetch_assoc();
    $sub_event_name = $sub_event['name'];
    $stmt->close();

    // 2. Insert registration with USER ID
    if ($sub_event_id > 0) {
        $stmt_insert = $conn->prepare("INSERT INTO registrations (sub_event_id, user_id, name, email, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("iisss", $sub_event_id, $user_id, $name, $email, $phone);
        $stmt_insert->execute();
        $registration_id = $conn->insert_id; // Get the new ID
        $stmt_insert->close();
    }
} elseif (!$user_id) {
    // Handle error if user is not logged in
    die("You must be logged in to register.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Success</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 p-6">
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow text-center">
        <h1 class="text-3xl font-bold mb-4 text-green-600">Registration Successful!</h1>
        <p class="mb-2"><strong>Event:</strong> <?php echo htmlspecialchars($sub_event_name); ?></p>
        <p class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
        <p class="mb-2"><strong>Price:</strong> Free</p>

        <?php if ($registration_id > 0): ?>
            <p class="mt-6">Your registration is confirmed. Download your certificate:</p>
            <a href="generate_certificate.php?id=<?php echo $registration_id; ?>" 
               target="_blank" 
               class="inline-block mt-2 bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Download Certificate
            </a>
        <?php endif; ?>
        
        <a href="index.php" class="block mt-6 text-purple-600 hover:underline">Back to All Events</a>
    </div>
</body>
</html>