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
                        <div class="info-group premium-info-group" style="background: linear-gradient(145deg, rgba(16, 185, 129, 0.1), rgba(0, 168, 230, 0.05)); border: 1px solid rgba(16, 185, 129, 0.25);">
                            <label style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 0;"><i class="fas fa-shield-check" style="margin-right: 0.5rem; color: #10b981;"></i>Account Status</label>
                            <p style="font-size: 1.15rem; font-weight: 800; margin: 0; color: #10b981; display: flex; align-items: center; gap: 0.4rem;">
                                <i class="fas fa-check-circle" style="font-size: 0.95rem;"></i> Verified & Active
                            </p>
                        </div>
                    </div>
                </div>
