<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center & Support | Vida Nexus AI</title>
    
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
            background: linear-gradient(to bottom, rgba(0, 102, 255, 0.05), transparent);
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 2rem 4rem;
        }

    .help-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        margin-bottom: 4rem;
    }

    @media (max-width: 1024px) {
        .help-grid {
            grid-template-columns: 1fr;
        }
    }

    .help-card {
        background: var(--card-bg);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        padding: 2.5rem;
        backdrop-filter: blur(20px);
        text-align: center;
        transition: var(--theme-transition);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .help-card:hover {
        transform: translateY(-5px);
        border-color: var(--primary-cyan);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .dash-btn.btn-outline {
        border: 1px solid var(--primary-cyan);
        color: var(--primary-cyan);
        background: transparent;
        padding: 0.8rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .dash-btn.btn-outline:hover {
        background: var(--primary-cyan);
        color: #000;
        box-shadow: 0 0 20px rgba(14, 165, 233, 0.4);
    }

        .help-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 1.5rem;
            background: rgba(0, 102, 255, 0.1);
            color: var(--electric-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .help-icon.green {
            background: var(--success-bg);
            color: var(--accent-success);
        }

        .help-icon.purple {
            background: rgba(176, 38, 255, 0.1);
            color: var(--neon-purple);
        }

        .help-card h3 {
            font-family: var(--font-heading);
            color: var(--text-main);
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }

        .help-card p {
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .faq-section {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem;
        }

        .faq-title {
            text-align: center;
            font-family: var(--font-heading);
            font-size: 2rem;
            color: var(--text-main);
            margin-bottom: 3rem;
        }

        .faq-item {
            border-bottom: 1px solid var(--glass-border);
            padding: 1.5rem 0;
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .faq-question {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        .faq-answer {
            color: var(--text-muted);
            line-height: 1.7;
        }

        .contact-box {
            text-align: center;
            margin-top: 4rem;
            padding: 3rem;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.05) 0%, rgba(176, 38, 255, 0.05) 100%);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
        }

        .contact-box h3 {
            font-family: var(--font-heading);
            color: var(--text-main);
            font-size: 1.6rem;
            margin-bottom: 1rem;
        }

        .cta-button {
            background: linear-gradient(135deg, var(--primary-cyan) 0%, var(--neon-purple) 100%);
            color: #000;
            padding: 1rem 2.5rem;
            border-radius: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            box-shadow: 0 10px 20px rgba(14, 165, 233, 0.2);
            border: none;
            cursor: pointer;
        }

        .cta-button:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 30px rgba(14, 165, 233, 0.4);
            filter: brightness(1.1);
        }

        .cta-button i {
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <canvas id="techCanvas"></canvas>

    <div class="main-container">
        @include('partials.header')


        <div class="page-header">
            <h1 class="page-title">Help Center</h1>
            <p class="page-subtitle">We're here to help you maximize your potential with the VidaNexus Ecosystem.</p>
        </div>

        <div class="content-container">
            <div class="help-grid">
                <div class="help-card">
                    <div class="help-icon"><i class="fas fa-rocket"></i></div>
                    <h3>Getting Started</h3>
                    <p>Learn the basics of setting up your account, integrating APIs, and launching your first AI automation campaign.</p>
                    <a href="#getting-started" class="dash-btn btn-outline" style="display: inline-block;">Read Guide</a>
                </div>

                <div class="help-card">
                    <div class="help-icon purple"><i class="fas fa-file-invoice-dollar"></i></div>
                    <h3>Billing & Credits</h3>
                    <p>Understand how the CRS system works, manage your subscriptions, and view your invoice history.</p>
                    <a href="/dashboard#billing" class="dash-btn btn-outline" style="display: inline-block;">View Billing</a>
                </div>

                <div class="help-card">
                    <div class="help-icon green"><i class="fas fa-screwdriver-wrench"></i></div>
                    <h3>Tool Mechanics</h3>
                    <p>Deep dives into how each specific tool works, from the SEO Analyzer to the Discovery Headlines generator.</p>
                    <a href="/#ecosystem" class="dash-btn btn-outline" style="display: inline-block;">Explore Tools</a>
                </div>
            </div>

            <div class="faq-section">
                <h2 class="faq-title">Frequently Asked Questions</h2>
                
                <div class="faq-item">
                    <div class="faq-question">What are CRS credits and how are they used?</div>
                    <div class="faq-answer">CRS (Core Resource Subunits) is our internal metric for AI processing power. Each tool execution consumes a specific amount of CRS based on complexity. Pro plans receive a bounded quota (e.g., 5000 CRS), while Ultimate plans are unmetered.</div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">Can I integrate VidaNexus with my existing WordPress site?</div>
                    <div class="faq-answer">Yes, our Guest Post Marketplace and Auto-Campaign tools are designed to hook directly into standard CMS platforms via secure APIs, enabling automated publishing directly from our dashboard.</div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">How accurate is the Drama Trends tool?</div>
                    <div class="faq-answer">The Drama Trends tool aggregates real-time data from Google Trends and WATCH IT, providing extremely accurate, millisecond-level intelligence on emerging entertainment queries.</div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">Can I downgrade from Ultimate to Pro?</div>
                    <div class="faq-answer">Absolutely. You can manage your subscription tier at any time from your Dashboard's Billing section. Changes will take effect at the start of your next billing cycle.</div>
                </div>
            </div>

            <div class="contact-box">
                <h3>Still need assistance?</h3>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">Our enterprise support team is available 24/7 to solve your technical and billing queries.</p>
                <a href="mailto:info@vidanexus.net" class="cta-button">
                    <i class="fas fa-envelope"></i> Contact Support
                </a>
            </div>
        </div>

        @include('partials.footer')
    </div>

    <script src="{{ asset('script.js?v=14') }}"></script>
</body>
</html>
