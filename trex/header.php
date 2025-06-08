<!-- header.php -->
<!DOCTYPE html>
<html>
<head>
    <title>My App</title>
    <style>
        /* Header Styles */
        header {
            background: linear-gradient(90deg, #6a11cb, #2575fc);
            color: white;
            padding: 15px 30px;
            font-size: 28px;
            font-weight: 700;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideDown 0.6s ease forwards;
        }

        /* Optional logo or app name styling */
        header .logo {
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: default;
        }

        /* Slide down animation */
        @keyframes slideDown {
            0% {
                opacity: 0;
                transform: translateY(-40px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
<header>
    <div class="logo">MyApp</div>
</header>
