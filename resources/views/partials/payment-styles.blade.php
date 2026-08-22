<style>
        .checkout-container {
            max-width: 1100px;
            margin: 2rem auto 4rem;
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 3rem;
            padding: 0 5%;
            position: relative;
            z-index: 10;
        }

        .payment-form {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            backdrop-filter: blur(20px);
        }

        /* Stepper Styles */
        .checkout-stepper {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin: 4rem auto 0;
            max-width: 600px;
            padding: 0 2rem;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .step.active {
            color: var(--primary-cyan);
            font-weight: 700;
        }

        .step.completed {
            color: var(--accent-success);
        }

        .step-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        .active .step-num {
            border-color: var(--primary-cyan);
            background: rgba(0, 168, 230, 0.1);
            box-shadow: 0 0 15px rgba(0, 168, 230, 0.3);
        }

        .completed .step-num {
            border-color: var(--accent-success);
            background: rgba(0, 255, 170, 0.1);
            color: var(--accent-success);
        }

        .step-line {
            flex-grow: 1;
            height: 2px;
            background: var(--glass-border);
            max-width: 60px;
        }

        .step-line.completed {
            background: var(--accent-success);
        }

        /* Summary Card */
        .summary-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 0;
            height: fit-content;
            overflow: hidden;
            position: sticky;
            top: 120px;
        }

        .summary-header {
            padding: 2rem;
            background: var(--card-bg);
            border-bottom: 1px solid var(--glass-border);
        }

        .summary-body {
            padding: 2rem;
        }

        .receipt-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .btn-pay {
            display: block;
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, var(--accent), var(--primary-cyan));
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            font-family: var(--font-heading);
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
            box-shadow: 0 10px 20px rgba(191, 0, 255, 0.3);
        }

        .btn-pay:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(191, 0, 255, 0.5);
        }

        .btn-pay:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--glass-border);
        }

        /* Error / Success Alerts */
        .alert-error {
            background: rgba(255, 75, 75, 0.1);
            border: 1px solid rgba(255, 75, 75, 0.3);
            color: #ff4b4b;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .trust-badges {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 2rem;
        }

        .trust-badge {
            background: var(--card-bg);
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            text-align: center;
        }

        .trust-badge i {
            display: block;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .trust-badge span {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .secure-info {
            background: var(--card-bg);
            border: 1px dashed var(--primary-cyan);
            border-radius: 16px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
        }

        .secure-info h3 {
            font-size: 1rem;
            margin: 0 0 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-main);
        }

        .secure-info p {
            color: var(--text-muted);
            line-height: 1.6;
            margin: 0;
            font-size: 0.9rem;
        }

        .payment-methods-strip {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
        }

        .methods-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .methods-icons {
            display: flex;
            gap: 1rem;
            font-size: 1.5rem;
            color: var(--text-muted);
            align-items: center;
        }

        .methods-footer {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--glass-border);
        }

        .partner-badge {
            height: 24px;
            opacity: 0.7;
        }

        @media (max-width: 850px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            .summary-card {
                position: static;
            }
        }

        @media (max-width: 500px) {
            .btn-pay {
                font-size: 0.95rem !important;
                padding: 1rem 0.75rem !important;
                height: auto !important;
                min-height: 55px;
                white-space: normal;
                line-height: 1.4;
                word-break: break-word;
            }
            .payment-form {
                padding: 1.5rem 1rem;
            }
            .secure-info {
                padding: 1rem 1.25rem;
            }
            .payment-methods-strip {
                padding: 1rem 1.25rem;
            }
        }
</style>
