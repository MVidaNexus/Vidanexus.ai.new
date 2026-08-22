<style>
        .tool-hero {
            position: relative;
            padding: 4rem 1.5rem 2rem;
            text-align: center;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }
        .tool-icon-large {
            font-size: 5rem;
            color: {{ $tool['color'] }};
            margin-bottom: 2rem;
            filter: drop-shadow(0 0 30px {{ $tool['color'] }}66);
            animation: float 6s ease-in-out infinite;
        }
        .tool-title {
            font-family: var(--font-heading);
            font-size: clamp(2rem, 8vw, 4rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -0.03em;
            text-transform: uppercase;
            background: linear-gradient(to right, var(--title-color), {{ $tool['color'] }});
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px var(--title-glow);
            color: var(--title-color); /* Fallback for browsers that don't support text-fill-color */
            display: block;
        }
        .tool-tagline {
            font-size: clamp(1.1rem, 3vw, 1.5rem);
            color: var(--text-muted);
            max-width: 800px;
            margin: 0 auto 3rem;
            font-weight: 400;
            line-height: 1.6;
            letter-spacing: 0.05em;
        }
        .tool-description {
            font-size: 1rem;
            color: var(--text-main);
            max-width: 900px;
            width: 100%;
            margin: 0 auto 3rem;
            line-height: 1.7;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            padding: 2rem 1.5rem;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            border-left: 4px solid {{ $tool['color'] }};
            text-align: left;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto 6rem;
            padding: 0 2rem;
        }
        .feature-item {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            text-align: left;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        .feature-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top right, {{ $tool['color'] }}15, transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .feature-item:hover {
            transform: translateY(-10px);
            border-color: {{ $tool['color'] }}44;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5), 0 0 20px {{ $tool['color'] }}22;
        }
        .feature-item:hover::before {
            opacity: 1;
        }
        .feature-icon {
            font-size: 2rem;
            color: {{ $tool['color'] }};
            margin-bottom: 1.5rem;
        }
        .feature-title {
            font-size: 1.2rem;
            color: var(--text-main);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .feature-desc {
            color: var(--text-muted); /* brighter than text-muted for dark backgrounds */
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .cta-section {
            text-align: center;
            padding: 4rem 2rem 8rem;
        }
        /* Removed legacy .cta-button styles to use global .vn-btn system */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .orb-special {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, {{ $tool['color'] }} 0%, transparent 70%);
            opacity: 0.15;
            filter: blur(80px);
            z-index: -1;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            pointer-events: none;
        }

        .coming-soon-btn {
            background: var(--card-bg) !important;
            border: 1px solid var(--card-border) !important;
            box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.1), 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            cursor: not-allowed !important;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            width: 100%;
            border-radius: 16px;
            transition: all 0.3s ease;
            backdrop-filter: var(--glass-blur);
        }
        .coming-soon-btn::before {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, var(--glass-bg), transparent);
            transform: skewX(-20deg);
            animation: shine 4s ease-in-out infinite;
        }
        @keyframes shine {
            0% { left: -100%; }
            20% { left: 200%; }
            100% { left: 200%; }
        }
        .coming-soon-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }
        .coming-soon-title {
            font-size: 1.3rem;
            color: var(--text-main);
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .coming-soon-sub {
            font-size: 0.8rem;
            color: var(--primary-cyan);
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 600;
            opacity: 0.8;
        }
        
        /* Light Theme Overrides for Coming Soon */
        [data-theme="light"] .coming-soon-btn {
            background: rgba(240, 245, 255, 0.8) !important;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
            box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.03), 0 10px 20px rgba(0,0,0,0.03);
        }
        [data-theme="light"] .coming-soon-btn::before {
            background: linear-gradient(to right, transparent, rgba(0,0,0,0.02), transparent);
        }
        [data-theme="light"] .coming-soon-btn .fa-lock {
            color: rgba(0,0,0,0.25) !important;
        }

        .tool-cta-btn {
            font-size: 1.5rem; 
            padding: 1.2rem 4rem;
            min-width: 320px;
            justify-content: center;
        }
        .tool-buy-btn {
            background: linear-gradient(135deg, #a855f7, #6366f1); 
            color: white !important; 
            border: none;
            box-shadow: 0 10px 25px rgba(168, 85, 247, 0.3);
            transition: all 0.3s ease;
        }
        .tool-buy-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(168, 85, 247, 0.4);
        }

        /* Marketing Content Classes */
        .marketing-subtitle { color: var(--text-muted); }
        .marketing-card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            padding: 28px; border-radius: 16px;
            position: relative; overflow: hidden;
            transition: all 0.3s ease;
        }
        .marketing-card:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }
        .marketing-card-accent { position: absolute; top: 0; left: 0; width: 4px; height: 100%; }
        .marketing-card-title {
            color: var(--text-main); font-size: 1.3rem; margin-bottom: 12px;
            display: flex; align-items: center; gap: 10px; font-weight: 900;
        }
        .marketing-card-desc {
            color: var(--text-muted); line-height: 1.6; margin: 0;
            font-style: italic; font-size: 0.9rem;
        }
        .marketing-features-box {
            background: linear-gradient(135deg, rgba(0, 168, 230, 0.05), rgba(168, 85, 247, 0.05));
            border-radius: 20px; padding: 40px; text-align: center;
            border: 1px solid var(--glass-border);
        }
        .marketing-features-title {
            color: var(--text-main); margin-bottom: 24px; font-size: 1.5rem;
            font-weight: 800; font-style: italic;
        }
        .marketing-features-list {
            list-style: none; padding: 0; margin: 0 auto; max-width: 650px;
            text-align: left; display: grid; gap: 20px;
        }
        .marketing-features-item {
            display: flex; gap: 16px; align-items: flex-start;
            background: var(--card-bg); padding: 15px; border-radius: 12px;
            border: 1px solid var(--glass-border); transition: all 0.3s ease;
        }
        .marketing-features-item:hover {
            border-color: rgba(0, 168, 230, 0.3);
        }
        .marketing-features-item-title { color: var(--text-main); display: block; margin-bottom: 2px; }
        .marketing-features-item-desc { font-size: 0.9rem; color: var(--text-muted); }
        
        [data-theme="light"] .marketing-features-box {
            background: linear-gradient(135deg, rgba(0, 168, 230, 0.02), rgba(168, 85, 247, 0.02));
        }

        @media (max-width: 768px) {
            .tool-cta-btn {
                font-size: 1.1rem !important;
                padding: 1rem 2rem !important;
                min-width: 100% !important;
            }
            /* CTA buttons now use global responsive classes */
            .tool-icon-large {
                font-size: 3rem;
                margin-bottom: 1rem;
            }
            .tool-hero {
                padding-top: 2rem;
            }
            .feature-item {
                padding: 1.5rem;
            }
            .tool-description {
                padding: 1.5rem 1rem;
            }
        }
</style>
