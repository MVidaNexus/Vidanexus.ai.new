@extends('admin.horizon.layout')

@section('title', 'Email Campaign Broadcaster')

@section('content')
<div class="space-y-6">

    {{-- Top Overview Stats --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card-admin" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(14, 165, 233, 0.15); color: #0ea5e9; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total Members</div>
                <div style="font-size: 1.8rem; font-weight: 900; color: var(--text-main); line-height: 1.2;">{{ number_format($stats['total_users']) }}</div>
            </div>
        </div>

        <div class="card-admin" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                <i class="fas fa-coins"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Low Balance (< 5 CR)</div>
                <div style="font-size: 1.8rem; font-weight: 900; color: var(--text-main); line-height: 1.2;">{{ number_format($stats['low_balance_users']) }}</div>
            </div>
        </div>

        <div class="card-admin" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(239, 68, 68, 0.15); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                <i class="fas fa-battery-empty"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Zero Credits</div>
                <div style="font-size: 1.8rem; font-weight: 900; color: var(--text-main); line-height: 1.2;">{{ number_format($stats['zero_balance_users']) }}</div>
            </div>
        </div>

        <div class="card-admin" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(16, 185, 129, 0.15); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                <i class="fas fa-paper-plane"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Queue Status</div>
                <div style="font-size: 1.2rem; font-weight: 900; color: #10b981; line-height: 1.2; padding-top: 4px;">Ready to Send</div>
            </div>
        </div>
    </div>

    {{-- Main Broadcast Form --}}
    <form method="POST" action="{{ route('admin.horizon.email-campaigns.send') }}" enctype="multipart/form-data" id="campaignForm">
        @csrf

        <div class="card-admin" style="margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--horizon-border);">
                <div>
                    <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.6rem;">
                        <i class="fas fa-bullhorn" style="color: #0ea5e9;"></i> Compose & Dispatch Campaign
                    </h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 4px 0 0;">Broadcast marketing, system notifications, or credit reminders to targeted user segments.</p>
                </div>

                <div id="audience-counter-badge" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 12px; background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.3); color: #38bdf8; font-weight: 800; font-size: 0.9rem;">
                    <i class="fas fa-users text-xs"></i> <span id="audience-count-text">{{ number_format($stats['total_users']) }} Recipients Target</span>
                </div>
            </div>

            {{-- STEP 1: Audience Selection --}}
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 800; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">
                    1. Select Target Audience Segment
                </label>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    <!-- Option 1: All Users -->
                    <label class="audience-option-card" style="display: flex; align-items: flex-start; gap: 1rem; padding: 1.25rem; border-radius: 16px; background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); cursor: pointer; transition: all 0.2s;">
                        <input type="radio" name="audience_type" value="all" checked onchange="handleAudienceChange()" style="margin-top: 4px; accent-color: #0ea5e9;">
                        <div>
                            <div style="font-weight: 800; color: var(--text-main); font-size: 0.95rem;">👥 All Registered Members</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 3px;">Send to entire user base ({{ number_format($stats['total_users']) }} users)</div>
                        </div>
                    </label>

                    <!-- Option 2: Low Balance -->
                    <label class="audience-option-card" style="display: flex; align-items: flex-start; gap: 1rem; padding: 1.25rem; border-radius: 16px; background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); cursor: pointer; transition: all 0.2s;">
                        <input type="radio" name="audience_type" value="low_balance" onchange="handleAudienceChange()" style="margin-top: 4px; accent-color: #0ea5e9;">
                        <div style="flex: 1;">
                            <div style="font-weight: 800; color: var(--text-main); font-size: 0.95rem;">💳 Low Balance Reminder</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 3px;">Users with wallet balance below threshold</div>
                            <div id="low-balance-input-wrap" style="display: none; margin-top: 8px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">Threshold:</span>
                                    <input type="number" name="balance_threshold" id="balance_threshold" value="5" min="0" step="1" onchange="updateEstimatedAudience()" style="width: 80px; padding: 4px 8px; border-radius: 8px; background: rgba(255,255,255,0.08); border: 1px solid var(--horizon-border); color: #fff; font-size: 0.85rem; outline: none;">
                                    <span style="font-size: 0.75rem; color: #f59e0b; font-weight: 700;">Credits</span>
                                </div>
                            </div>
                        </div>
                    </label>

                    <!-- Option 3: Custom List / File Upload -->
                    <label class="audience-option-card" style="display: flex; align-items: flex-start; gap: 1rem; padding: 1.25rem; border-radius: 16px; background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); cursor: pointer; transition: all 0.2s;">
                        <input type="radio" name="audience_type" value="custom_list" onchange="handleAudienceChange()" style="margin-top: 4px; accent-color: #0ea5e9;">
                        <div>
                            <div style="font-weight: 800; color: var(--text-main); font-size: 0.95rem;">📁 Custom List / Import File</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 3px;">Upload .csv/.txt or paste external email lists</div>
                        </div>
                    </label>
                </div>

                <!-- Custom List Details Box (Hidden by default) -->
                <div id="custom-list-box" style="display: none; margin-top: 1rem; padding: 1.25rem; border-radius: 16px; background: rgba(255,255,255,0.02); border: 1px solid rgba(14, 165, 233, 0.2);">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Paste Emails (comma or newline separated):</label>
                            <textarea name="custom_emails" id="custom_emails" placeholder="john@example.com, Sarah <sarah@domain.com>&#10;user3@domain.com" rows="4" oninput="updateEstimatedAudience()" style="width: 100%; padding: 0.75rem; background: rgba(0,0,0,0.3); border: 1px solid var(--horizon-border); border-radius: 10px; color: #fff; font-family: monospace; font-size: 0.85rem; outline: none;"></textarea>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Or Upload File (.csv / .txt):</label>
                            <div style="border: 2px dashed var(--horizon-border); border-radius: 12px; padding: 1.5rem; text-align: center; background: rgba(255,255,255,0.01);">
                                <i class="fas fa-file-csv" style="font-size: 1.8rem; color: #0ea5e9; margin-bottom: 8px;"></i>
                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">Choose a .csv or .txt file containing email addresses</div>
                                <input type="file" name="csv_file" accept=".csv,.txt" style="font-size: 0.8rem; color: var(--text-muted);">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- STEP 2: Subject & Template Selection --}}
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 800; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">
                    2. Campaign Subject & Template
                </label>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 6px;">Email Subject Line <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="subject" id="campaign_subject" value="Exciting New Features & Updates on VidaNexus AI!" required placeholder="e.g. Special Offer: 50% Bonus Credits this week" style="width: 100%; padding: 0.8rem 1.25rem; background: rgba(255,255,255,0.05); border: 1px solid var(--horizon-border); border-radius: 12px; color: var(--text-main); font-size: 1rem; outline: none; font-weight: 600;">
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                    <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin: 0;">Choose Starter Template:</label>
                    <div style="display: flex; gap: 0.5rem;">
                        @foreach($defaultTemplates as $tpl)
                            <button type="button" onclick="loadCampaignTemplate({{ json_encode($tpl) }})" class="vn-btn" style="padding: 0.35rem 0.8rem; font-size: 0.75rem; border-radius: 8px;">
                                {{ $tpl['name'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Dynamic Variable Chips --}}
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; flex-wrap: wrap; background: rgba(255,255,255,0.02); padding: 0.5rem 0.75rem; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05);">
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;">Click to Insert Tag:</span>
                    <button type="button" onclick="insertTag('{name}')" style="padding: 2px 8px; border-radius: 6px; background: rgba(14,165,233,0.15); border: 1px solid rgba(14,165,233,0.3); color: #38bdf8; font-size: 0.75rem; font-weight: 700; cursor: pointer;">{name}</button>
                    <button type="button" onclick="insertTag('{email}')" style="padding: 2px 8px; border-radius: 6px; background: rgba(14,165,233,0.15); border: 1px solid rgba(14,165,233,0.3); color: #38bdf8; font-size: 0.75rem; font-weight: 700; cursor: pointer;">{email}</button>
                    <button type="button" onclick="insertTag('{balance}')" style="padding: 2px 8px; border-radius: 6px; background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.3); color: #f59e0b; font-size: 0.75rem; font-weight: 700; cursor: pointer;">{balance}</button>
                    <button type="button" onclick="insertTag('{site_url}')" style="padding: 2px 8px; border-radius: 6px; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: #10b981; font-size: 0.75rem; font-weight: 700; cursor: pointer;">{site_url}</button>
                    <button type="button" onclick="insertTag('{app_name}')" style="padding: 2px 8px; border-radius: 6px; background: rgba(168,85,247,0.15); border: 1px solid rgba(168,85,247,0.3); color: #c084fc; font-size: 0.75rem; font-weight: 700; cursor: pointer;">{app_name}</button>
                </div>

                {{-- HTML Code Editor --}}
                <div style="position: relative;">
                    <textarea name="content" id="campaign_content" rows="16" required style="width: 100%; padding: 1rem; background: #080c14; border: 1px solid var(--horizon-border); border-radius: 14px; color: #38bdf8; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; font-size: 0.85rem; line-height: 1.6; outline: none; resize: vertical;">{{ $defaultTemplates[0]['html'] }}</textarea>
                </div>
            </div>

            {{-- Actions Toolbar --}}
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; padding-top: 1.5rem; border-top: 1px solid var(--horizon-border);">
                <div style="display: flex; gap: 0.75rem;">
                    <button type="button" onclick="previewCampaign()" class="vn-btn" style="padding: 0.7rem 1.25rem; border-radius: 12px; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-eye text-sky-400"></i> Preview HTML
                    </button>
                    <button type="button" onclick="sendTestEmail()" id="btn-send-test" class="vn-btn" style="padding: 0.7rem 1.25rem; border-radius: 12px; display: inline-flex; align-items: center; gap: 0.5rem; color: #a855f7; border-color: rgba(168,85,247,0.3);">
                        <i class="fas fa-paper-plane"></i> Send Test to Me
                    </button>
                </div>

                <button type="button" onclick="confirmDispatch()" class="vn-btn vn-btn-primary" style="padding: 0.75rem 2rem; border-radius: 12px; font-size: 1rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.6rem; box-shadow: 0 4px 20px rgba(14, 165, 233, 0.35);">
                    <i class="fas fa-rocket"></i> Launch Campaign Now
                </button>
            </div>

        </div>
    </form>
</div>

{{-- Preview Modal --}}
<div id="previewModal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px);" onclick="if(event.target===this)closePreviewModal()">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 95%; max-width: 680px; height: 85vh; background: #0b0f17; border: 1px solid var(--horizon-border); border-radius: 20px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.8);">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid var(--horizon-border); background: #111827;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-eye text-sky-400"></i> Email Live Preview
            </h4>
            <button onclick="closePreviewModal()" style="background: none; border: none; color: var(--text-muted); font-size: 1.2rem; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div style="flex: 1; padding: 1rem; overflow-y: auto;">
            <iframe id="previewIframe" style="width: 100%; height: 100%; border: none; border-radius: 12px; background: #0b0f17;"></iframe>
        </div>
    </div>
</div>

<script>
    function handleAudienceChange() {
        const type = document.querySelector('input[name="audience_type"]:checked').value;
        const lowWrap = document.getElementById('low-balance-input-wrap');
        const customBox = document.getElementById('custom-list-box');

        lowWrap.style.display = type === 'low_balance' ? 'block' : 'none';
        customBox.style.display = type === 'custom_list' ? 'block' : 'none';

        updateEstimatedAudience();
    }

    function updateEstimatedAudience() {
        const type = document.querySelector('input[name="audience_type"]:checked').value;
        const threshold = document.getElementById('balance_threshold')?.value || 5;
        const customEmails = document.getElementById('custom_emails')?.value || '';

        fetch('{{ route("admin.horizon.email-campaigns.estimate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                audience_type: type,
                balance_threshold: threshold,
                custom_emails: customEmails
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('audience-count-text').textContent = `${data.count} Recipients Target`;
            }
        })
        .catch(err => console.error(err));
    }

    function loadCampaignTemplate(tpl) {
        if (!confirm('Replace editor content with ' + tpl.name + '?')) return;
        document.getElementById('campaign_subject').value = tpl.subject;
        document.getElementById('campaign_content').value = tpl.html;
    }

    function insertTag(tag) {
        const textarea = document.getElementById('campaign_content');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        textarea.value = text.substring(0, start) + tag + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        textarea.focus();
    }

    function previewCampaign() {
        const content = document.getElementById('campaign_content').value;
        const parsed = content
            .replaceAll('{name}', 'Demo User')
            .replaceAll('{email}', 'user@vidanexus.ai')
            .replaceAll('{balance}', '25.00')
            .replaceAll('{site_url}', 'https://vidanexus.ai')
            .replaceAll('{app_name}', 'VidaNexus AI');

        const iframe = document.getElementById('previewIframe');
        iframe.srcdoc = parsed;
        document.getElementById('previewModal').style.display = 'block';
    }

    function closePreviewModal() {
        document.getElementById('previewModal').style.display = 'none';
    }

    function sendTestEmail() {
        const btn = document.getElementById('btn-send-test');
        const subject = document.getElementById('campaign_subject').value;
        const content = document.getElementById('campaign_content').value;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending Test...';

        fetch('{{ route("admin.horizon.email-campaigns.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                subject: subject,
                content: content
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Test to Me';
            if (data.success) {
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Test to Me';
            alert('Failed to send test email: ' + err);
        });
    }

    function confirmDispatch() {
        const targetText = document.getElementById('audience-count-text').textContent;
        if (confirm(`Are you sure you want to launch this campaign to ${targetText}? Emails will be dispatched in background queue.`)) {
            document.getElementById('campaignForm').submit();
        }
    }
</script>
@endsection
