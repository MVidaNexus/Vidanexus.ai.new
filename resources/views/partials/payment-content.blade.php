
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
                            <div class="methods-icons" style="display: flex; gap: 0.75rem; font-size: 1.3rem; color: var(--primary-cyan);">
                                <i class="fab fa-cc-visa" title="Visa"></i>
                                <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                                <i class="fas fa-wallet" title="Mobile Wallets (Vodafone Cash, InstaPay, etc.)"></i>
                                <i class="fas fa-credit-card" title="Meeza Cards & Debit Cards"></i>
                            </div>
                        </div>
                        <div class="methods-footer">
                            <span style="color: var(--text-muted); font-size: 0.85rem;">Powered by Fawaterk — Visa, Mastercard, Meeza & Mobile Wallets</span>
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
