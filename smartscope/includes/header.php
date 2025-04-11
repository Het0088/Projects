<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar">
    <a href="index.html" class="logo">SmartScope</a>
    <button class="mobile-menu-btn">☰</button>
    <div class="nav-content">
        <div class="nav-links">
            <a href="index.html" class="nav-link">Home</a>
            <a href="features.php" class="nav-link">Features</a>
            <a href="about.php" class="nav-link">About</a>
            <a href="resources.php" class="nav-link">Resources</a>
            <a href="contact.php" class="nav-link">Contact</a>
        </div>
        <div class="auth-buttons" id="authButtons">
            <?php if (isset($_SESSION['username'])): ?>
                <a href="/hehe/logout.php" class="auth-btn login-btn logout-btn">Logout</a>
                <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <?php else: ?>
                <a href="/hehe/login.php" class="auth-btn login-btn">Login</a>
                <a href="/hehe/register.php" class="auth-btn register-btn">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.navbar {
    padding: 1.1rem 2rem;
    background: rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(10px);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
}

.logo {
    font-size: 2rem;
    font-weight: bold;
    color: #00ff9d;
    text-decoration: none;
    transition: all 0.3s ease;
}

.logo:hover {
    color: #00cc7d;
    transform: translateY(-2px);
}

.nav-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.nav-links {
    display: flex;
    gap: 1.5rem;
    align-items: center;
}

.nav-link {
    color: #fff;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    padding: 0.5rem 0;
    font-size: 1.05rem;
}

.nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: #00ff9d;
    transition: width 0.3s ease;
}

.nav-link:hover {
    color: #00ff9d;
}

.nav-link:hover::after {
    width: 100%;
}

.auth-buttons {
    display: flex;
    gap: 1rem;
    margin-left: 1.5rem;
    align-items: center;
}

.auth-btn {
    padding: 0.7rem 1.7rem;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    text-align: center;
    min-width: 110px;
    font-size: 1.05rem;
}

.login-btn {
    background: transparent;
    color: #00ff9d;
    border: 1px solid #00ff9d;
}

.login-btn:hover {
    background: rgba(0, 255, 157, 0.1);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 255, 157, 0.2);
}

.register-btn {
    background: #00ff9d;
    color: #0f2027;
    border: none;
}

.register-btn:hover {
    background: #00cc7d;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 255, 157, 0.3);
}

.welcome-text {
    color: #fff;
    margin-left: 1rem;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.logout-btn {
    margin-right: 0.5rem;
    display: inline-block;
}

.mobile-menu-btn {
    display: none;
    background: none;
    border: none;
    color: #fff;
    font-size: 1.5rem;
    cursor: pointer;
}

@media (max-width: 768px) {
    .navbar {
        padding: 1rem;
    }
    
    .nav-links {
        gap: 1rem;
    }
    
    .auth-buttons {
        margin-left: 1rem;
    }
    
    .mobile-menu-btn {
        display: block;
    }

    .nav-content {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: rgba(15, 32, 39, 0.95);
        padding: 1rem;
        flex-direction: column;
        gap: 1rem;
        backdrop-filter: blur(10px);
    }

    .nav-content.active {
        display: flex;
    }

    .nav-links {
        flex-direction: column;
        width: 100%;
        text-align: center;
    }

    .auth-buttons {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
    document.querySelector('.nav-content').classList.toggle('active');
});
</script> 