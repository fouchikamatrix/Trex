<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current service type from session only
$service_type = isset($_SESSION['service_type']) ? $_SESSION['service_type'] : 'gas';

// Update session if service is being switched
if (isset($_GET['switch_service']) && in_array($_GET['switch_service'], ['gas', 'electricity'])) {
    $service_type = $_GET['switch_service'];
    $_SESSION['service_type'] = $service_type;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Gaztronik' : 'Gaztronik - Gas & Electricity Management'; ?></title>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gas-gradient: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            --electricity-gradient: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-light: 0 8px 32px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 12px 40px rgba(0, 0, 0, 0.15);
            --shadow-heavy: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
            position: relative;
        }

        /* Dynamic Page Backgrounds */
        body.dashboard-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        }

        body.bill-page {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 50%, #43e97b 100%);
        }

        body.payment-page {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 50%, #ffecd2 100%);
        }

        body.news-page {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 50%, #d299c2 100%);
        }

        body.contact-page {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
        }

        body.history-page {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 50%, #ff8a80 100%);
        }

        body.information-page {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 50%, #a8edea 100%);
        }

        body.reclamation-page {
            background: linear-gradient(135deg, #ffd89b 0%, #19547b 50%, #667eea 100%);
        }

        /* Animated Background Particles */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 107, 53, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(77, 171, 247, 0.3) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(120deg); }
            66% { transform: translateY(20px) rotate(240deg); }
        }

        .app-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Enhanced Sidebar Styles */
        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-medium);
            overflow: hidden;
        }

        .sidebar-bg-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, 
                rgba(102, 126, 234, 0.1) 0%, 
                rgba(118, 75, 162, 0.1) 25%,
                rgba(255, 107, 53, 0.1) 50%,
                rgba(77, 171, 247, 0.1) 75%,
                rgba(102, 126, 234, 0.1) 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            z-index: -1;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .sidebar-header {
            padding: 30px 25px;
            border-bottom: 1px solid var(--glass-border);
            position: relative;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-wrapper {
            position: relative;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-icon {
            font-size: 28px;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            z-index: 2;
            position: relative;
        }

        .logo-pulse {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: 50%;
            opacity: 0.3;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.7; }
            50% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.3; }
            100% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.7; }
        }

        .app-name {
            font-size: 26px;
            font-weight: 800;
            font-family: 'Poppins', sans-serif;
        }

        .volt {
            background: var(--electricity-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .gaz {
            background: var(--gas-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .service-switcher {
            padding: 25px 20px;
            border-bottom: 1px solid var(--glass-border);
        }

        .switch-container {
            display: flex;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 6px;
            position: relative;
        }

        .switch-btn {
            flex: 1;
            padding: 16px 20px;
            border: none;
            background: transparent;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            color: rgba(255, 255, 255, 0.7);
            position: relative;
            overflow: hidden;
        }

        .switch-btn.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            box-shadow: var(--shadow-light);
            transform: translateY(-2px);
        }

        .switch-btn.loading {
            pointer-events: none;
        }

        .switch-btn.loading .switch-icon-wrapper {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .switch-icon-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .switch-btn.active .icon-glow {
            opacity: 0.3;
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { box-shadow: 0 0 10px currentColor; }
            to { box-shadow: 0 0 20px currentColor, 0 0 30px currentColor; }
        }

        .switch-ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0);
            pointer-events: none;
        }

        .switch-ripple.animate {
            animation: ripple 0.6s linear;
        }

        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
            overflow-y: auto;
        }

        .nav-list {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 6px;
            position: relative;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 18px 25px;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 0;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-gradient);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            transform: scaleY(1);
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(8px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(8px);
        }

        .nav-icon-wrapper {
            position: relative;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-icon-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 35px;
            height: 35px;
            background: var(--primary-gradient);
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .nav-link:hover .nav-icon-bg,
        .nav-link.active .nav-icon-bg {
            opacity: 0.2;
        }

        .nav-link i {
            font-size: 18px;
            z-index: 2;
            position: relative;
        }

        .nav-text {
            font-weight: 500;
            font-size: 15px;
        }

        .sidebar-footer {
            padding: 25px 20px;
            border-top: 1px solid var(--glass-border);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 18px 25px;
            text-decoration: none;
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            border-radius: 16px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            position: relative;
            overflow: hidden;
        }

        .logout-btn:hover {
            background: rgba(255, 107, 107, 0.2);
            transform: translateY(-3px);
            box-shadow: var(--shadow-light);
        }

        .logout-icon-wrapper {
            position: relative;
        }

        .logout-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 30px;
            height: 30px;
            background: #ff6b6b;
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .logout-btn:hover .logout-glow {
            opacity: 0.3;
            animation: glow 1s ease-in-out infinite alternate;
        }

        .logout-ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 107, 107, 0.3);
            transform: scale(0);
            pointer-events: none;
        }

        .logout-ripple.animate {
            animation: ripple 0.6s linear;
        }

        /* Enhanced Header Styles */
        .main-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            padding: 0 35px;
            height: 85px;
            display: flex;
            align-items: center;
            box-shadow: var(--shadow-light);
            position: sticky;
            top: 0;
            z-index: 1001;
            overflow: visible;
        }

        .header-bg-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                rgba(255, 255, 255, 0.1) 0%, 
                rgba(255, 255, 255, 0.05) 50%,
                rgba(255, 255, 255, 0.1) 100%);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .service-indicator {
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
        }

        .service-icon-wrapper {
            position: relative;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        .service-icon-wrapper i {
            font-size: 22px;
            z-index: 2;
        }

        .service-icon-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 35px;
            height: 35px;
            border-radius: 50%;
            opacity: 0.3;
            animation: pulse 2s ease-in-out infinite;
        }

        .service-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .service-name {
            color: white;
            font-size: 16px;
            font-weight: 600;
        }

        .service-status {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: #4ade80;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: white;
            position: relative;
            font-family: 'Poppins', sans-serif;
        }

        .title-text {
            position: relative;
            z-index: 2;
        }

        .title-underline {
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 3px;
            background: var(--primary-gradient);
            border-radius: 2px;
            transition: width 0.6s ease;
        }

        .page-title.animate-in .title-underline {
            width: 100%;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .notification-wrapper {
            position: relative;
        }

        .notification-icon {
            position: relative;
            cursor: pointer;
            padding: 12px;
            border-radius: 50%;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .notification-icon:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .notification-icon i {
            font-size: 20px;
            color: white;
        }

        .notification-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            border: 2px solid white;
        }

        .notification-badge.pulse {
            animation: pulse 2s ease-in-out infinite;
        }

        .notification-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            background: rgba(239, 68, 68, 0.3);
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .notification-icon:hover .notification-glow {
            opacity: 1;
            animation: glow 1s ease-in-out infinite alternate;
        }

        .notification-dropdown {
            position: absolute;
            top: calc(100% + 15px);
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            box-shadow: var(--shadow-heavy);
            min-width: 320px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-15px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--glass-border);
            overflow: hidden;
            z-index: 1002;
        }

        .notification-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .notification-header {
            padding: 20px 20px 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header h4 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .notification-count {
            font-size: 12px;
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 500;
        }

        .notification-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .notification-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 15px 20px;
            transition: background 0.3s ease;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .notification-item:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-icon-small {
            width: 35px;
            height: 35px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
        }

        .notification-content p {
            font-size: 14px;
            color: #333;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .notification-time {
            font-size: 12px;
            color: #666;
        }

        .notification-footer {
            padding: 15px 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .view-all-btn {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .view-all-btn:hover {
            color: #764ba2;
        }

        .user-dropdown {
            position: relative;
        }

        .user-btn {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px 18px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }

        .user-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: var(--shadow-light);
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .avatar-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .user-btn:hover .avatar-glow {
            opacity: 1;
            animation: pulse 1s ease-in-out infinite;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .user-name {
            font-weight: 600;
            color: white;
            font-size: 15px;
        }

        .user-email {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.7);
        }

        .dropdown-arrow {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            transition: transform 0.3s ease;
        }

        .user-btn:hover .dropdown-arrow {
            transform: rotate(180deg);
        }

        .dropdown-menu {
            position: absolute;
            top: calc(100% + 15px);
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            box-shadow: var(--shadow-heavy);
            min-width: 250px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-15px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--glass-border);
            overflow: hidden;
            z-index: 1002;
        }

        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-header {
            padding: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .dropdown-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .dropdown-user-info {
            display: flex;
            flex-direction: column;
        }

        .dropdown-name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .dropdown-email {
            font-size: 12px;
            color: #666;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .dropdown-item:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .dropdown-item.logout {
            color: #ef4444;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .dropdown-item.logout:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .dropdown-divider {
            height: 1px;
            background: rgba(0, 0, 0, 0.1);
            margin: 8px 0;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            margin-left: 280px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        .content-area {
            flex: 1;
            padding: 35px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 25px 25px 0 0;
            margin: 25px 25px 0 25px;
            overflow-y: auto;
            box-shadow: var(--shadow-light);
            border: 1px solid var(--glass-border);
            position: relative;
            z-index: 1;
        }

        /* Sidebar Toggle for Mobile */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 25px;
            left: 25px;
            z-index: 1003;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: none;
            border-radius: 50%;
            width: 55px;
            height: 55px;
            cursor: pointer;
            box-shadow: var(--shadow-medium);
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-heavy);
        }

        .toggle-lines {
            display: flex;
            flex-direction: column;
            gap: 4px;
            align-items: center;
            justify-content: center;
        }

        .toggle-lines span {
            width: 20px;
            height: 2px;
            background: white;
            border-radius: 1px;
            transition: all 0.3s ease;
        }

        .sidebar-toggle.active .toggle-lines span:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }

        .sidebar-toggle.active .toggle-lines span:nth-child(2) {
            opacity: 0;
        }

        .sidebar-toggle.active .toggle-lines span:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
        }

        /* Custom Scrollbar */
        .sidebar-nav::-webkit-scrollbar,
        .notification-list::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-nav::-webkit-scrollbar-track,
        .notification-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb,
        .notification-list::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover,
        .notification-list::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .sidebar-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .main-content {
                margin-left: 0;
            }

            .main-header {
                padding: 0 90px 0 25px;
            }

            .header-content {
                flex-wrap: wrap;
                gap: 15px;
            }

            .page-title {
                font-size: 22px;
            }

            .user-info {
                display: none;
            }

            .content-area {
                margin: 15px;
                padding: 25px;
                border-radius: 20px;
            }

            .notification-dropdown,
            .dropdown-menu {
                min-width: 280px;
            }
        }

        @media (max-width: 480px) {
            .header-left {
                display: none;
            }

            .page-title {
                font-size: 20px;
            }

            .content-area {
                padding: 20px;
                margin: 10px;
            }

            .main-header {
                padding: 0 70px 0 20px;
                height: 75px;
            }

            .user-menu {
                gap: 15px;
            }

            .notification-dropdown,
            .dropdown-menu {
                min-width: 260px;
            }
        }

        /* Page-specific body classes */
        <?php
        $page_classes = [
            'dashboard.php' => 'dashboard-page',
            'gas_bill.php' => 'bill-page',
            'electricity_bill.php' => 'bill-page',
            'gas_payment.php' => 'payment-page',
            'electricity_payment.php' => 'payment-page',
            'gas_news.php' => 'news-page',
            'electricity_news.php' => 'news-page',
            'gas_contact.php' => 'contact-page',
            'electricity_contact.php' => 'contact-page',
            'gas_history.php' => 'history-page',
            'electricity_history.php' => 'history-page',
            'gas_information.php' => 'information-page',
            'electricity_information.php' => 'information-page',
            'gas_reclamation.php' => 'reclamation-page',
            'electricity_reclamation.php' => 'reclamation-page'
        ];
        
        $body_class = isset($page_classes[$current_page]) ? $page_classes[$current_page] : 'dashboard-page';
        ?>
    </style>
    
    <?php if (isset($additional_css)): ?>
        <style><?php echo $additional_css; ?></style>
    <?php endif; ?>
</head>
<body class="<?php echo $body_class; ?>">
    <div class="app-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'header.php'; ?>
            
            <div class="content-area">
                <?php 
                if (isset($content)) {
                    echo $content;
                } else {
                    if (isset($content_file) && file_exists($content_file)) {
                        include $content_file;
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <?php if (isset($additional_js)): ?>
        <script><?php echo $additional_js; ?></script>
    <?php endif; ?>
</body>
</html>
