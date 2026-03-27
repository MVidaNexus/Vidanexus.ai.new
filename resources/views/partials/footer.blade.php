<style>
    .app-footer, .app-footer * {
        direction: ltr !important;
        text-align: left !important;
        unicode-bidi: isolate !important;
    }
</style>
<footer class="app-footer">
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-col brand-col">
                <a href="/" style="text-decoration: none;">
                    <div class="logo-container" style="margin-bottom: 1.5rem;">
                        <img src="{{ asset('assets/logo.png') }}" alt="VidaNexus" class="logo-img" style="height: 32px;">
                        <div class="logo-text" style="font-size: 1.5rem;">
                            <span class="logo-vida">VIDA</span>
                            <span class="logo-nexus">NEXUS</span>
                        </div>
                    </div>
                </a>
                <p class="footer-bio">
                    VidaNexus is a high-performance, modular AI ecosystem designed for long-term scalability. We are building the backbone of next-generation intelligent automation.
                </p>
            </div>
            
            <div class="footer-col">
                <h4>Platform</h4>
                <ul class="footer-links">
                    <li><a href="/">Home</a></li>
                    <li><a href="/pricing">Pricing Plans</a></li>
                    <li><a href="{{ auth()->check() ? '/dashboard' : '/login' }}">My Dashboard</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h4>Resources</h4>
                <ul class="footer-links">
                    <li><a href="/help-center">Help Center</a></li>
                    <li><a href="/privacy">Privacy Policy</a></li>
                    <li><a href="/refund">Refund Policy</a></li>
                    <li><a href="/shipping">Shipping Policy</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h4>Contact</h4>
                <ul class="footer-contact">
                    <li>
                        <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                        <span>info@vidanexus.net</span>
                    </li>
                    <li>
                        <div class="contact-icon"><i class="fas fa-phone"></i></div>
                        <a href="tel:+201019944589" style="color: inherit; text-decoration: none;">+20 1019944589</a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} <a href="https://vidanexus.net/" style="color: inherit; text-decoration: none;">Vida Nexus</a> AI. All rights reserved.</p>
            <div class="footer-social">
                <a href="https://www.facebook.com/VidaNexus" aria-label="Facebook" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                <a href="mailto:info@vidanexus.net" aria-label="Email"><i class="fas fa-envelope"></i></a>
                <a href="https://www.linkedin.com/company/vida-nexus/" aria-label="LinkedIn" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </div>
</footer>

{{-- Floating Action Buttons --}}
<div id="floating-actions" style="
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    z-index: 9999;
">
    {{-- Back to Top --}}
    <button id="btn-scroll-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Back to top" style="
        width: 48px; height: 48px;
        border-radius: 50%;
        border: 1px solid var(--glass-border, rgba(14, 165, 233,0.25));
        background: var(--card-bg, rgba(10,15,30,0.85));
        backdrop-filter: blur(12px);
        color: var(--primary-cyan, #0ea5e9);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        transition: all 0.35s cubic-bezier(.4,0,.2,1);
        box-shadow: 0 4px 20px rgba(0,0,0,0.3), 0 0 15px rgba(14, 165, 233,0.08);
        opacity: 0;
        transform: translateY(20px) scale(0.8);
        pointer-events: none;
    ">
        <i class="fas fa-chevron-up" style="font-size: 1rem;"></i>
    </button>

    {{-- WhatsApp --}}
    <a href="https://wa.me/201019944589" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" id="btn-whatsapp" style="
        width: 56px; height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #25D366, #128C7E);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.35s cubic-bezier(.4,0,.2,1);
        box-shadow: 0 4px 20px rgba(37,211,102,0.35), 0 0 25px rgba(37,211,102,0.15);
        animation: wa-pulse 2.5s infinite;
    ">
        <i class="fab fa-whatsapp" style="font-size: 1.6rem;"></i>
    </a>
</div>

<style>
    @keyframes wa-pulse {
        0%, 100% { box-shadow: 0 4px 20px rgba(37,211,102,0.35), 0 0 25px rgba(37,211,102,0.15); }
        50% { box-shadow: 0 4px 25px rgba(37,211,102,0.5), 0 0 40px rgba(37,211,102,0.25); }
    }
    #btn-whatsapp:hover {
        transform: scale(1.12) !important;
        box-shadow: 0 6px 30px rgba(37,211,102,0.5), 0 0 40px rgba(37,211,102,0.3) !important;
    }
    #btn-scroll-top:hover {
        background: rgba(14, 165, 233,0.15) !important;
        border-color: rgba(14, 165, 233,0.5) !important;
        box-shadow: 0 4px 25px rgba(14, 165, 233,0.2), 0 0 20px rgba(14, 165, 233,0.15) !important;
    }
    #btn-scroll-top.visible {
        opacity: 1 !important;
        transform: translateY(0) scale(1) !important;
        pointer-events: auto !important;
    }
    @media (max-width: 640px) {
        #floating-actions { bottom: 1rem; right: 1rem; }
        #btn-whatsapp { width: 50px; height: 50px; }
        #btn-whatsapp i { font-size: 1.4rem; }
        #btn-scroll-top { width: 42px; height: 42px; }
    }
</style>

<script>
(function(){
    const btn = document.getElementById('btn-scroll-top');
    if (!btn) return;
    let ticking = false;
    window.addEventListener('scroll', function(){
        if (!ticking) {
            window.requestAnimationFrame(function(){
                if (window.scrollY > 400) {
                    btn.classList.add('visible');
                } else {
                    btn.classList.remove('visible');
                }
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });
})();
</script>
{!! App\Models\Setting::get('footer_script') !!}
