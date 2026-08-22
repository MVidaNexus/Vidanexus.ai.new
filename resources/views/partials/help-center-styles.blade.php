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
        box-shadow: 0 0 20px rgba(0, 168, 230, 0.4);
    }

        .help-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 1.5rem;
            background: rgba(0, 102, 255, 0.1);
            color: var(--primary);
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
            color: var(--accent);
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
            background: linear-gradient(135deg, rgba(0, 168, 230, 0.05) 0%, rgba(176, 38, 255, 0.05) 100%);
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
            background: linear-gradient(135deg, var(--primary-cyan) 0%, var(--accent) 100%);
            color: #000;
            padding: 1rem 2.5rem;
            border-radius: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            box-shadow: 0 10px 20px rgba(0, 168, 230, 0.2);
            border: none;
            cursor: pointer;
        }

        .cta-button:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 168, 230, 0.4);
            filter: brightness(1.1);
        }

        .cta-button i {
            font-size: 1.1rem;
        }
</style>
