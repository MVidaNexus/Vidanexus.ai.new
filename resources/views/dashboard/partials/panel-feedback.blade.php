                <div class="content-panel" id="feedback" style="display: none;">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fas fa-comment-dots"></i> Send feedback</h2>
                    </div>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem; max-width: 560px;">
                        Share bugs, ideas, or requests. Your message is stored for support and emailed to the admin inbox.
                    </p>
                    <form action="{{ route('dashboard.feedback.store') }}" method="POST" style="max-width: 560px;">
                        @csrf
                        <div style="margin-bottom: 1.25rem;">
                            <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Subject <span style="opacity:0.6">(optional)</span></label>
                            <input type="text" name="subject" value="{{ old('subject') }}" maxlength="200"
                                   style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text-main); padding: 1rem; border-radius: 8px; font-family: inherit;">
                        </div>
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Message <span style="color: #ff4b4b;">*</span></label>
                            <textarea name="message" rows="6" required maxlength="10000"
                                      style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text-main); padding: 1rem; border-radius: 8px; font-family: inherit; resize: vertical;">{{ old('message') }}</textarea>
                        </div>
                        <button type="submit" class="vn-btn vn-btn-primary">Submit feedback</button>
                    </form>
                </div>
