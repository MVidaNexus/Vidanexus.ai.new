<style>
        .verify-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .verify-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            max-width: 520px;
            width: 100%;
            text-align: center;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .verify-icon-wrapper {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(0, 168, 230,0.15), rgba(6,182,212,0.15));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: pulse-glow 2s ease-in-out infinite;
        }
        .verify-icon-wrapper i {
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(0, 168, 230,0.15); }
            50% { box-shadow: 0 0 40px rgba(0, 168, 230,0.3); }
        }
        .verify-title {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            color: var(--text-main);
            margin-bottom: 0.75rem;
        }
        .verify-subtitle {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }
        .verify-email-highlight {
            color: var(--primary-cyan);
            font-weight: 600;
        }
        .verify-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            border-radius: 12px;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            justify-content: center;
        }
        .verify-btn-primary {
            background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal));
            color: #fff;
        }
        .verify-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 168, 230,0.3);
        }
        .verify-btn-secondary {
            background: rgba(255,255,255,0.05);
            color: var(--text-muted);
            border: 1px solid var(--glass-border);
            margin-top: 0.75rem;
        }
        .verify-btn-secondary:hover {
            background: rgba(255,255,255,0.1);
            color: var(--text-main);
        }
        .verify-success {
            background: rgba(34,197,94,0.1);
            border: 1px solid rgba(34,197,94,0.3);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: #22c55e;
            font-size: 0.95rem;
        }
        .verify-divider {
            border: none;
            border-top: 1px solid var(--glass-border);
            margin: 1.5rem 0;
        }
        .verify-footer {
            color: var(--text-muted);
            font-size: 0.85rem;
            line-height: 1.6;
        }
</style>
