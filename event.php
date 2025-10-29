<?php
session_start();
include 'db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$main_event_id = $_GET['id'] ?? 0;
$main_event = null;
$sub_events = [];

if ($main_event_id) {
    $stmt = $conn->prepare("SELECT name, description FROM main_events WHERE id = ?");
    $stmt->bind_param("i", $main_event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $main_event = $result->fetch_assoc();
    }
    $stmt->close();

    if ($main_event) {
        $stmt_sub = $conn->prepare("SELECT id, name, date, venue, price FROM sub_events WHERE main_event_id = ? ORDER BY date ASC");
        $stmt_sub->bind_param("i", $main_event_id);
        $stmt_sub->execute();
        $result_sub = $stmt_sub->get_result();
        $sub_events = $result_sub->fetch_all(MYSQLI_ASSOC);
        $stmt_sub->close();
    }
}

if (!$main_event) {
    die("Event not found.");
}

// Redirect logic for simple events
if (count($sub_events) === 1) {
    header("Location: sub_event.php?id=" . $sub_events[0]['id']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($main_event['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <nav class="bg-purple-600 text-white shadow-md">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold">Event Portal</a>
            <div class="flex space-x-4">
                <a href="index.php" class="py-2 px-4 hover:bg-purple-700 rounded">&larr; Back to Events</a>
                <a href="logout.php" class="py-2 px-4 bg-purple-700 hover:bg-purple-800 rounded">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto p-6">
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h1 class="text-4xl font-bold mb-2"><?php echo htmlspecialchars($main_event['name']); ?></h1>
            <p class="text-gray-700 text-lg"><?php echo htmlspecialchars($main_event['description']); ?></p>
        </div>

        <h2 class="text-3xl font-bold mb-6 text-gray-900">Available Sessions</h2>

        <div class="space-y-4">
            <?php if (!empty($sub_events)): ?>
                <?php foreach ($sub_events as $sub): ?>
                    <div class="bg-white p-6 rounded-lg shadow-md flex flex-col md:flex-row items-start md:items-center justify-between">
                        <div>
                            <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($sub['name']); ?></h3>
                            <p class="text-gray-600"><strong>When:</strong> <?php echo htmlspecialchars(date('M j, Y - g:ia', strtotime($sub['date']))); ?></p>
                            <p class="text-gray-600"><strong>Where:</strong> <?php echo htmlspecialchars($sub['venue']); ?></p>
                        </div>
                        <div class="mt-4 md:mt-0 md:text-right">
                            <p class="text-2xl font-bold text-purple-600 mb-2">
                                <?php echo ($sub['price'] > 0) ? '₹' . number_format($sub['price'], 2) : 'Free'; ?>
                            </p>
                            <a href="sub_event.php?id=<?php echo $sub['id']; ?>" class="inline-block bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700 transition-colors">
                                Register Now
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-6 bg-white rounded shadow-md text-center text-gray-500">
                    No sessions found for this event.
                </div>
            <?php endif; ?>
        </div>

    </main>
</body>
</html>