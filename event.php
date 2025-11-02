<?php
session_start();
include 'db.php'; // Assuming db.php is in the same directory

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$main_event_id = $_GET['id'] ?? 0;
$main_event = null;
$sub_events = [];

// db.php is not provided, so for testing, we'll create a mock connection
// In your real environment, comment out or remove this mock db.php section
if (!function_exists('mysqli_connect') && !class_exists('MockMySQLi')) {
    // Mock the database connection and data if db.php isn't present
    // This allows the page to be previewed with dummy data
    class MockMySQLi {
        public function prepare($query) { return new MockMySQLiStatement($this); }
        public function close() {}
        public $error;
    }
    class MockMySQLiStatement {
        private $main_event_id;
        private $query_type;
        public function __construct($conn) { /* ... */ }
        public function bind_param($types, &$var1) { $this->main_event_id = $var1; }
        public function execute() {
            // Determine if we're fetching main or sub events based on $this->main_event_id
            $this->query_type = ($this->main_event_id == 1) ? 'main' : 'sub';
        }
        public function get_result() { return new MockMySQLiResult($this->main_event_id); }
        public function close() {}
    }
    class MockMySQLiResult {
        public $num_rows = 0;
        private $data = [];
        private $pointer = 0;
        private $main_event_id;

        public function __construct($id) {
            $this->main_event_id = $id;
            // Check if we are fetching main event data
            if (isset($_GET['id']) && $id == $_GET['id']) {
                $this->data = [
                    ['id' => 1, 'name' => 'Annual Tech Conference 2025', 'description' => 'Join us for the biggest tech event of the year, featuring keynote speakers, workshops, and networking opportunities.']
                ];
                $this->num_rows = 1;
            } else { // Fetching sub-events
                $this->data = [
                    ['id' => 1, 'main_event_id' => 1, 'name' => 'Keynote: The Future of AI', 'date' => '2025-10-20 09:00:00', 'venue' => 'Main Hall', 'price' => 50.00],
                    ['id' => 2, 'main_event_id' => 1, 'name' => 'Workshop: Advanced JavaScript', 'date' => '2025-10-20 11:00:00', 'venue' => 'Room 101', 'price' => 75.00],
                    ['id' => 3, 'main_event_id' => 1, 'name' => 'Panel: Cybersecurity Threats', 'date' => '2025-10-20 14:00:00', 'venue' => 'Room 102', 'price' => 50.00],
                    ['id' => 4, 'main_event_id' => 1, 'name' => 'Networking Mixer', 'date' => '2025-10-20 17:00:00', 'venue' => 'Rooftop Lounge', 'price' => 0.00],
                    ['id' => 5, 'main_event_id' => 1, 'name' => 'Workshop: Mastering React', 'date' => '2025-10-21 10:00:00', 'venue' => 'Room 101', 'price' => 75.00],
                ];
                $this->num_rows = 5;
            }
        }
        public function fetch_assoc() {
            if ($this->pointer < $this->num_rows) {
                return $this->data[$this->pointer++];
            }
            return null;
        }
        public function fetch_all($type) { return $this->data; }
    }
    // Mock the $conn object
    $conn = new MockMySQLi();
}
// End of mock db.php section


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
        // The sub-events are already sorted by date from this SQL query
        $stmt_sub = $conn->prepare("SELECT id, name, date, venue, price FROM sub_events WHERE main_event_id = ? ORDER BY date ASC");
        $stmt_sub->bind_param("i", $main_event_id);
        $stmt_sub->execute();
        $result_sub = $stmt_sub->get_result();
        $sub_events = $result_sub->fetch_all(MYSQLI_ASSOC);
        $stmt_sub->close();
    }
}

$conn->close();

if (!$main_event) {
    die("Event not found.");
}

// Redirect logic for simple events
if (count($sub_events) === 1 && !isset($_GET['preview'])) { // Added preview flag to stop redirect for testing
    header("Location: sub_event.php?id=" . $sub_events[0]['id']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($main_event['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .price-free {
            color: #16a34a; /* Tailwind green-600 */
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">

    <nav class="sticky top-0 z-50 bg-purple-600 text-white shadow-md">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex justify-between items-center h-16">
                
                <a href="index.php" class="text-xl sm:text-2xl font-bold">Event Portal</a>
                
                <div class="hidden md:flex space-x-4 items-center">
                    <a href="index.php" class="py-2 px-3 sm:px-4 text-sm sm:text-base hover:bg-purple-700 rounded">&larr; Back to Events</a>
                    <a href="logout.php" class="py-2 px-3 sm:px-4 text-sm sm:text-base bg-purple-700 hover:bg-purple-800 rounded">Logout</a>
                </div>

                <div class="md:hidden">
                    <button id="mobile-menu-button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-100 hover:text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <span class="sr-only">Open main menu</span>
                        <svg id="menu-open-icon" class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg id="menu-close-icon" class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden border-t border-purple-700">
            <div class="px-4 pt-2 pb-4 space-y-2">
                <a href="index.php" class="block py-2 px-3 text-base font-medium hover:bg-purple-700 rounded">&larr; Back to Events</a>
                <a href="logout.php" class="block w-full text-center py-2 px-4 bg-purple-700 hover:bg-purple-800 rounded">Logout</a>
            </div>
        </div>
    </nav>
    <main class="container mx-auto p-4 sm:p-6">
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold mb-2"><?php echo htmlspecialchars($main_event['name']); ?></h1>
            <p class="text-gray-700 text-base sm:text-lg"><?php echo nl2br(htmlspecialchars($main_event['description'])); ?></p>
        </div>

        <h2 class="text-2xl sm:text-3xl font-bold mb-6 text-gray-900">Available Sessions</h2>

        <div class="space-y-4">
            <?php if (!empty($sub_events)): ?>
                <?php foreach ($sub_events as $sub): ?>
                    <div class="bg-white p-6 rounded-lg shadow-md flex flex-col md:flex-row items-start md:items-center justify-between">
                        
                        <div>
                            <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($sub['name']); ?></h3>
                            <p class="text-gray-600"><strong>When:</strong> <?php echo htmlspecialchars(date('M j, Y - g:ia', strtotime($sub['date']))); ?></p>
                            <p class="text-gray-600"><strong>Where:</strong> <?php echo htmlspecialchars($sub['venue']); ?></p>
                        </div>
                        <div class="mt-4 md:mt-0 md:text-right w-full md:w-auto">
                            <p class="text-2xl font-bold text-purple-600 mb-2 <?php echo ($sub['price'] <= 0) ? 'price-free' : ''; ?>">
                                <?php echo ($sub['price'] > 0) ? '₹' . number_format($sub['price'], 2) : 'Free'; ?>
                            </p>
                            <a href="sub_event.php?id=<?php echo $sub['id']; ?>" class="inline-block text-center w-full md:w-auto bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700 transition-colors">
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const openIcon = document.getElementById('menu-open-icon');
            const closeIcon = document.getElementById('menu-close-icon');

            menuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
                openIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
            });
        });
    </script>
</body>
</html>