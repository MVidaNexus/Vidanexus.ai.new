@extends('layouts.marketing')

@section('title', 'Help Center & Support | VidaNexus AI')

@push('meta')
    <meta name="description" content="Find answers to frequently asked questions about VidaNexus AI tools, credits, billing, and account management.">
    <link rel="canonical" href="https://vidanexus.ai/help-center">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://vidanexus.ai/help-center">
    <meta property="og:title" content="Help Center & Support | VidaNexus AI">
    <meta property="og:description" content="Find answers to frequently asked questions about VidaNexus AI tools, credits, billing, and account management.">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://vidanexus.ai/help-center">
    <meta property="twitter:title" content="Help Center & Support | VidaNexus AI">
    <meta property="twitter:description" content="Find answers to frequently asked questions about VidaNexus AI tools, credits, billing, and account management.">
    <meta property="twitter:image" content="{{ asset('assets/social-preview.png?v=2') }}">
@endpush

@push('schema')
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "FAQPage",
          "mainEntity": [
            {
              "@type": "Question",
              "name": "What are credits and how are they used?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Credits are the usage metric for VidaNexus AI tools. Each tool execution consumes a specific amount of credits based on complexity with transparent pay-as-you-go pricing."
              }
            },
            {
              "@type": "Question",
              "name": "How does the Keyword Spy Radar work?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Keyword Spy Radar performs automated competitor searches and trend scans to extract high-value ranking keywords before they saturate."
              }
            },
            {
              "@type": "Question",
              "name": "Can I upgrade or top up my credit wallet anytime?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes, you can top up your wallet with credit packages at any time with zero expiration dates and instant digital activation."
              }
            }
          ]
        },
        {
          "@type": "BreadcrumbList",
          "itemListElement": [
            {
              "@type": "ListItem",
              "position": 1,
              "name": "Home",
              "item": "https://vidanexus.ai/"
            },
            {
              "@type": "ListItem",
              "position": 2,
              "name": "Help Center",
              "item": "https://vidanexus.ai/help-center"
            }
          ]
        }
      ]
    }
    </script>
@endpush

@section('content')
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
                <p style="color: var(--text-muted); margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.6;">
                    Have questions about tools, credits, or account access? Our team is always ready to help. Reach out directly at <strong>info@vidanexus.net</strong>.
                </p>
                <a href="mailto:info@vidanexus.net" class="vn-btn vn-btn-primary contact-support-btn">
                    <i class="fas fa-envelope"></i>
                    <span>Contact Support</span>
                </a>
            </div>
        </div>
@endsection

@push('after_main')
    @include('partials.footer')
@endpush
