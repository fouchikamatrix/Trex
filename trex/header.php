<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$host = 'localhost';
$dbname = 'gas_electricity_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get user information
$user_name = 'Guest';
$user_email = '';

if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $user_name = $user['name'];
            $user_email = $user['email'];
        }
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

// Get current service type from session only
$service_type = isset($_SESSION['service_type']) ? $_SESSION['service_type'] : 'gas';

// Update session if service is being switched
if (isset($_GET['switch_service']) && in_array($_GET['switch_service'], ['gas', 'electricity'])) {
    $service_type = $_GET['switch_service'];
    $_SESSION['service_type'] = $service_type;
}

$service_icon = ($service_type == 'gas') ? 'fas fa-fire' : 'fas fa-bolt';
$service_color = ($service_type == 'gas') ? '#ff6b35' : '#4dabf7';
?>

<header class="main-header">
    <!-- Animated Background -->
    <div class="header-bg-animation"></div>
    
    <div class="header-content">
        <!-- Left section - Service indicator -->
        <div class="header-left">
            <div class="service-indicator">
                <div class="service-icon-wrapper">
                    <i class="<?php echo $service_icon; ?>" style="color: <?php echo $service_color; ?>"></i>
                    <div class="service-icon-glow" style="background: <?php echo $service_color; ?>"></div>
                </div>
                <div class="service-info">
                    <span class="service-name"><?php echo ucfirst($service_type); ?> Service</span>
                    <div class="service-status">
                        <div class="status-dot"></div>
                        <span>Active</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Center section - Page title -->
        <div class="header-center">
            <h1 class="page-title">
                <span class="title-text">
                    <?php 
                    $page_titles = [
                        'dashboard.php' => 'Dashboard',
                        'gas_bill.php' => 'Gas Bill',
                        'electricity_bill.php' => 'Electricity Bill',
                        'gas_news.php' => 'Gas News',
                        'electricity_news.php' => 'Electricity News',
                        'gas_information.php' => 'Gas Information',
                        'electricity_information.php' => 'Electricity Information',
                        'gas_payment.php' => 'Gas Payment',
                        'electricity_payment.php' => 'Electricity Payment',
                        'gas_contact.php' => 'Gas Contact',
                        'electricity_contact.php' => 'Electricity Contact',
                        'gas_history.php' => 'Gas History',
                        'electricity_history.php' => 'Electricity History',
                        'gas_reclamation.php' => 'Gas Reclamation',
                        'electricity_reclamation.php' => 'Electricity Reclamation'
                    ];
                    
                    $current_page = basename($_SERVER['PHP_SELF']);
                    echo isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'VoltGaz';
                    ?>
                </span>
                <div class="title-underline"></div>
            </h1>
        </div>

        <!-- Right section - User info and actions -->
        <div class="header-right">
            <div class="user-menu">
                <!-- Notifications -->
                <div class="notification-wrapper">
                    <div class="notification-icon" onclick="toggleNotifications()">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge pulse">3</span>
                        <div class="notification-glow"></div>
                    </div>
                    
                    <!-- Notification Dropdown -->
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-header">
                            <h4>Notifications</h4>
                            <span class="notification-count">3 new</span>
                        </div>
                        <div class="notification-list">
                            <div class="notification-item">
                                <div class="notification-icon-small">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div class="notification-content">
                                    <p>New bill available</p>
                                    <span class="notification-time">2 hours ago</span>
                                </div>
                            </div>
                            <div class="notification-item">
                                <div class="notification-icon-small">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="notification-content">
                                    <p>Service maintenance scheduled</p>
                                    <span class="notification-time">1 day ago</span>
                                </div>
                            </div>
                            <div class="notification-item">
                                <div class="notification-icon-small">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="notification-content">
                                    <p>Payment confirmed</p>
                                    <span class="notification-time">3 days ago</span>
                                </div>
                            </div>
                        </div>
                        <div class="notification-footer">
                            <a href="#" class="view-all-btn">View All</a>
                        </div>
                    </div>
                </div>

                <!-- User dropdown -->
                <div class="user-dropdown">
                    <button class="user-btn" onclick="toggleUserDropdown()">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                            <div class="avatar-glow"></div>
                        </div>
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                            <span class="user-email"><?php echo htmlspecialchars($user_email); ?></span>
                        </div>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </button>

                    <div class="dropdown-menu" id="userDropdown">
                        <div class="dropdown-header">
                            <div class="dropdown-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="dropdown-user-info">
                                <span class="dropdown-name"><?php echo htmlspecialchars($user_name); ?></span>
                                <span class="dropdown-email"><?php echo htmlspecialchars($user_email); ?></span>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user-circle"></i>
                            <span>Profile</span>
                        </a>
                        <a href="settings.php" class="dropdown-item">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                        <a href="help.php" class="dropdown-item">
                            <i class="fas fa-question-circle"></i>
                            <span>Help & Support</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    dropdown.classList.toggle('show');
    notificationDropdown.classList.remove('show');
}

function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    const userDropdown = document.getElementById('userDropdown');
    
    dropdown.classList.toggle('show');
    userDropdown.classList.remove('show');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const userDropdown = document.getElementById('userDropdown');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const userBtn = document.querySelector('.user-btn');
    const notificationBtn = document.querySelector('.notification-icon');
    
    if (!userBtn.contains(event.target)) {
        userDropdown.classList.remove('show');
    }
    
    if (!notificationBtn.contains(event.target)) {
        notificationDropdown.classList.remove('show');
    }
});

// Update notification badge
function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'block';
    } else {
        badge.style.display = 'none';
    }
}

// Add floating animation to page title
document.addEventListener('DOMContentLoaded', function() {
    const title = document.querySelector('.page-title');
    if (title) {
        title.classList.add('animate-in');
    }
});
</script>
