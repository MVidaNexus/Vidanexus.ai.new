<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Policy | Vida Nexus AI</title>
    
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
            background: linear-gradient(to bottom, rgba(255, 75, 75, 0.05), transparent);
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
            background: rgba(255, 75, 75, 0.1);
            color: #ff4b4b;
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
            <h1 class="page-title">Refund Policy</h1>
            <p class="page-subtitle">Our transparent approach to billing and satisfaction.</p>
        </div>

        <div class="content-container">
            <div class="legal-content">
                <span class="last-updated">Last Updated: March 2026</span>

                <h2>1. General Terms</h2>
                <p>VidaNexus AI provides digital SaaS tools and AI generation services. Due to the high computational costs associated with AI generation (processing power, API fees), our refund policy is designed to be fair to both the user and the platform.</p>

                <h2>2. Credit Package Refunds</h2>
                <p>One-time credit packages (Lite, Creator, Agency, etc.) are refundable within <strong>24 hours</strong> of purchase, provided that you have consumed less than <strong>2% of the package's credits</strong>. Once the 24-hour window has passed or more than 2% of credits have been used (even for a single tool execution), the package becomes non-refundable due to the immediate computational costs incurred.</p>

                <h2>3. Service Malfunctions</h2>
                <p>If a technical error on our platform results in the failure of a generation or tool execution, the credits used for that specific action will be automatically or manually restored to your wallet. If a persistent system-wide issue prevents you from using the service for more than 48 hours, we may issue bonus credits or a partial refund at our discretion.</p>

                <h2>5. Processing Refunds</h2>
                <p>Refunds are processed back to the original payment method used via Fawaterk. Please note that it may take 5-10 business days for the funds to appear in your account depending on your bank's policies.</p>

                <h2>6. Contact For Refunds</h2>
                <p>To request a refund, please contact us at:</p>
                <p><strong>Email:</strong> support@vidanexus.net</p>
                <p>Please include your order reference number and the email associated with your account.</p>
            </div>
        </div>

        @include('partials.footer')
    </div>

    <script src="{{ asset('script.js?v=14') }}"></script>
</body>
</html>
