<?php
require_once 'config.php';

// Fetch featured pets from database
$featured_pets_query = "SELECT TOP 3 * FROM pets WHERE status = 'available' ORDER BY NEWID()";
$featured_pets = sqlsrv_query($conn, $featured_pets_query);

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streeties - Pet Adoption</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="logo.png">
</head>
<body>
    <header>
        <div class="navbar">
            <div class="logo-container">
                <a href="index.php">
                    <img src="logo.png" alt="Streeties Logo" class="logo">
                </a>
            </div>
            <nav class="nav-links">
                <a href="index.php" class="active">Home</a>
                <a href="about.php">About</a>
                <a href="adopt.php">Adopt</a>
                <a href="contact.php">Contact</a>
                <?php if ($is_logged_in): ?>
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <!-- Hero section remains the same -->
    <section class="hero">
        <div class="hero-content">
            <h1>Find Your New Best Friend</h1>
            <p>Adopt a pet and give them a loving home. Every pet deserves love.</p>
            <div class="hero-buttons">
                <a href="adopt.php" class="btn primary-btn">View Pets <i class="fas fa-paw"></i></a>
                <a href="about.php" class="btn secondary-btn">Learn More <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
        <div class="hero-image">
            <img src="shelter-puppy.jpg" alt="Puppy waiting for adoption" class="shelter-image">
        </div>
    </section>

    <section class="featured-pets">
        <div class="container">
            <h2><i class="fas fa-heart"></i> Meet Our Featured Pets</h2>
            <div class="pets-grid">
                <?php
                if ($featured_pets) {
                    while ($pet = sqlsrv_fetch_array($featured_pets, SQLSRV_FETCH_ASSOC)) {
                        ?>
                        <div class="pet-card" data-type="<?php echo htmlspecialchars($pet['type']); ?>">
                            <div class="pet-image">
                                <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>">
                                <div class="pet-badge">Available</div>
                            </div>
                            <div class="pet-info">
                                <h3 class="pet-name"><?php echo htmlspecialchars($pet['name']); ?></h3>
                                <p class="pet-details">
                                    <i class="fas fa-birthday-cake"></i> <?php echo htmlspecialchars($pet['age']); ?> • 
                                    <i class="fas fa-<?php echo $pet['type'] === 'dog' ? 'dog' : 'cat'; ?>"></i> 
                                    <?php echo htmlspecialchars($pet['breed']); ?>
                                </p>
                                <p class="pet-description"><?php echo htmlspecialchars($pet['description']); ?></p>
                                <a href="pet-details.php?id=<?php echo $pet['id']; ?>" class="btn secondary-btn">
                                    Meet <?php echo htmlspecialchars($pet['name']); ?> <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <div class="view-all">
                <a href="adopt.php" class="btn primary-btn">View All Pets <i class="fas fa-paw"></i></a>
            </div>
        </div>
    </section>

    <!-- Rest of the sections remain the same -->
    <section class="how-it-works">
        <!-- ... existing content ... -->
    </section>

    <section class="cta">
        <!-- ... existing content ... -->
    </section>

    <footer>
        <!-- ... existing content ... -->
    </footer>

    <script src="script.js"></script>
</body>
</html> 