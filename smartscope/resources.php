<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources - SmartScope</title>
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

        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem;
        }

        .resource-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .resource-card:hover {
            transform: translateY(-5px);
        }

        .resource-card h3 {
            color: #00ff9d;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .resource-card p {
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .resource-link {
            display: inline-block;
            color: #00ff9d;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border: 1px solid #00ff9d;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .resource-link:hover {
            background: rgba(0, 255, 157, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 157, 0.2);
        }
    </style>
</head>
<body>
    <a href="index.html" class="back-link">← Back to Home</a>
    
    <main class="main-content">
        <div class="hero">
            <h1>Research Resources</h1>
            <p>Access our comprehensive collection of research tools, guides, and materials to enhance your academic journey.</p>
        </div>

        <section class="resources-grid">
            <div class="resource-card">
                <h3>Research Guides</h3>
                <p>Comprehensive guides on research methodologies, paper writing, and academic standards.</p>
                <a href="#" class="resource-link">Access Guides</a>
            </div>

            <div class="resource-card">
                <h3>Citation Tools</h3>
                <p>Tools and templates for proper academic citations and references.</p>
                <a href="#" class="resource-link">Use Tools</a>
            </div>

            <div class="resource-card">
                <h3>Paper Templates</h3>
                <p>Professional templates for various types of research papers and academic documents.</p>
                <a href="#" class="resource-link">Download Templates</a>
            </div>

            <div class="resource-card">
                <h3>Video Tutorials</h3>
                <p>Step-by-step video guides on using SmartScope's features effectively.</p>
                <a href="#" class="resource-link">Watch Tutorials</a>
            </div>

            <div class="resource-card">
                <h3>Research Database</h3>
                <p>Access to our extensive database of academic papers and research materials.</p>
                <a href="#" class="resource-link">Browse Database</a>
            </div>

            <div class="resource-card">
                <h3>Community Forum</h3>
                <p>Connect with other researchers and share knowledge in our community forum.</p>
                <a href="#" class="resource-link">Join Discussion</a>
            </div>
        </section>

       
    </main>
</body>
</html> 