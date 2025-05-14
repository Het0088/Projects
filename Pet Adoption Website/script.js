document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.querySelector(".sidebar");
    const menuBtn = document.querySelector(".menu-btn");
    const closeBtn = document.querySelector(".close-btn");

    menuBtn.addEventListener("click", () => {
        sidebar.classList.toggle("active");
    });

    closeBtn.addEventListener("click", () => {
        sidebar.classList.remove("active");
    });
});

// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    const body = document.body;

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            body.classList.toggle('menu-open');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (navLinks.classList.contains('active') && 
                !navLinks.contains(event.target) && 
                !mobileMenuBtn.contains(event.target)) {
                navLinks.classList.remove('active');
                body.classList.remove('menu-open');
            }
        });
    }

    // Pet filtering functionality
    const petSearch = document.getElementById('pet-search');
    const petType = document.getElementById('pet-type');
    const petAge = document.getElementById('pet-age');
    const petCards = document.querySelectorAll('.pet-card');
    const shuffleBtn = document.querySelector('.shuffle-pets');
    const petsGrid = document.querySelector('.pets-grid');
    
    // Add animation classes to pet cards
    if (petCards.length > 0) {
        petCards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('fade-in');
            }, index * 100);
        });
    }

    function filterPets() {
        const searchTerm = petSearch ? petSearch.value.toLowerCase() : '';
        const selectedType = petType ? petType.value : 'all';
        const selectedAge = petAge ? petAge.value : 'all';
        
        let visibleCount = 0;

        petCards.forEach(card => {
            const name = card.querySelector('.pet-name').textContent.toLowerCase();
            const type = card.dataset.type;
            const ageText = card.querySelector('.pet-details').textContent;
            const ageMatch = ageText.match(/\d+(\.\d+)?/);
            const age = ageMatch ? parseFloat(ageMatch[0]) : 0;

            let isVisible = true;

            // Search filter
            if (searchTerm && !name.includes(searchTerm)) {
                isVisible = false;
            }

            // Type filter
            if (selectedType !== 'all' && type !== selectedType) {
                isVisible = false;
            }

            // Age filter
            if (selectedAge !== 'all') {
                if (selectedAge === 'young' && age >= 1) isVisible = false;
                if (selectedAge === 'adult' && (age < 1 || age > 5)) isVisible = false;
                if (selectedAge === 'senior' && age <= 5) isVisible = false;
            }

            // Apply visibility with animation
            if (isVisible) {
                card.style.display = 'block';
                setTimeout(() => {
                    card.classList.add('fade-in');
                }, visibleCount * 50);
                visibleCount++;
            } else {
                card.classList.remove('fade-in');
                setTimeout(() => {
                    card.style.display = 'none';
                }, 300);
            }
        });
        
        // Show "no results" message if no pets visible
        const noResultsMsg = document.querySelector('.no-results-message');
        if (visibleCount === 0 && petsGrid) {
            if (!noResultsMsg) {
                const message = document.createElement('div');
                message.className = 'no-results-message';
                message.innerHTML = `
                    <i class="fas fa-search"></i>
                    <h3>No pets found</h3>
                    <p>Try adjusting your filters or search terms</p>
                `;
                petsGrid.parentNode.appendChild(message);
            }
        } else if (noResultsMsg) {
            noResultsMsg.remove();
        }
    }

    // Shuffle pets functionality with animation
    function shufflePets() {
        if (!petsGrid) return;
        
        const petCards = Array.from(petsGrid.children);
        
        // Hide all cards first
        petCards.forEach(card => {
            card.classList.remove('fade-in');
        });
        
        // Wait for fade out animation
        setTimeout(() => {
            // Shuffle the array
            for (let i = petCards.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [petCards[i], petCards[j]] = [petCards[j], petCards[i]];
            }
            
            // Clear the grid
            while (petsGrid.firstChild) {
                petsGrid.removeChild(petsGrid.firstChild);
            }
            
            // Add cards back in new order with fade-in animation
            petCards.forEach((card, index) => {
                petsGrid.appendChild(card);
                setTimeout(() => {
                    card.classList.add('fade-in');
                }, index * 50);
            });
        }, 300);
    }

    // Add event listeners for filters
    if (petSearch) petSearch.addEventListener('input', filterPets);
    if (petType) petType.addEventListener('change', filterPets);
    if (petAge) petAge.addEventListener('change', filterPets);
    if (shuffleBtn) shuffleBtn.addEventListener('click', shufflePets);
    
    // Form validation for contact form
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const messageInput = document.getElementById('message');
            let isValid = true;
            
            // Reset previous errors
            document.querySelectorAll('.error-message').forEach(el => el.remove());
            
            // Validate name
            if (!nameInput.value.trim()) {
                showError(nameInput, 'Please enter your name');
                isValid = false;
            }
            
            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value)) {
                showError(emailInput, 'Please enter a valid email address');
                isValid = false;
            }
            
            // Validate message
            if (!messageInput.value.trim()) {
                showError(messageInput, 'Please enter your message');
                isValid = false;
            }
            
            if (isValid) {
                // Show success message
                const successMsg = document.createElement('div');
                successMsg.className = 'success-message';
                successMsg.innerHTML = '<i class="fas fa-check-circle"></i> Your message has been sent successfully!';
                contactForm.appendChild(successMsg);
                
                // Clear form
                contactForm.reset();
                
                // Remove success message after 5 seconds
                setTimeout(() => {
                    successMsg.remove();
                }, 5000);
            }
        });
    }
    
    function showError(input, message) {
        const errorMsg = document.createElement('div');
        errorMsg.className = 'error-message';
        errorMsg.textContent = message;
        input.parentNode.appendChild(errorMsg);
        input.classList.add('error');
        
        // Remove error state on input
        input.addEventListener('input', function() {
            input.classList.remove('error');
            const error = input.parentNode.querySelector('.error-message');
            if (error) error.remove();
        });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Add scroll animation for elements
    const animateElements = document.querySelectorAll('.animate-on-scroll');
    
    function checkVisibility() {
        animateElements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementTop < windowHeight - 100) {
                element.classList.add('visible');
            }
        });
    }
    
    // Run once on load
    if (animateElements.length > 0) {
        checkVisibility();
        window.addEventListener('scroll', checkVisibility);
    }
    
    // Lazy load images
    const lazyImages = document.querySelectorAll('.lazy-image');
    
    if ('IntersectionObserver' in window && lazyImages.length > 0) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for browsers without IntersectionObserver
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
        });
    }
});
