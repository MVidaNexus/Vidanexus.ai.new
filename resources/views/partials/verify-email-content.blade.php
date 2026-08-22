        <div class="verify-container">
            <div class="verify-card">
                <div class="verify-icon-wrapper">
                    <i class="fas fa-envelope-circle-check"></i>
                </div>

                <h1 class="verify-title">Verify Your Email</h1>
                
                <p class="verify-subtitle">
                    We've sent a verification link to
                    <br>
                    <span class="verify-email-highlight">{{ Auth::user()->email }}</span>
                    <br><br>
                    Click the link in your inbox to activate your VidaNexus AI account and unlock your free credits.
                </p>

                @if (session('status') === 'verification-link-sent' || session('resent'))
                    <div class="verify-success">
                        <i class="fas fa-check-circle"></i>
                        A new verification link has been sent to your email address.
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="verify-btn verify-btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Resend Verification Email
                    </button>
                </form>

                <a href="/" class="verify-btn verify-btn-secondary" style="text-decoration: none;">
                    <i class="fas fa-arrow-left"></i>
                    Back to Home
                </a>

                <hr class="verify-divider">

                <div class="verify-footer">
                    <i class="fas fa-info-circle"></i>
                    Didn't receive the email? Check your spam folder or contact support at
                    <a href="mailto:support@vidanexus.ai" style="color: var(--primary-cyan);">support@vidanexus.ai</a>
                </div>
            </div>
        </div>
