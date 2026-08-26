            <aside class="dash-sidebar">
                <div class="user-profile-widget premium-profile-widget">
                    <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(0, 168, 230,0.15) 0%, transparent 60%); pointer-events: none;"></div>
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="premium-profile-avatar" style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 2.5px solid var(--primary-cyan); margin-bottom: 1rem; box-shadow: 0 0 15px rgba(0, 168, 230, 0.25);">
                    @else
                        <div class="premium-profile-avatar" style="width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-cyan), var(--primary)); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-family: var(--font-heading); font-weight: 800; margin-bottom: 1rem; text-transform: uppercase; color: #000;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                    <h3 style="font-family: var(--font-heading); font-size: 1.15rem; margin-bottom: 0.25rem; font-weight: 700; color: var(--text-main);">{{ $user->name }}</h3>
                    <p style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 1rem; word-break: break-all;">{{ $user->email }}</p>
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); padding: 0.3rem 0.75rem; border-radius: 20px; color: #00A58B; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #00A58B; display: inline-block; box-shadow: 0 0 8px #00A58B;"></span> Active
                    </div>
                </div>

                <nav class="dash-nav-list">

                    <a href="#overview" class="dash-nav-item active">
                        <i class="fas fa-chart-pie"></i>
                        <span>Overview</span>
                    </a>
                    <a href="#subscriptions" class="dash-nav-item">
                        <i class="fas fa-layer-group"></i>
                        <span>My Tools</span>
                    </a>
                    <a href="#billing" class="dash-nav-item">
                        <i class="fas fa-wallet"></i>
                        <span>Wallet & Credits</span>
                    </a>
                    <a href="#credits" class="dash-nav-item">
                        <i class="fas fa-hand-holding-heart"></i>
                        <span>Welcome Credits</span>
                    </a>
                    <a href="#feedback" class="dash-nav-item">
                        <i class="fas fa-comment-dots"></i>
                        <span>Feedback</span>
                    </a>
                    <a href="#settings" class="dash-nav-item">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </nav>
            </aside>
