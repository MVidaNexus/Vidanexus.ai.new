                @if(!$user->hasCompletedProfile())
                    <div style="background: rgba(255, 170, 0, 0.1); border: 1px solid rgba(255, 170, 0, 0.3); color: #ffaa00; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; font-weight: 500; display: flex; align-items: center; gap: 1rem;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 1.2rem;"></i>
                        <div>
                            <strong>Complete Your Profile</strong> — Please add your phone number and country in <a href="#settings" class="dash-nav-link" style="color: #ffaa00; text-decoration: underline; font-weight: 700;">Account Settings</a> to enable payments.
                        </div>
                    </div>
                @endif

                @if(session('success'))
                    <div style="background: var(--success-bg); border: 1px solid var(--success-border); color: var(--accent-success); padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; font-weight: 500;">
                        <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i> {{ session('success') }}
                    </div>
                @endif
