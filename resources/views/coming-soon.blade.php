<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vida Nexus — AI Solutions Coming Soon</title>
    <link rel="preload" as="image" href="{{ asset('assets/logo.png') }}" fetchpriority="high">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" as="style">
    <link rel="preload" href="{{ asset('style.v2.css') }}?v={{ config('vidanexus.style_css_version') }}" as="style">
    <meta name="description" content="Vida Nexus offers next-generation AI solutions, empowering businesses with professional machine learning, automation, and data analytics.">
    <meta name="keywords" content="AI, Artificial Intelligence, Machine Learning, Vida Nexus, AI Services Egypt, AI Automation, Data Analytics, Intelligence Innovation">
    <meta name="author" content="Vida Nexus">
    <meta name="robots" content="index, follow, max-image-preview:large">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://vidanexus.ai/">
    <meta property="og:title" content="Vida Nexus — Next-Gen AI Solutions & Innovation">
    <meta property="og:description" content="Empowering businesses with professional machine learning, automation, and next-generation AI solutions.">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">
    <meta property="og:site_name" content="Vida Nexus">
    <meta property="og:locale" content="en_US">
    <meta property="og:updated_time" content="2026-02-13T05:58:00+02:00">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://vidanexus.ai/">
    <meta property="twitter:title" content="Vida Nexus — Next-Gen AI Solutions & Innovation">
    <meta property="twitter:description" content="Empowering businesses with professional machine learning, automation, and next-generation AI solutions.">
    <meta property="twitter:image" content="https://vidanexus.ai/assets/logo.png">

    <!-- SEO Dates -->
    <meta property="article:published_time" content="2026-02-12T10:00:00+02:00">
    <meta property="article:modified_time" content="2026-02-13T05:58:00+02:00">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Vida Nexus",
      "url": "https://vidanexus.ai",
      "logo": "https://vidanexus.ai/assets/logo.png",
      "description": "Leading provider of professional AI services and intelligent automation solutions.",
      "address": {
        "@type": "PostalAddress",
        "addressCountry": "Egypt"
      },
      "sameAs": [
        "https://www.facebook.com/VidaNexus",
        "https://www.linkedin.com/company/vida-nexus/"
      ]
    }
    </script>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="canonical" href="https://vidanexus.ai/">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style.v2.css') }}?v={{ config('vidanexus.style_css_version') }}">
    @include('partials.theme-init')
</head>
<body>
    <canvas id="techCanvas" style="position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:0;pointer-events:none;display:block;"></canvas>
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>
    <div class="glow-orb orb-3"></div>

    <div class="main-container">
        <header class="header">
            <div class="logo-container">
                <img src="{{ asset('assets/logo.svg') }}" alt="Vida Nexus - Next-Generation AI Solutions & Innovation" class="logo-img" id="logoImg" fetchpriority="high">
                <div class="logo-text">
                    <span class="logo-vida">VIDA</span>
                    <span class="logo-nexus">NEXUS</span>
                </div>
            </div>
        </header>

        <main class="hero">
            <div class="badge">
                <span class="badge-dot"></span>
                <span>AI-Powered Solutions</span>
            </div>

            <h1 class="hero-title">
                <span class="line-1">Something</span>
                <span class="line-2">
                    <span class="gradient-text">Extraordinary</span>
                </span>
                <span class="line-3">is Coming</span>
            </h1>

            <p class="hero-subtitle">
                We're crafting next-generation AI services to revolutionize your business.
                <br>Stay tuned for the future of intelligent solutions.
            </p>

            <div class="countdown-section">
                <h3 class="countdown-label">Launching In</h3>
                <div class="countdown">
                    <div class="countdown-item">
                        <div class="countdown-value" id="days">00</div>
                        <div class="countdown-unit">Days</div>
                    </div>
                    <div class="countdown-separator">:</div>
                    <div class="countdown-item">
                        <div class="countdown-value" id="hours">00</div>
                        <div class="countdown-unit">Hours</div>
                    </div>
                    <div class="countdown-separator">:</div>
                    <div class="countdown-item">
                        <div class="countdown-value" id="minutes">00</div>
                        <div class="countdown-unit">Minutes</div>
                    </div>
                    <div class="countdown-separator">:</div>
                    <div class="countdown-item">
                        <div class="countdown-value" id="seconds">00</div>
                        <div class="countdown-unit">Seconds</div>
                    </div>
                </div>
            </div>

            <div class="notify-section">
                <form class="notify-form" id="email-form" action="{{ url('/api/waitlist') }}" method="POST">
                    @csrf
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" placeholder="Enter your email for updates..." required id="emailInput">
                        @honeypot
                    </div>
                    <button type="submit" class="notify-btn">
                        <span>Notify Me</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
                <p class="notify-note" id="notifyNote">Be the first to know when we launch.</p>
            </div>

            <div class="features-preview">
                <div class="feature-chip">
                    <i class="fas fa-brain"></i>
                    <span>Machine Learning</span>
                </div>
                <div class="feature-chip">
                    <i class="fas fa-robot"></i>
                    <span>AI Automation</span>
                </div>
                <div class="feature-chip">
                    <i class="fas fa-chart-line"></i>
                    <span>Data Analytics</span>
                </div>
                <div class="feature-chip">
                    <i class="fas fa-comments"></i>
                    <span>NLP Solutions</span>
                </div>
                <div class="feature-chip">
                    <i class="fas fa-eye"></i>
                    <span>Computer Vision</span>
                </div>
            </div>
        </main>

        @include('partials.footer')

    </div>

    <a href="https://wa.me/201019944589" target="_blank" rel="noopener noreferrer" class="whatsapp-float" aria-label="Contact us on WhatsApp" id="whatsappFloat">
        <i class="fab fa-whatsapp"></i>
    </a>

    <script src="{{ asset('script.js?v=14') }}"></script>
    <script>
        document.getElementById('email-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = e.target;
            const email = document.getElementById('emailInput').value;
            const note = document.getElementById('notifyNote');
            const submitBtn = form.querySelector('button');

            submitBtn.disabled = true;
            note.textContent = 'Subscribing...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: email })
                });

                const data = await response.json();

                if (response.ok) {
                    note.textContent = 'Thank you! You have been added to our waitlist.';
                    note.style.color = '#00A8E6';
                    form.reset();
                } else {
                    note.textContent = data.message || 'Something went wrong. Please try again.';
                    note.style.color = '#ff4b4b';
                }
            } catch (error) {
                note.textContent = 'Connection error. Please try again later.';
                note.style.color = '#ff4b4b';
            } finally {
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
