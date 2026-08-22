                <div class="content-panel" id="credits" style="display: none;">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fas fa-hand-holding-heart"></i> Welcome &amp; system credits</h2>
                    </div>
                    <p style="color: var(--text-muted); line-height: 1.6; max-width: 640px; margin-bottom: 2rem;">
                        New accounts on the <strong>Beginner</strong> path receive welcome CRS from the system matrix (<code style="font-size:0.85em;">plan_credits_beginner</code> in Horizon).
                        Marketplace tool subscriptions bill your <strong>wallet</strong> first for each AI action; trial-style tools can also use a <strong>per-tool bonus pool</strong> after wallet CRS are used.
                    </p>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                        <div style="background: rgba(0, 168, 230,0.08); border: 1px solid rgba(0, 168, 230,0.25); border-radius: 14px; padding: 1.25rem;">
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Configured welcome CRS</div>
                            <div style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: var(--primary-cyan);">{{ number_format($welcomeCredits, 0) }}</div>
                        </div>
                        <div style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25); border-radius: 14px; padding: 1.25rem;">
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Wallet balance now</div>
                            <div style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: #00A58B;">{{ number_format($walletBalance, 0) }} CRS</div>
                        </div>
                    </div>
                </div>
