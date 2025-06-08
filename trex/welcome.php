<!-- welcome.php -->
<?php
include 'header.php';
include 'sidebar.php';
?>

<style>
    /* Main content container */
    .main-content {
        margin-left: 220px; /* sidebar width */
        padding: 40px 30px 80px 30px;
        padding-top: 100px; /* space for header */
        min-height: 90vh;
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        color: white;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        animation: fadeInUp 1s ease forwards;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        max-width: 900px;
        margin-left: 220px;
        margin-top: 40px;
    }

    h1 {
        font-size: 3rem;
        margin-bottom: 10px;
        text-shadow: 2px 2px 5px rgba(0,0,0,0.3);
    }

    p {
        font-size: 1.25rem;
        line-height: 1.6;
        max-width: 700px;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
    }

    /* Simple fade in + slide up animation */
    @keyframes fadeInUp {
        0% {
            opacity: 0;
            transform: translateY(30px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="main-content">
    <h1>Welcome to Your Dashboard!</h1>
    <p>You've successfully logged in. This dashboard is your command center where you can manage everything with ease and style.</p>
</div>

<?php
include 'footer.php';
?>
