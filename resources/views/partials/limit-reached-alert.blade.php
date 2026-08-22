@php
    $toolNames = [
        'ai-keyword-radar' => 'AI Keyword Radar',
        'article-writer' => 'Article Writer',
        'pro-article-writer' => 'Pro Article Writer',
        'discover-headlines' => 'Discover Headlines',
        'drama-trends' => 'Drama Trends',
        'global-news-monitor' => 'Global News Monitor',
        'seo-analyzer' => 'SEO Analyzer',
        'trending-search-monitor' => 'Trending Search Monitor',
    ];
    $name = is_array($slug) ? ($slug['name'] ?? 'Unknown Tool') : ($toolNames[$slug] ?? $slug);
    $safeName = e($name);
    $user = auth()->user();

    $hasBalance = $user->wallet && $user->wallet->balance_credits >= 1.0;
    
    // Ensure slug is a string if it's an array for model checks
    $slugStr = is_array($slug) ? ($slug['slug'] ?? '') : $slug;
    
    if (!$user->ownsTool($slugStr)) {
        $title = "Tool Access Locked";
        $desc = "You haven't subscribed to <span style='color: #ef4444; font-weight: 700;'>\"{$safeName}\"</span> yet. This is a premium tool that requires a monthly subscription.";
        $badge = "LOCKED";
    } else {
        $title = "Action Credits Exhausted";
        $desc = "Your current credit balance is too low to perform this action. Please top up your wallet to continue using <span style='color: #ef4444; font-weight: 700;'>\"{$safeName}\"</span>.";
        $badge = "INSUFFICIENT FUNDS";
    }
@endphp

<style>
    @keyframes shimmer-fast { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    .limit-reached-alert::after {
        content: "";
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 75, 75, 0.05), transparent);
        animation: shimmer-fast 3s infinite linear;
    }
</style>

<div class="limit-reached-alert glass-panel" style="
    max-width: 440px; 
    margin: 2rem auto; 
    padding: 2.5rem 1.5rem; 
    text-align: center; 
    border-radius: 20px; 
    border: 1px solid var(--glass-border); 
    background: var(--card-bg); 
    backdrop-filter: blur(20px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.5), inset 0 0 20px rgba(255,255,255,0.02);
    position: relative;
    overflow: hidden;
">
    {{-- Subtle Red Glow --}}
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: linear-gradient(90deg, transparent, rgba(239, 68, 68, 0.4), transparent);"></div>
    
    <div class="relative z-10">
        {{-- Elegant Lock Icon --}}
        <div style="
            width: 50px; height: 50px; 
            margin: 0 auto 1.5rem; 
            background: rgba(239, 68, 68, 0.1); 
            border: 1px solid rgba(239, 68, 68, 0.2); 
            border-radius: 12px; 
            display: flex; align-items: center; justify-content: center;
            color: #ef4444;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.1);
        ">
            <i class="fas fa-lock" style="font-size: 1.25rem;"></i>
        </div>
        
        <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.75rem; letter-spacing: -0.02em;">
            {{ $title }}
        </h2>
        
        <p style="font-size: 0.95rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 2rem; padding: 0 0.5rem;">
            {!! $desc !!}
        </p>
        
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <a href="/pricing" class="vn-btn-primary" style="
                display: flex; align-items: center; justify-content: center; gap: 0.5rem;
                padding: 1rem; border-radius: 12px; font-weight: 700; text-decoration: none;
                background: linear-gradient(135deg, #00A8E6 0%, #0284c7 100%);
                color: #fff; box-shadow: 0 10px 20px rgba(0, 168, 230, 0.25);
                transition: all 0.3s ease;
            ">
                <i class="fas fa-coins" style="font-size: 0.8rem;"></i>
                Purchase Credits Now
            </a>
            
            <a href="/dashboard" style="
                display: block; padding: 0.9rem; border-radius: 12px; 
                color: var(--text-muted); text-decoration: none; font-size: 0.9rem;
                font-weight: 600; background: rgba(255,255,255,0.03);
                border: 1px solid var(--glass-border); transition: all 0.3s ease;
            ">
                Back to Dashboard
            </a>
        </div>
        
        <div style="margin-top: 1.5rem; opacity: 0.4; font-size: 0.7rem; font-weight: 800; color: #ef4444; letter-spacing: 2px; text-transform: uppercase;">
            {{ $badge }}
        </div>
    </div>
</div>
