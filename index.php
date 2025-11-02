<?php
session_start();
include 'db.php';

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = $isLoggedIn && isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

// Fetch MAIN events
$main_events = [];
$result = $conn->query("SELECT id, name, description, main_image_url FROM main_events ORDER BY name ASC");
if ($result) {
    $main_events = $result->fetch_all(MYSQLI_ASSOC);
}

// --- REMOVED: $default_images array ---

$event_index = 0; // Still used for the "Featured" badge
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
</head>
<body class="bg-gray-50 text-gray-800">

     <!-- UPDATED: Responsive Navbar -->
    <nav class="sticky top-0 z-50 bg-white shadow-sm border-b border-gray-200">
        <!-- Primary Navbar content -->
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex justify-between items-center h-16">
                
                <!-- Brand -->
                <a href="index.php" class="text-2xl font-bold tracking-tight text-gray-900">Event Portal</a>
                
                <!-- Desktop Menu (Hidden on mobile) -->
                <div class="hidden md:flex space-x-4 items-center">
                    <?php if ($isLoggedIn): ?>
                        <span class="py-2 font-medium text-gray-700">Welcome, <?php echo htmlspecialchars($username); ?></span>
                        <a href="logout.php" class="py-2 px-4 bg-purple-600 text-white hover:bg-purple-700 rounded-lg shadow-md transition-all duration-300">Logout</a>
                    <?php else: ?>
                        <a href="signup.php" class="py-2 px-4 text-gray-700 font-medium hover:text-purple-600 rounded-lg transition-all duration-300">Sign Up</a>
                        <a href="login.php" class="py-2 px-4 bg-purple-600 text-white font-semibold rounded-lg shadow-md hover:bg-purple-700 transition-all duration-300">Login</a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button (Hidden on desktop) -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-purple-500">
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
        <div id="mobile-menu" class="hidden md:hidden">
            <div class="px-4 pt-2 pb-4 space-y-2">
                <?php if ($isLoggedIn): ?>
                    <span class="block py-2 font-medium text-gray-700">Welcome, <?php echo htmlspecialchars($username); ?></span>
                    <a href="logout.php" class="block w-full text-center py-2 px-4 bg-purple-600 text-white hover:bg-purple-700 rounded-lg shadow-md transition-all duration-300">Logout</a>
                <?php else: ?>
                    <a href="signup.php" class_name="block w-full text-left py-2 px-3 text-gray-700 font-medium hover:text-purple-600 hover:bg-gray-50 rounded-lg transition-all duration-300">Sign Up</a>
                    <a href="login.php" class="block w-full text-center py-2 px-4 bg-purple-600 text-white font-semibold rounded-lg shadow-md hover:bg-purple-700 transition-all duration-300">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <!-- End of Navbar -->

    <main class="container mx-auto p-6 md:p-12">
        <h2 class="text-4xl font-bold mb-8 text-gray-900 text-center">Upcoming Events</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (!empty($main_events)): ?>
                <?php foreach ($main_events as $event): ?>
                    
                    <div class="relative rounded-lg overflow-hidden bg-white border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-2 flex flex-col">
                        
                        <?php if ($event_index == 0): ?>
                            <span class="absolute top-4 left-4 bg-pink-600 text-white text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider z-10">Featured</span>
                        <?php endif; ?>

                        <?php 
                            // --- MODIFIED LOGIC ---
                            // 1. Get the image URL
                            $image_url = $event['main_image_url'];
                            
                            // 2. ONLY if the URL is NOT empty, display the image block
                            if (!empty($image_url)): 
                        ?>
                            <div class="h-56 w-full">
                                <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                     alt="<?php echo htmlspecialchars($event['name']); ?>" 
                                     class="w-full h-full object-cover">
                            </div>
                        <?php 
                            endif; 
                            // If $image_url was empty, the code skips rendering the image entirely
                        ?>

                        <div class="p-6 flex-grow flex flex-col">
                            <h3 class="text-xl font-semibold mb-2 text-gray-900"><?php echo htmlspecialchars($event['name']); ?></h3>
                            <p class="text-gray-600 mb-4 flex-grow">
                                <?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?>
                            </p>
                            
                            <a href="event.php?id=<?php echo $event['id']; ?>" class="inline-block w-full text-center bg-purple-600 text-white px-4 py-3 rounded-lg shadow-md hover:bg-purple-700 transition-all duration-300">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php 
                    $event_index++; // Increment for the badge
                    endforeach; 
                ?>
            <?php else: ?>
                <div class="col-span-3 p-10 bg-white rounded-lg border border-gray-200 text-center text-gray-700">
                    No upcoming events found. Please check back soon!
                </div>
            <?php endif; ?>
        </div>
    </main>
     <!-- JavaScript for Mobile Menu Toggle -->
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