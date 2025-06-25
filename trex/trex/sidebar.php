<?php
// Get the current service type from session only (remove URL parameter auto-switching)
$service_type = isset($_SESSION['service_type']) ? $_SESSION['service_type'] : 'gas';

// Only update service type if explicitly requested via service switcher
if (isset($_GET['switch_service']) && in_array($_GET['switch_service'], ['gas', 'electricity'])) {
    $service_type = $_GET['switch_service'];
    $_SESSION['service_type'] = $service_type;
}

// Define menu items for each service type
$menu_items = [
    'gas' => [
        'dashboard' => ['url' => 'dashboard.php', 'icon' => 'fas fa-tachometer-alt', 'title' => 'Tableau de bord'],
        'bill' => ['url' => 'gas_bill.php', 'icon' => 'fas fa-file-invoice-dollar', 'title' => 'Facture gaz'],
        'news' => ['url' => 'gas_news.php', 'icon' => 'fas fa-newspaper', 'title' => 'Actualités gaz'],
        'information' => ['url' => 'gas_information.php', 'icon' => 'fas fa-info-circle', 'title' => 'Informations gaz'],
        'bill_payment' => ['url' => 'gas_payment.php', 'icon' => 'fas fa-credit-card', 'title' => 'Paiement gaz'],
        'contact' => ['url' => 'gas_contact.php', 'icon' => 'fas fa-phone', 'title' => 'Contact gaz'],
        'history' => ['url' => 'gas_history.php', 'icon' => 'fas fa-history', 'title' => 'Historique gaz'],
        'reclamation' => ['url' => 'reclamations.php', 'icon' => 'fas fa-exclamation-triangle', 'title' => 'Réclamation gaz']
    ],
    'electricity' => [
        'dashboard' => ['url' => 'dashboard.php', 'icon' => 'fas fa-tachometer-alt', 'title' => 'Tableau de bord'],
        'bill' => ['url' => 'electricity_bill.php', 'icon' => 'fas fa-file-invoice-dollar', 'title' => 'Facture électricité'],
        'news' => ['url' => 'electricity_news.php', 'icon' => 'fas fa-newspaper', 'title' => 'Actualités électricité'],
        'information' => ['url' => 'electricity_information.php', 'icon' => 'fas fa-info-circle', 'title' => 'Informations électricité'],
        'bill_payment' => ['url' => 'electricity_payment.php', 'icon' => 'fas fa-credit-card', 'title' => 'Paiement électricité'],
        'contact' => ['url' => 'electricity_contact.php', 'icon' => 'fas fa-phone', 'title' => 'Contact électricité'],
        'history' => ['url' => 'electricity_history.php', 'icon' => 'fas fa-history', 'title' => 'Historique électricité'],
        'reclamation' => ['url' => 'reclamations.php', 'icon' => 'fas fa-exclamation-triangle', 'title' => 'Réclamation électricité']
    ]
];

$current_menu = $menu_items[$service_type];
$current_page = basename($_SERVER['PHP_SELF']);

// French translations for menu items
$menu_translations = [
    'dashboard' => 'Tableau de bord',
    'bill' => 'Facture',
    'news' => 'Actualités',
    'information' => 'Informations',
    'bill_payment' => 'Paiement',
    'contact' => 'Contact',
    'history' => 'Historique',
    'reclamation' => 'Réclamation'
];
?>

<div class="sidebar" id="sidebar">
    <!-- Animated Background -->
    <div class="sidebar-bg-animation"></div>
    
    <!-- Logo and App Name -->
    <div class="sidebar-header">
        <div class="logo-container">
            <div class="logo-wrapper">
                <i class="fas fa-bolt logo-icon"></i>
                <div class="logo-pulse"></div>
            </div>
            <h2 class="app-name">
                <span class="volt">Gaz</span><span class="gaz">Tronik</span>
            </h2>
        </div>
    </div>

    <!-- Service Type Switcher -->
    <div class="service-switcher">
        <div class="switch-container">
            <button class="switch-btn <?php echo ($service_type == 'gas') ? 'active' : ''; ?>" 
                    onclick="switchService('gas')" data-service="gas">
                <div class="switch-icon-wrapper">
                    <i class="fas fa-fire"></i>
                    <div class="icon-glow"></div>
                </div>
                <span>Gaz</span>
                <div class="switch-ripple"></div>
            </button>
            <button class="switch-btn <?php echo ($service_type == 'electricity') ? 'active' : ''; ?>" 
                    onclick="switchService('electricity')" data-service="electricity">
                <div class="switch-icon-wrapper">
                    <i class="fas fa-bolt"></i>
                    <div class="icon-glow"></div>
                </div>
                <span>Électricité</span>
                <div class="switch-ripple"></div>
            </button>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <?php foreach ($current_menu as $key => $item): ?>
                <li class="nav-item">
                    <a href="<?php echo $item['url']; ?>" 
                       class="nav-link <?php echo (strpos($current_page, $key) !== false || $current_page == $item['url'] || ($key == 'dashboard' && $current_page == 'user_dashboard.php')) ? 'active' : ''; ?>">
                        <div class="nav-icon-wrapper">
                            <i class="<?php echo $item['icon']; ?>"></i>
                            <div class="nav-icon-bg"></div>
                        </div>
                        <span class="nav-text"><?php echo $menu_translations[$key] ?? ucfirst(str_replace('_', ' ', $key)); ?></span>
                        <div class="nav-hover-effect"></div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Logout Button -->
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">
            <div class="logout-icon-wrapper">
                <i class="fas fa-sign-out-alt"></i>
                <div class="logout-glow"></div>
            </div>
            <span>Déconnexion</span>
            <div class="logout-ripple"></div>
        </a>
    </div>
</div>

<!-- Sidebar Toggle Button for Mobile -->
<button class="sidebar-toggle" id="sidebarToggle">
    <div class="toggle-lines">
        <span></span>
        <span></span>
        <span></span>
    </div>
</button>

<script>
function switchService(serviceType) {
    // Add loading animation
    const btn = document.querySelector(`[data-service="${serviceType}"]`);
    btn.classList.add('loading');
    
    // Update the service type in session and reload the page
    const url = new URL(window.location);
    url.searchParams.set('switch_service', serviceType);
    
    setTimeout(() => {
        window.location.href = url.toString();
    }, 300);
}

// Sidebar toggle functionality for mobile
document.getElementById('sidebarToggle').addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    const toggle = this;
    
    sidebar.classList.toggle('active');
    toggle.classList.toggle('active');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    
    if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
        sidebar.classList.remove('active');
        toggle.classList.remove('active');
    }
});

// Add ripple effect to buttons
document.querySelectorAll('.switch-btn, .logout-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        const ripple = this.querySelector('.switch-ripple, .logout-ripple');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('animate');
        
        setTimeout(() => {
            ripple.classList.remove('animate');
        }, 600);
    });
});
</script>
