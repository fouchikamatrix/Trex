<!-- footer.php -->
<style>
    /* Footer styles */
    footer {
        background: linear-gradient(90deg, #6a11cb, #2575fc);
        color: white;
        padding: 12px 30px;
        position: fixed;
        bottom: 0;
        left: 220px; /* sidebar width */
        right: 0;
        text-align: center;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 14px;
        box-shadow: 0 -3px 10px rgba(0,0,0,0.2);
        user-select: none;
        animation: slideUp 0.6s ease forwards;
        z-index: 1000;
    }

    /* Slide up animation */
    @keyframes slideUp {
        0% {
            opacity: 0;
            transform: translateY(40px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<footer>
    &copy; <?= date('Y') ?> My Application. All rights reserved.
</footer>

</body>
</html>
