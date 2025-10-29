<?php
session_start();
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = $isLoggedIn && isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
    <header class="bg-purple-600 text-white p-4 flex justify-between items-center">
        <h1 class="text-3xl font-bold">Event Registration Portal</h1>
        <nav class="mt-2">
            <?php if ($isLoggedIn): ?>
                <span class="mr-4">Welcome, <?php echo htmlspecialchars($username); ?></span>
                <a href="logout.php" class="hover:underline">Logout</a>
            <?php else: ?>
                <a href="signup.php" class="mr-4 hover:underline">Sign Up</a>
                <a href="login.php" class="hover:underline">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="p-6">
        <h2 class="text-2xl font-semibold mb-4">Upcoming Events</h2>
        <ul class="space-y-3">
            <li>
                <a href="event.php?id=1" class="block p-4 bg-white rounded shadow hover:bg-purple-50">
                    Tech Conference 2025 - Sep 1
                </a>
            </li>
            <li>
                <a href="event.php?id=2" class="block p-4 bg-white rounded shadow hover:bg-purple-50">
                    Music Festival - Oct 10
                </a>
            </li>
            <li>
                <a href="event.php?id=3" class="block p-4 bg-white rounded shadow hover:bg-purple-50">
                    Art Exhibition - Nov 15
                </a>
            </li>
        </ul>
    </main>
</body>
</html>
