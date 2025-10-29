<?php
session_start();
include 'db.php';

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = $isLoggedIn && isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

// Fetch MAIN events from the database
$main_events = [];
$result = $conn->query("SELECT id, name, description FROM main_events ORDER BY name ASC");
if ($result) {
    $main_events = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

    <nav class="bg-purple-600 text-white shadow-md">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold">Event Portal</a>
            <div class="flex space-x-4">
                <?php if ($isLoggedIn): ?>
                    <span class="py-2">Welcome, <?php echo htmlspecialchars($username); ?></span>
                    <a href="logout.php" class="py-2 px-4 bg-purple-700 hover:bg-purple-800 rounded">Logout</a>
                <?php else: ?>
                    <a href="signup.php" class="py-2 px-4 hover:bg-purple-700 rounded">Sign Up</a>
                    <a href="login.php" class="py-2 px-4 bg-white text-purple-600 font-semibold rounded shadow hover:bg-gray-200">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="container mx-auto p-6">
        [Image of a modern website layout with event cards]
        <h2 class="text-3xl font-bold mb-6 text-gray-900">Upcoming Events</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (!empty($main_events)): ?>
                <?php foreach ($main_events as $event): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col">
                        <div class="p-6 flex-grow">
                            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($event['name']); ?></h3>
                            <p class="text-gray-600 mb-4">
                                <?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?>
                            </p>
                        </div>
                        <div class="p-6 bg-gray-50">
                            <a href="event.php?id=<?php echo $event['id']; ?>" class="inline-block w-full text-center bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition-colors">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-3 p-6 bg-white rounded shadow-md text-center text-gray-500">
                    No upcoming events found.
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>