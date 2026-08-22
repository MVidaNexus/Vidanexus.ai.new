                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="stat-card premium-stat-card" style="border: 1px solid rgba(0, 168, 230,0.3);">
                        <div style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: rgba(0, 168, 230,0.1); filter: blur(30px); border-radius: 50%;"></div>
                        <div class="stat-icon cyan" style="background: linear-gradient(135deg, rgba(0, 168, 230,0.2), rgba(0, 168, 230,0.05)); border: 1px solid rgba(0, 168, 230,0.2); box-shadow: 0 0 15px rgba(0, 168, 230,0.1);">
                            <i class="fas fa-wallet" style="background: linear-gradient(135deg, #00A8E6, #38bdf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        </div>
                        <div class="stat-info">
                            <h4 style="color: var(--primary-cyan); font-weight: 700;">Wallet Balance</h4>
                            <div class="value" style="display: flex; align-items: baseline; gap: 0.5rem; font-size: 2.2rem;">
                                <span class="js-credit-balance"
                                      data-credit-value="{{ (float) $walletBalance }}"
                                      data-decimals="2">{{ number_format($walletBalance, 2) }}</span>
                                <span class="premium-stat-value-unit" style="font-size: 0.85rem;">Credits</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card premium-stat-card" style="border: 1px solid rgba(16,185,129,0.3);">
                        <div style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: rgba(16,185,129,0.1); filter: blur(30px); border-radius: 50%;"></div>
                        <div class="stat-icon green" style="background: linear-gradient(135deg, rgba(16,185,129,0.2), rgba(16,185,129,0.05)); border: 1px solid rgba(16,185,129,0.2); box-shadow: 0 0 15px rgba(16,185,129,0.1);">
                            <i class="fas fa-box-open" style="background: linear-gradient(135deg, #00A58B, #34d399); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        </div>
                        <div class="stat-info">
                            <h4 style="color: #00A58B; font-weight: 700;">Tools Unlocked</h4>
                            <div class="value" style="display: flex; align-items: baseline; gap: 0.5rem; font-size: 2.2rem;">
                                {{ $accessibleCount }} <span class="premium-stat-value-slash" style="font-size: 1.2rem;">/ {{ $totalTools }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card premium-stat-card" style="border: 1px solid rgba(168,85,247,0.3);">
                        <div style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: rgba(168,85,247,0.1); filter: blur(30px); border-radius: 50%;"></div>
                        <div class="stat-icon purple" style="background: linear-gradient(135deg, rgba(168,85,247,0.2), rgba(168,85,247,0.05)); border: 1px solid rgba(168,85,247,0.2); box-shadow: 0 0 15px rgba(168,85,247,0.1);">
                            <i class="fas fa-bolt" style="background: linear-gradient(135deg, #a855f7, #c084fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        </div>
                        <div class="stat-info">
                            <h4 style="color: #a855f7; font-weight: 700;">Account Model</h4>
                            <div class="value premium-account-model-text" style="font-size: 1.4rem; line-height: 1.2; padding-top: 0.4rem;">
                                Pay-per-Action
                            </div>
                        </div>
                    </div>
                </div>
