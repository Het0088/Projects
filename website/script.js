document.addEventListener('DOMContentLoaded', function() {
    const billBtn = document.querySelector('.bill-btn');
    const challanBtn = document.querySelector('.challan-btn');
    const shopModal = document.getElementById('shopModal');
    const closeShopBtn = document.querySelector('.close-shop-btn');
    const shopBtns = document.querySelectorAll('.shop-btn');
    
    // Update shop links to use PHP instead of HTML
    shopBtns.forEach(btn => {
        const href = btn.getAttribute('href');
        if (href && href.includes('bill.html')) {
            btn.setAttribute('href', href.replace('bill.html', 'bill.php'));
        }
    });
    
    // Add click animations and functionality
    billBtn.addEventListener('click', function() {
        animateButton(this);
        setTimeout(() => {
            // Show shop selection modal with smoother animation
            shopModal.style.display = 'block';
            
            // Delay adding the visible class for a smoother fade-in effect
            setTimeout(() => {
                shopModal.classList.add('visible');
                
                // Add hover effect to each shop button sequentially with more elegant timing
                shopBtns.forEach((btn, index) => {
                    setTimeout(() => {
                        btn.classList.add('ready');
                        
                        // Add subtle shimmer effect after button appears
                        setTimeout(() => {
                            btn.classList.add('shimmer');
                            
                            // Remove shimmer after it completes
                            setTimeout(() => {
                                btn.classList.remove('shimmer');
                            }, 1500);
                        }, 500);
                    }, 800 + (index * 250)); // More spaced out timing
                });
            }, 80);
        }, 300);
    });
    
    closeShopBtn.addEventListener('click', function() {
        closeModalSmoothly();
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target == shopModal) {
            closeModalSmoothly();
        }
    });
    
    // Function to close modal with smooth animation
    function closeModalSmoothly() {
        // First fade out the shop buttons elegantly
        shopBtns.forEach((btn, index) => {
            setTimeout(() => {
                btn.classList.add('closing');
            }, index * 120);
            
            setTimeout(() => {
                btn.classList.remove('ready');
                btn.classList.remove('closing');
            }, 300 + (index * 120));
        });
        
        // Delay removing visible class for smoother transition
        setTimeout(() => {
            shopModal.classList.remove('visible');
            
            // Only hide modal after animation completes
            setTimeout(() => {
                shopModal.style.display = 'none';
                // Reset all classes
                shopBtns.forEach(btn => {
                    btn.classList.remove('ready', 'shimmer', 'closing');
                });
            }, 800); // Match this with the CSS transition time
        }, 400);
    }
    
    challanBtn.addEventListener('click', function() {
        animateButton(this);
        setTimeout(() => {
            alert('Challan section is opening...');
            // Add redirect or functionality here
        }, 300);
    });
    
    function animateButton(button) {
        button.classList.add('clicked');
        setTimeout(() => {
            button.classList.remove('clicked');
        }, 200);
    }
    
    // Add enhanced animation classes
    document.head.insertAdjacentHTML('beforeend', `
        <style>
            .clicked {
                transform: scale(0.95);
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                opacity: 0.9;
                transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
            }
            
            .shop-btn {
                opacity: 0.92;
                transform: translateY(0);
                transition: all 1.5s cubic-bezier(0.25, 1, 0.5, 1);
            }
            
            .shop-btn.ready {
                opacity: 1;
                transform: translateY(0);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            }
            
            /* Enhanced soft pulse animation */
            @keyframes softPulse {
                0% { transform: scale(1); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08); }
                50% { transform: scale(1.015); box-shadow: 0 10px 25px rgba(52, 152, 219, 0.15); }
                100% { transform: scale(1); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08); }
            }
            
            /* Enhanced shimmer effect */
            @keyframes shimmer {
                0% { background-position: -100% 0; }
                100% { background-position: 200% 0; }
            }
            
            .shop-btn.ready {
                animation: softPulse 4s infinite cubic-bezier(0.25, 1, 0.5, 1);
            }
            
            .shop-btn.shimmer:before {
                background: linear-gradient(90deg, 
                    transparent 0%, 
                    rgba(255, 255, 255, 0.2) 20%, 
                    rgba(255, 255, 255, 0.5) 50%, 
                    rgba(255, 255, 255, 0.2) 80%, 
                    transparent 100%);
                background-size: 200% 100%;
                animation: shimmer 1.5s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            }
            
            /* Closing animation */
            .shop-btn.closing {
                opacity: 0;
                transform: translateY(10px);
                transition: all 0.8s cubic-bezier(0.25, 1, 0.5, 1);
            }
            
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    `);
}); 