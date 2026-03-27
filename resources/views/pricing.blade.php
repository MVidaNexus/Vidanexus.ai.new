<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing & Subscriptions | VidaNexus AI</title>
    <meta name="description" content="Select your VidaNexus AI tier. Access our suite of 12 powerful AI tools with tailored limits for Pro and Ultimate.">
    <!-- OpenGraph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Pricing & Subscriptions | VidaNexus AI">
    <meta property="og:description" content="Select your VidaNexus AI tier. Access our suite of 12 powerful AI tools with tailored limits for Pro and Ultimate.">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style.v2.css?v=30') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <style>
        .pricing-hero {
            text-align: center;
            padding: clamp(3rem, 10vh, 6rem) 1.5rem clamp(2rem, 5vh, 4rem);
        }
        .pricing-title {
            font-family: var(--font-heading);
            font-size: clamp(2.2rem, 8vw, 4rem);
            margin-bottom: 1rem;
            color: var(--text-main);
            text-shadow: 0 0 30px var(--title-glow);
            line-height: 1.1;
        }
        .pricing-subtitle {
            color: var(--text-muted);
            font-size: clamp(1rem, 2vw, 1.25rem);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }
    </style>
    <script>(function(){const t=localStorage.getItem("theme")||"dark";document.documentElement.setAttribute("data-theme",t);})();</script>
</head>
<body>
    <canvas id="techCanvas"></canvas>
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>
    
    <div class="main-container">
        @include('partials.header')

        <main>
            <div class="pricing-hero">
                <h1 class="pricing-title">Fuel Your <span class="gradient-text">Intelligence</span></h1>
                <p class="pricing-subtitle">Purchase credit packages to unlock tools and generate content. Experience institutional-grade performance on demand.</p>
            </div>

            <div style="max-width: 1400px; margin: 0 auto 6rem; padding: 0 1rem;">
                @include('partials.pricing_cards')
            </div>
        </main>

        @include('partials.footer')

    </div>

    <script src="{{ asset('script.js?v=14') }}"></script>
</body>
</html>
