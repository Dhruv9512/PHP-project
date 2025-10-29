<?php
session_start();
include 'db.php';

// Redirect to login.php if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$sub_event_id = $_GET['id'] ?? 0;
$sub_event = null;

if ($sub_event_id) {
    $stmt = $conn->prepare("SELECT * FROM sub_events WHERE id = ?");
    $stmt->bind_param("i", $sub_event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $sub_event = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$sub_event) {
    die("Sub-event not found.");
}

// Price logic
$is_free = $sub_event['price'] <= 0;
$price_display = $is_free ? 'Free' : '₹' . number_format($sub_event['price']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register for <?php echo htmlspecialchars($sub_event['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <nav class="bg-purple-600 text-white shadow-md">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold">Event Portal</a>
            <div class="flex space-x-4">
                <a href="event.php?id=<?php echo $sub_event['main_event_id']; ?>" class="py-2 px-4 hover:bg-purple-700 rounded">&larr; Back to Sessions</a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto p-6">
        <div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div>
                    <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($sub_event['name']); ?></h1>
                    <div class="mb-6">
                        <p class="text-lg text-gray-700"><strong>When:</strong> <?php echo htmlspecialchars(date('F j, Y - g:ia', strtotime($sub_event['date']))); ?></p>
                        <p class="text-lg text-gray-700"><strong>Where:</strong> <?php echo htmlspecialchars($sub_event['venue']); ?></p>
                    </div>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($sub_event['description']); ?></p>
                </div>

                <div classs="bg-gray-50 p-6 rounded-lg shadow-inner">
                    <h2 class="text-2xl font-semibold mb-4">Register for this Session</h2>
                    
                    <div class="text-3xl font-bold text-purple-600 mb-6">
                        Price: <?php echo $price_display; ?>
                    </div>

                    <?php if ($is_free): ?>
                        <form action="register.php" method="POST" class="space-y-4">
                            <input type="hidden" name="sub_event_id" value="<?php echo htmlspecialchars($sub_event['id']); ?>">
                            <input type="hidden" name="price" value="0.00">
                            
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Your Name</label>
                                <input type="text" name="name" id="name" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Your Email</label>
                                <input type="email" name="email" id="email" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number (Optional)</label>
                                <input type="text" name="phone" id="phone" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                            </div>
                            <button type="submit" class="w-full bg-purple-600 text-white py-3 px-4 rounded-md shadow hover:bg-purple-700 font-semibold text-lg">
                                Register for Free
                            </button>
                        </form>
                    <?php else: ?>
                        <form action="checkout.php" method="POST" class="space-y-4">
                            <input type="hidden" name="sub_event_id" value="<?php echo htmlspecialchars($sub_event['id']); ?>">
                            
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Your Name</tabel>
                                <input type="text" name="name" id="name" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Your Email</label>
                                <input type="email" name="email" id="email" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input type="text" name="phone" id="phone" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                            </div>
                            <button type="submit" class="w-full bg-green-600 text-white py-3 px-4 rounded-md shadow hover:bg-green-700 font-semibold text-lg">
                                Pay with UPI (<?php echo $price_display; ?>)
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>