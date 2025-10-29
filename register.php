<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_name = $_POST['event_name'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Get event id from events table
    $stmt = $conn->prepare("SELECT id FROM events WHERE name=?");
    $stmt->bind_param("s", $event_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    $event_id = $event['id'] ?? 0;

    // Insert registration
    $stmt = $conn->prepare("INSERT INTO registrations (event_id, name, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $event_id, $name, $email, $phone);
    $stmt->execute();
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
        <p class="mb-2"><strong>Event:</strong> <?php echo $event_name; ?></p>
        <p class="mb-2"><strong>Name:</strong> <?php echo $name; ?></p>
        <p class="mb-2"><strong>Email:</strong> <?php echo $email; ?></p>
        <p class="mb-4"><strong>Phone:</strong> <?php echo $phone; ?></p>
        <a href="index.php" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">Back to Events</a>
    </div>
</body>
</html>
