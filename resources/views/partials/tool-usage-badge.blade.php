@php
    $user = auth()->user();
    
    // Robust slug handling to prevent 'array given to echo' lint errors
    $actualSlug = is_string($slug) ? $slug : (is_array($slug) && isset($slug['slug']) && is_string($slug['slug']) ? $slug['slug'] : 'unknown');
    
    $toolConfig = collect(config('tools.all_tools', []))->where('slug', $actualSlug)->first();
    $isOwned = $user->ownsTool($actualSlug);
    $creditCost = $user->getToolCreditCost($actualSlug);
    $balance = $user->wallet ? $user->wallet->balance_credits : 0;
    
    $statusLabel = $isOwned ? 'Owned & Ready' : 'Locked';
    $statusColor = $isOwned ? 'var(--accent-success)' : 'var(--text-muted)';
    
    // Determine if we show a warning if balance is low
    $hasEnough = $balance >= $creditCost;
    $balanceColor = $hasEnough ? 'var(--primary-cyan)' : '#ff4b4b';
@endphp

@if(!$isOwned)
    @include('partials.limit-reached-alert', ['slug' => $actualSlug])
@endif

<style>
.tool-usage-badge {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1rem 1.5rem;
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: 16px;
    margin-bottom: 2rem;
    backdrop-filter: blur(15px);
}
@media (max-width: 640px) {
    .tool-usage-badge {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
        padding: 1.5rem 1rem;
    }
    .tool-usage-badge > div {
        border-left: none !important;
        padding-left: 0 !important;
        text-align: center !important;
    }
    .badge-divider {
        border-top: 1px solid var(--glass-border);
        padding-top: 1rem;
        width: 100%;
    }
}
</style>
<div class="tool-usage-badge">
    {{-- Status Icon --}}
    <div style="flex-shrink: 0; width: 48px; height: 48px; border-radius: 12px; background: {{ $isOwned ? 'rgba(0, 255, 170, 0.1)' : 'var(--feature-item-bg)' }}; display: flex; align-items: center; justify-content: center; color: {{ $statusColor }}; border: 1px solid {{ $isOwned ? 'rgba(0, 255, 170, 0.2)' : 'var(--glass-border)' }};">
        <i class="fas {{ $isOwned ? 'fa-check-circle' : 'fa-lock' }}" style="font-size: 1.4rem;"></i>
    </div>
    
    {{-- Ownership Info --}}
    <div style="flex-grow: 1;">
        <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px;">Marketplace Status</div>
        <div style="font-size: 1.1rem; font-weight: 800; color: {{ $statusColor }}; font-family: var(--font-heading);">
            {{ $statusLabel }}
        </div>
    </div>

    {{-- Credit Info --}}
    <div class="badge-divider" style="text-align: right; border-left: 1px solid var(--glass-border); padding-left: 1.5rem;">
        <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px;">Action Cost</div>
        <div style="font-size: 1.1rem; font-weight: 800; color: var(--primary-cyan); font-family: var(--font-heading);">
            {{ $creditCost }} <span style="font-size: 0.75rem; font-weight: 400; opacity: 0.7;">CRS</span>
        </div>
    </div>

    {{-- Balance Info --}}
    <div class="badge-divider" style="text-align: right; border-left: 1px solid var(--glass-border); padding-left: 1.5rem;">
        <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px;">Your Balance</div>
        <div style="font-size: 1.1rem; font-weight: 800; color: {{ $balanceColor }}; font-family: var(--font-heading);">
            <span class="js-credit-balance"
                  data-credit-value="{{ (float) $balance }}"
                  data-decimals="1">{{ number_format($balance, 1) }}</span>
            <span style="font-size: 0.75rem; font-weight: 400; opacity: 0.7;">CRS</span>
        </div>
    </div>
</div>
