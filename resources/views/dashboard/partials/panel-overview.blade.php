                <div class="content-panel" id="overview">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fas fa-id-card"></i> Account Details</h2>
                    </div>
                    <div class="profile-info" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                        <div class="info-group premium-info-group">
                            <label style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 0;"><i class="fas fa-user" style="margin-right: 0.5rem; opacity: 0.5;"></i>Full Name</label>
                            <p style="font-size: 1.2rem; font-weight: 600; color: var(--text-main); margin: 0;">{{ $user->name }}</p>
                        </div>
                        <div class="info-group premium-info-group">
                            <label style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 0;"><i class="fas fa-envelope" style="margin-right: 0.5rem; opacity: 0.5;"></i>Email Address</label>
                            <p style="font-size: 1.2rem; font-weight: 600; color: var(--text-main); margin: 0; word-break: break-all;">{{ $user->email }}</p>
                        </div>
                        <div class="info-group premium-info-group">
                            <label style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 0;"><i class="fas fa-phone" style="margin-right: 0.5rem; opacity: 0.5;"></i>Phone Number</label>
                            <p style="font-size: 1.2rem; font-weight: 600; color: var(--text-main); margin: 0;">{{ $user->phone ?? '⚠️ Not set' }}</p>
                        </div>
                        <div class="info-group premium-info-group">
                            <label style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 0;"><i class="fas fa-globe" style="margin-right: 0.5rem; opacity: 0.5;"></i>Country</label>
                            <p style="font-size: 1.2rem; font-weight: 600; color: var(--text-main); margin: 0;">{{ $user->country ?? '⚠️ Not set' }}</p>
                        </div>
                        <div class="info-group premium-info-group">
                            <label style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 0;"><i class="fas fa-calendar-alt" style="margin-right: 0.5rem; opacity: 0.5;"></i>Member Since</label>
                            <p style="font-size: 1.2rem; font-weight: 600; color: var(--text-main); margin: 0;">{{ $user->created_at->format('F j, Y') }}</p>
                        </div>
                        <div class="info-group premium-info-group" style="background: linear-gradient(145deg, rgba(0, 168, 230,0.1), rgba(168,85,247,0.1)); border: 1px solid rgba(0, 168, 230,0.2);">
                            <label style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 0;"><i class="fas fa-star" style="margin-right: 0.5rem; color: #f59e0b;"></i>Account Model</label>
                            <p class="premium-account-model-text" style="font-size: 1.2rem; font-weight: 800; margin: 0;">Modular AI Marketplace</p>
                        </div>
                    </div>
                </div>
