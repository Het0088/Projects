<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartScope - AI Research Paper Generator</title>
    <style>
        /* ... existing styles ... */
        .welcome-message {
            color: #00ff9d;
            margin-right: 1rem;
        }
        
        .logout-btn {
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            border: 1px solid #00ff9d;
            background: transparent;
            color: #00ff9d;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(0, 255, 157, 0.1);
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const username = localStorage.getItem('username');
        const authButtons = document.querySelector('.auth-buttons');
        
        if (username) {
            authButtons.innerHTML = `
                <a href="logout.php" class="logout-btn">Logout</a>
                <span class="welcome-text">Welcome, <span class="username">${username}</span></span>
            `;
        }
    });
    </script>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">SmartScope</a>
        <button class="mobile-menu-btn">☰</button>
        <div class="nav-content">
            <div class="nav-links">
                <a href="features.php">Features</a>
                <a href="about.php">About</a>
                <a href="resources.php">Resources</a>
                <a href="contact.php">Contact</a>
            </div>
            <div class="auth-buttons">
                <?php
                if (isset($_SESSION['username'])) {
                    // Show logout button and welcome message if logged in
                    echo '<form action="http://localhost/hehe/logout.php" method="post" style="margin: 0;">
                            <button type="submit" class="auth-btn login-btn">Logout</button>
                          </form>';
                    echo '<span class="welcome-text">Welcome, ' . htmlspecialchars($_SESSION['username']) . '</span>';
                } else {
                    // Show login/register buttons if not logged in
                    echo '<form action="http://localhost/hehe/login.html" method="get" style="margin: 0;">
                            <button type="submit" class="auth-btn login-btn">Login</button>
                          </form>
                          <form action="http://localhost/hehe/register.html" method="get" style="margin: 0;">
                            <button type="submit" class="auth-btn register-btn">Register</button>
                          </form>';
                }
                ?>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <h1>AI-Powered Research Paper Generator</h1>
        <p>Transform your research with SmartScope's advanced AI technology. Generate comprehensive research papers from arXiv's vast database in minutes.</p>
    </section>

    <!-- Rest of your existing content -->
</body>
</html> 