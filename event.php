<?php
session_start();

// Redirect to login.php if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$event_id = $_GET['id'] ?? 0;
$events = [
    1 => ["name" => "Tech Conference 2025", "date" => "2025-09-01", "venue" => "Auditorium"],
    2 => ["name" => "Music Festival", "date" => "2025-10-10", "venue" => "Open Ground"],
    3 => ["name" => "Art Exhibition", "date" => "2025-11-15", "venue" => "Art Gallery"]
];
$event = $events[$event_id] ?? ["name" => "Unknown Event", "date" => "N/A", "venue" => "N/A"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($event['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 p-6">
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($event['name']); ?></h1>
        <p class="mb-1"><strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
        <p class="mb-4"><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>

        <h2 class="text-2xl font-semibold mb-2">Register for this Event</h2>
        <form action="register.php" method="POST" class="space-y-3">
            <input type="hidden" name="event_name" value="<?php echo htmlspecialchars($event['name']); ?>">
            <input type="text" name="name" placeholder="Your Name" class="w-full p-2 border rounded" required>
            <input type="email" name="email" placeholder="Your Email" class="w-full p-2 border rounded" required>
            <input type="text" name="phone" placeholder="Phone Number" class="w-full p-2 border rounded">
            <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">Register</button>
        </form>
    </div>
</body>
</html>
