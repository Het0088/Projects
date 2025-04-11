<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - SmartScope</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            transition: transform 0.3s ease, color 0.3s ease;
            font-weight: 500;
        }

        .back-link:hover {
            transform: translateX(-5px);
            color: #00cc7d;
        }

        .main-content {
            padding: 100px 2rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .hero {
            text-align: center;
            padding: 3rem 0;
            animation: fadeIn 1s ease;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(45deg, #00ff9d, #00f0ff);
            background-clip: text;
            -webkit-background-clip: text;
            -moz-background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 5px 15px rgba(0, 255, 157, 0.1);
        }

        .hero p {
            font-size: 1.2rem;
            color: #e0e0e0;
            max-width: 800px;
            margin: 0 auto 2rem;
            line-height: 1.6;
        }

        .contact-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem;
            animation: slideUp 0.8s ease;
        }

        .contact-form {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .contact-form:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #00ff9d;
            margin-bottom: 0.5rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(0, 255, 157, 0.3);
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #00ff9d;
            box-shadow: 0 0 15px rgba(0, 255, 157, 0.2);
            transform: translateY(-2px);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(to right, #00ff9d, #00d6ff);
            border: none;
            border-radius: 8px;
            color: #0f2027;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.5s;
        }

        .submit-btn:hover {
            background: linear-gradient(to right, #00cc7d, #00b8dd);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 255, 157, 0.3);
        }

        .submit-btn:hover::before {
            left: 100%;
        }
        
        .submit-btn:active {
            transform: translateY(-1px);
        }

        .contact-info {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .contact-info:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .contact-info h3 {
            color: #00ff9d;
            margin-bottom: 2rem;
            font-size: 1.5rem;
            position: relative;
            display: inline-block;
        }

        .contact-info h3::after {
            content: '';
            position: absolute;
            width: 50%;
            height: 2px;
            bottom: -10px;
            left: 25%;
            background: linear-gradient(to right, #00ff9d, #00d6ff);
            border-radius: 2px;
        }

        .contact-item {
            margin-bottom: 1.8rem;
            transition: transform 0.3s ease;
        }

        .contact-item:hover {
            transform: translateY(-3px);
        }

        .contact-item h4 {
            color: #00ff9d;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .contact-item p {
            color: #e0e0e0;
            line-height: 1.6;
        }

        .social-links {
            display: flex;
            gap: 1.5rem;
            margin-top: 2rem;
            justify-content: center;
        }

        .social-links a {
            color: #00ff9d;
            text-decoration: none;
            width: 45px;
            height: 45px;
            border: 1px solid #00ff9d;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 1.2rem;
        }

        .social-links a:hover {
            background: rgba(0, 255, 157, 0.2);
            transform: translateY(-3px) rotate(360deg);
            box-shadow: 0 5px 15px rgba(0, 255, 157, 0.3);
            border-color: #00d6ff;
            color: #00d6ff;
        }

        .success-message, .error-message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .success-message {
            background-color: rgba(0, 255, 157, 0.1);
            border-left: 4px solid #00ff9d;
            color: #00ff9d;
        }

        .error-message {
            background-color: rgba(255, 77, 77, 0.1);
            border-left: 4px solid #ff4d4d;
            color: #ff4d4d;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(50px); 
            }
            to { 
                opacity: 1;
                transform: translateY(0); 
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .main-content {
                padding: 80px 1rem 1rem;
            }
            
            .contact-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <a href="index.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
    
    <main class="main-content">
        <div class="hero">
            <h1>Contact Us</h1>
            <p>Have questions or feedback? We'd love to hear from you. Reach out to our team using the form below.</p>
        </div>

        <div class="contact-container">
            <div class="contact-form">
                <div id="success-message" class="success-message">
                    <i class="fas fa-check-circle"></i> Your message has been sent successfully! We'll get back to you soon.
                </div>
                <div id="error-message" class="error-message">
                    <i class="fas fa-exclamation-circle"></i> There was a problem sending your message. Please try again.
                </div>
                <form id="contactForm" action="process_contact.php" method="POST">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter your name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" placeholder="Enter subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" placeholder="Enter your message here..." required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>

            <div class="contact-info">
                <h3>Get in Touch</h3>
                <div class="contact-item">
                    <h4><i class="fas fa-envelope"></i> Email</h4>
                    <p>support@smartscope.com</p>
                </div>
                <div class="contact-item">
                    <h4><i class="fas fa-phone"></i> Phone</h4>
                    <p>+91 9876543210</p>
                </div>
                <div class="contact-item">
                    <h4><i class="fas fa-map-marker-alt"></i> Address</h4>
                    <p>20 Horizontal Avenue<br>Vadodara<br>Gujarat, India</p>
                </div>
                <div class="social-links">
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" title="GitHub"><i class="fab fa-github"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Form submission handler with AJAX
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('.submit-btn');
            const successMsg = document.getElementById('success-message');
            const errorMsg = document.getElementById('error-message');
            
            // Change button text and disable it
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitBtn.disabled = true;
            
            // Hide any previous messages
            successMsg.style.display = 'none';
            errorMsg.style.display = 'none';
            
            fetch('process_contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    successMsg.style.display = 'block';
                    form.reset();
                } else {
                    errorMsg.textContent = data.message || 'There was a problem sending your message. Please try again.';
                    errorMsg.style.display = 'block';
                }
            })
            .catch(error => {
                errorMsg.textContent = 'Network error. Please try again later.';
                errorMsg.style.display = 'block';
                console.error('Error:', error);
            })
            .finally(() => {
                // Reset button
                submitBtn.innerHTML = 'Send Message';
                submitBtn.disabled = false;
                
                // Scroll to the message
                if (successMsg.style.display === 'block') {
                    successMsg.scrollIntoView({behavior: 'smooth'});
                } else if (errorMsg.style.display === 'block') {
                    errorMsg.scrollIntoView({behavior: 'smooth'});
                }
            });
        });
    </script>
</body>
</html> 