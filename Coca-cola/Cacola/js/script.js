// Wait for DOM content to load
document.addEventListener('DOMContentLoaded', () => {
    // Elements
    const loader = document.querySelector('.loader');
    const header = document.querySelector('header');
    const navToggle = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    const backToTop = document.querySelector('.back-to-top');
    const productSlider = document.querySelector('.product-slider');
    const sliderDots = document.querySelector('.slider-dots');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    const particleContainer = document.querySelector('.particle-container');

    // Loader with typewriter effect
    const loaderText = document.querySelector('.loader p');
    const messages = [
        'Initializing Experience...',
        'Calibrating Refresh Parameters...',
        'Optimizing Flavor Algorithms...',
        'Launching Sensation Matrix...',
        'Preparing Quantum Cola...'
    ];
    
    let messageIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let typingSpeed = 80;
    
    function typeWriter() {
        const currentMessage = messages[messageIndex];
        
        if (isDeleting) {
            loaderText.textContent = currentMessage.substring(0, charIndex - 1);
            charIndex--;
            typingSpeed = 40;
        } else {
            loaderText.textContent = currentMessage.substring(0, charIndex + 1);
            charIndex++;
            typingSpeed = 80;
        }
        
        if (!isDeleting && charIndex === currentMessage.length) {
            isDeleting = true;
            typingSpeed = 1000; // Pause at end
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            messageIndex = (messageIndex + 1) % messages.length;
        }
        
        setTimeout(typeWriter, typingSpeed);
    }
    
    typeWriter();

    // Generate particles in hero section
    if (particleContainer) {
        // Reduced number of particles for less visual distraction
        for (let i = 0; i < 20; i++) {
            createParticle();
        }
    }

    function createParticle() {
        const particle = document.createElement('span');
        particle.classList.add('particle');
        
        // Random properties with slower animation
        const size = Math.random() * 8 + 2;
        const posX = Math.random() * 100;
        const posY = Math.random() * 100;
        const duration = Math.random() * 30 + 20; // Increased duration for slower movement
        const delay = Math.random() * 10;
        
        // Apply styles
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.left = `${posX}%`;
        particle.style.top = `${posY}%`;
        particle.style.animationDuration = `${duration}s`;
        particle.style.animationDelay = `${delay}s`;
        
        // Add to container
        particleContainer.appendChild(particle);
    }

    // Loader
    window.addEventListener('load', () => {
        setTimeout(() => {
            loader.classList.add('hidden');
            document.body.style.overflow = 'visible';
            // Start animations once page is loaded
            animateSections();
        }, 4000); // Longer loader time for the futuristic experience
    });

    // Header scroll effect with parallax
    window.addEventListener('scroll', () => {
        const scrollPosition = window.scrollY;
        
        if (scrollPosition > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }

        // Back to top button
        if (scrollPosition > 500) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }

        // Animation on scroll
        animateSections();
    });

    // Disabled mouse move effect for hero section
    // This effect was causing the cursor tracking which has been removed
    const heroSection = document.querySelector('.hero');
    if (heroSection) {
        // The mouse tracking and parallax effects have been disabled
        // to prevent background movement when the cursor moves
    }

    // Mobile navigation toggle with animation
    if (navToggle) {
        navToggle.addEventListener('click', () => {
            navToggle.classList.toggle('active');
            navLinks.classList.toggle('active');
            
            // Prevent body scrolling when menu is open
            if (navLinks.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = 'visible';
            }
        });
    }

    // Close mobile menu when clicking on a link
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            navToggle.classList.remove('active');
            navLinks.classList.remove('active');
            document.body.style.overflow = 'visible';
        });
    });

    // Smooth scrolling for anchor links with acceleration
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                const headerOffset = 80;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                // Custom smooth scroll with easing
                const startPosition = window.pageYOffset;
                const distance = offsetPosition - startPosition;
                const duration = 1000;
                let start = null;
                
                function step(timestamp) {
                    if (!start) start = timestamp;
                    const progress = Math.min((timestamp - start) / duration, 1);
                    
                    // Easing function: easeInOutCubic
                    const easing = t => 1 - Math.pow(1 - t, 3);
                    const easedProgress = easing(progress);
                    
                    window.scrollTo(0, startPosition + distance * easedProgress);
                    
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    }
                }
                
                window.requestAnimationFrame(step);
            }
        });
    });

    // Enhanced product slider with 3D effect
    if (productSlider && sliderDots) {
        const productCards = productSlider.querySelectorAll('.product-card');
        let currentIndex = 0;
        let touchStartX = 0;
        let touchEndX = 0;

        // Create dots
        productCards.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.classList.add('dot');
            if (index === 0) dot.classList.add('active');
            dot.addEventListener('click', () => goToSlide(index));
            sliderDots.appendChild(dot);
        });

        // Next slide
        function nextSlide() {
            currentIndex = (currentIndex + 1) % productCards.length;
            goToSlide(currentIndex);
        }

        // Previous slide
        function prevSlide() {
            currentIndex = (currentIndex - 1 + productCards.length) % productCards.length;
            goToSlide(currentIndex);
        }

        // Go to specific slide with 3D rotation
        function goToSlide(index) {
            if (index < 0) {
                index = productCards.length - 1;
            } else if (index >= productCards.length) {
                index = 0;
            }
            
            // Update dot indicators
            document.querySelectorAll('.dot').forEach(dot => dot.classList.remove('active'));
            document.querySelectorAll('.dot')[index].classList.add('active');

            // Remove active class from all cards
            productCards.forEach(card => {
                card.classList.remove('active-card');
            });
            
            // Add active class to the current card
            productCards[index].classList.add('active-card');
            
            // For desktop - Improved 3D transform effect with centered active card
            if (window.innerWidth > 768) {
                // Calculate dimensions for centering
                const sliderWidth = productSlider.offsetWidth;
                const cardWidth = productCards[0].offsetWidth;
                const gapWidth = parseInt(window.getComputedStyle(productSlider).gap) || 48; // Default to 3rem if gap not found
                
                // Calculate offset to center the active card
                const centerOffset = (sliderWidth / 2) - (cardWidth / 2);
                
                // Reset productSlider transform to ensure proper positioning
                productSlider.style.transform = 'translateX(0)';
                
                // Calculate the translationX for each card based on the currently selected card
                productCards.forEach((card, i) => {
                    const diff = i - index;
                    const translateX = diff * (cardWidth + gapWidth) + centerOffset;
                    const translateZ = -Math.abs(diff) * 50;
                    const rotateY = diff * 8;
                    const opacity = 1 - Math.min(0.2, Math.abs(diff) * 0.1);
                    
                    card.style.transform = `translateX(${translateX}px) translateZ(${translateZ}px) rotateY(${rotateY}deg)`;
                    card.style.opacity = opacity;
                    card.style.transition = 'all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    card.style.zIndex = 10 - Math.abs(diff);
                });
            } else {
                // For mobile - smoother slide with easing
                const slidePosition = -index * 100;
                productSlider.style.transition = 'transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1)';
                productSlider.style.transform = `translateX(${slidePosition}%)`;
            }
            
            currentIndex = index;
        }

        // Initialize first slide
        goToSlide(0);

        // Event listeners for buttons
        if (prevBtn) prevBtn.addEventListener('click', prevSlide);
        if (nextBtn) nextBtn.addEventListener('click', nextSlide);

        // Touch events for mobile swipe
        productSlider.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });

        productSlider.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            if (touchEndX < touchStartX) {
                nextSlide();
            }
            if (touchEndX > touchStartX) {
                prevSlide();
            }
        }

        // Auto slide every 8 seconds (increased from 5 seconds for less frequent transitions)
        const autoSlideInterval = setInterval(nextSlide, 8000);
        
        // 3D tilt effect on hover - Disabled to prevent cursor tracking
        productCards.forEach(card => {
            // Mouse tracking on product cards has been disabled
            // to prevent movement when cursor hovers over cards
        });
    }

    // Futuristic contact form with validation and animation
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        // Input animation
        const formInputs = contactForm.querySelectorAll('input, textarea, select');
        formInputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', () => {
                if (input.value.trim() === '') {
                    input.parentElement.classList.remove('focused');
                }
            });
            
            // Check if input already has value (e.g. on page refresh)
            if (input.value.trim() !== '') {
                input.parentElement.classList.add('focused');
            }
        });
        
        // Form submission
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Get form values
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value;

            // Simple validation
            if (name && email && subject && message) {
                // Animated success message
                contactForm.innerHTML = `
                    <div class="success-message">
                        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                        <h3>Message Transmitted</h3>
                        <p>Thank you for connecting, ${name}. Your input has been received and we'll respond shortly.</p>
                    </div>
                `;
                
                // Animate success message
                const successMessage = document.querySelector('.success-message');
                successMessage.style.animation = 'fadeInUp 0.8s ease-out forwards';
            }
        });
    }

    // Enhanced animation on scroll with staggered reveal
    function animateSections() {
        const sections = document.querySelectorAll('section');
        
        sections.forEach(section => {
            const sectionTop = section.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (sectionTop < windowHeight * 0.75) {
                section.classList.add('animate');
                
                // Staggered animation for children
                const animatedElements = section.querySelectorAll('.product-card, .timeline-item, .sus-card, .contact-card');
                animatedElements.forEach((el, index) => {
                    setTimeout(() => {
                        el.classList.add('animate');
                    }, 150 * index);
                });
            }
        });
    }

    // Generate dynamic bubbles in hero section with glowing effect - Reduced for stability
    const bubblesContainer = document.querySelector('.bubbles');
    if (bubblesContainer) {
        // Reduced number of bubbles from 30 to 10 for better performance and less visual movement
        for (let i = 0; i < 10; i++) {
            const bubble = document.createElement('span');
            bubble.classList.add('bubble');
            
            // Random properties with slower movement
            const size = Math.random() * 60 + 10;
            const positionLeft = Math.random() * 100;
            const delay = Math.random() * 20; // Increased delay
            const duration = Math.random() * 15 + 15; // Slower duration
            const glowSize = Math.random() * 15 + 5;
            const glowColor = Math.random() > 0.5 ? 'rgba(0, 184, 255, ' : 'rgba(112, 0, 255, ';
            
            bubble.style.width = `${size}px`;
            bubble.style.height = `${size}px`;
            bubble.style.left = `${positionLeft}%`;
            bubble.style.animationDelay = `${delay}s`;
            bubble.style.animationDuration = `${duration}s`;
            bubble.style.boxShadow = `0 0 ${glowSize}px ${glowColor}0.7)`;
            
            bubblesContainer.appendChild(bubble);
        }
    }

    // Add CSS for enhanced animations
    const enhancedAnimations = document.createElement('style');
    enhancedAnimations.innerHTML = `
        .bubble {
            position: absolute;
            bottom: -100px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            animation: rise cubic-bezier(0.23, 1, 0.32, 1) infinite; /* Improved easing */
            pointer-events: none;
            backdrop-filter: blur(1px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        @keyframes rise {
            0% {
                transform: translateY(0) scale(1) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 0.5;
            }
            100% {
                transform: translateY(-100vh) scale(1.5) rotate(360deg);
                opacity: 0;
            }
        }
        
        .particle {
            position: absolute;
            background: linear-gradient(135deg, rgba(0, 184, 255, 0.6), rgba(112, 0, 255, 0.6));
            border-radius: 50%;
            pointer-events: none;
            animation: float-around linear infinite;
            opacity: 0.2;
            filter: blur(1px);
        }
        
        @keyframes float-around {
            0% {
                transform: translate(0, 0) scale(1);
            }
            25% {
                transform: translate(50px, -50px) scale(1.2);
            }
            50% {
                transform: translate(0, -100px) scale(0.8);
            }
            75% {
                transform: translate(-50px, -50px) scale(1.1);
            }
            100% {
                transform: translate(0, 0) scale(1);
            }
        }
        
        section {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s cubic-bezier(0.5, 0, 0.1, 1), transform 0.8s cubic-bezier(0.5, 0, 0.1, 1);
        }
        
        section.animate {
            opacity: 1;
            transform: translateY(0);
        }
        
        .product-card, .timeline-item, .sus-card, .contact-card {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s cubic-bezier(0.5, 0, 0.1, 1), transform 0.6s cubic-bezier(0.5, 0, 0.1, 1);
        }
        
        .product-card.animate, .timeline-item.animate, .sus-card.animate, .contact-card.animate {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Success checkmark animation */
        .checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            display: block;
        }
        
        .checkmark-circle {
            stroke: rgba(0, 184, 255, 0.2);
            stroke-width: 2;
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        
        .checkmark-check {
            stroke: #00b8ff;
            stroke-width: 3;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            fill: none;
            animation: stroke 0.5s cubic-bezier(0.65, 0, 0.45, 1) 0.7s forwards;
        }
        
        @keyframes stroke {
            100% {
                stroke-dashoffset: 0;
            }
        }
    `;
    document.head.appendChild(enhancedAnimations);

    // Auth modal handling
    const loginButton = document.getElementById('loginButton');
    const signupButton = document.getElementById('signupButton');
    const authModal = document.getElementById('authModal');
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const tabBtns = document.querySelectorAll('.tab-btn');
    
    // Close modal button
    const closeModalBtn = document.querySelector('.close-modal');
    
    if (loginButton && signupButton) {
        // Login button opens the modal with login tab active
        loginButton.addEventListener('click', (e) => {
            e.preventDefault();
            authModal.classList.add('active');
            // Make sure login tab is active
            tabBtns.forEach(btn => btn.classList.remove('active'));
            tabBtns[0].classList.add('active'); // First tab is login
            
            // Show login form, hide signup form
            loginForm.classList.add('active');
            signupForm.classList.remove('active');
            
            document.body.style.overflow = 'hidden';
        });
        
        // Signup button opens the modal with signup tab active
        signupButton.addEventListener('click', (e) => {
            e.preventDefault();
            authModal.classList.add('active');
            // Make sure signup tab is active
            tabBtns.forEach(btn => btn.classList.remove('active'));
            tabBtns[1].classList.add('active'); // Second tab is signup
            
            // Show signup form, hide login form
            signupForm.classList.add('active');
            loginForm.classList.remove('active');
            
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Close modal via close button
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            authModal.classList.remove('active');
            document.body.style.overflow = 'visible';
        });
    }
    
    // Close modal when clicking outside
    authModal.addEventListener('click', (e) => {
        if (e.target === authModal) {
            authModal.classList.remove('active');
            document.body.style.overflow = 'visible';
        }
    });

    // After successful login
    
    // Replace both login and signup buttons with My Account button
    if (loginButton && signupButton) {
        const accountBtn = document.createElement('a');
        accountBtn.href = '#';
        accountBtn.id = 'accountButton';
        accountBtn.className = 'nav-auth-btn account-btn';
        accountBtn.textContent = 'My Account';
        
        // Replace the login button with the account button
        loginButton.parentNode.replaceChild(accountBtn, loginButton);
        
        // Remove the signup button
        if (signupButton.parentNode) {
            signupButton.parentNode.removeChild(signupButton);
        }
        
        // Add event listener for the new account button
        accountBtn.addEventListener('click', (e) => {
            e.preventDefault();
            // Open account section or dropdown
            // Implementation depends on your account UI
        });
    }
});

// Animated counter function for numbers with smoother animation
function animateValue(obj, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        
        // Easing function - easeOutCubic
        const easing = t => 1 - Math.pow(1 - t, 3);
        const easedProgress = easing(progress);
        
        obj.innerHTML = Math.floor(easedProgress * (end - start) + start).toLocaleString();
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Initialize number counters when they come into view with Intersection Observer
const counters = document.querySelectorAll('.counter-number');
if (counters.length > 0) {
    const observerOptions = {
        threshold: 0.5,
        rootMargin: "0px 0px -100px 0px"
    };

    const counterObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = entry.target;
                const endValue = parseInt(target.getAttribute('data-count'));
                animateValue(target, 0, endValue, 2000);
                counterObserver.unobserve(target);
            }
        });
    }, observerOptions);

    counters.forEach(counter => {
        counterObserver.observe(counter);
    });
}

// Login/Signup Modal Functionality
document.addEventListener('DOMContentLoaded', () => {
    // Get elements
    const authButton = document.getElementById('authButton');
    const authModal = document.getElementById('authModal');
    const closeModal = document.querySelector('.close-modal');
    const tabBtns = document.querySelectorAll('.tab-btn');
    const authForms = document.querySelectorAll('.auth-form');
    const togglePasswordBtns = document.querySelectorAll('.toggle-password');
    
    // Open modal
    if (authButton) {
        authButton.addEventListener('click', (e) => {
            e.preventDefault();
            authModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Close modal
    if (closeModal) {
        closeModal.addEventListener('click', () => {
            authModal.classList.remove('active');
            document.body.style.overflow = 'visible';
        });
    }
    
    // Close modal when clicking outside
    authModal.addEventListener('click', (e) => {
        if (e.target === authModal) {
            authModal.classList.remove('active');
            document.body.style.overflow = 'visible';
        }
    });
    
    // Tab switching
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabTarget = btn.getAttribute('data-tab');
            
            // Update active state for buttons
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            // Show the corresponding form
            authForms.forEach(form => {
                form.classList.remove('active');
                if (form.id === `${tabTarget}Form`) {
                    form.classList.add('active');
                }
            });
        });
    });
    
    // Toggle password visibility
    togglePasswordBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const passwordInput = btn.parentElement.querySelector('input');
            const icon = btn.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Form submission with validation and database connectivity
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            
            // Animation to show processing
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
            
            try {
                // Simulate API call to database
                await new Promise(resolve => setTimeout(resolve, 1500));
                
                // For demo purposes, we'll simulate successful login
                // In a real implementation, this would verify credentials against a database
                
                // Show success message
                loginForm.innerHTML = `
                    <div class="success-message">
                        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                        <h3>Login Successful</h3>
                        <p>Welcome back! You are now logged in.</p>
                    </div>
                `;
                
                // Close modal after success
                setTimeout(() => {
                    authModal.classList.remove('active');
                    document.body.style.overflow = 'visible';
                    
                    // Update the auth button to show logged in state
                    authButton.textContent = 'My Account';
                }, 2000);
            } catch (error) {
                submitBtn.innerHTML = 'Login';
                submitBtn.disabled = false;
                
                // Show error message
                const errorMsg = document.createElement('div');
                errorMsg.className = 'error-message';
                errorMsg.textContent = 'Login failed. Please check your credentials.';
                loginForm.prepend(errorMsg);
                
                setTimeout(() => {
                    errorMsg.remove();
                }, 3000);
            }
        });
    }
    
    if (signupForm) {
        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const name = document.getElementById('signup-name').value;
            const email = document.getElementById('signup-email').value;
            const password = document.getElementById('signup-password').value;
            const confirmPassword = document.getElementById('signup-confirm').value;
            
            // Check if passwords match
            if (password !== confirmPassword) {
                const errorMsg = document.createElement('div');
                errorMsg.className = 'error-message';
                errorMsg.textContent = 'Passwords do not match';
                signupForm.prepend(errorMsg);
                
                setTimeout(() => {
                    errorMsg.remove();
                }, 3000);
                
                return;
            }
            
            // Animation to show processing
            const submitBtn = signupForm.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
            
            try {
                // Simulate API call to database
                await new Promise(resolve => setTimeout(resolve, 1500));
                
                // For demo purposes, we'll simulate successful registration
                // In a real implementation, this would store user information in a database
                
                // Show success message
                signupForm.innerHTML = `
                    <div class="success-message">
                        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                        <h3>Account Created</h3>
                        <p>Welcome, ${name}! Your account has been created successfully.</p>
                    </div>
                `;
                
                // Close modal after success
                setTimeout(() => {
                    authModal.classList.remove('active');
                    document.body.style.overflow = 'visible';
                    
                    // Update the auth button to show logged in state
                    authButton.textContent = 'My Account';
                }, 2000);
            } catch (error) {
                submitBtn.innerHTML = 'Create Account';
                submitBtn.disabled = false;
                
                // Show error message
                const errorMsg = document.createElement('div');
                errorMsg.className = 'error-message';
                errorMsg.textContent = 'Registration failed. Please try again.';
                signupForm.prepend(errorMsg);
                
                setTimeout(() => {
                    errorMsg.remove();
                }, 3000);
            }
        });
    }
});

// Product detail modal functionality
document.addEventListener('DOMContentLoaded', () => {
    // Create modals for each product dynamically
    const productBtns = document.querySelectorAll('.product-btn');
    
    // Define product details
    const products = {
        'quantum-cola': {
            title: 'Quantum Cola',
            tagline: 'The classic taste reimagined with molecular enhancement technology',
            description: 'Experience the revolution in refreshment with Quantum Cola. Our signature flavor has been enhanced at the molecular level to deliver an unprecedented taste experience that adapts to your preferences. The quantum particles in our formula create a multi-dimensional flavor profile that evolves as you drink, ensuring every sip is a new experience.',
            features: [
                {icon: 'fa-atom', text: 'Adaptive flavor profile'},
                {icon: 'fa-sparkles', text: 'Quantum particle infusion'},
                {icon: 'fa-dna', text: 'Molecular enhancement'},
                {icon: 'fa-lightbulb', text: 'Sensory activation'}
            ],
            nutrition: [
                {name: 'Energy', value: '80 kcal', percentage: 25},
                {name: 'Quantum Nutrients', value: '12g', percentage: 80},
                {name: 'Sugars', value: '0g', percentage: 0},
                {name: 'Flavor Enhancers', value: '5g', percentage: 45}
            ],
            price: '$3.99'
        },
        'zero-gravity': {
            title: 'Zero Gravity',
            tagline: 'Zero sugar, zero calories, infinite refreshment with antigravity bubbles',
            description: 'Defy the laws of physics with Zero Gravity. Our zero-sugar formula features anti-gravity bubbles that float upward when you open the bottle and create a unique sensation as they gently lift toward your palate. Experience refreshment that feels weightless yet delivers full flavor with every sip.',
            features: [
                {icon: 'fa-weight-scale', text: 'Zero calorie formula'},
                {icon: 'fa-droplet', text: 'Anti-gravity bubbles'},
                {icon: 'fa-rocket', text: 'Space-inspired cooling'},
                {icon: 'fa-wind', text: 'Weightless sensation'}
            ],
            nutrition: [
                {name: 'Energy', value: '0 kcal', percentage: 0},
                {name: 'Anti-G Compounds', value: '8g', percentage: 65},
                {name: 'Sugars', value: '0g', percentage: 0},
                {name: 'Hydro Boosters', value: '3g', percentage: 30}
            ],
            price: '$3.99'
        },
        'bio-energy': {
            title: 'Bio Energy',
            tagline: 'Natural energy infusion with adaptive mood-enhancing compounds',
            description: "Bio Energy harnesses the power of bioluminescent compounds found in deep-sea organisms to deliver sustainable energy that adapts to your body's needs. Unlike traditional energy drinks, Bio Energy won't cause crashes but instead provides a steady flow of revitalizing energy that responds to your activity level.",
            features: [
                {icon: 'fa-bolt', text: 'Adaptive energy release'},
                {icon: 'fa-leaf', text: 'Plant-based formula'},
                {icon: 'fa-brain', text: 'Neural-responsive'},
                {icon: 'fa-heart-pulse', text: 'Cardio-supportive'}
            ],
            nutrition: [
                {name: 'Energy', value: '95 kcal', percentage: 30},
                {name: 'Bio Nutrients', value: '15g', percentage: 75},
                {name: 'Natural Sugars', value: '3g', percentage: 15},
                {name: 'Adaptogens', value: '7g', percentage: 60}
            ],
            price: '$4.49'
        },
        'nebula-fusion': {
            title: 'Nebula Fusion',
            tagline: 'Multi-dimensional flavor that evolves as you drink, with color-shifting effects',
            description: "Nebula Fusion offers a cosmic experience with its galaxy-inspired formula that actually changes color as you drink it. The swirling flavor patterns combine sweet, tangy, and unexpected notes that reveal themselves at different temperatures, creating a drinking experience that's truly out of this world.",
            features: [
                {icon: 'fa-palette', text: 'Color-shifting liquid'},
                {icon: 'fa-star', text: 'Cosmic flavor fusion'},
                {icon: 'fa-temperature-half', text: 'Temperature-reactive'},
                {icon: 'fa-hurricane', text: 'Swirling patterns'}
            ],
            nutrition: [
                {name: 'Energy', value: '110 kcal', percentage: 35},
                {name: 'Fusion Compounds', value: '14g', percentage: 70},
                {name: 'Flavor Spectrum', value: '9g', percentage: 55},
                {name: 'Chromatic Elements', value: '6g', percentage: 40}
            ],
            price: '$4.99'
        }
    };
    
    // Create product detail modal template
    function createProductModal(product, productData) {
        const modalTemplate = `
            <div class="product-details-modal" id="${product}-modal">
                <div class="product-details-container">
                    <div class="product-details-image">
                        <img src="images/${product}.svg" alt="${productData.title}" class="product-image-3d">
                    </div>
                    <div class="product-details-info">
                        <button class="close-product-modal"><i class="fas fa-times"></i></button>
                        <h2 class="product-title">${productData.title}</h2>
                        <p class="product-tagline">${productData.tagline}</p>
                        <div class="product-description">
                            <p>${productData.description}</p>
                        </div>
                        <div class="product-features">
                            <h3>Key Features</h3>
                            <div class="feature-list">
                                ${productData.features.map(feature => `
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <i class="fas ${feature.icon}"></i>
                                        </div>
                                        <div class="feature-text">${feature.text}</div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        <div class="product-nutrition">
                            <h3>Nutrition Facts</h3>
                            <div class="nutrition-bars">
                                ${productData.nutrition.map(item => `
                                    <div class="nutrition-item">
                                        <div class="nutrition-info">
                                            <span>${item.name}</span>
                                            <span>${item.value}</span>
                                        </div>
                                        <div class="nutrition-bar">
                                            <div class="nutrition-progress" style="width: ${item.percentage}%"></div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        <div class="product-cta">
                            <span class="product-price">${productData.price}</span>
                            <button class="btn primary-btn add-to-cart">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                            <button class="btn secondary-btn">Subscribe</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to the page
        document.body.insertAdjacentHTML('beforeend', modalTemplate);
        
        // Add event listeners for the modal
        const modal = document.getElementById(`${product}-modal`);
        const closeBtn = modal.querySelector('.close-product-modal');
        
        closeBtn.addEventListener('click', () => {
            modal.classList.remove('active');
            document.body.style.overflow = 'visible';
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
                document.body.style.overflow = 'visible';
            }
        });
        
        // Add to cart functionality
        const addToCartBtn = modal.querySelector('.add-to-cart');
        addToCartBtn.addEventListener('click', () => {
            addToCartBtn.innerHTML = '<i class="fas fa-check"></i> Added to Cart';
            addToCartBtn.classList.add('added');
            
            setTimeout(() => {
                addToCartBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                addToCartBtn.classList.remove('added');
            }, 2000);
        });
    }
    
    // Create modals for each product
    Object.keys(products).forEach(product => {
        createProductModal(product, products[product]);
    });
    
    // Add event listeners to product buttons
    productBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Get the product name from the nearest product card
            const productCard = btn.closest('.product-card');
            const productImage = productCard.querySelector('.product-image img');
            const productAlt = productImage.alt.toLowerCase().replace(' ', '-');
            
            // Show the corresponding modal
            const modal = document.getElementById(`${productAlt}-modal`);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });
});

// Database simulation for products
const db = {
    products: [
        {
            id: 1,
            name: 'Quantum Cola',
            slug: 'quantum-cola',
            price: 3.99,
            stock: 150,
            rating: 4.8
        },
        {
            id: 2,
            name: 'Zero Gravity',
            slug: 'zero-gravity',
            price: 3.99,
            stock: 120,
            rating: 4.7
        },
        {
            id: 3,
            name: 'Bio Energy',
            slug: 'bio-energy',
            price: 4.49,
            stock: 85,
            rating: 4.9
        },
        {
            id: 4,
            name: 'Nebula Fusion',
            slug: 'nebula-fusion',
            price: 4.99,
            stock: 65,
            rating: 4.6
        }
    ],
    
    users: [],
    
    cart: [],
    
    // Methods to interact with the database
    getProduct: function(slug) {
        return this.products.find(product => product.slug === slug);
    },
    
    getAllProducts: function() {
        return this.products;
    },
    
    addToCart: function(productId, quantity = 1) {
        const product = this.products.find(p => p.id === productId);
        if (!product) return false;
        
        const existingItem = this.cart.find(item => item.productId === productId);
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.cart.push({
                productId: productId,
                quantity: quantity,
                price: product.price
            });
        }
        
        return true;
    },
    
    getCart: function() {
        return this.cart.map(item => {
            const product = this.products.find(p => p.id === item.productId);
            return {
                productId: item.productId,
                quantity: item.quantity,
                price: item.price,
                product: product
            };
        });
    },
    
    registerUser: function(name, email, password) {
        // In a real implementation, this would hash the password
        const userId = this.users.length + 1;
        this.users.push({
            id: userId,
            name: name,
            email: email,
            password: password
        });
        
        return userId;
    },
    
    loginUser: function(email, password) {
        // In a real implementation, this would verify the hashed password
        const user = this.users.find(u => u.email === email && u.password === password);
        return user ? user.id : null;
    }
};

// Add CSS for error messages
const additionalStyles = document.createElement('style');
additionalStyles.innerHTML = `
    .error-message {
        color: #ff006c;
        background: rgba(255, 0, 108, 0.1);
        padding: 1rem;
        border-radius: 5px;
        margin-bottom: 2rem;
        text-align: center;
    }
    
    .add-to-cart.added {
        background: linear-gradient(45deg, #00c853, #00b8ff);
    }
    
    .add-to-cart.added::before {
        background: linear-gradient(45deg, #00c853, #00b8ff);
    }
    
    .add-to-cart.added::after {
        background: linear-gradient(45deg, #00b8ff, #00c853);
    }
    
    .product-price {
        font-size: 2.4rem;
        font-weight: 700;
        color: var(--primary-color);
    }
`;
document.head.appendChild(additionalStyles);

// Initialize animations for sustainability section
document.addEventListener('DOMContentLoaded', () => {
    // Counter animation for sustainability stats
    const statsSection = document.querySelector('.sustainability-stats');
    if (statsSection) {
        // Only initialize if the section exists
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = statsSection.querySelectorAll('.counter-number');
                    counters.forEach(counter => {
                        const target = parseInt(counter.getAttribute('data-count'));
                        let count = 0;
                        const duration = 2000; // 2 seconds
                        const increment = target / (duration / 30); // Update every 30ms
                        
                        const updateCount = () => {
                            if (count < target) {
                                count += increment;
                                counter.textContent = Math.min(Math.round(count), target);
                                requestAnimationFrame(updateCount);
                            } else {
                                counter.textContent = target;
                            }
                        };
                        
                        updateCount();
                    });
                    
                    // Unobserve after animation
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        observer.observe(statsSection);
    }
    
    // Staggered animation for timeline steps
    const timelineSteps = document.querySelectorAll('.timeline-step');
    if (timelineSteps.length) {
        const timelineObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('animate');
                    }, index * 300); // Stagger by 300ms
                    
                    timelineObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });
        
        timelineSteps.forEach(step => {
            timelineObserver.observe(step);
        });
    }
    
    // Video lazy loading
    const sustainabilityVideo = document.querySelector('.sustainability-header video');
    if (sustainabilityVideo) {
        // Create a placeholder until video is loaded
        const videoObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Check if source is already set
                    const source = sustainabilityVideo.querySelector('source');
                    const videoSrc = source.getAttribute('src');
                    
                    // Only load video when in viewport
                    if (videoSrc && !sustainabilityVideo.getAttribute('src')) {
                        sustainabilityVideo.load();
                        sustainabilityVideo.play().catch(error => {
                            console.log('Auto-play was prevented:', error);
                        });
                    }
                    
                    videoObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        videoObserver.observe(sustainabilityVideo);
    }
});

// CSS for timeline animations
const timelineStyles = document.createElement('style');
timelineStyles.innerHTML = `
    .timeline-step {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }
    
    .timeline-step.animate {
        opacity: 1;
        transform: translateY(0);
    }
    
    .timeline-content::before {
        transform: translateY(-50%) scale(0);
        transition: transform 0.5s ease;
    }
    
    .timeline-step.animate .timeline-content::before {
        transform: translateY(-50%) scale(1);
    }
    
    .sustainability-header {
        position: relative;
    }
    
    .sustainability-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--dark-color);
        z-index: 1;
        opacity: 1;
        transition: opacity 0.5s ease;
    }
    
    .sustainability-header.loaded::before {
        opacity: 0;
    }
`;
document.head.appendChild(timelineStyles);

// Set the video to 'loaded' state when it starts playing
document.addEventListener('DOMContentLoaded', () => {
    const video = document.querySelector('.sustainability-header video');
    if (video) {
        video.addEventListener('playing', () => {
            const header = video.closest('.sustainability-header');
            if (header) {
                header.classList.add('loaded');
            }
        });
    }
});

// Initialize 3D bottle model and innovation lab interactions
document.addEventListener('DOMContentLoaded', () => {
    // Initialize 3D bottle model
    const bottleModelSection = document.getElementById('bottle-model-section');
    
    if (bottleModelSection) {
        // Create bubbles for the bottle
        const bottleBubbles = bottleModelSection.querySelector('.bottle-bubbles');
        if (bottleBubbles) {
            for (let i = 0; i < 20; i++) {
                const bubble = document.createElement('div');
                bubble.classList.add('bottle-bubble');
                
                // Random properties
                const size = Math.random() * 8 + 2;
                const left = Math.random() * 100;
                const delay = Math.random() * 5;
                const duration = Math.random() * 5 + 3;
                
                bubble.style.width = `${size}px`;
                bubble.style.height = `${size}px`;
                bubble.style.left = `${left}%`;
                bubble.style.animationDelay = `${delay}s`;
                bubble.style.animationDuration = `${duration}s`;
                
                bottleBubbles.appendChild(bubble);
            }
        }
        
        // Toggle bottle rotation
        const rotateBtn = bottleModelSection.querySelector('.rotate-toggle');
        const bottleModel = bottleModelSection.querySelector('.bottle-3d');
        
        if (rotateBtn && bottleModel) {
            rotateBtn.addEventListener('click', () => {
                bottleModel.classList.toggle('rotating');
                
                if (bottleModel.classList.contains('rotating')) {
                    rotateBtn.innerHTML = '<i class="fas fa-pause"></i> Stop Rotation';
                } else {
                    rotateBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Rotate Bottle';
                }
            });
        }
        
        // Formula exploration modal
        const formulaBtn = bottleModelSection.querySelector('.formula-btn');
        const exploreBtn = bottleModelSection.querySelector('.formula-modal-btn');
        
        if (formulaBtn) {
            formulaBtn.addEventListener('click', () => {
                createFormulaModal();
            });
        }
        
        if (exploreBtn) {
            exploreBtn.addEventListener('click', () => {
                createFormulaModal();
            });
        }
        
        function createFormulaModal() {
            // Create modal container
            const modal = document.createElement('div');
            modal.className = 'formula-exploration-modal';
            
            modal.innerHTML = `
                <div class="formula-container">
                    <button class="close-formula"><i class="fas fa-times"></i></button>
                    
                    <div class="formula-header">
                        <h3>Quantum Cola Formula</h3>
                        <p>Explore the molecular structure of our revolutionary beverage</p>
                    </div>
                    
                    <div class="formula-visualization">
                        <div class="molecular-structure">
                            <div class="molecule molecule-h2o">
                                <div class="atom atom-o"></div>
                                <div class="atom atom-h h1"></div>
                                <div class="atom atom-h h2"></div>
                                <div class="bond bond-oh1"></div>
                                <div class="bond bond-oh2"></div>
                            </div>
                            
                            <div class="molecule molecule-quantum">
                                <div class="atom atom-q"></div>
                                <div class="quantum-field"></div>
                            </div>
                            
                            <div class="molecule molecule-flavor">
                                <div class="atom atom-f1"></div>
                                <div class="atom atom-f2"></div>
                                <div class="atom atom-f3"></div>
                                <div class="bond bond-f1f2"></div>
                                <div class="bond bond-f2f3"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="formula-tabs">
                        <button class="formula-tab active" data-tab="composition">Composition</button>
                        <button class="formula-tab" data-tab="effects">Effects</button>
                        <button class="formula-tab" data-tab="production">Production</button>
                    </div>
                    
                    <div class="formula-content">
                        <div class="formula-panel active" id="composition-panel">
                            <h4>Quantum-Enhanced Composition</h4>
                            <p>Our revolutionary formula combines classic refreshment with cutting-edge molecular science:</p>
                            <ul class="formula-list">
                                <li><span>Base Matrix:</span> Purified water infused with quantum stabilizers</li>
                                <li><span>Flavor Complex:</span> Proprietary blend of natural and synthetic enhancers</li>
                                <li><span>Quantum Particles:</span> Nano-scale particles that adapt to your taste preferences</li>
                                <li><span>Sensory Amplifiers:</span> Compounds that enhance flavor perception</li>
                            </ul>
                        </div>
                        
                        <div class="formula-panel" id="effects-panel">
                            <h4>Adaptive Experience Effects</h4>
                            <p>The Quantum Cola formula creates a unique experience for each consumer:</p>
                            <ul class="formula-list">
                                <li><span>Taste Evolution:</span> Flavor profile adapts during consumption</li>
                                <li><span>Sensory Enhancement:</span> Heightened perception of complementary flavors</li>
                                <li><span>Optimal Hydration:</span> Advanced hydration delivery system</li>
                                <li><span>Temperature Adaptation:</span> Formula remains at optimal temperature longer</li>
                            </ul>
                        </div>
                        
                        <div class="formula-panel" id="production-panel">
                            <h4>Revolutionary Production Process</h4>
                            <p>Creating Quantum Cola requires our advanced manufacturing technology:</p>
                            <ul class="formula-list">
                                <li><span>Quantum Infusion:</span> Specialized chambers for particle integration</li>
                                <li><span>Molecular Alignment:</span> Laser-guided flavor molecule arrangement</li>
                                <li><span>Zero-Impact Manufacturing:</span> 100% sustainable production process</li>
                                <li><span>Quantum Field Stabilization:</span> Ensures consistent experience in every bottle</li>
                            </ul>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Add animation
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
            
            // Tab switching
            const formulaTabs = modal.querySelectorAll('.formula-tab');
            const formulaPanels = modal.querySelectorAll('.formula-panel');
            
            formulaTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const targetTab = tab.getAttribute('data-tab');
                    
                    // Update active tab
                    formulaTabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    
                    // Show corresponding panel
                    formulaPanels.forEach(panel => {
                        panel.classList.remove('active');
                        if (panel.id === `${targetTab}-panel`) {
                            panel.classList.add('active');
                        }
                    });
                });
            });
            
            // Close modal
            const closeBtn = modal.querySelector('.close-formula');
            closeBtn.addEventListener('click', () => {
                modal.classList.remove('active');
                
                setTimeout(() => {
                    modal.remove();
                }, 300);
            });
            
            // Close when clicking outside the container
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                    
                    setTimeout(() => {
                        modal.remove();
                    }, 300);
                }
            });
        }
    }
    
    // Enhanced login/signup functionality with user display
    const authButton = document.getElementById('authButton');
    
    // Check if user is logged in (from localStorage)
    const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
    const userName = localStorage.getItem('userName');
    
    if (isLoggedIn && userName && authButton) {
        // Replace login button with user dropdown
        const userDropdown = document.createElement('div');
        userDropdown.className = 'user-dropdown';
        
        // Create user avatar with initials
        const initials = userName.split(' ').map(name => name[0]).join('').toUpperCase();
        
        userDropdown.innerHTML = `
            <div class="user-avatar">
                <span class="initials">${initials}</span>
            </div>
            <div class="dropdown-menu">
                <div class="user-info">
                    <div class="avatar-small">
                        <span class="initials">${initials}</span>
                    </div>
                    <div class="user-name">
                        <h4>${userName}</h4>
                        <p>Premium Member</p>
                    </div>
                </div>
                <ul class="dropdown-links">
                    <li><a href="#"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="#"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                    <li><a href="#"><i class="fas fa-heart"></i> Favorites</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
                <div class="dropdown-footer">
                    <button class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </div>
            </div>
        `;
        
        authButton.parentNode.replaceChild(userDropdown, authButton);
        
        // Toggle dropdown menu
        const avatar = userDropdown.querySelector('.user-avatar');
        const dropdownMenu = userDropdown.querySelector('.dropdown-menu');
        
        avatar.addEventListener('click', () => {
            dropdownMenu.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!userDropdown.contains(e.target)) {
                dropdownMenu.classList.remove('active');
            }
        });
        
        // Logout functionality
        const logoutBtn = userDropdown.querySelector('.logout-btn');
        logoutBtn.addEventListener('click', () => {
            localStorage.setItem('isLoggedIn', 'false');
            localStorage.removeItem('userName');
            window.location.reload();
        });
    }
    
    // Improved login/signup form with tab switching and validation
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            
            // Simple validation
            if (email && password) {
                // Show loading state
                const submitBtn = loginForm.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
                
                // Simulate backend validation (would be an API call in production)
                setTimeout(() => {
                    // Generate user name from email
                    const userName = email.split('@')[0].replace(/[^a-zA-Z0-9]/g, ' ');
                    const formattedName = userName.split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ');
                    
                    // Save to localStorage
                    localStorage.setItem('isLoggedIn', 'true');
                    localStorage.setItem('userName', formattedName);
                    
                    // Show success message
                    loginForm.innerHTML = `
                        <div class="success-message">
                            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                            </svg>
                            <h3>Login Successful</h3>
                            <p>Welcome back, ${formattedName}! Redirecting to your account...</p>
                        </div>
                    `;
                    
                    // Close modal and refresh page
                    const authModal = document.getElementById('authModal');
                    setTimeout(() => {
                        authModal.classList.remove('active');
                        document.body.style.overflow = 'visible';
                        window.location.reload();
                    }, 2000);
                }, 1500);
            }
        });
    }
    
    if (signupForm) {
        signupForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const name = document.getElementById('signup-name').value;
            const email = document.getElementById('signup-email').value;
            const password = document.getElementById('signup-password').value;
            const confirmPassword = document.getElementById('signup-confirm').value;
            
            // Simple validation
            if (name && email && password && password === confirmPassword) {
                // Show loading state
                const submitBtn = signupForm.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
                
                // Simulate account creation (would be an API call in production)
                setTimeout(() => {
                    // Save to localStorage
                    localStorage.setItem('isLoggedIn', 'true');
                    localStorage.setItem('userName', name);
                    
                    // Show success message
                    signupForm.innerHTML = `
                        <div class="success-message">
                            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                            </svg>
                            <h3>Account Created</h3>
                            <p>Welcome, ${name}! Your account has been created successfully. Redirecting to your account...</p>
                        </div>
                    `;
                    
                    // Close modal and refresh page
                    const authModal = document.getElementById('authModal');
                    setTimeout(() => {
                        authModal.classList.remove('active');
                        document.body.style.overflow = 'visible';
                        window.location.reload();
                    }, 2000);
                }, 1500);
            } else if (password !== confirmPassword) {
                // Show password error
                const passwordError = document.createElement('div');
                passwordError.className = 'error-message';
                passwordError.textContent = 'Passwords do not match';
                
                const existingError = signupForm.querySelector('.error-message');
                if (existingError) {
                    existingError.remove();
                }
                
                signupForm.prepend(passwordError);
                
                setTimeout(() => {
                    passwordError.remove();
                }, 3000);
            }
        });
    }
});

// Roadmap section animations
document.addEventListener('DOMContentLoaded', () => {
    const roadmapSection = document.getElementById('roadmap-section');
    
    if (roadmapSection) {
        // Add CSS for roadmap phase animations
        const roadmapStyles = document.createElement('style');
        roadmapStyles.innerHTML = `
            .roadmap-phase {
                opacity: 0;
                transform: translateY(30px);
                transition: opacity 0.6s ease, transform 0.6s ease;
            }
            
            .roadmap-phase.animated {
                opacity: 1;
                transform: translateY(0);
            }
            
            .phase-marker {
                transform: scale(0);
                transition: transform 0.5s ease 0.3s;
            }
            
            .roadmap-phase.animated .phase-marker {
                transform: scale(1);
            }
        `;
        document.head.appendChild(roadmapStyles);
        
        // Animate phases on scroll
        const phases = roadmapSection.querySelectorAll('.roadmap-phase');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('animated');
                    }, index * 200);
                }
            });
        }, { threshold: 0.2 });
        
        phases.forEach(phase => {
            observer.observe(phase);
        });
        
        // Join mission button opens modal with form
        const joinMissionBtn = roadmapSection.querySelector('.join-mission-btn');
        
        if (joinMissionBtn) {
            joinMissionBtn.addEventListener('click', () => {
                // Create mission modal
                const modal = document.createElement('div');
                modal.className = 'mission-modal';
                
                modal.innerHTML = `
                    <div class="mission-container">
                        <button class="close-mission"><i class="fas fa-times"></i></button>
                        
                        <div class="mission-header">
                            <h3>Join Our Future Mission</h3>
                            <p>Be part of the Coca-Cola revolution and help shape the future of refreshment</p>
                        </div>
                        
                        <form class="mission-form">
                            <div class="form-group">
                                <input type="text" id="mission-name" placeholder=" " required>
                                <label for="mission-name">Your Name</label>
                                <i class="fas fa-user"></i>
                            </div>
                            
                            <div class="form-group">
                                <input type="email" id="mission-email" placeholder=" " required>
                                <label for="mission-email">Your Email</label>
                                <i class="fas fa-envelope"></i>
                            </div>
                            
                            <div class="form-group">
                                <select id="mission-interest" required>
                                    <option value="" disabled selected>Your Area of Interest</option>
                                    <option value="innovation">Product Innovation</option>
                                    <option value="sustainability">Sustainability Initiatives</option>
                                    <option value="technology">Future Technology</option>
                                    <option value="marketing">Experience Marketing</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <textarea id="mission-message" placeholder=" " required></textarea>
                                <label for="mission-message">Why you want to join our mission</label>
                            </div>
                            
                            <div class="form-footer">
                                <label class="checkbox-container">
                                    <input type="checkbox" required>
                                    <span class="checkmark"></span>
                                    <span>I agree to receive future communications about Coca-Cola innovations</span>
                                </label>
                            </div>
                            
                            <button type="submit" class="btn primary-btn submit-mission">Join the Mission</button>
                        </form>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                // Add animation
                setTimeout(() => {
                    modal.classList.add('active');
                }, 10);
                
                // Form input animation
                const formInputs = modal.querySelectorAll('input, textarea, select');
                
                formInputs.forEach(input => {
                    input.addEventListener('focus', () => {
                        input.parentElement.classList.add('focused');
                    });
                    
                    input.addEventListener('blur', () => {
                        if (input.value.trim() === '') {
                            input.parentElement.classList.remove('focused');
                        }
                    });
                });
                
                // Form submission
                const missionForm = modal.querySelector('.mission-form');
                
                missionForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    
                    const submitBtn = missionForm.querySelector('.submit-mission');
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    submitBtn.disabled = true;
                    
                    // Simulate form submission
                    setTimeout(() => {
                        missionForm.innerHTML = `
                            <div class="success-message">
                                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                                </svg>
                                <h3>You're Now Part of Our Mission!</h3>
                                <p>Thank you for joining us. We'll contact you soon with more information about our future initiatives.</p>
                                <button class="btn secondary-btn close-success">Return to Experience</button>
                            </div>
                        `;
                        
                        const closeSuccessBtn = missionForm.querySelector('.close-success');
                        closeSuccessBtn.addEventListener('click', () => {
                            modal.classList.remove('active');
                            setTimeout(() => {
                                modal.remove();
                            }, 300);
                        });
                    }, 2000);
                });
                
                // Close modal
                const closeBtn = modal.querySelector('.close-mission');
                closeBtn.addEventListener('click', () => {
                    modal.classList.remove('active');
                    setTimeout(() => {
                        modal.remove();
                    }, 300);
                });
                
                // Close when clicking outside
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.classList.remove('active');
                        setTimeout(() => {
                            modal.remove();
                        }, 300);
                    }
                });
            });
        }
    }
});

// Evolution section animations and interactions
document.addEventListener('DOMContentLoaded', () => {
    const evolutionSection = document.getElementById('evolution-section');
    
    if (evolutionSection) {
        // Add CSS for evolution timeline animations
        const evolutionStyles = document.createElement('style');
        evolutionStyles.innerHTML = `
            .timeline-point {
                opacity: 0;
                transform: translateY(30px);
                transition: opacity 0.8s ease, transform 0.8s ease;
            }
            
            .timeline-point.animated {
                opacity: 1;
                transform: translateY(0);
            }
            
            .point-marker {
                transform: scale(0);
                transition: transform 0.5s ease 0.3s;
            }
            
            .timeline-point.animated .point-marker {
                transform: scale(1);
            }
            
            .point-image {
                transition: transform 0.5s ease;
                perspective: 1000px;
            }
            
            .point-image img {
                transition: transform 0.5s ease;
            }
        `;
        document.head.appendChild(evolutionStyles);
        
        // Animate timeline points on scroll
        const timelinePoints = evolutionSection.querySelectorAll('.timeline-point');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('animated');
                    }, index * 300);
                }
            });
        }, { threshold: 0.2 });
        
        timelinePoints.forEach(point => {
            observer.observe(point);
        });
        
        // 3D hover effect for images
        const pointImages = evolutionSection.querySelectorAll('.point-image');
        
        pointImages.forEach(image => {
            // Mouse tracking on evolution timeline images has been disabled
            // to prevent movement when cursor hovers over images
        });
        
        // Join Mission button in Evolution section opens the same modal
        const joinMissionBtn = evolutionSection.querySelector('.cta-btn');
        
        if (joinMissionBtn) {
            joinMissionBtn.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Find and click the Join Mission button in the roadmap section
                const roadmapMissionBtn = document.querySelector('.join-mission-btn');
                if (roadmapMissionBtn) {
                    roadmapMissionBtn.click();
                }
            });
        }
    }
});

// === ACCOUNT SECTIONS FUNCTIONALITY ===
function initAccountSections() {
    const accountSections = document.querySelector('.account-sections');
    const userDropdown = document.querySelector('.user-dropdown');
    
    if (!accountSections || !userDropdown) return;
    
    // Close account section
    const closeAccountBtn = document.querySelector('.account-close');
    if (closeAccountBtn) {
        closeAccountBtn.addEventListener('click', () => {
            accountSections.style.display = 'none';
            document.body.style.overflow = '';
        });
    }
    
    // Add event listeners to account links in dropdown
    const accountLinks = userDropdown.querySelectorAll('[data-section]');
    accountLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const sectionName = link.getAttribute('data-section');
            openAccountSection(sectionName);
        });
    });
    
    // Open specific account section
    function openAccountSection(sectionName) {
        const allSections = document.querySelectorAll('.account-section');
        allSections.forEach(section => {
            section.classList.remove('active');
        });
        
        const targetSection = document.getElementById(sectionName);
        if (targetSection) {
            accountSections.style.display = 'block';
            targetSection.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    // Settings navigation
    const settingsNavItems = document.querySelectorAll('.settings-nav-item');
    const settingsPanels = document.querySelectorAll('.settings-panel');
    
    settingsNavItems.forEach(item => {
        item.addEventListener('click', () => {
            // Remove active class from all nav items
            settingsNavItems.forEach(navItem => {
                navItem.classList.remove('active');
            });
            
            // Add active class to clicked item
            item.classList.add('active');
            
            // Show corresponding panel
            const targetPanel = item.getAttribute('data-panel');
            settingsPanels.forEach(panel => {
                panel.classList.remove('active');
            });
            
            document.getElementById(targetPanel).classList.add('active');
        });
    });
    
    // Theme Toggle
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        // Check if user has a saved theme preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            document.documentElement.classList.add('light-theme');
            themeToggle.checked = true;
            updateThemeLabels(true);
        }
        
        themeToggle.addEventListener('change', () => {
            if (themeToggle.checked) {
                document.documentElement.classList.add('light-theme');
                localStorage.setItem('theme', 'light');
                updateThemeLabels(true);
            } else {
                document.documentElement.classList.remove('light-theme');
                localStorage.setItem('theme', 'dark');
                updateThemeLabels(false);
            }
        });
    }
    
    function updateThemeLabels(isLight) {
        const darkLabel = document.querySelector('.mode-label[data-mode="dark"]');
        const lightLabel = document.querySelector('.mode-label[data-mode="light"]');
        
        if (darkLabel && lightLabel) {
            if (isLight) {
                darkLabel.classList.remove('active');
                lightLabel.classList.add('active');
            } else {
                lightLabel.classList.remove('active');
                darkLabel.classList.add('active');
            }
        }
    }
    
    // Color Selection
    const colorOptions = document.querySelectorAll('.color-option');
    if (colorOptions.length > 0) {
        // Check if user has a saved color preference
        const savedColor = localStorage.getItem('accentColor');
        if (savedColor) {
            document.documentElement.style.setProperty('--primary-color', savedColor);
            updateSelectedColor(savedColor);
        }
        
        colorOptions.forEach(option => {
            option.addEventListener('click', () => {
                const color = option.getAttribute('data-color');
                let colorValue;
                
                switch(color) {
                    case 'blue':
                        colorValue = '#00b8ff';
                        break;
                    case 'purple':
                        colorValue = '#8a2be2';
                        break;
                    case 'green':
                        colorValue = '#00c853';
                        break;
                    case 'red':
                        colorValue = '#ff1744';
                        break;
                    case 'orange':
                        colorValue = '#ff9100';
                        break;
                    default:
                        colorValue = '#00b8ff';
                }
                
                document.documentElement.style.setProperty('--primary-color', colorValue);
                localStorage.setItem('accentColor', colorValue);
                updateSelectedColor(colorValue);
            });
        });
    }
    
    function updateSelectedColor(colorValue) {
        const colorOptions = document.querySelectorAll('.color-option');
        colorOptions.forEach(option => {
            option.classList.remove('selected');
            
            let optionColor;
            const color = option.getAttribute('data-color');
            
            switch(color) {
                case 'blue':
                    optionColor = '#00b8ff';
                    break;
                case 'purple':
                    optionColor = '#8a2be2';
                    break;
                case 'green':
                    optionColor = '#00c853';
                    break;
                case 'red':
                    optionColor = '#ff1744';
                    break;
                case 'orange':
                    optionColor = '#ff9100';
                    break;
            }
            
            if (optionColor === colorValue) {
                option.classList.add('selected');
            }
        });
    }
    
    // Animation Intensity
    const animationSlider = document.getElementById('animation-intensity');
    if (animationSlider) {
        // Check if user has a saved animation preference
        const savedIntensity = localStorage.getItem('animationIntensity');
        if (savedIntensity) {
            animationSlider.value = savedIntensity;
            updateAnimationIntensity(savedIntensity);
        }
        
        animationSlider.addEventListener('input', () => {
            const intensity = animationSlider.value;
            updateAnimationIntensity(intensity);
        });
        
        animationSlider.addEventListener('change', () => {
            localStorage.setItem('animationIntensity', animationSlider.value);
        });
    }
    
    function updateAnimationIntensity(intensity) {
        // Adjust animation scale based on intensity value
        const scale = intensity / 100;
        
        // Apply to all animations
        document.documentElement.style.setProperty('--animation-scale', scale);
        
        // Adjust particle count, bottle rotation speed, etc.
        if (window.bottleRotationSpeed) {
            window.bottleRotationSpeed = 0.2 + (scale * 0.3); // Base speed + scaling
        }
        
        if (window.bubbleCount) {
            const newCount = Math.floor(10 + (scale * 40)); // Between 10-50 bubbles
            updateBubbleCount(newCount);
        }
    }
    
    function updateBubbleCount(count) {
        const bottleModel = document.querySelector('.bottle-model');
        if (!bottleModel) return;
        
        // Remove existing bubbles
        const existingBubbles = bottleModel.querySelectorAll('.bubble');
        existingBubbles.forEach(bubble => bubble.remove());
        
        // Create new bubbles
        for (let i = 0; i < count; i++) {
            createBottleBubble(bottleModel);
        }
        
        window.bubbleCount = count;
    }
    
    // Save settings
    const saveSettingsBtn = document.querySelector('.btn-save-settings');
    if (saveSettingsBtn) {
        saveSettingsBtn.addEventListener('click', () => {
            const formData = new FormData(document.querySelector('.settings-form'));
            // In a real app, you would send this data to the server
            
            // Show success message
            const successMsg = document.createElement('div');
            successMsg.className = 'settings-success';
            successMsg.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <p>Settings saved successfully!</p>
            `;
            
            const settingsContent = document.querySelector('.settings-content');
            settingsContent.appendChild(successMsg);
            
            // Remove after 3 seconds
            setTimeout(() => {
                successMsg.style.opacity = '0';
                setTimeout(() => {
                    successMsg.remove();
                }, 300);
            }, 3000);
        });
    }
}

// Reset settings
function resetSettings() {
    const themeToggle = document.getElementById('theme-toggle');
    const resetSettingsBtn = document.querySelector('.btn-reset-settings');
    
    if (resetSettingsBtn) {
        resetSettingsBtn.addEventListener('click', () => {
            // Reset theme
            document.documentElement.classList.remove('light-theme');
            if (themeToggle) themeToggle.checked = false;
            localStorage.removeItem('theme');
            
            // Update theme labels
            const darkLabel = document.querySelector('.mode-label[data-mode="dark"]');
            const lightLabel = document.querySelector('.mode-label[data-mode="light"]');
            if (darkLabel && lightLabel) {
                lightLabel.classList.remove('active');
                darkLabel.classList.add('active');
            }
            
            // Reset color
            document.documentElement.style.setProperty('--primary-color', '#00b8ff');
            localStorage.removeItem('accentColor');
            
            // Reset animation intensity
            const animationSlider = document.getElementById('animation-intensity');
            if (animationSlider) {
                animationSlider.value = 50;
                localStorage.removeItem('animationIntensity');
            }
            
            // Reset form fields
            const form = document.querySelector('.settings-form');
            if (form) form.reset();
            
            // Show success message
            const successMsg = document.createElement('div');
            successMsg.className = 'settings-success';
            successMsg.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <p>Settings reset successfully!</p>
            `;
            
            const settingsContent = document.querySelector('.settings-content');
            if (settingsContent) {
                settingsContent.appendChild(successMsg);
                
                // Remove after 3 seconds
                setTimeout(() => {
                    successMsg.style.opacity = '0';
                    setTimeout(() => {
                        successMsg.remove();
                    }, 300);
                }, 3000);
            }
        });
    }
}

// Two-factor Authentication Toggle
function initTwoFactorAuth() {
    const twoFactorToggle = document.getElementById('two-factor-toggle');
    if (twoFactorToggle) {
        twoFactorToggle.addEventListener('change', () => {
            const statusIndicator = document.querySelector('.two-factor-status .status-indicator');
            const statusText = document.querySelector('.two-factor-status .status-text');
            
            if (twoFactorToggle.checked) {
                // In a real app, this would trigger a 2FA setup flow
                statusIndicator.className = 'status-indicator enabled';
                statusText.className = 'status-text enabled';
                statusText.textContent = 'Enabled';
                
                // Show verification modal (simplified for demo)
                const verifyModal = document.createElement('div');
                verifyModal.className = 'security-verify-modal';
                verifyModal.innerHTML = `
                    <div class="security-verify-content">
                        <h3>Set Up Two-Factor Authentication</h3>
                        <p>We've sent a verification code to your email. Please enter it below to complete setup.</p>
                        <div class="verify-form">
                            <input type="text" placeholder="Enter verification code" class="verify-input" maxlength="6">
                            <button class="btn-verify">Verify</button>
                        </div>
                        <button class="btn-cancel-verify">Cancel</button>
                    </div>
                `;
                
                document.body.appendChild(verifyModal);
                
                // Handle verification
                const btnVerify = verifyModal.querySelector('.btn-verify');
                const btnCancel = verifyModal.querySelector('.btn-cancel-verify');
                
                btnVerify.addEventListener('click', () => {
                    const code = verifyModal.querySelector('.verify-input').value;
                    if (code && code.length === 6) {
                        verifyModal.remove();
                        // In a real app, this would verify the code with the server
                    }
                });
                
                btnCancel.addEventListener('click', () => {
                    verifyModal.remove();
                    twoFactorToggle.checked = false;
                    statusIndicator.className = 'status-indicator disabled';
                    statusText.className = 'status-text disabled';
                    statusText.textContent = 'Disabled';
                });
            } else {
                statusIndicator.className = 'status-indicator disabled';
                statusText.className = 'status-text disabled';
                statusText.textContent = 'Disabled';
            }
        });
    }
}

// === INITIALIZE ACCOUNT FEATURES ===
function initializeAccount() {
    // Initialize theme toggle
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        // Check saved theme preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            document.documentElement.classList.add('light-theme');
            themeToggle.checked = true;
            updateThemeLabels(true);
        }
        
        themeToggle.addEventListener('change', () => {
            if (themeToggle.checked) {
                document.documentElement.classList.add('light-theme');
                localStorage.setItem('theme', 'light');
                updateThemeLabels(true);
            } else {
                document.documentElement.classList.remove('light-theme');
                localStorage.setItem('theme', 'dark');
                updateThemeLabels(false);
            }
        });
    }
    
    function updateThemeLabels(isLight) {
        const darkLabel = document.querySelector('.mode-label[data-mode="dark"]');
        const lightLabel = document.querySelector('.mode-label[data-mode="light"]');
        
        if (darkLabel && lightLabel) {
            if (isLight) {
                darkLabel.classList.remove('active');
                lightLabel.classList.add('active');
            } else {
                lightLabel.classList.remove('active');
                darkLabel.classList.add('active');
            }
        }
    }
    
    // Two-factor authentication
    const twoFactorToggle = document.getElementById('two-factor-toggle');
    if (twoFactorToggle) {
        twoFactorToggle.addEventListener('change', () => {
            const statusIndicator = document.querySelector('.two-factor-status .status-indicator');
            const statusText = document.querySelector('.two-factor-status .status-text');
            
            if (twoFactorToggle.checked) {
                // In a real app, this would trigger a 2FA setup flow
                statusIndicator.className = 'status-indicator enabled';
                statusText.className = 'status-text enabled';
                statusText.textContent = 'Enabled';
                
                // Show verification modal
                const verifyModal = document.createElement('div');
                verifyModal.className = 'security-verify-modal';
                verifyModal.innerHTML = `
                    <div class="security-verify-content">
                        <h3>Set Up Two-Factor Authentication</h3>
                        <p>We've sent a verification code to your email. Please enter it below to complete setup.</p>
                        <div class="verify-form">
                            <input type="text" placeholder="Enter verification code" class="verify-input" maxlength="6">
                            <button class="btn-verify">Verify</button>
                        </div>
                        <button class="btn-cancel-verify">Cancel</button>
                    </div>
                `;
                
                document.body.appendChild(verifyModal);
                
                // Handle verification
                const btnVerify = verifyModal.querySelector('.btn-verify');
                const btnCancel = verifyModal.querySelector('.btn-cancel-verify');
                
                btnVerify.addEventListener('click', () => {
                    const code = verifyModal.querySelector('.verify-input').value;
                    if (code && code.length === 6) {
                        verifyModal.remove();
                    }
                });
                
                btnCancel.addEventListener('click', () => {
                    verifyModal.remove();
                    twoFactorToggle.checked = false;
                    statusIndicator.className = 'status-indicator disabled';
                    statusText.className = 'status-text disabled';
                    statusText.textContent = 'Disabled';
                });
            } else {
                statusIndicator.className = 'status-indicator disabled';
                statusText.className = 'status-text disabled';
                statusText.textContent = 'Disabled';
            }
        });
    }
    
    // Reset settings
    const resetSettingsBtn = document.querySelector('.btn-reset-settings');
    if (resetSettingsBtn) {
        resetSettingsBtn.addEventListener('click', () => {
            // Reset theme
            document.documentElement.classList.remove('light-theme');
            if (themeToggle) themeToggle.checked = false;
            localStorage.removeItem('theme');
            updateThemeLabels(false);
            
            // Reset form fields
            const form = document.querySelector('.settings-form');
            if (form) form.reset();
        });
    }
    
    // Open account sections
}

// Account features initialization
document.addEventListener('DOMContentLoaded', () => {
    // Theme toggle functionality
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        // Check saved theme preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            document.documentElement.classList.add('light-theme');
            themeToggle.checked = true;
            
            // Update labels if they exist
            const darkLabel = document.querySelector('.mode-label[data-mode="dark"]');
            const lightLabel = document.querySelector('.mode-label[data-mode="light"]');
            if (darkLabel && lightLabel) {
                darkLabel.classList.remove('active');
                lightLabel.classList.add('active');
            }
        }
        
        // Toggle theme on change
        themeToggle.addEventListener('change', () => {
            if (themeToggle.checked) {
                document.documentElement.classList.add('light-theme');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.remove('light-theme');
                localStorage.setItem('theme', 'dark');
            }
            
            // Update labels if they exist
            const darkLabel = document.querySelector('.mode-label[data-mode="dark"]');
            const lightLabel = document.querySelector('.mode-label[data-mode="light"]');
            if (darkLabel && lightLabel) {
                if (themeToggle.checked) {
                    darkLabel.classList.remove('active');
                    lightLabel.classList.add('active');
                } else {
                    lightLabel.classList.remove('active');
                    darkLabel.classList.add('active');
                }
            }
        });
    }
    
    // Two-factor authentication toggle
    const twoFactorToggle = document.getElementById('two-factor-toggle');
    if (twoFactorToggle) {
        twoFactorToggle.addEventListener('change', () => {
            const statusIndicator = document.querySelector('.two-factor-status .status-indicator');
            const statusText = document.querySelector('.two-factor-status .status-text');
            
            if (twoFactorToggle.checked) {
                // In a real app, this would trigger a 2FA setup flow
                statusIndicator.className = 'status-indicator enabled';
                statusText.className = 'status-text enabled';
                statusText.textContent = 'Enabled';
                
                // Show verification modal
                const verifyModal = document.createElement('div');
                verifyModal.className = 'security-verify-modal';
                verifyModal.innerHTML = `
                    <div class="security-verify-content">
                        <h3>Set Up Two-Factor Authentication</h3>
                        <p>We've sent a verification code to your email. Please enter it below to complete setup.</p>
                        <div class="verify-form">
                            <input type="text" placeholder="Enter verification code" class="verify-input" maxlength="6">
                            <button class="btn-verify">Verify</button>
                        </div>
                        <button class="btn-cancel-verify">Cancel</button>
                    </div>
                `;
                
                document.body.appendChild(verifyModal);
                
                // Handle verification
                const btnVerify = verifyModal.querySelector('.btn-verify');
                const btnCancel = verifyModal.querySelector('.btn-cancel-verify');
                
                btnVerify.addEventListener('click', () => {
                    const code = verifyModal.querySelector('.verify-input').value;
                    if (code && code.length === 6) {
                        verifyModal.remove();
                    }
                });
                
                btnCancel.addEventListener('click', () => {
                    verifyModal.remove();
                    twoFactorToggle.checked = false;
                    statusIndicator.className = 'status-indicator disabled';
                    statusText.className = 'status-text disabled';
                    statusText.textContent = 'Disabled';
                });
            } else {
                statusIndicator.className = 'status-indicator disabled';
                statusText.className = 'status-text disabled';
                statusText.textContent = 'Disabled';
            }
        });
    }
    
    // Account section navigation
    const userDropdown = document.querySelector('.user-dropdown');
    const accountSections = document.querySelector('.account-sections');
    
    if (userDropdown && accountSections) {
        // Close account section
        const closeAccountBtn = accountSections.querySelector('.account-close');
        if (closeAccountBtn) {
            closeAccountBtn.addEventListener('click', () => {
                accountSections.style.display = 'none';
                document.body.style.overflow = '';
            });
        }
        
        // Open account sections from dropdown
        const accountLinks = userDropdown.querySelectorAll('[data-section]');
        accountLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const sectionName = link.getAttribute('data-section');
                
                // Hide all sections, show the target one
                const allSections = document.querySelectorAll('.account-section');
                allSections.forEach(section => {
                    section.classList.remove('active');
                });
                
                const targetSection = document.getElementById(sectionName);
                if (targetSection) {
                    accountSections.style.display = 'block';
                    targetSection.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
                
                // Hide dropdown menu
                userDropdown.querySelector('.dropdown-menu').classList.remove('active');
            });
        });
    }
    
    // Settings panel navigation
    const settingsNavItems = document.querySelectorAll('.settings-nav-item');
    const settingsPanels = document.querySelectorAll('.settings-panel');
    
    settingsNavItems.forEach(item => {
        item.addEventListener('click', () => {
            // Remove active class from all nav items
            settingsNavItems.forEach(navItem => {
                navItem.classList.remove('active');
            });
            
            // Add active class to clicked item
            item.classList.add('active');
            
            // Show corresponding panel
            const targetPanel = item.getAttribute('data-panel');
            settingsPanels.forEach(panel => {
                panel.classList.remove('active');
            });
            
            document.getElementById(targetPanel).classList.add('active');
        });
    });
    
    // Improved bottle model
    const bottleModel = document.querySelector('.bottle-model');
    if (bottleModel) {
        // Add bubbles to the bottle model
        const bubbleCount = 30;
        window.bubbleCount = bubbleCount;
        
        for (let i = 0; i < bubbleCount; i++) {
            const bubble = document.createElement('div');
            bubble.className = 'bubble';
            
            // Random properties
            const size = Math.random() * 15 + 5; // 5-20px
            const positionX = Math.random() * 100; // 0-100%
            const positionY = Math.random() * 100; // 0-100%
            const delay = Math.random() * 5; // 0-5s
            const duration = Math.random() * 10 + 10; // 10-20s
            
            // Set styles
            bubble.style.width = `${size}px`;
            bubble.style.height = `${size}px`;
            bubble.style.left = `${positionX}%`;
            bubble.style.bottom = `${positionY}%`;
            bubble.style.animationDelay = `${delay}s`;
            bubble.style.animationDuration = `${duration}s`;
            
            // Add to container
            bottleModel.appendChild(bubble);
        }
        
        // Add bottle rotation
        let isRotating = true;
        window.bottleRotationSpeed = 0.3;
        let rotationAngle = 0;
        
        function animateBottle() {
            if (isRotating) {
                rotationAngle += window.bottleRotationSpeed;
                bottleModel.style.transform = `rotateY(${rotationAngle}deg)`;
            }
            requestAnimationFrame(animateBottle);
        }
        
        animateBottle();
        
        // Toggle rotation button
        const rotateToggle = document.querySelector('.rotate-toggle');
        if (rotateToggle) {
            rotateToggle.addEventListener('click', () => {
                isRotating = !isRotating;
                if (isRotating) {
                    rotateToggle.innerHTML = '<i class="fas fa-pause"></i> Pause Rotation';
                } else {
                    rotateToggle.innerHTML = '<i class="fas fa-play"></i> Resume Rotation';
                }
            });
        }
    }
});