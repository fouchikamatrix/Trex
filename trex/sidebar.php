<!-- sidebar.php -->
<style>
    /* Sidebar styles */
    nav.sidebar {
        width: 220px;
        background: #1e1e2f;
        height: 100vh;
        padding-top: 80px; /* to clear fixed header */
        position: fixed;
        top: 0;
        left: 0;
        overflow-y: auto;
        box-shadow: 3px 0 12px rgba(0,0,0,0.3);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #bbb;
        transition: background 0.3s ease;
        z-index: 900;
    }

    nav.sidebar:hover {
        background: #29293d;
    }

    nav.sidebar a {
        display: block;
        padding: 15px 30px;
        color: #bbb;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1rem;
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }

    nav.sidebar a:hover {
        background: #3a3a5a;
        color: #fff;
        border-left: 4px solid #6a11cb;
    }

    nav.sidebar a.active {
        background: #6a11cb;
        color: white;
        border-left: 4px solid #2575fc;
    }

    /* Scrollbar styling */
    nav.sidebar::-webkit-scrollbar {
        width: 6px;
    }
    nav.sidebar::-webkit-scrollbar-thumb {
        background: #555;
        border-radius: 3px;
    }
    nav.sidebar::-webkit-scrollbar-track {
        background: #1e1e2f;
    }
</style>

<nav class="sidebar">
    <a href="#" class="active">Dashboard</a>
    <a href="#">Cars</a>
    <a href="#">Drivers</a>
    <a href="#">Tasks</a>
    <a href="#">Reports</a>
    <a href="#">Settings</a>
</nav>
