<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Policy | Vida Nexus AI</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('style.v2.css?v=30') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <script>(function(){const t=localStorage.getItem("theme")||"dark";document.documentElement.setAttribute("data-theme",t);})();</script>

    <style>
        .page-header {
            padding: 8rem 2rem 4rem;
            text-align: center;
            background: linear-gradient(to bottom, rgba(0, 255, 170, 0.05), transparent);
        }

        .page-title {
            font-family: var(--font-heading);
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--text-main);
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .content-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 2rem 4rem;
        }

        .legal-content {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem;
            backdrop-filter: blur(20px);
            color: var(--text-muted);
            font-size: 1.05rem;
            line-height: 1.8;
        }

        .legal-content h2 {
            font-family: var(--font-heading);
            color: var(--text-main);
            font-size: 1.8rem;
            margin: 3rem 0 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .legal-content h2:first-child {
            margin-top: 0;
        }

        .legal-content p {
            margin-bottom: 1.5rem;
        }

        .last-updated {
            display: inline-block;
            background: rgba(0, 255, 170, 0.1);
            color: var(--accent-success);
            padding: 0.5rem 1rem;
            border-radius: 100px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .legal-content {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <canvas id="techCanvas"></canvas>

    <div class="main-container">
        @include('partials.header')

        <div class="page-header">
            <h1 class="page-title">Digital Delivery Policy</h1>
            <p class="page-subtitle">How we deliver our AI services and automation assets.</p>
        </div>

        <div class="content-container">
            <div class="legal-content">
                <span class="last-updated">Last Updated: March 2026</span>

                <h2>1. Instant Digital Fulfillment</h2>
                <p>VidaNexus AI exclusively provides digital services and assets. There are no physical goods shipped as part of any plan or package. Upon successful payment, your account features or credits are typically activated <strong>instantly</strong>.</p>

                <h2>2. Delivery Mechanism</h2>
                <p>All tool outputs (articles, images, data reports) are delivered directly within your user dashboard or via the specified API endpoints. You will receive an email confirmation for all subscription upgrades and credit purchases.</p>

                <h2>3. Access Issues</h2>
                <p>If your account does not reflect your purchase within 30 minutes, please refresh your dashboard or log out and log back in. If the issue persists, contact our technical support team for manual verification.</p>

                <h2>4. Geographical Availability</h2>
                <p>As a cloud-based platform, our services are available globally. However, execution speed and latency may vary based on your local network conditions and proximity to our server nodes.</p>

                <h2>5. Contact Support</h2>
                <p>For any questions regarding your access or digital delivery, please contact us at:</p>
                <p><strong>Email:</strong> technical@vidanexus.net</p>
            </div>
        </div>

        @include('partials.footer')
    </div>

    <script src="{{ asset('script.js?v=14') }}"></script>
</body>
</html>
