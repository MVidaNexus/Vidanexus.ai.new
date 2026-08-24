@extends('admin.horizon.layout')

@section('title', 'User Feedback')

@section('content')
<div class="space-y-6">

    {{-- Top Stats Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card-admin" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(14, 165, 233, 0.15); color: #0ea5e9; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                <i class="fas fa-comments"></i>
            </div>
            <div>
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total Feedbacks</div>
                <div style="font-size: 1.8rem; font-weight: 900; color: var(--text-main); line-height: 1.2;">{{ number_format($stats['total']) }}</div>
            </div>
        </div>

        <div class="card-admin" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(16, 185, 129, 0.15); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div>
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Past 7 Days</div>
                <div style="font-size: 1.8rem; font-weight: 900; color: var(--text-main); line-height: 1.2;">{{ number_format($stats['this_week']) }}</div>
            </div>
        </div>

        <div class="card-admin" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(168, 85, 247, 0.15); color: #a855f7; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Unique Contributors</div>
                <div style="font-size: 1.8rem; font-weight: 900; color: var(--text-main); line-height: 1.2;">{{ number_format($stats['unique_users']) }}</div>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="card-admin">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--horizon-border);">
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0;">User Feedback Inbox</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 4px 0 0;">Review ideas, suggestions, bug reports, and feedback submitted by users.</p>
            </div>

            {{-- Search Bar --}}
            <form method="GET" action="{{ route('admin.horizon.feedback.index') }}" style="display: flex; gap: 0.5rem; width: 100%; max-width: 360px;">
                <div style="position: relative; flex: 1;">
                    <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search user, email, topic..." 
                           style="width: 100%; padding: 0.6rem 1rem 0.6rem 2.4rem; background: rgba(255,255,255,0.05); border: 1px solid var(--horizon-border); border-radius: 12px; color: var(--text-main); font-size: 0.9rem; outline: none;">
                </div>
                <button type="submit" class="vn-btn vn-btn-primary" style="padding: 0.6rem 1rem; border-radius: 12px; font-size: 0.85rem;">Filter</button>
                @if(request('search'))
                    <a href="{{ route('admin.horizon.feedback.index') }}" class="vn-btn" style="padding: 0.6rem 0.8rem; border-radius: 12px; font-size: 0.85rem; color: var(--text-muted); text-decoration: none;">Clear</a>
                @endif
            </form>
        </div>

        @if($feedbacks->isEmpty())
            <div style="text-align: center; padding: 4rem 1rem;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: var(--text-muted); font-size: 1.5rem;">
                    <i class="fas fa-inbox"></i>
                </div>
                <h4 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">No Feedback Found</h4>
                <p style="color: var(--text-muted); font-size: 0.9rem; max-width: 400px; margin: 0 auto;">No feedback submissions match your criteria yet.</p>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--horizon-border); color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em;">
                            <th style="padding: 1rem 0.75rem;">User / Contributor</th>
                            <th style="padding: 1rem 0.75rem;">Subject & Message</th>
                            <th style="padding: 1rem 0.75rem;">Device & IP</th>
                            <th style="padding: 1rem 0.75rem;">Submitted</th>
                            <th style="padding: 1rem 0.75rem; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.9rem;">
                        @foreach($feedbacks as $fb)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                                {{-- User Column --}}
                                <td style="padding: 1rem 0.75rem; vertical-align: top;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #0ea5e9, #6366f1); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9rem; flex-shrink: 0;">
                                            {{ strtoupper(substr($fb->name ?: ($fb->user->name ?? 'U'), 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: var(--text-main);">
                                                {{ $fb->name ?: ($fb->user->name ?? 'Anonymous') }}
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                {{ $fb->email ?: ($fb->user->email ?? 'N/A') }}
                                            </div>
                                            @if($fb->user_id)
                                                <span style="display: inline-block; font-size: 0.65rem; font-weight: 700; color: #0ea5e9; background: rgba(14, 165, 233, 0.1); padding: 1px 6px; border-radius: 4px; margin-top: 2px;">User #{{ $fb->user_id }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Message Column --}}
                                <td style="padding: 1rem 0.75rem; vertical-align: top; max-width: 400px;">
                                    @if($fb->subject)
                                        <div style="font-weight: 800; color: #38bdf8; margin-bottom: 4px; font-size: 0.95rem;">
                                            <i class="fas fa-tag text-xs opacity-60 mr-1"></i> {{ $fb->subject }}
                                        </div>
                                    @endif
                                    <div style="color: var(--text-main); line-height: 1.5; font-size: 0.88rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $fb->message }}
                                    </div>
                                </td>

                                {{-- Device & IP --}}
                                <td style="padding: 1rem 0.75rem; vertical-align: top; color: var(--text-muted); font-size: 0.8rem;">
                                    <div><i class="fas fa-network-wired text-xs mr-1 opacity-50"></i> {{ $fb->ip_address ?: 'Unknown IP' }}</div>
                                    <div style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 4px; font-size: 0.75rem;" title="{{ $fb->user_agent }}">
                                        <i class="fas fa-laptop text-xs mr-1 opacity-50"></i> {{ $fb->user_agent ?: 'Unknown' }}
                                    </div>
                                </td>

                                {{-- Date --}}
                                <td style="padding: 1rem 0.75rem; vertical-align: top; color: var(--text-muted); font-size: 0.85rem; white-space: nowrap;">
                                    <div style="font-weight: 600; color: var(--text-main);">{{ $fb->created_at->diffForHumans() }}</div>
                                    <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 2px;">{{ $fb->created_at->format('M d, Y • h:i A') }}</div>
                                </td>

                                {{-- Actions --}}
                                <td style="padding: 1rem 0.75rem; vertical-align: top; text-align: right; white-space: nowrap;">
                                    <div style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                        {{-- View Modal Trigger --}}
                                        <button type="button" 
                                                onclick="openFeedbackModal({{ json_encode($fb) }})"
                                                title="View Full Feedback"
                                                style="width: 32px; height: 32px; border-radius: 8px; background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.25); color: #38bdf8; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;"
                                                onmouseover="this.style.background='rgba(14, 165, 233, 0.25)'" onmouseout="this.style.background='rgba(14, 165, 233, 0.1)'">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>

                                        {{-- Reply via Email --}}
                                        @if($fb->email)
                                            <a href="mailto:{{ $fb->email }}?subject={{ urlencode('Re: ' . ($fb->subject ?: 'Your Feedback on VidaNexus')) }}"
                                               title="Reply via Email"
                                               style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); color: #10b981; cursor: pointer; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;"
                                               onmouseover="this.style.background='rgba(16, 185, 129, 0.25)'" onmouseout="this.style.background='rgba(16, 185, 129, 0.1)'">
                                                <i class="fas fa-reply text-xs"></i>
                                            </a>
                                        @endif

                                        {{-- Delete --}}
                                        <form method="POST" action="{{ route('admin.horizon.feedback.destroy', $fb->id) }}" onsubmit="return confirm('Are you sure you want to delete this feedback?');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="Delete Entry"
                                                    style="width: 32px; height: 32px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); color: #ef4444; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;"
                                                    onmouseover="this.style.background='rgba(239, 68, 68, 0.25)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div style="margin-top: 1.5rem;">
                {{ $feedbacks->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Feedback Detail Modal --}}
<div id="feedbackDetailModal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,0.75); backdrop-filter: blur(10px);" onclick="if(event.target===this)closeFeedbackModal()">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 600px; background: var(--horizon-card, #0f172a); border: 1px solid var(--horizon-border); border-radius: 24px; padding: 2rem; box-shadow: 0 25px 60px rgba(0,0,0,0.6);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--horizon-border);">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(14, 165, 233, 0.15); color: #0ea5e9; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-main); margin: 0;" id="modal_fb_name">Feedback Details</h3>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin: 2px 0 0;" id="modal_fb_email"></p>
                </div>
            </div>
            <button onclick="closeFeedbackModal()" style="background: none; border: none; color: var(--text-muted); font-size: 1.2rem; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <div style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px;">Subject</div>
            <div style="font-size: 1.05rem; font-weight: 800; color: #38bdf8;" id="modal_fb_subject"></div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <div style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px;">Message</div>
            <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--horizon-border); border-radius: 14px; padding: 1.25rem; color: var(--text-main); line-height: 1.7; font-size: 0.95rem; white-space: pre-wrap; max-height: 280px; overflow-y: auto;" id="modal_fb_message"></div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1.5rem;">
            <div><strong style="color: var(--text-main);">IP:</strong> <span id="modal_fb_ip"></span></div>
            <div><strong style="color: var(--text-main);">Date:</strong> <span id="modal_fb_date"></span></div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" onclick="closeFeedbackModal()" class="vn-btn" style="padding: 0.6rem 1.25rem; border-radius: 12px;">Close</button>
            <a id="modal_fb_reply" href="#" class="vn-btn vn-btn-primary" style="padding: 0.6rem 1.25rem; border-radius: 12px; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                <i class="fas fa-reply"></i> Reply to User
            </a>
        </div>
    </div>
</div>

<script>
    function openFeedbackModal(fb) {
        document.getElementById('modal_fb_name').textContent = fb.name || 'Anonymous User';
        document.getElementById('modal_fb_email').textContent = fb.email || 'No email provided';
        document.getElementById('modal_fb_subject').textContent = fb.subject || 'No Subject';
        document.getElementById('modal_fb_message').textContent = fb.message || '';
        document.getElementById('modal_fb_ip').textContent = fb.ip_address || 'Unknown';
        document.getElementById('modal_fb_date').textContent = fb.created_at || '';
        
        const replyBtn = document.getElementById('modal_fb_reply');
        if (fb.email) {
            replyBtn.href = `mailto:${fb.email}?subject=${encodeURIComponent('Re: ' + (fb.subject || 'Your Feedback on VidaNexus'))}`;
            replyBtn.style.display = 'inline-flex';
        } else {
            replyBtn.style.display = 'none';
        }

        document.getElementById('feedbackDetailModal').style.display = 'block';
    }

    function closeFeedbackModal() {
        document.getElementById('feedbackDetailModal').style.display = 'none';
    }
</script>
@endsection
