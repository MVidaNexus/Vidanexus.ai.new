@extends('admin.horizon.layout')

@section('title', 'User Management & Access Control')

@section('content')
<style>
    :root {
        --user-row-bg: rgba(255, 255, 255, 0.03);
        --user-row-hover: rgba(255, 255, 255, 0.05);
        --user-border: rgba(255, 255, 255, 0.06);
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2.5rem;
    }

    .stat-card-premium {
        background: var(--glass-bg);
        border: 1px solid var(--user-border);
        padding: 1.5rem;
        border-radius: 24px;
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        gap: 1.25rem;
        position: relative;
        overflow: hidden;
    }

    .stat-card-premium::after {
        content: '';
        position: absolute;
        top: 0; right: 0; width: 100px; height: 100px;
        background: radial-gradient(circle, var(--accent-glow) 0%, transparent 70%);
        opacity: 0.1;
        pointer-events: none;
    }

    .stat-icon-box {
        width: 54px; height: 54px;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--user-border);
    }

    .stat-info h4 { margin: 0; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
    .stat-info .stat-number { font-size: 1.6rem; font-weight: 800; color: var(--text-main); font-family: 'Space+Grotesk', sans-serif; }

    /* Table Hybrid Layout */
    .user-table-header {
        display: grid;
        grid-template-columns: 2.5fr 1.5fr 1.2fr 1fr 1.8fr;
        padding: 1rem 1.5rem;
        color: var(--text-muted);
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        background: rgba(0,0,0,0.1);
        border-radius: 12px;
        margin-bottom: 0.75rem;
        border: 1px solid var(--user-border);
    }

    .user-row {
        display: grid;
        grid-template-columns: 2.5fr 1.5fr 1.2fr 1fr 1.8fr;
        padding: 1.25rem 1.5rem;
        background: var(--user-row-bg);
        border: 1px solid var(--user-border);
        border-radius: 20px;
        margin-bottom: 0.75rem;
        align-items: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: default;
    }

    .user-row:hover {
        background: var(--user-row-hover);
        border-color: var(--primary-admin);
        transform: scale(1.002) translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .avatar-wrap {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: var(--horizon-primary-bg);
        color: var(--primary-admin);
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 1.1rem;
        border: 1px solid rgba(255,255,255,0.05);
    }

    .tier-badge-premium {
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border: 1px solid rgba(255,255,255,0.05);
    }

    .tier-beginner { background: rgba(148, 163, 184, 0.1); color: #94a3b8; }
    .tier-starter { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2); }
    .tier-pro { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; border-color: rgba(14, 165, 233, 0.2); }
    .tier-ultimate { background: rgba(168, 85, 247, 0.1); color: #a855f7; border-color: rgba(168, 85, 247, 0.2); }
    .tier-agency { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.2); }

    .action-pill {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--user-border);
        color: var(--text-muted);
        transition: all 0.2s;
        cursor: pointer;
    }

    .action-pill:hover {
        background: var(--primary-admin);
        color: #000;
        border-color: var(--primary-admin);
        transform: translateY(-2px);
    }

    .search-box-premium {
        background: var(--glass-bg);
        border: 1px solid var(--user-border);
        border-radius: 18px;
        padding: 0.75rem 1.5rem;
        display: flex; align-items: center; gap: 1rem;
        margin-bottom: 2rem;
        transition: all 0.3s;
    }

    .search-box-premium:focus-within {
        border-color: var(--primary-cyan);
        box-shadow: 0 0 20px rgba(14, 165, 233, 0.1);
    }

    .search-input {
        background: none; border: none; outline: none;
        color: var(--text-main); font-size: 0.95rem; width: 100%;
        font-family: inherit;
    }

    @media (max-width: 1200px) {
        .user-table-header { display: none; }
        .user-row { grid-template-columns: 1fr; gap: 1rem; padding: 1.5rem; }
    }

    /* Modals - Cyber-Premium Adaptive Design */
    .modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0; top: 0;
        width: 100%; height: 100%;
        background-color: rgba(2, 2, 8, 0.85);
        backdrop-filter: blur(12px) saturate(180%);
        align-items: center;
        justify-content: center;
        padding: 20px;
        transition: all 0.3s ease;
    }

    /* Light Mode Backdrop Adjustment */
    [data-theme="light"] .modal {
        background-color: rgba(255, 255, 255, 0.6);
    }

    .modal-content {
        background: radial-gradient(circle at top left, var(--horizon-card), var(--horizon-bg));
        border: 1px solid var(--horizon-border);
        padding: 0;
        border-radius: 28px;
        width: 100%;
        max-width: 520px;
        position: relative;
        box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.4);
        animation: modalSlide 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
        color: var(--text-main);
    }

    [data-theme="light"] .modal-content {
        background: #fff;
        box-shadow: 0 20px 60px -10px rgba(15, 23, 42, 0.15);
    }

    .modal-header {
        padding: 2rem 2.5rem 1.5rem;
        background: rgba(255,255,255,0.02);
        border-bottom: 1px solid var(--horizon-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--text-main) 0%, var(--text-muted) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .modal-body {
        padding: 2rem 2.5rem;
    }

    .target-identity-badge {
        background: var(--horizon-nav-hover);
        border: 1px solid var(--horizon-border);
        padding: 0.8rem 1.25rem;
        border-radius: 14px;
        margin-bottom: 2rem;
    }

    .modal-input {
        width: 100%;
        padding: 1.1rem 1.4rem;
        background: var(--vn-input-bg);
        border: 1px solid var(--vn-input-border);
        border-radius: 14px;
        color: var(--text-main);
        font-size: 1rem;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        font-family: 'Space Grotesk', sans-serif;
    }

    .modal-input:focus {
        border-color: var(--primary-cyan);
        box-shadow: 0 0 20px rgba(14, 165, 233, 0.15);
        outline: none;
    }

    .modal-footer {
        padding: 1.5rem 2.5rem 2.5rem;
        background: rgba(0,0,0,0.03);
    }

    .btn-save {
        background: linear-gradient(135deg, var(--primary-cyan), #0ea5e9);
        color: #fff;
        border: none;
        padding: 1.1rem 2rem;
        border-radius: 14px;
        font-weight: 900;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s;
        box-shadow: 0 10px 25px rgba(14, 165, 233, 0.2);
    }

    /* Keep button text dark on cyan for better contrast if needed, but white is usually sharper in dark mode. 
       Actually, standard vn strategy is dark text on cyan. Let's stick to that for Consistency. */
    [data-theme="dark"] .btn-save { color: #000; }

    .btn-save:hover {
        transform: translateY(-3px) scale(1.01);
        box-shadow: 0 15px 35px rgba(14, 165, 233, 0.4);
        filter: brightness(1.1);
    }

    @keyframes modalSlide {
        from { opacity: 0; transform: translateY(30px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
</style>

<div class="stats-container">
    <div class="stat-card-premium" style="--accent-glow: var(--primary-cyan);">
        <div class="stat-icon-box" style="color: var(--primary-cyan);"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <h4>Total Identities</h4>
            <div class="stat-number">{{ $users->total() }}</div>
        </div>
    </div>
    <div class="stat-card-premium" style="--accent-glow: #ffd700;">
        <div class="stat-icon-box" style="color: #ffd700;"><i class="fas fa-coins"></i></div>
        <div class="stat-info">
            <h4>System Credits</h4>
            <div class="stat-number">{{ number_format(\App\Models\Wallet::sum('balance_credits'), 0) }}</div>
        </div>
    </div>
    <div class="stat-card-premium" style="--accent-glow: var(--neon-purple);">
        <div class="stat-icon-box" style="color: var(--neon-purple);"><i class="fas fa-bolt"></i></div>
        <div class="stat-info">
            <h4>Total Invocations</h4>
            <div class="stat-number">{{ number_format(\App\Models\AiUsage::count(), 0) }}</div>
        </div>
    </div>
    <div class="stat-card-premium" style="--accent-glow: #10b981;">
        <div class="stat-icon-box" style="color: #10b981;"><i class="fas fa-user-check"></i></div>
        <div class="stat-info">
            <h4>Admin Verified</h4>
            <div class="stat-number">{{ \App\Models\User::where('is_admin', true)->count() }}</div>
        </div>
    </div>
</div>

@php
    $isVerificationEnabled = (bool) \App\Models\Setting::get('global_email_verification', true);
@endphp

<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; gap: 2rem; flex-wrap: wrap;">
    <!-- Security Matrix Quick-Toggle -->
    <div style="background: var(--horizon-card); border: 1px solid {{ $isVerificationEnabled ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)' }}; padding: 1.25rem; border-radius: 20px; display: flex; align-items: center; gap: 1.5rem; transition: all 0.3s ease; flex: 1; min-width: 300px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: {{ $isVerificationEnabled ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; display: flex; align-items: center; justify-content: center; color: {{ $isVerificationEnabled ? '#10b981' : '#ef4444' }}; font-size: 1.25rem;">
            <i class="fas {{ $isVerificationEnabled ? 'fa-shield-check' : 'fa-shield-exclamation' }}"></i>
        </div>
        <div style="flex: 1;">
            <div style="font-weight: 800; color: var(--text-main); font-size: 0.9rem; letter-spacing: 0.5px;">SYSTEM-WIDE VERIFICATION</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.1rem;">
                Status: <strong style="color: {{ $isVerificationEnabled ? '#10b981' : '#ef4444' }}; text-transform: uppercase;">{{ $isVerificationEnabled ? 'Active' : 'Bypassed' }}</strong>
            </div>
        </div>
        <form action="{{ route('admin.users.toggle-verification') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="vn-btn {{ $isVerificationEnabled ? 'vn-btn-primary' : 'vn-btn-outline' }}" style="padding: 0.6rem 1.25rem; font-size: 0.8rem; border-radius: 12px; {{ !$isVerificationEnabled ? 'border-color: #ef4444; color: #ef4444;' : '' }}">
                <i class="fas {{ $isVerificationEnabled ? 'fa-toggle-on' : 'fa-toggle-off' }} mr-2"></i>
                {{ $isVerificationEnabled ? 'Disable Verification' : 'Enable Verification' }}
            </button>
        </form>
    </div>

    <!-- Search Box Premium -->
    <div class="search-box-premium" style="flex: 1; min-width: 300px; margin-bottom: 0;">
        <i class="fas fa-search" style="color: var(--primary-cyan);"></i>
        <input type="text" id="userFilterInput" placeholder="Hunt by name, email, or identity token..." class="search-input">
        <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Press / to focus</div>
    </div>
</div>

<div class="user-table-header">
    <div>Identity & Registry</div>
    <div>Intelligence Assets</div>
    <div>Economy (CRS)</div>
    <div>Activity Metrics</div>
    <div style="text-align: right;">Authorization Controls</div>
</div>



@foreach($users as $user)
    <div class="user-row">
        <!-- Identity -->
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div class="avatar-wrap">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
                    {{ $user->name }}
                    @if($user->isAdmin())
                        <i class="fas fa-shield-check" style="color: var(--primary-admin); font-size: 0.7rem;" title="System Administrator"></i>
                    @endif
                </div>
                <div style="color: var(--text-muted); font-size: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="fas fa-envelope" style="font-size: 0.65rem;"></i> {{ $user->email }}
                </div>
                <div style="color: var(--text-muted); font-size: 0.65rem; margin-top: 0.1rem;">
                    Joined {{ $user->created_at->format('M d, Y') }}
                </div>
            </div>
        </div>

        <!-- Intelligence Assets (Tools) -->
        <div>
            <div style="display: flex; flex-wrap: wrap; gap: 0.25rem; max-width: 200px;">
                @php $accessibleTools = $user->getAccessibleTools(); @endphp
                @if(count($accessibleTools) > 0)
                    @foreach(array_slice($accessibleTools, 0, 3) as $tool)
                        <span style="font-size: 0.6rem; background: rgba(14, 165, 233, 0.1); color: var(--primary-cyan); padding: 0.2rem 0.4rem; border-radius: 4px; border: 1px solid rgba(14, 165, 233, 0.2);">
                            {{ $tool['name'] }}
                        </span>
                    @endforeach
                    @if(count($accessibleTools) > 3)
                        <span style="font-size: 0.6rem; color: var(--text-muted);">+{{ count($accessibleTools) - 3 }} more</span>
                    @endif
                @else
                    <span style="font-size: 0.6rem; color: var(--text-muted);">No Tools Owned</span>
                @endif
            </div>
        </div>

        <!-- Economy -->
        <div>
            <div style="font-size: 1.1rem; font-weight: 800; color: var(--primary-admin); font-family: 'Space+Grotesk', sans-serif;">
                {{ number_format($user->wallet?->balance_credits ?? 0, 2) }}
                <span style="font-size: 0.6rem; color: var(--text-muted); font-weight: 600;">CRS</span>
            </div>
        </div>

        <!-- Activity -->
        <div style="display: flex; flex-direction: column; gap: 0.25rem;">
            <div style="font-size: 0.85rem; color: var(--text-main); font-weight: 700; display: flex; align-items: center; gap: 0.4rem;">
                <i class="fas fa-microchip" style="font-size: 0.75rem; color: var(--neon-purple);"></i>
                {{ $user->aiUsages()->count() }} AI Operations
            </div>
            <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                Total Tool Usage
            </div>
        </div>

        <!-- Controls -->
        <div style="display: flex; justify-content: flex-end; gap: 0.4rem;">
            <button class="action-pill" onclick="openCreditsModal({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->wallet->balance_credits ?? 0 }})" title="Refill Wallet">
                <i class="fas fa-coins"></i>
            </button>
            <button class="action-pill" onclick="openToolsModal({{ $user->id }}, '{{ addslashes($user->name) }}', {{ json_encode($user->ownedTools->pluck('tool_slug')->toArray()) }})" title="Toggle Privileges">
                <i class="fas fa-toolbox"></i>
            </button>
            <button class="action-pill" onclick="openPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}')" title="Reset Credentials">
                <i class="fas fa-key"></i>
            </button>
            
            <form action="{{ route('admin.users.impersonate', $user->id) }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="action-pill" style="color: var(--primary-cyan);" title="Direct Access (Impersonate)">
                    <i class="fas fa-user-secret"></i>
                </button>
            </form>

            <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" onsubmit="return confirm('Wipe identity? Irreversible.')" style="margin:0;">
                @csrf @method('DELETE')
                <button class="action-pill" style="color: #ef4444;" title="Terminate Identity" {{ $user->id === auth()->id() ? 'disabled style="opacity:0.3;"' : '' }}>
                    <i class="fas fa-user-slash"></i>
                </button>
            </form>
        </div>
    </div>
@endforeach

<div style="margin-top: 2rem; display: flex; justify-content: center;">
    {{ $users->links() }}
</div>

<!-- Credits Management Modal -->
<div id="creditsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-coins" style="color: #ffd700;"></i> Modify Credit Balance</h3>
            <button onclick="closeModal('creditsModal')" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:1.2rem;"><i class="fas fa-times"></i></button>
        </div>
        <form id="creditsForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="target-identity-badge">
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0; text-transform: uppercase; letter-spacing: 1px;">Target Identity</p>
                    <p id="creditsUserName" style="color: var(--text-main); font-weight: 800; font-size: 1.1rem; margin-top: 5px; font-family: 'Space Grotesk', sans-serif;"></p>
                </div>
                
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:0.75rem; font-weight: 700;">Exact New Balance (CRS)</label>
                <input type="number" name="amount" id="creditsInput" step="0.01" min="0" class="modal-input" placeholder="e.g. 500.00" required>
            </div>
            
            <div class="modal-footer">
                <button type="submit" class="btn-save"><i class="fas fa-sync-alt"></i> Update Balance</button>
            </div>
        </form>
    </div>
</div>



<!-- Tools Management Modal -->
<div id="toolsModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 style="background: linear-gradient(135deg, var(--neon-purple), var(--primary-cyan)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><i class="fas fa-toolbox"></i> Precision Tool Access</h3>
            <button onclick="closeModal('toolsModal')" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:1.2rem;"><i class="fas fa-times"></i></button>
        </div>
        <form id="toolsForm" method="POST">
            @csrf
            <div class="modal-body" style="max-height: 50vh; overflow-y: auto; scrollbar-width: thin;">
                <div class="target-identity-badge" style="background: rgba(176, 38, 255, 0.05); border-color: rgba(176, 38, 255, 0.2);">
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0; text-transform: uppercase; letter-spacing: 1px;">Target Identity</p>
                    <p id="toolsUserName" style="color: var(--text-main); font-weight: 800; font-size: 1.1rem; margin-top: 5px; font-family: 'Space Grotesk', sans-serif;"></p>
                    <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.6rem;"><i class="fas fa-info-circle" style="color: var(--neon-purple);"></i> Overrides take precedence over system defaults.</p>
                </div>

                <div id="toolsList" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    {{-- Tools will be injected here via JS --}}
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn-save" style="background: linear-gradient(135deg, var(--neon-purple), #8b5cf6); box-shadow: 0 10px 25px rgba(139, 92, 246, 0.2);"><i class="fas fa-save"></i> Save Access Controls</button>
            </div>
        </form>
    </div>
</div>

<!-- Password Reset Modal -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-key" style="color: #ffaa00;"></i> Reset Password</h3>
            <button onclick="closeModal('passwordModal')" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:1.2rem;"><i class="fas fa-times"></i></button>
        </div>
        <form id="passwordForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="target-identity-badge" style="background: rgba(255, 170, 0, 0.05); border-color: rgba(255, 170, 0, 0.2);">
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0; text-transform: uppercase; letter-spacing: 1px;">Target Identity</p>
                    <p id="passwordUserName" style="color: var(--text-main); font-weight: 800; font-size: 1.1rem; margin-top: 5px; font-family: 'Space Grotesk', sans-serif;"></p>
                </div>

                <label style="display:block; font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:0.75rem; font-weight: 700;">New Credentials</label>
                <div style="position: relative;">
                    <input type="password" id="adminNewPassword" name="new_password" required minlength="8" class="modal-input" placeholder="Min 8 characters">
                    <i class="fas fa-eye" id="toggleAdminPassword" style="position: absolute; right: 1.25rem; top: 1.1rem; color: var(--text-muted); cursor: pointer;" onclick="togglePassword('adminNewPassword', 'toggleAdminPassword')"></i>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-save" style="background: linear-gradient(135deg, #ffaa00, #f59e0b); box-shadow: 0 10px 25px rgba(245, 158, 11, 0.2);"><i class="fas fa-shield-alt"></i> Update Password</button>
            </div>
        </form>
    </div>
</div>

<!-- Email Update Modal -->
<div id="emailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-envelope" style="color: #0ea5e9;"></i> Update Identity Email</h3>
            <button onclick="closeModal('emailModal')" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:1.2rem;"><i class="fas fa-times"></i></button>
        </div>
        <form id="emailForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="target-identity-badge">
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0; text-transform: uppercase; letter-spacing: 1px;">Target Identity</p>
                    <p id="emailUserName" style="color: var(--text-main); font-weight: 800; font-size: 1.1rem; margin-top: 5px; font-family: 'Space Grotesk', sans-serif;"></p>
                </div>

                <label style="display:block; font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:0.75rem; font-weight: 700;">New Primary Email</label>
                <input type="email" id="adminNewEmail" name="new_email" required class="modal-input" placeholder="new.email@example.com">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-save"><i class="fas fa-at"></i> Update Email Address</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Premium Search Filter Logic
    document.getElementById('userFilterInput').addEventListener('keyup', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('.user-row');
        
        rows.forEach(row => {
            const name = row.querySelector('div[style*="font-weight: 700"]').innerText.toLowerCase();
            const email = row.querySelector('div[style*="color: var(--text-muted)"]').innerText.toLowerCase();
            
            if (name.includes(query) || email.includes(query)) {
                row.style.display = 'grid';
                row.style.animation = 'fadeIn 0.3s ease';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Keyboard Shortcut (/)
    document.addEventListener('keydown', function(e) {
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            document.getElementById('userFilterInput').focus();
        }
    });

    function openCreditsModal(userId, userName, currentBalance) {
        const form = document.getElementById('creditsForm');
        form.action = `/horizon-admin/users/${userId}/update-balance`;
        document.getElementById('creditsUserName').innerText = userName;
        document.getElementById('creditsInput').value = currentBalance;
        document.getElementById('creditsModal').style.display = 'flex';
    }

    function openPasswordModal(userId, userName) {
        const form = document.getElementById('passwordForm');
        form.action = `/horizon-admin/users/${userId}/update-password`;
        document.getElementById('passwordUserName').innerText = userName;
        document.getElementById('passwordModal').style.display = 'flex';
    }

    function openEmailModal(userId, userName, currentEmail) {
        const form = document.getElementById('emailForm');
        form.action = `/horizon-admin/users/${userId}/update-email`;
        document.getElementById('emailUserName').innerText = userName;
        document.getElementById('adminNewEmail').value = currentEmail;
        document.getElementById('emailModal').style.display = 'flex';
    }



    @php
        $tools_discovery = collect(config('tools.all_tools', []))->map(fn($t) => [
            'name' => $t['name'] ?? 'Unknown Tool',
            'slug' => $t['slug'] ?? 'unknown',
            'icon' => $t['icon'] ?? 'fa-gear'
        ])->values();
    @endphp

    const ALL_SYSTEM_TOOLS = @json($tools_discovery);

    function openToolsModal(userId, userName, ownedTools) {
        const form = document.getElementById('toolsForm');
        form.action = `/horizon-admin/users/${userId}/update-tools`;
        document.getElementById('toolsUserName').innerText = userName;
        
        const list = document.getElementById('toolsList');
        list.innerHTML = '';
        
        const ownedSet = new Set(ownedTools || []);

        // Create Categorized Grouping
        const ownedHeader = document.createElement('div');
        ownedHeader.style = "grid-column: 1 / -1; font-size: 0.7rem; text-transform: uppercase; color: var(--primary-cyan); letter-spacing: 1.5px; margin: 1rem 0 0.5rem; font-weight: 800; display: flex; align-items: center; gap: 8px;";
        ownedHeader.innerHTML = '<i class="fas fa-check-circle"></i> Intelligence Assets (Owned)';
        
        const availableHeader = document.createElement('div');
        availableHeader.style = "grid-column: 1 / -1; font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1.5px; margin: 2rem 0 0.5rem; font-weight: 800; display: flex; align-items: center; gap: 8px;";
        availableHeader.innerHTML = '<i class="fas fa-shopping-cart"></i> Marketplace Availability';

        // Separate tools
        const ownedList = ALL_SYSTEM_TOOLS.filter(t => ownedSet.has(t.slug));
        const availableList = ALL_SYSTEM_TOOLS.filter(t => !ownedSet.has(t.slug));

        list.appendChild(ownedHeader);
        renderToolItems(ownedList, true, list);
        
        list.appendChild(availableHeader);
        renderToolItems(availableList, false, list);

        document.getElementById('toolsModal').style.display = 'flex';
    }

    function renderToolItems(tools, isOwned, parentElement) {
        if (tools.length === 0) {
            const empty = document.createElement('div');
            empty.style = "grid-column: 1 / -1; color: var(--text-muted); font-size: 0.8rem; padding: 1rem; text-align: center; background: rgba(0,0,0,0.1); border-radius: 12px; border: 1px dashed var(--horizon-border);";
            empty.innerText = "No tools in this category.";
            parentElement.appendChild(empty);
            return;
        }

        tools.forEach(tool => {
            const div = document.createElement('div');
            div.className = "tool-item-card";
            div.style = `background: var(--horizon-nav-hover); padding: 1rem; border-radius: 18px; border: 1px solid ${isOwned ? 'rgba(14, 165, 233, 0.2)' : 'var(--horizon-border)'}; display: flex; flex-direction: column; gap: 12px; transition: all 0.3s; position: relative; overflow: hidden;`;
            
            if (isOwned) {
                div.style.boxShadow = "inset 0 0 20px rgba(14, 165, 233, 0.03)";
            }

            div.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 10px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: ${isOwned ? 'var(--primary-cyan)' : 'var(--text-muted)'};">
                        <i class="fas ${tool.icon}"></i>
                    </div>
                    <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-main);">${tool.name}</div>
                </div>
                
                <input type="hidden" name="tools[${tool.slug}]" id="input-${tool.slug}" value="${isOwned ? '1' : ''}">
                
                <button type="button" onclick="toggleToolState('${tool.slug}')" id="btn-${tool.slug}" 
                    style="border: none; padding: 0.6rem; border-radius: 10px; font-size: 0.75rem; font-weight: 800; cursor: pointer; transition: all 0.2s; 
                    background: ${isOwned ? 'rgba(239, 68, 68, 0.1)' : 'rgba(16, 185, 129, 0.1)'}; 
                    color: ${isOwned ? '#ef4444' : '#10b981'};
                    border: 1px solid ${isOwned ? 'rgba(239, 68, 68, 0.2)' : 'rgba(16, 185, 129, 0.2)'};">
                    <i class="fas ${isOwned ? 'fa-lock' : 'fa-unlock'}"></i> ${isOwned ? 'Revoke Access' : 'Quick Unlock'}
                </button>
            `;
            parentElement.appendChild(div);
        });
    }

    function toggleToolState(slug) {
        const input = document.getElementById(`input-${slug}`);
        const btn = document.getElementById(`btn-${slug}`);
        const card = btn.parentElement;
        
        if (input.value === "1") {
            // Decalring for removal
            input.value = "0";
            btn.innerHTML = '<i class="fas fa-undo"></i> Keep Access';
            btn.style.background = 'rgba(255,255,255,0.05)';
            btn.style.color = 'var(--text-muted)';
            btn.style.borderColor = 'var(--horizon-border)';
            card.style.opacity = '0.5';
            card.style.filter = 'grayscale(1)';
        } else if (input.value === "0") {
            // Undo removal
            input.value = "1";
            btn.innerHTML = '<i class="fas fa-lock"></i> Revoke Access';
            btn.style.background = 'rgba(239, 68, 68, 0.1)';
            btn.style.color = '#ef4444';
            btn.style.borderColor = 'rgba(239, 68, 68, 0.2)';
            card.style.opacity = '1';
            card.style.filter = 'none';
        } else {
            // Activating for the first time
            input.value = "1";
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Unlocking...';
            btn.style.background = 'var(--primary-cyan)';
            btn.style.color = '#000';
            btn.style.borderColor = 'var(--primary-cyan)';
            card.style.borderHeader = '1px solid var(--primary-cyan)';
            card.style.boxShadow = '0 0 20px rgba(14, 165, 233, 0.2)';
        }
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = 'none';
        }
    }
</script>
@endsection
