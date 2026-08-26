// Tech Canvas Background Animation
const canvas = document.getElementById('techCanvas');
if (canvas) {
    const ctx = canvas.getContext('2d');
    let width, height;
    let particles = [];

    function init() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
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
    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
    
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
    document.documentElement.classList.toggle('dark', isDarkMode);
    document.documentElement.style.colorScheme = isDarkMode ? 'dark' : 'light';
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
// Mobile Sidebar Logic
function closeMobileMenu() {
    const mobileSidebar = document.getElementById('mobileSidebar');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const hamburger = document.querySelector('.hamburger');
    if (mobileSidebar) mobileSidebar.classList.remove('active');
    if (mobileOverlay) mobileOverlay.classList.remove('active');
    if (hamburger) hamburger.innerHTML = '<i class="fas fa-bars"></i>';
    document.body.style.overflow = '';
}

function toggleMobileMenu() {
    const mobileSidebar = document.getElementById('mobileSidebar');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const hamburger = document.querySelector('.hamburger');
    if (mobileSidebar && mobileOverlay) {
        const isActive = mobileSidebar.classList.contains('active');
        if (isActive) {
            closeMobileMenu();
        } else {
            mobileSidebar.classList.add('active');
            mobileOverlay.classList.add('active');
            if (hamburger) hamburger.innerHTML = '<i class="fas fa-times"></i>';
            document.body.style.overflow = 'hidden';
        }
    }
}

const hamburgerBtn = document.querySelector('.hamburger');
if (hamburgerBtn) {
    hamburgerBtn.addEventListener('click', toggleMobileMenu);
}

const mobileOverlayEl = document.getElementById('mobileOverlay');
if (mobileOverlayEl) {
    mobileOverlayEl.addEventListener('click', closeMobileMenu);
}

// Global Alert Helpers
window.showInsufficientBalanceAlert = (message = 'Sorry, your balance is insufficient. Please recharge to continue.') => {
    if (typeof Swal === 'undefined') {
        alert(message);
        return;
    }

    Swal.fire({
        title: '<span style="color: var(--text-main); font-weight: 800; font-family: Tajawal;">Insufficient Balance</span>',
        html: `
            <div style="padding: 1rem 0; text-align: center; font-family: Tajawal;">
                <div style="width: 80px; height: 80px; background: rgba(255, 75, 75, 0.1); border-radius: 100%; display: flex; align-items: center; justify-content: center; color: #ff4b4b; margin: 0 auto 1.5rem; border: 1px solid rgba(255, 75, 75, 0.2); box-shadow: 0 0 30px rgba(255, 75, 75, 0.1);">
                    <i class="fas fa-wallet" style="font-size: 2.2rem;"></i>
                </div>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 0;">
                    ${message}
                </p>
            </div>
        `,
        background: 'var(--card-bg, #1a1b1e)',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-bolt me-2"></i> Recharge Now',
        cancelButtonText: 'Close',
        buttonsStyling: false,
        customClass: {
            popup: 'glass-panel border-0 rounded-[2rem]',
            confirmButton: 'vn-btn-primary px-8 py-3 rounded-xl font-bold text-sm mx-2',
            cancelButton: 'bg-white/5 text-gray-400 px-8 py-3 rounded-xl font-bold text-sm mx-2 border border-white/5'
        },
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/pricing';
        }
    });
};
