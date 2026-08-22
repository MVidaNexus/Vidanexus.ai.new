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
            color: var(--accent);
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
