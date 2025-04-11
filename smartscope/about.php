<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - SmartScope</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            min-height: 100vh;
        }

        .back-link {
            display: inline-block;
            color: #00ff9d;
            text-decoration: none;
            margin: 2rem 0 0 2rem;
            transition: transform 0.3s ease;
        }

        .back-link:hover {
            transform: translateX(-5px);
        }

        .main-content {
            padding: 120px 2rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .hero {
            text-align: center;
            padding: 3rem 0;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(45deg, #00ff9d, #00f0ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.2rem;
            color: #ccc;
            max-width: 800px;
            margin: 0 auto 2rem;
            line-height: 1.6;
        }

        .about-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem;
        }

        .about-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .about-card:hover {
            transform: translateY(-5px);
        }

        .about-card h3 {
            color: #00ff9d;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .about-card p {
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .team-section {
            text-align: center;
            padding: 4rem 0;
        }

        .team-section h2 {
            color: #00ff9d;
            margin-bottom: 2rem;
            font-size: 2rem;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            padding: 2rem;
        }

        .team-member {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .team-member h3 {
            color: #00ff9d;
            margin: 1rem 0;
        }

        .team-member p {
            color: #ccc;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-link">← Back to Home</a>
    
    <main class="main-content">
        <div class="hero">
            <h1>About SmartScope</h1>
            <p>Learn about our mission to revolutionize academic research through AI-powered solutions.</p>
        </div>

        <section class="about-section">
            <div class="about-card">
                <h3>Our Mission</h3>
                <p>To empower researchers and students with cutting-edge AI technology that streamlines the research paper generation process while maintaining high academic standards.</p>
            </div>
            <div class="about-card">
                <h3>Our Vision</h3>
                <p>To become the leading platform for AI-assisted academic research, making quality research accessible to everyone.</p>
            </div>
            <div class="about-card">
                <h3>Our Values</h3>
                <p>Innovation, accuracy, accessibility, and academic integrity are at the core of everything we do.</p>
            </div>
        </section>

        <section class="team-section">
            <h2>Our Team</h2>
            <div class="team-grid">
                <div class="team-member">
                    <h3>Research Team</h3>
                    <p>Our dedicated researchers ensure the highest quality of AI-generated content.</p>
                </div>
                <div class="team-member">
                    <h3>Development Team</h3>
                    <p>Expert developers building and maintaining our cutting-edge platform.</p>
                </div>
                <div class="team-member">
                    <h3>Support Team</h3>
                    <p>24/7 support to help you with any questions or concerns.</p>
                </div>
            </div>
        </section>

       
    </main>
</body>
</html> 