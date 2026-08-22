                <!-- Billing & Invoices Panel -->
                <div class="content-panel" id="billing" style="display: none;">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fas fa-file-invoice-dollar"></i> Billing & Invoices</h2>
                    </div>

                    <div class="billing-summary">
                        <div class="billing-item">
                            <label>Wallet Status</label>
                            <div class="val" style="color: var(--primary-cyan);">Active</div>
                        </div>
                        <div class="billing-item">
                            <label>Current Balance</label>
                            <div class="val">
                                <span class="js-credit-balance"
                                      data-credit-value="{{ (float) $walletBalance }}"
                                      data-decimals="2"
                                      data-suffix=" Credits">{{ number_format($walletBalance, 2) }} Credits</span>
                            </div>
                        </div>
                        <div class="billing-item">
                            <label>Account Type</label>
                            <div class="val">Pay-per-Action</div>
                        </div>
                        <div class="billing-item">
                            <label>Last Activity</label>
                            <div class="val" style="font-size: 1.1rem;">
                                {{ now()->format('M j, Y') }}
                            </div>
                        </div>
                    </div>

                    <div class="premium-coupon-block">
                        <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(245,158,11,0.15) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
                        <h3 style="font-size: 1.25rem; font-family: var(--font-heading); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem; color: var(--text-main);">
                            <i class="fas fa-ticket-alt" style="color: #f59e0b; filter: drop-shadow(0 0 10px rgba(245,158,11,0.5));"></i> Redeem a Coupon Code
                        </h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.5; max-width: 600px;">Have a discount or promo code? Enter it below to add credits instantly to your wallet.</p>

                        @if(session('coupon_redeemed'))
                            <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#00A58B;padding:0.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:0.9rem;font-weight:600;">
                                <i class="fas fa-check-circle"></i> {{ session('coupon_redeemed') }}
                            </div>
                        @endif

                        <form action="{{ route('dashboard.redeem-coupon') }}" method="POST" style="display:flex;gap:0.75rem;align-items:flex-start;flex-wrap:wrap;">
                            @csrf
                            <div style="flex:1;min-width:200px;">
                                <input type="text" name="coupon_code" id="coupon_code_input"
                                       value="{{ old('coupon_code') }}"
                                       placeholder="Enter coupon code…"
                                       class="premium-coupon-input"
                                       style="border: 1px solid {{ $errors->has('coupon_code') ? 'rgba(239,68,68,0.6)' : 'rgba(245,158,11,0.3)' }};"
                                       onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='inset 0 2px 10px rgba(0,0,0,0.2), 0 0 15px rgba(245,158,11,0.2)';"
                                       onblur="this.style.borderColor='rgba(245,158,11,0.3)'; this.style.boxShadow='inset 0 2px 10px rgba(0,0,0,0.2)';"
                                       autocomplete="off">
                                @error('coupon_code')
                                    <p style="color:#ef4444;font-size:0.85rem;margin-top:0.5rem;display:flex;align-items:center;gap:0.4rem;"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit"
                                    style="padding:1rem 2rem;border-radius:12px;border:none;background:linear-gradient(135deg, #f59e0b, #d97706);color:#fff;font-family:var(--font-heading);font-weight:700;font-size:1rem;cursor:pointer;transition:all 0.3s;white-space:nowrap;box-shadow:0 5px 15px rgba(245,158,11,0.4);display:flex;align-items:center;gap:0.75rem;"
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(245,158,11,0.5)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(245,158,11,0.4)';">
                                Redeem Credits <i class="fas fa-arrow-right"></i>
                            </button>
                        </form>
                    </div>

                    <div style="margin-top: 4rem; position: relative;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 1px; background: linear-gradient(90deg, transparent, rgba(0, 168, 230,0.3), transparent);"></div>
                        <div style="padding-top: 2.5rem;">
                            <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.75rem; color: var(--text-main);">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, rgba(0, 168, 230,0.2), rgba(0, 168, 230,0.05)); display: flex; align-items: center; justify-content: center; border: 1px solid rgba(0, 168, 230,0.2);">
                                    <i class="fas fa-plus-circle" style="color: var(--primary-cyan); font-size: 1.2rem;"></i>
                                </div>
                                Purchase Extra Credits
                            </h3>
                            <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 2rem; max-width: 600px; line-height: 1.6;">Need more points? Top up your wallet with supplemental credit packages. Credits never expire and can be used to unlock any tool or action across the entire marketplace.</p>
                        </div>

                        @php
                            $defaultPackages = [
                                'lite' => [ 'name' => 'Lite Dash', 'credits' => '100', 'price' => '35', 'desc' => 'Perfect for testing a single tool.', 'icon' => 'fa-seedling', 'color' => '#00ffaa' ],
                                'standard' => [ 'name' => 'Creator Pack', 'credits' => '500', 'price' => '150', 'desc' => 'Best for social media managers.', 'icon' => 'fa-rocket', 'color' => 'var(--primary-cyan)', 'popular' => true ],
                                'pro' => [ 'name' => 'Agency Pro', 'credits' => '2,500', 'price' => '650', 'desc' => 'High-volume SEO & Content.', 'icon' => 'fa-bolt-lightning', 'color' => 'var(--accent)' ],
                                'enterprise' => [ 'name' => 'Power Node', 'credits' => '10,000', 'price' => '2,250', 'desc' => 'Infrastructure level usage.', 'icon' => 'fa-crown', 'color' => '#ffcc00' ]
                            ];
                            $savedPackagesJson = \App\Models\Setting::get('marketplace_packages');
                            $packages = is_string($savedPackagesJson) ? json_decode($savedPackagesJson, true) : ($savedPackagesJson ?: $defaultPackages);
                        @endphp

                        <div class="billing-grid">
                            @foreach($packages as $id => $pkg)
                                @php
                                    $discount = isset($pkg['discount']) && is_numeric($pkg['discount']) ? (float)$pkg['discount'] : 0;
                                    $basePrice = (float)str_replace(',', '', $pkg['price']);
                                    $finalPrice = $discount > 0 ? $basePrice - ($basePrice * ($discount / 100)) : $basePrice;
                                @endphp
                                <div class="pkg-card" style="position: relative; overflow: hidden; border-color: {{ !empty($pkg['popular']) ? 'var(--primary-cyan)' : 'var(--glass-border)' }};">
                                    @if($discount > 0)
                                        <div class="pkg-ribbon">SAVE {{ (int)$discount }}%</div>
                                    @endif

                                    <div style="font-size: 1.5rem; color: {{ $pkg['color'] ?? 'var(--primary-cyan)' }}; margin-bottom: 1rem;">
                                        <i class="fas {{ $pkg['icon'] ?? 'fa-box' }}"></i>
                                    </div>
                                    <div class="pkg-name">{{ $pkg['name'] }}</div>
                                    <div class="pkg-desc">{{ $pkg['desc'] }}</div>
                                    <div class="pkg-credits">{{ $pkg['credits'] }} Credits</div>
                                    <div class="pkg-price">
                                        @if($discount > 0)
                                            <span style="text-decoration: line-through; font-size: 0.75rem; opacity: 0.5; margin-right: 5px;">{{ number_format($basePrice) }}</span>
                                        @endif
                                        {{ number_format($finalPrice) }} EGP
                                    </div>
                                    <a href="/payment?type=package&id={{ $id }}" class="vn-btn {{ !empty($pkg['popular']) ? 'vn-btn-primary' : 'vn-btn-outline' }}" style="padding: 0.5rem 1rem; font-size: 0.85rem; width: 100%;">Buy Now</a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($invoices->count())
                        <h3 style="font-family: var(--font-heading); font-size: 1.2rem; margin-bottom: 1rem;">Invoice History</h3>
                        <div style="overflow-x: auto;">
                            <table class="invoice-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Credits</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        <tr>
                                            <td style="color: var(--text-muted);">INV-{{ str_pad($invoice->id, 4, '0', STR_PAD_LEFT) }}</td>
                                            <td>{{ $invoice->created_at->format('M j, Y') }}</td>
                                            <td>{{ $invoice->description }}</td>
                                            <td style="font-weight: 600;">{{ number_format($invoice->amount, 0) }} EGP</td>
                                            <td style="color: var(--primary-cyan);">+{{ number_format($invoice->credits_granted) }} Credits</td>
                                            <td><span class="invoice-status {{ $invoice->status === 'paid' ? 'inv-paid' : 'inv-pending' }}">{{ $invoice->status }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="text-align: center; padding: 3rem 0; color: var(--text-muted);">
                            <i class="fas fa-receipt" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p>No transactions found in your history.</p>
                        </div>
                    @endif
                </div>
