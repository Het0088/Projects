<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Features - SmartScope</title>
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

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-card h3 {
            color: #00ff9d;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .feature-card p {
            color: #ccc;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-link">← Back to Home</a>
    
    <main class="main-content">
        <div class="hero">
            <h1>Powerful Features for Research Excellence</h1>
            <p>Discover how SmartScope's cutting-edge features revolutionize the way you generate and analyze research papers.</p>
        </div>

        <section class="features">
            <div class="feature-card">
                <h3>Smart Search Technology</h3>
                <p>Our advanced AI-powered search algorithm helps you find relevant research papers quickly and efficiently.</p>
            </div>
            <div class="feature-card">
                <h3>Automated Paper Generation</h3>
                <p>Generate comprehensive research papers with proper citations and formatting in minutes.</p>
            </div>
            <div class="feature-card">
                <h3>Lightning-Fast Processing</h3>
                <p>Get your research papers generated within seconds using our optimized processing system.</p>
            </div>
        </section>
    </main>
</body>
</html>
