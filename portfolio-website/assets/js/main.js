/*
 * Main JavaScript for Portfolio Website
 * Includes: Smooth Scrolling, Navigation, Dark Mode, Project Filtering, Animations, and Form Validation
 */

document.addEventListener('DOMContentLoaded', () => {
  // Initialize AOS Animation Library
  AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true,
    mirror: false
  });

  // DOM Elements
  const header = document.querySelector('header');
  const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
  const navLinks = document.querySelector('.nav-links');
  const themeSwitcher = document.querySelector('.theme-switcher');
  const filterBtns = document.querySelectorAll('.filter-btn');
  const projectCards = document.querySelectorAll('.project-card');
  const backToTopBtn = document.querySelector('.back-to-top');
  const contactForm = document.querySelector('.contact-form');

  // Project Preview Modal
  const previewModal = document.querySelector('.preview-modal');
  const previewTitle = document.querySelector('.preview-title');
  const previewFrame = document.querySelector('.preview-frame');
  const previewTags = document.querySelector('.preview-tags');
  const previewLink = document.querySelector('.preview-link');
  const previewGithub = document.querySelector('.preview-github');
  const closePreview = document.querySelector('.close-preview');

  // Project URLs for preview
  const projectUrls = {
    'SmartScope': 'https://your-smartscope-url.com',
    '3D Car Showcase': 'https://your-3d-car-url.com',
    '3D Pepsi Store Management': 'https://your-pepsi-store-url.com',
    'Super Website': 'https://your-super-website-url.com',
    'Pet Adoption Website': 'https://your-pet-adoption-url.com'
  };

  // Project GitHub URLs
  const projectGithubUrls = {
    'SmartScope': 'https://github.com/yourusername/smartscope',
    '3D Car Showcase': 'https://github.com/yourusername/3d-car',
    '3D Pepsi Store Management': 'https://github.com/yourusername/pepsi-store',
    'Super Website': 'https://github.com/yourusername/super-website',
    'Pet Adoption Website': 'https://github.com/yourusername/pet-adoption'
  };

  // ===== Header Scroll Effect =====
  function headerScrollEffect() {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  }

  // ===== Mobile Menu Toggle =====
  function toggleMobileMenu() {
    navLinks.classList.toggle('active');
    
    // Change the hamburger icon to X
    const bars = document.querySelectorAll('.bar');
    bars.forEach((bar, index) => {
      bar.classList.toggle('active');
      if (bar.classList.contains('active')) {
        if (index === 0) {
          bar.style.transform = 'rotate(45deg) translate(5px, 5px)';
        } else if (index === 1) {
          bar.style.opacity = '0';
        } else if (index === 2) {
          bar.style.transform = 'rotate(-45deg) translate(7px, -6px)';
        }
      } else {
        bar.style.transform = 'none';
        bar.style.opacity = '1';
      }
    });
  }

  // ===== Dark Mode Toggle =====
  function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    
    // Enhanced animation for theme switcher
    if (themeSwitcher.querySelector('i').classList.contains('fa-moon')) {
      themeSwitcher.querySelector('i').classList.remove('fa-moon');
      themeSwitcher.querySelector('i').classList.add('fa-sun');
      themeSwitcher.style.transform = 'rotate(360deg)';
    } else {
      themeSwitcher.querySelector('i').classList.remove('fa-sun');
      themeSwitcher.querySelector('i').classList.add('fa-moon');
      themeSwitcher.style.transform = 'rotate(0deg)';
    }
    
    // Save user preference to localStorage
    if (document.body.classList.contains('dark-mode')) {
      localStorage.setItem('theme', 'dark');
    } else {
      localStorage.setItem('theme', 'light');
    }
    
    // Apply specific dark mode styles for project cards
    applyDarkModeStyles();
  }
  
  // ===== Apply specific dark mode styles =====
  function applyDarkModeStyles() {
    const isDarkMode = document.body.classList.contains('dark-mode');
    
    // Apply styles to project tags for better visibility in dark mode
    const projectTags = document.querySelectorAll('.project-tags span');
    projectTags.forEach(tag => {
      if (isDarkMode) {
        tag.style.backgroundColor = 'rgba(65, 105, 225, 0.2)';
        tag.style.color = '#6c63ff';
      } else {
        tag.style.backgroundColor = 'rgba(65, 105, 225, 0.1)';
        tag.style.color = '#4169e1';
      }
    });
    
    // Enhance form fields contrast in dark mode
    const formInputs = document.querySelectorAll('input, textarea');
    formInputs.forEach(input => {
      if (isDarkMode) {
        input.style.borderColor = '#333';
        input.style.backgroundColor = '#1a1a1a';
      } else {
        input.style.borderColor = '';
        input.style.backgroundColor = '';
      }
    });
  }

  // ===== Check for Saved Theme Preference =====
  function checkThemePreference() {
    const savedTheme = localStorage.getItem('theme');
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Check saved preference or use system preference
    if (savedTheme === 'dark' || (!savedTheme && prefersDarkScheme.matches)) {
      document.body.classList.add('dark-mode');
      themeSwitcher.querySelector('i').classList.remove('fa-moon');
      themeSwitcher.querySelector('i').classList.add('fa-sun');
    } else {
      themeSwitcher.querySelector('i').classList.remove('fa-sun');
      themeSwitcher.querySelector('i').classList.add('fa-moon');
    }
    
    // Apply specific dark mode styles
    applyDarkModeStyles();
  }

  // ===== Project Filtering =====
  function filterProjects() {
    const filter = this.getAttribute('data-filter');
    
    // Update active button
    filterBtns.forEach(btn => {
      btn.classList.remove('active');
    });
    this.classList.add('active');
    
    // Filter projects with animation
    projectCards.forEach(card => {
      card.style.transition = 'all 0.4s ease';
      
      if (filter === 'all' || card.getAttribute('data-category') === filter) {
        card.style.display = 'block';
        setTimeout(() => {
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
        }, 10);
      } else {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
          card.style.display = 'none';
        }, 400);
      }
    });
  }

  // ===== Back to Top Button =====
  function scrollFunction() {
    if (window.scrollY > 500) {
      backToTopBtn.classList.add('active');
    } else {
      backToTopBtn.classList.remove('active');
    }
  }

  function scrollToTop() {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }

  // ===== Smooth Scrolling for Navigation Links =====
  function smoothScroll(e) {
    e.preventDefault();
    
    // Close mobile menu if open
    if (navLinks.classList.contains('active')) {
      toggleMobileMenu();
    }
    
    const targetId = this.getAttribute('href');
    const targetElement = document.querySelector(targetId);
    
    if (targetElement) {
      const targetPosition = targetElement.offsetTop - 80;
      
      window.scrollTo({
        top: targetPosition,
        behavior: 'smooth'
      });
    }
  }

  // ===== Highlight Active Navigation Link on Scroll =====
  function highlightNavOnScroll() {
    const sections = document.querySelectorAll('section');
    const navItems = document.querySelectorAll('.nav-links a');
    
    let current = '';
    
    sections.forEach(section => {
      const sectionTop = section.offsetTop - 100;
      const sectionHeight = section.clientHeight;
      
      if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
        current = section.getAttribute('id');
      }
    });
    
    navItems.forEach(item => {
      item.classList.remove('active');
      if (item.getAttribute('href') === `#${current}`) {
        item.classList.add('active');
      }
    });
  }

  // ===== Form Validation =====
  function validateForm(e) {
    e.preventDefault();
    
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const subjectInput = document.getElementById('subject');
    const messageInput = document.getElementById('message');
    
    let isValid = true;
    
    // Check name
    if (nameInput.value.trim() === '') {
      showError(nameInput, 'Name is required');
      isValid = false;
    } else {
      removeError(nameInput);
    }
    
    // Check email
    if (emailInput.value.trim() === '') {
      showError(emailInput, 'Email is required');
      isValid = false;
    } else if (!isValidEmail(emailInput.value)) {
      showError(emailInput, 'Please enter a valid email');
      isValid = false;
    } else {
      removeError(emailInput);
    }
    
    // Check subject
    if (subjectInput.value.trim() === '') {
      showError(subjectInput, 'Subject is required');
      isValid = false;
    } else {
      removeError(subjectInput);
    }
    
    // Check message
    if (messageInput.value.trim() === '') {
      showError(messageInput, 'Message is required');
      isValid = false;
    } else {
      removeError(messageInput);
    }
    
    // If all valid, submit the form
    if (isValid) {
      // Here you would normally send the form data to a server
      // For now, we'll just show a success message
      showSuccessMessage();
      contactForm.reset();
    }
  }

  function showError(input, message) {
    const formGroup = input.parentElement;
    const errorElement = formGroup.querySelector('.error-message') || document.createElement('div');
    
    errorElement.className = 'error-message';
    errorElement.textContent = message;
    errorElement.style.color = '#ff4d4d';
    errorElement.style.fontSize = '0.85rem';
    errorElement.style.marginTop = '5px';
    errorElement.style.transition = 'all 0.3s ease';
    
    if (!formGroup.querySelector('.error-message')) {
      formGroup.appendChild(errorElement);
    }
    
    input.style.borderColor = '#ff4d4d';
    input.style.boxShadow = '0 0 0 3px rgba(255, 77, 77, 0.2)';
  }

  function removeError(input) {
    const formGroup = input.parentElement;
    const errorElement = formGroup.querySelector('.error-message');
    
    if (errorElement) {
      errorElement.style.opacity = '0';
      setTimeout(() => {
        formGroup.removeChild(errorElement);
      }, 300);
    }
    
    input.style.borderColor = '';
    input.style.boxShadow = '';
  }

  function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
  }

  function showSuccessMessage() {
    const successMsg = document.createElement('div');
    successMsg.className = 'success-message';
    successMsg.textContent = 'Your message has been sent successfully!';
    successMsg.style.backgroundColor = 'rgba(0, 199, 183, 0.1)';
    successMsg.style.color = '#00c7b7';
    successMsg.style.padding = '15px';
    successMsg.style.borderRadius = '8px';
    successMsg.style.marginBottom = '20px';
    successMsg.style.textAlign = 'center';
    successMsg.style.opacity = '0';
    successMsg.style.transform = 'translateY(-10px)';
    successMsg.style.transition = 'all 0.3s ease';
    
    contactForm.insertBefore(successMsg, contactForm.firstChild);
    
    // Animate the success message
    setTimeout(() => {
      successMsg.style.opacity = '1';
      successMsg.style.transform = 'translateY(0)';
    }, 10);
    
    // Remove the success message after 5 seconds
    setTimeout(() => {
      successMsg.style.opacity = '0';
      successMsg.style.transform = 'translateY(-10px)';
      setTimeout(() => {
        contactForm.removeChild(successMsg);
      }, 300);
    }, 5000);
  }

  // ===== Typing Effect for Hero Section =====
  function initTypewriter() {
    const typeTarget = document.querySelector('.typewriter');
    
    if (typeTarget) {
      let roles;
      try {
        roles = JSON.parse(typeTarget.getAttribute('data-roles'));
      } catch (e) {
        roles = ["Full Stack Developer", "Software Engineer", "Windows App Developer"];
      }
      
      let roleIndex = 0;
      let charIndex = 0;
      let isDeleting = false;
      let typeDelay = 100;
      
      function type() {
        const currentRole = roles[roleIndex];
        
        if (isDeleting) {
          typeTarget.textContent = currentRole.substring(0, charIndex - 1);
          charIndex--;
          typeDelay = 50;
        } else {
          typeTarget.textContent = currentRole.substring(0, charIndex + 1);
          charIndex++;
          typeDelay = 200;
        }
        
        if (!isDeleting && charIndex === currentRole.length) {
          isDeleting = true;
          typeDelay = 1000; // Pause before deleting
        } else if (isDeleting && charIndex === 0) {
          isDeleting = false;
          roleIndex = (roleIndex + 1) % roles.length;
          typeDelay = 500; // Pause before typing new role
        }
        
        setTimeout(type, typeDelay);
      }
      
      type();
    }
  }

  // ===== Event Listeners =====
  window.addEventListener('scroll', headerScrollEffect);
  window.addEventListener('scroll', scrollFunction);
  window.addEventListener('scroll', highlightNavOnScroll);
  
  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', toggleMobileMenu);
  }
  
  if (themeSwitcher) {
    themeSwitcher.addEventListener('click', toggleDarkMode);
    // Add transition for smooth rotation
    themeSwitcher.style.transition = 'transform 0.5s ease';
  }
  
  if (filterBtns) {
    filterBtns.forEach(btn => {
      btn.addEventListener('click', filterProjects);
    });
  }
  
  if (backToTopBtn) {
    backToTopBtn.addEventListener('click', scrollToTop);
  }
  
  if (contactForm) {
    contactForm.addEventListener('submit', validateForm);
  }
  
  // Add smooth scrolling to all navigation links
  document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', smoothScroll);
  });

  // Listen for changes to color scheme preference
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    if (!localStorage.getItem('theme')) {
      if (e.matches) {
        document.body.classList.add('dark-mode');
        themeSwitcher.querySelector('i').classList.remove('fa-moon');
        themeSwitcher.querySelector('i').classList.add('fa-sun');
      } else {
        document.body.classList.remove('dark-mode');
        themeSwitcher.querySelector('i').classList.remove('fa-sun');
        themeSwitcher.querySelector('i').classList.add('fa-moon');
      }
      
      applyDarkModeStyles();
    }
  });

  // Initialize
  checkThemePreference();
  initTypewriter();
  
  // Preload project images
  function preloadImages() {
    const projectImages = document.querySelectorAll('.project-image img');
    projectImages.forEach(img => {
      const src = img.getAttribute('src');
      if (src) {
        const newImg = new Image();
        newImg.src = src;
      }
    });
  }
  
  // Call preload function after page loads
  window.addEventListener('load', preloadImages);

  // Add click event to project cards
  document.querySelectorAll('.project-card').forEach(card => {
    card.addEventListener('click', (e) => {
      // Don't open preview if clicking on links
      if (e.target.closest('a')) return;
      
      const projectName = card.querySelector('h4').textContent;
      const projectTags = card.querySelectorAll('.project-tags span');
      
      // Update preview modal content
      previewTitle.textContent = projectName;
      previewFrame.src = projectUrls[projectName];
      previewLink.href = projectUrls[projectName];
      previewGithub.href = projectGithubUrls[projectName];
      
      // Update tags
      previewTags.innerHTML = '';
      projectTags.forEach(tag => {
        const span = document.createElement('span');
        span.textContent = tag.textContent;
        previewTags.appendChild(span);
      });
      
      // Show modal
      previewModal.classList.add('active');
      document.body.style.overflow = 'hidden';
    });
  });

  // Close preview modal
  closePreview.addEventListener('click', () => {
    previewModal.classList.remove('active');
    document.body.style.overflow = '';
    previewFrame.src = ''; // Clear iframe source
  });

  // Close preview when clicking outside
  previewModal.addEventListener('click', (e) => {
    if (e.target === previewModal) {
      previewModal.classList.remove('active');
      document.body.style.overflow = '';
      previewFrame.src = ''; // Clear iframe source
    }
  });

  // Close preview with Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && previewModal.classList.contains('active')) {
      previewModal.classList.remove('active');
      document.body.style.overflow = '';
      previewFrame.src = ''; // Clear iframe source
    }
  });
}); 