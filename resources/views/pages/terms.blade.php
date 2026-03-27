<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service | Vida Nexus AI</title>
    
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
            background: linear-gradient(to bottom, rgba(176, 38, 255, 0.05), transparent);
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
            background: rgba(176, 38, 255, 0.1);
            color: var(--neon-purple);
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
            <h1 class="page-title">Terms of Service</h1>
            <p class="page-subtitle">Standard terms and conditions governing the use of VidaNexus APIs and platform tools.</p>
        </div>

        <div class="content-container">
            <div class="legal-content">
                <span class="last-updated">Last Updated: March 2026</span>

                <h2>1. Acceptance of Terms</h2>
                <p>By accessing or using the VidaNexus AI ecosystem, APIs, and associated infrastructure, you agree to be bound by these Terms of Service. If you do not agree to these terms, you must disconnect from our services immediately.</p>

                <h2>2. Description of Service</h2>
                <p>VidaNexus provides modular artificial intelligence tools, including but not limited to, SEO analysis, article generation, viral trend monitoring, and data visualization. These services are delivered "as-is", and availability may vary based on your active subscription plan.</p>

                <h2>3. Credits, Marketplace, and Billing</h2>
                <p>Our services operate on a Marketplace Credit model (CRS):</p>
                <ul>
                    <li>Credits (CRS) are purchased as one-time packages and have no expiration date unless the account is terminated.</li>
                    <li>Individual tools are unlocked via credits or granted as part of specific marketplace bundles.</li>
                    <li>Credits are consumed per tool execution according to the specific "Action Cost" documented for each tool.</li>
                    <li>Payments are processed securely via third-party providers (e.g., Fawaterk) and are non-refundable once consumption begins.</li>
                    <li>Account sharing or API key sharing is strictly prohibited and will result in immediate termination.</li>
                </ul>

                <h2>4. Acceptable Use Policy</h2>
                <p>You agree not to misuse our ecosystem. Prohibited activities include:</p>
                <ul>
                    <li>Attempting to reverse-engineer, decompile, or bypass the security of our platform or APIs.</li>
                    <li>Using our AI generation tools to produce illegal, abusive, or explicitly harmful content.</li>
                    <li>Automating interactions outside of authorized API endpoints resulting in Denial of Service (DoS) conditions.</li>
                    <li>Reselling platform access without an explicit enterprise partnership agreement.</li>
                </ul>

                <h2>5. Intellectual Property</h2>
                <p>VidaNexus retains all rights, title, and interest in and to the platform, including its proprietary algorithms, design, and branding. You retain ownership of any distinct inputs provided to the service and the standard usage rights for the specific outputs generated, subject to any open-source licensing constraints applicable to our underlying foundation models.</p>

                <h2>6. Limitation of Liability</h2>
                <p>VidaNexus shall not be liable for any indirect, incidental, or consequential damages resulting from the use or inability to use our services, including data loss or business interruption. In no event shall our total liability exceed the aggregate amount paid by you for the service in the preceding 12 months.</p>

                <h2>7. Changes to Terms</h2>
                <p>We reserve the right to modify these Terms at any time. Significant changes will be communicated via your registered email or through a prominent notice on the platform dashboard.</p>
            </div>
        </div>

        @include('partials.footer')
    </div>

    <script src="{{ asset('script.js?v=14') }}"></script>
</body>
</html>
