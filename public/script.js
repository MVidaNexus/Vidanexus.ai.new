// Tech Canvas Background Animation
const canvas = document.getElementById('techCanvas');
if (canvas) {
    const ctx = canvas.getContext('2d');
    let width, height;
    let particles = [];

    function init() {
        const parent = canvas.parentElement || document.body;
        width = canvas.width = parent.clientWidth;
        height = canvas.height = parent.clientHeight || window.innerHeight;
        particles = [];
        for (let i = 0; i < 100; i++) {
            particles.push({
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5,
                size: Math.random() * 2
            });
        }
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = 'rgba(14, 165, 233, 0.5)';
        ctx.strokeStyle = 'rgba(14, 165, 233, 0.1)';

        particles.forEach((p, i) => {
            p.x += p.vx;
            p.y += p.vy;

            if (p.x < 0 || p.x > width) p.vx *= -1;
            if (p.y < 0 || p.y > height) p.vy *= -1;

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
            ctx.fill();

            for (let j = i + 1; j < particles.length; j++) {
                const p2 = particles[j];
                const dx = p.x - p2.x;
                const dy = p.y - p2.y;
                const dist = Math.sqrt(dx * dx + dy * dy);

                if (dist < 150) {
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y);
                    ctx.lineTo(p2.x, p2.y);
                    ctx.stroke();
                }
            }
        });
        requestAnimationFrame(animate);
    }

    window.addEventListener('resize', init);
    init();
    animate();
}

// Countdown Logic (for Coming Soon)
const countdown = () => {
    const launchDate = new Date('2026-05-01T00:00:00').getTime();
    const now = new Date().getTime();
    const gap = launchDate - now;

    if (gap < 0) return;

    const second = 1000;
    const minute = second * 60;
    const hour = minute * 60;
    const day = hour * 24;

    const d = Math.floor(gap / day);
    const h = Math.floor((gap % day) / hour);
    const m = Math.floor((gap % hour) / minute);
    const s = Math.floor((gap % minute) / second);

    const daysEl = document.getElementById('days');
    const hoursEl = document.getElementById('hours');
    const minsEl = document.getElementById('minutes');
    const secsEl = document.getElementById('seconds');

    if (daysEl) daysEl.innerText = d.toString().padStart(2, '0');
    if (hoursEl) hoursEl.innerText = h.toString().padStart(2, '0');
    if (minsEl) minsEl.innerText = m.toString().padStart(2, '0');
    if (secsEl) secsEl.innerText = s.toString().padStart(2, '0');
};

if (document.getElementById('days')) {
    setInterval(countdown, 1000);
}

// Theme Toggle Logic (Dribbble Morphing Switch)
const initTheme = () => {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    const isDark = savedTheme === 'dark';
    
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // Sync all checkboxes (Checked = Dark Mode)
    const checkboxes = document.querySelectorAll('.theme-switch-dribbble input');
    checkboxes.forEach(cb => {
        cb.checked = isDark;
    });
};

const handleThemeChange = (e) => {
    const isDarkMode = e.target.checked;
    const newTheme = isDarkMode ? 'dark' : 'light';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    // Sync other checkboxes
    const checkboxes = document.querySelectorAll('.theme-switch-dribbble input');
    checkboxes.forEach(cb => {
        if (cb !== e.target) cb.checked = isDarkMode;
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    
    document.body.addEventListener('change', (e) => {
        if (e.target.closest('.theme-switch-dribbble input')) {
            handleThemeChange(e);
        }
    });

    // Mobile Sidebar Logic...
});

// Mobile Sidebar Logic (Restored)
const hamburger = document.querySelector('.hamburger');
const mobileSidebar = document.getElementById('mobileSidebar');
const mobileOverlay = document.getElementById('mobileOverlay');

function toggleMobileMenu() {
    if (mobileSidebar && mobileOverlay) {
        const isActive = mobileSidebar.classList.contains('active');
        
        if (isActive) {
            mobileSidebar.classList.remove('active');
            mobileOverlay.classList.remove('active');
            if(hamburger) hamburger.innerHTML = '<i class="fas fa-bars"></i>';
            document.body.style.overflow = ''; // Restore scrolling
        } else {
            mobileSidebar.classList.add('active');
            mobileOverlay.classList.add('active');
            if(hamburger) hamburger.innerHTML = '<i class="fas fa-times"></i>';
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
    }
}

if (hamburger) {
    hamburger.addEventListener('click', toggleMobileMenu);
}

if (mobileOverlay) {
    mobileOverlay.addEventListener('click', toggleMobileMenu);
}

// Close sidebar when clicking any link or button inside it
if (mobileSidebar) {
    mobileSidebar.addEventListener('click', (e) => {
        // If they click a link or a button within the sidebar (but not the theme switch itself)
        if (e.target.closest('a') || (e.target.closest('button') && !e.target.closest('.theme-switch-dribbble'))) {
            toggleMobileMenu();
        }
    });
}
