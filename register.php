<?php
session_start();
include 'db.php'; // Assuming db.php is in the same directory

// --- Mock DB & Session for Preview ---
if (!function_exists('mysqli_connect') && !class_exists('MockMySQLi')) {
    // Set mock session
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = 123; // Mock user ID

    // Mock POST data as if a form was just submitted
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_POST['sub_event_id'] = 1;
        $_POST['name'] = 'John Doe';
        $_POST['email'] = 'john.doe@example.com';
        $_POST['phone'] = '1234567890';
        $_POST['price'] = 0.00;
        // This fakes a POST request for preview purposes
        $_SERVER['REQUEST_METHOD'] = 'POST'; 
    }

    class MockMySQLi {
        public $insert_id = 999; // Mock registration ID
        public function prepare($query) { return new MockMySQLiStatement($this, $query); }
        public function close() {}
        public $error;
    }
    class MockMySQLiStatement {
        private $main_event_id;
        private $query;
        public function __construct($conn, $query) { $this->query = $query; }
        public function bind_param($types, ...$vars) { /* ... */ }
        public function execute() { /* ... */ }
        public function get_result() { return new MockMySQLiResult($this->query); }
        public function close() {}
    }
    class MockMySQLiResult {
        public $num_rows = 1;
        private $data;
        public function __construct($query) {
            // Check if it's the SELECT query
            if (strpos($query, "SELECT name FROM sub_events") !== false) {
                $this->data = ['name' => 'Mock Event: Keynote'];
            } else {
                $this->data = [];
                $this->num_rows = 0;
            }
        }
        public function fetch_assoc() { return $this->data; }
    }
    $conn = new MockMySQLi();
}
// --- End of Mock DB ---


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
    $sub_event_name = $sub_event['name'] ?? 'Event Not Found';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Success</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans min-h-screen flex flex-col">

    <!-- UPDATED: Responsive Navbar -->
    <nav class="sticky top-0 z-50 bg-purple-600 text-white shadow-md">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="text-xl sm:text-2xl font-bold">Event Portal</a>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-4 items-center">
                    <a href="index.php" class="inline-flex items-center py-2 px-3 sm:px-4 text-sm sm:text-base hover:bg-purple-700 rounded transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        All Events
                    </a>
                    <a href="logout.php" class="py-2 px-3 sm:px-4 text-sm sm:text-base bg-purple-700 hover:bg-purple-800 rounded">Logout</a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-100 hover:text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <span class="sr-only">Open main menu</span>
                        <!-- Hamburger Icon -->
                        <svg id="menu-open-icon" class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <!-- Close Icon (hidden by default) -->
                        <svg id="menu-close-icon" class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu (Hidden by default, shown on button click) -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-purple-700">
            <div class="px-4 pt-2 pb-4 space-y-2">
                <a href="index.php" class="inline-flex items-center py-2 px-3 text-base font-medium hover:bg-purple-700 rounded transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    All Events
                </a>
                <a href="logout.php" class="block w-full text-center py-2 px-4 bg-purple-700 hover:bg-purple-800 rounded">Logout</a>
            </div>
        </div>
    </nav>
    <!-- End of Navbar -->

    <!-- Main Content Area -->
    <main class="flex-grow flex items-center justify-center p-4 sm:p-6">
        <div class="max-w-xl w-full mx-auto bg-white p-6 sm:p-8 rounded-lg shadow-xl text-center">
            
            <!-- Checkmark Icon -->
            <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold mb-4 text-gray-900">Registration Successful!</h1>
            
            <div class="border-t border-b border-gray-200 my-6 py-4">
                <p class="text-lg text-gray-700 mb-2">You are confirmed for:</p>
                <p class="text-xl font-semibold text-purple-600 mb-2"><?php echo htmlspecialchars($sub_event_name); ?></p>
                <p class="text-gray-600"><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
                <p class="text-gray-600"><strong>Price:</strong> <span class="font-medium text-green-600">Free</span></p>
            </div>

            <?php if ($registration_id > 0): ?>
                <p class="text-gray-700 mt-6">Your registration is confirmed. Download your certificate:</p>
                <a href="generate_certificate.php?id=<?php echo $registration_id; ?>" 
                   target="_blank" 
                   class="inline-block mt-4 bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-md">
                    Download Certificate
                </a>
            <?php endif; ?>
            
            <a href="index.php" class="block mt-8 text-purple-600 hover:underline">Return to All Events</a>
        </div>
    </main>

    <!-- ADDED: JavaScript for Mobile Menu Toggle -->
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

