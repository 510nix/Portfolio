// Initialize AOS (Animate On Scroll)
AOS.init({
    duration: 1000,
    easing: 'ease-in-out',
    once: true,
    mirror: false
});

// Loading Screen
window.addEventListener('load', () => {
    const loader = document.querySelector('.loading-screen');
    loader.style.opacity = '0';
    setTimeout(() => {
        loader.style.display = 'none';
    }, 500);
});

// Typed.js Implementation
const typed = new Typed('#typed-text', {
    strings: ['a CSE Student', 'an Aspiring Software Engineer', 'an AI Enthusiast'],
    typeSpeed: 50,
    backSpeed: 30,
    backDelay: 2000,
    loop: true
});

// Theme Toggle with Cookie Persistence
const themeToggle = document.getElementById('theme-toggle');
const body = document.body;
const icon = themeToggle.querySelector('i');

// Cookie helper functions
function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Check for saved theme preference in cookie
const savedTheme = getCookie('theme');
if (savedTheme) {
    body.classList.toggle('dark-mode', savedTheme === 'dark');
    icon.classList.toggle('fa-sun', savedTheme === 'dark');
    icon.classList.toggle('fa-moon', savedTheme === 'light');
} else {
    // Default to dark mode if no preference is saved
    body.classList.add('dark-mode');
    icon.classList.add('fa-sun');
    setCookie('theme', 'dark', 365); // Save default preference for 1 year
}

themeToggle.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    const isDark = body.classList.contains('dark-mode');
    setCookie('theme', isDark ? 'dark' : 'light', 365); // Save for 1 year
    icon.classList.toggle('fa-sun', isDark);
    icon.classList.toggle('fa-moon', !isDark);
});

// Mobile Navigation
const navToggle = document.querySelector('.nav-toggle');
const navLinks = document.querySelector('.nav-links');

navToggle.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    // Ensure the hamburger icon also animates
    navToggle.classList.toggle('active');
});

// Smooth Scroll for Navigation Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            // Close mobile menu if open
            if (navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                navToggle.classList.remove('active'); // Also close hamburger animation
            }
        }
    });
});

// Active Navigation Link on Scroll
const sections = document.querySelectorAll('section');
const navItems = document.querySelectorAll('.nav-link');

window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
        // Adjust this value if sections are too close or too far
        if (pageYOffset >= sectionTop - sectionHeight / 3) {
            current = section.getAttribute('id');
        }
    });

    navItems.forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('href').slice(1) === current) {
            item.classList.add('active');
        }
    });
});

// Skill Bars Animation
const skillFills = document.querySelectorAll('.skill-fill');

const animateSkillBars = () => {
    skillFills.forEach(fill => {
        const width = fill.dataset.width;
        fill.style.width = width + '%';
    });
};

// Trigger skill bars animation when skills section is in view
const skillsSection = document.querySelector('.skills');
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateSkillBars();
            // Optional: Unobserve after animation if you only want it to run once
            // observer.unobserve(skillsSection);
        }
    });
}, { threshold: 0.5 }); // Adjust threshold as needed

observer.observe(skillsSection);

// Project filtering functionality - Works with database-loaded projects
document.addEventListener('DOMContentLoaded', () => {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const projectCards = document.querySelectorAll('.project-card');

    // Add event listeners for project filtering
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            btn.classList.add('active');

            const filter = btn.dataset.filter; // 'all', 'app', 'web', 'other'

            projectCards.forEach(card => {
                if (filter === 'all' || card.dataset.category === filter) {
                    card.style.display = 'block';
                    card.classList.remove('hidden');
                    card.classList.add('visible');
                } else {
                    card.style.display = 'none';
                    card.classList.add('hidden');
                    card.classList.remove('visible');
                }
            });
        });
    });
});