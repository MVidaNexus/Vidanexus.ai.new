<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy | Vida Nexus AI</title>
    
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
            background: linear-gradient(to bottom, rgba(14, 165, 233, 0.05), transparent);
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

        .legal-content h3 {
            color: var(--text-main);
            font-size: 1.3rem;
            margin: 2rem 0 1rem;
        }

        .legal-content p {
            margin-bottom: 1.5rem;
        }

        .legal-content ul {
            margin-bottom: 1.5rem;
            padding-left: 1.5rem;
        }

        .legal-content li {
            margin-bottom: 0.5rem;
        }

        .last-updated {
            display: inline-block;
            background: rgba(14, 165, 233, 0.1);
            color: var(--primary-cyan);
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
            <h1 class="page-title">Privacy Policy</h1>
            <p class="page-subtitle">How we collect, use, and protect your data across the VidaNexus ecosystem.</p>
        </div>

        <div class="content-container">
            <div class="legal-content">
                <span class="last-updated">Last Updated: March 2026</span>

                <h2>1. Introduction</h2>
                <p>Welcome to VidaNexus AI. We are committed to protecting your personal data and respecting your privacy. This policy outlines our data processing practices when you use our core automation tools, APIs, and broader ecosystem.</p>

                <h2>2. Data We Collect</h2>
                <p>To provide you with our high-performance AI services, we collect the following types of information:</p>
                <ul>
                    <li><strong>Account Information:</strong> Name, email address, and secure password hashes required for platform access.</li>
                    <li><strong>Usage Data:</strong> Metrics on tool execution, API calls, and credit consumption.</li>
                    <li><strong>Payment Data:</strong> Handled securely via our PCI-compliant partners (Fawaterk); we do not store raw credit card information.</li>
                    <li><strong>Generated Content:</strong> Temporary storage of inputs and outputs for AI processing, which are routinely purged according to our retention schedules.</li>
                </ul>

                <h2>3. How We Use Your Data</h2>
                <p>Your data is strictly utilized to:</p>
                <ul>
                    <li>Provide, maintain, and improve the VidaNexus tools and APIs.</li>
                    <li>Process transactions and manage your subscription state.</li>
                    <li>Analyze usage to scale infrastructure and optimize AI model performance.</li>
                    <li>Send critical service updates, security alerts, and support messages.</li>
                </ul>

                <h2>4. Data Security</h2>
                <p>We implement enterprise-grade security measures including SSL/TLS encryption for data in transit and AES-256 encryption for sensitive data at rest. Access to production environments is strictly gated and monitored.</p>

                <h2>5. Third-Party Services</h2>
                <p>VidaNexus integrates with specialized APIs (e.g., AI Engine Providers, Google Trends, Fawaterk) to deliver our services. Data shared with these sub-processors is limited to what is strictly necessary for the operation requested.</p>

                <h2>6. Your Rights</h2>
                <p>You have the right to access, correct, or request deletion of your personal data. You may also export your usage data or withdraw consent where processing is consent-based. Contact our DPO at policy@vidanexus.net for assistance.</p>

                <h2>7. Contact Us</h2>
                <p>If you have any questions about this Privacy Policy, please contact us at:</p>
                <p><strong>Email:</strong> info@vidanexus.net</p>
            </div>
        </div>

        @include('partials.footer')
    </div>

    <script src="{{ asset('script.js?v=14') }}"></script>
</body>
</html>
