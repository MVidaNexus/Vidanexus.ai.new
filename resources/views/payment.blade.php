<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Order — VidaNexus</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Core Styles -->
    <link rel="stylesheet" href="{{ asset('style.v2.css?v=31') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">

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
            background: rgba(14, 165, 233, 0.1);
            box-shadow: 0 0 15px rgba(14, 165, 233, 0.3);
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
            background: linear-gradient(135deg, var(--neon-purple), var(--primary-cyan));
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
    <script>(function(){const t=localStorage.getItem("theme")||"dark";document.documentElement.setAttribute("data-theme",t);})();</script>
</head>
<body>
    <canvas id="techCanvas"></canvas>
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>

    <div class="main-container">
        @include('partials.header')

        <!-- Checkout Stepper -->
        <div class="checkout-stepper" style="margin-top: 8rem;">
            <div class="step completed">
                <div class="step-num"><i class="fas fa-check"></i></div>
                <span>Select Package</span>
            </div>
            <div class="step-line completed"></div>
            <div class="step active">
                <div class="step-num">2</div>
                <span>Review Order</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-num">3</div>
                <span>Payment</span>
            </div>
        </div>

        <div class="checkout-container">
            <div class="payment-form">
                <div style="margin-bottom: 2.5rem;">
                    <h2 style="font-family: var(--font-heading); font-size: 2rem; margin: 0 0 0.75rem; color: var(--text-main);">
                        Finalize Your <span class="gradient-text">{{ $type === 'package' ? 'Purchase' : 'Order' }}</span>
                    </h2>
                    <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.6; margin: 0;">
                        Complete your purchase to activate your monthly subscription to VidaNexus AI automation.
                    </p>
                </div>

                @if(session('error'))
                    <div class="alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif
                
                <div class="secure-info">
                    <h3>
                        <i class="fas fa-shield-alt" style="color: var(--primary-cyan);"></i>
                        Secure Merchant Redirection
                    </h3>
                    <p>
                        For your security, you will be redirected to our official payment partner <b>Fawaterk</b> to process your payment. Your card details never touch our servers.
                    </p>
                </div>

                <form action="/payment/initiate" method="POST" id="paymentForm">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="id" value="{{ $id }}">

                    <div class="payment-methods-strip">
                        <div class="methods-header">
                            <span style="font-weight: 600; color: var(--text-main);">Accepted Payment Methods</span>
                            <div class="methods-icons">
                                <i class="fab fa-cc-visa" title="Visa"></i>
                                <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                                <i class="fab fa-cc-amex" title="Amex"></i>
                            </div>
                        </div>
                        <div class="methods-footer">
                            <span style="color: var(--text-muted); font-size: 0.85rem;">Powered by Fawaterk — Official Payment Gateway Partner</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-pay" id="payBtn" style="min-height: 65px; font-size: 1.2rem;">
                        <i class="fas fa-lock" style="margin-right: 0.75rem;"></i>
                        Pay Securely — {{ number_format($item['price']) }} EGP
                    </button>
                    
                    <div class="trust-badges">
                        <div class="trust-badge">
                            <i class="fas fa-lock" style="color: var(--accent-success);"></i>
                            <span>256-bit SSL Secure</span>
                        </div>
                        <div class="trust-badge">
                            <i class="fas fa-undo-alt" style="color: var(--primary-cyan);"></i>
                            <span>Satisfaction Guarantee</span>
                        </div>
                    </div>
                </form>
            </div>

            <div class="summary-card">
                <div class="summary-header">
                    <h3 style="font-family: var(--font-heading); margin: 0; font-size: 1.3rem; color: var(--text-main);">Order Summary</h3>
                </div>
                
                <div class="summary-body">
                    <div style="display: flex; gap: 1.5rem; margin-bottom: 2rem;">
                        <div style="width: 56px; height: 56px; border-radius: 12px; background: var(--card-bg); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: {{ $item['color'] ?? 'var(--primary-cyan)' }}; flex-shrink: 0;">
                            <i class="fas {{ $item['icon'] ?? ($type === 'package' ? 'fa-box' : 'fa-gem') }}"></i>
                        </div>
                        <div>
                            <div style="color: var(--text-main); font-weight: 700; font-size: 1.15rem;">{{ $item['name'] }}</div>
                            <div style="color: var(--text-muted); font-size: 0.85rem;">{{ $item['tagline'] ?? ($type === 'package' ? 'Credit Package' : 'Marketplace Activation') }}</div>
                        </div>
                    </div>

                        <div style="margin-bottom: 2rem;">
                            <div style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">Order Inclusions</div>
                            
                            @if(isset($item['features']) && is_array($item['features']))
                                @foreach($item['features'] as $feature)
                                    <div class="receipt-item">
                                        <span style="color: var(--text-muted);">
                                            <i class="fas fa-check" style="color: var(--accent-success); margin-right: 0.5rem;"></i> 
                                            {{ $feature }}
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <div class="receipt-item">
                                    <span style="color: var(--text-muted);"><i class="fas fa-check" style="color: var(--accent-success); margin-right: 0.5rem;"></i> {{ number_format($item['credits']) }} Credits Bonus</span>
                                </div>
                                <div class="receipt-item">
                                    <span style="color: var(--text-muted);"><i class="fas fa-check" style="color: var(--accent-success); margin-right: 0.5rem;"></i> Monthly Tool Activation</span>
                                </div>

                            @endif
                        </div>

                    <div style="border-top: 1px solid var(--glass-border); padding-top: 1.5rem;">
                        <div class="receipt-item">
                            <span style="color: var(--text-muted);">Subtotal</span>
                            <span style="color: var(--text-main);">{{ number_format($item['price']) }} EGP</span>
                        </div>
                        <div class="receipt-item">
                            <span style="color: var(--text-muted);">Processing Fee</span>
                            <span style="color: var(--text-main);">0 EGP</span>
                        </div>
                        <div class="total-row" style="margin-top: 1rem; border: none; padding-top: 0;">
                            <span style="color: var(--text-main); font-weight: 600; font-size: 1.1rem;">Total</span>
                            <span style="font-weight: 800; font-size: 1.8rem; color: var(--primary-cyan); font-family: var(--font-heading);">{{ number_format($item['price']) }} EGP</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 4rem;">
            <a href="/pricing" style="color: var(--text-muted); text-decoration: none; font-size: 0.95rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem; transition: color 0.3s;" onmouseover="this.style.color='var(--text-main)'" onmouseout="this.style.color='var(--text-muted)'">
                <i class="fas fa-arrow-left"></i> Change Package or Cancel
            </a>
        </div>

        @include('partials.footer')
    </div>

    <script src="{{ asset('script.js?v=14') }}"></script>
    <script>
        // Prevent double-click on Pay button
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            var btn = document.getElementById('payBtn');
            if (btn.disabled) {
                e.preventDefault();
                return;
            }
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 0.75rem;"></i> Processing...';
        });
    </script>
</body>
</html>
