<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horizon Super Admin | VidaNexus</title>
    @include('partials.favicons')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style.v2.css') }}?v={{ config('vidanexus.style_css_version') }}">
    @include('partials.theme-init')
    <style>
        :root {
            --horizon-bg: #021B3A;
            --horizon-card: rgba(20, 20, 30, 0.4);
            --horizon-border: rgba(255, 255, 255, 0.1);
            --horizon-nav-hover: rgba(255, 255, 255, 0.05);
            --horizon-icon-bg: rgba(255, 255, 255, 0.05);
            --primary-admin: var(--primary-cyan);
            --secondary-admin: var(--accent);
            --horizon-primary-bg: rgba(0, 168, 230, 0.1);
            --horizon-secondary-bg: rgba(191, 0, 255, 0.1);
            --horizon-success: #00A58B;
            --horizon-success-bg: rgba(16, 185, 129, 0.1);
            
            /* Premium Input System */
            --vn-input-bg: #0a0f19;
            --vn-input-border: rgba(255, 255, 255, 0.1);
            --vn-input-focus: var(--primary-admin);
        }

        [data-theme="light"] {
            --horizon-bg: #f8fafc;
            --horizon-card: #ffffff;
            --horizon-border: rgba(15, 23, 42, 0.1);
            --horizon-nav-hover: rgba(15, 23, 42, 0.05);
            --horizon-icon-bg: rgba(15, 23, 42, 0.05);
            --text-main: #020617;
            --text-muted: #334155;
            --horizon-primary-bg: rgba(8, 145, 178, 0.1);
            --horizon-secondary-bg: rgba(147, 51, 234, 0.1);
            --horizon-success: #059669;
            --horizon-success-bg: rgba(5, 150, 105, 0.1);

            /* Premium Input System (Light) */
            --vn-input-bg: #ffffff;
            --vn-input-border: rgba(15, 23, 42, 0.15);
            --vn-input-focus: var(--primary-admin);
        }

        /* Premium Toggle Switch (.vn-switch) */
        .vn-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .vn-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .vn-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--horizon-icon-bg);
            transition: .4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--horizon-border);
            border-radius: 34px;
        }

        .vn-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: #fff;
            transition: .4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 2;
        }

        .vn-switch input:checked + .vn-slider {
            background-color: var(--primary-admin);
            border-color: var(--primary-admin);
        }

        .vn-switch input:checked + .vn-slider:before {
            transform: translateX(24px);
        }

        /* Hover effect */
        .vn-slider:hover {
            border-color: var(--primary-admin);
        }

        body {
            background-color: var(--horizon-bg);
            color: var(--text-main);
            font-family: 'Poppins', sans-serif;
            margin: 0;
            display: flex;
            min-height: 100vh;
            transition: background-color 0.3s ease, color 0.3s ease;
            overflow-x: hidden;
        }

        .horizon-sidebar {
            width: 280px;
            background: rgba(10, 10, 15, 0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--horizon-border);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 100;
            transition: background 0.3s ease;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--horizon-border) transparent;
        }

        .horizon-sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .sidebar-close-btn,
        .sidebar-toggle-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 1px solid var(--horizon-border);
            background: var(--horizon-nav-hover);
            color: var(--text-main);
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .sidebar-close-btn:hover,
        .sidebar-toggle-btn:hover {
            border-color: var(--primary-admin);
            color: var(--primary-admin);
        }

        .horizon-sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .horizon-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .horizon-sidebar::-webkit-scrollbar-thumb {
            background: var(--horizon-border);
            border-radius: 10px;
        }

        [data-theme="light"] .horizon-sidebar {
            background: rgba(255, 255, 255, 0.8);
        }

        .horizon-logo {
            font-family: 'Space+Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-admin);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 3rem;
            text-decoration: none;
        }

        .nav-group {
            margin-bottom: 2rem;
        }

        .nav-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-muted);
            margin-bottom: 1rem;
            padding-left: 1rem;
        }

        .nav-tools-group {
            margin-bottom: 0.4rem;
        }

        .nav-tools-toggle {
            width: 100%;
            margin-top: 0.4rem;
            padding: 0.65rem 1rem;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--primary-admin);
            font-weight: 800;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 12px;
            border: 1px solid transparent;
            background: transparent;
            transition: all 0.2s ease;
        }

        .nav-tools-toggle:hover {
            background: var(--horizon-nav-hover);
            border-color: var(--horizon-border);
        }

        .nav-tools-toggle.active {
            background: var(--horizon-nav-hover);
            color: var(--text-main);
            border-color: var(--horizon-border);
        }

        .nav-tools-toggle .toggle-icon {
            transition: transform 0.3s ease;
        }

        .nav-tools-group:not(.is-open) .toggle-icon {
            transform: rotate(-90deg);
        }

        .nav-tools-content {
            margin-top: 0.25rem;
            display: none;
        }

        .nav-tools-group.is-open .nav-tools-content {
            display: block;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.8rem 1.2rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--horizon-nav-hover);
            color: var(--text-main);
        }

        .nav-link.active {
            border-left: 3px solid var(--primary-admin);
            color: var(--primary-admin);
            background: linear-gradient(90deg, rgba(0, 168, 230, 0.1), transparent);
        }

        .horizon-main {
            flex: 1;
            margin-left: 280px;
            padding: 2.5rem;
            min-width: 0;
        }

        .horizon-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            position: sticky;
            top: 1rem;
            z-index: 90;
            background: color-mix(in srgb, var(--horizon-bg) 88%, transparent);
            backdrop-filter: blur(14px);
            border: 1px solid var(--horizon-border);
            border-radius: 18px;
            padding: 1rem 1.2rem;
        }

        .horizon-header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 0;
        }

        .horizon-header-meta p {
            color: var(--text-muted);
            margin: 0.35rem 0 0;
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.5);
            z-index: 95;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        body.sidebar-open .sidebar-overlay {
            opacity: 1;
            pointer-events: auto;
        }

        .admin-badge {
            background: linear-gradient(135deg, var(--secondary-admin), var(--primary-admin));
            padding: 6px 16px;
            border-radius: 64px;
            font-size: 0.75rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #000;
            box-shadow: 0 0 20px rgba(0, 168, 230, 0.2);
        }

        .logo-text-vida {
            color: #fff;
        }

        [data-theme="light"] .logo-text-vida {
            color: var(--text-main);
        }

        .card-admin {
            background: var(--horizon-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--horizon-border);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        [data-theme="light"] .card-admin {
            box-shadow: 0 10px 40px rgba(15, 23, 42, 0.05);
        }

        .btn-save {
            background: var(--primary-admin);
            color: #000;
            padding: 0.8rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 168, 230, 0.4);
        }

        pre, code, textarea.mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Brief green pulse after an AJAX form save confirms inputs persisted */
        .admin-ajax-saved {
            animation: adminAjaxSavedFlash 1.1s ease;
        }

        @keyframes adminAjaxSavedFlash {
            0%   { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.0); border-color: var(--horizon-border); }
            30%  { box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.35); border-color: var(--horizon-success); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        @media (max-width: 1200px) {
            .horizon-main {
                padding: 1.5rem;
            }
        }

        @media (max-width: 992px) {
            .sidebar-toggle-btn,
            .sidebar-close-btn {
                display: inline-flex;
            }

            .horizon-sidebar {
                transform: translateX(-100%);
                transition: transform 0.25s ease, background 0.3s ease;
            }

            body.sidebar-open .horizon-sidebar {
                transform: translateX(0);
            }

            .horizon-main {
                margin-left: 0;
            }

            .horizon-header {
                top: 0.75rem;
                padding: 0.9rem 1rem;
            }
        }

        @media (max-width: 768px) {
            .horizon-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .horizon-header > div:last-child {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar(false)"></div>
    <aside class="horizon-sidebar">
        <div class="horizon-sidebar-header">
            <a href="{{ route('admin.horizon.index') }}" class="horizon-logo" style="text-decoration: none; margin-bottom: 0; display: flex; align-items: center;">
                <img src="{{ asset('assets/brand-logo.png?v=2026_2') }}" alt="VidaNexus" style="height: 46px; width: auto; object-fit: contain;">
            </a>
            <button type="button" class="sidebar-close-btn" onclick="toggleSidebar(false)" aria-label="Close menu">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="nav-group">
            <div class="nav-label">Main System</div>
            <a href="{{ route('admin.horizon.index') }}" class="nav-link {{ request()->routeIs('admin.horizon.index') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                <i class="fas fa-users-cog"></i> Global Users
            </a>
            @php
                $settingsNavItems = [
                    'availability' => ['label' => 'Tool Availability', 'icon' => 'fa-toggle-on'],
                    'welcome' => ['label' => 'Marketplace', 'icon' => 'fa-store'],
                    'credit-system' => ['label' => 'Credit System', 'icon' => 'fa-coins'],
                    'countries' => ['label' => 'Country Registry', 'icon' => 'fa-globe'],
                    'trial' => ['label' => 'Trial Package', 'icon' => 'fa-gift'],
                    'coupons' => ['label' => 'Coupons', 'icon' => 'fa-tag'],
                    'packages' => ['label' => 'Credit Packages', 'icon' => 'fa-box'],
                    'smtp' => ['label' => 'Email Setup (SMTP)', 'icon' => 'fa-envelope-open-text'],
                    'scripts' => ['label' => 'Global Scripts', 'icon' => 'fa-code'],
                    'infrastructure' => ['label' => 'Infrastructure', 'icon' => 'fa-server'],
                    'ledger' => ['label' => 'Transaction Ledger', 'icon' => 'fa-coins'],
                    'command' => ['label' => 'Command Center', 'icon' => 'fa-terminal'],
                    'markdown' => ['label' => 'AI Crawler SEO', 'icon' => 'fa-robot'],
                ];
                $activeSettingsTab = request()->route('tab') ?: 'availability';
                $inSettingsRoute = request()->routeIs('admin.horizon.settings.index') || request()->routeIs('admin.horizon.settings.tab');
            @endphp
            <div class="nav-tools-group {{ $inSettingsRoute ? 'is-open' : '' }}">
                <button type="button" class="nav-tools-toggle {{ $inSettingsRoute ? 'active' : '' }}" data-tools-toggle>
                    <span><i class="fas fa-sliders-h" style="margin-right: 0.5rem;"></i>System Settings</span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </button>
                <div class="nav-tools-content">
                    @foreach($settingsNavItems as $tabKey => $tabMeta)
                        <a href="{{ route('admin.horizon.settings.tab', ['tab' => $tabKey]) }}" class="nav-link {{ $inSettingsRoute && $activeSettingsTab === $tabKey ? 'active' : '' }}" style="padding-left: 2rem;">
                            <i class="fas {{ $tabMeta['icon'] }}" style="font-size: 0.7rem;"></i> {{ $tabMeta['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('admin.horizon.ledger.index') }}" class="nav-link {{ request()->routeIs('admin.horizon.ledger.index') ? 'active' : '' }}">
                <i class="fas fa-book"></i> Financial Ledger
            </a>
            <a href="{{ route('admin.horizon.api-keys.index') }}" class="nav-link {{ request()->routeIs('admin.horizon.api-keys.index') ? 'active' : '' }}">
                <i class="fas fa-network-wired"></i> API & Connectivity
            </a>
            <a href="{{ route('admin.horizon.roadmap') }}" class="nav-link {{ request()->routeIs('admin.horizon.roadmap') ? 'active' : '' }}">
                <i class="fas fa-rocket" style="color: #00A8E6;"></i> Phase: v2.0 To do
            </a>
        </div>

        <div class="nav-group">
            <div class="nav-label">AI Tool Control</div>
            @php
                $tools = config('tools.all_tools', []);
                $groupedTools = collect($tools)->groupBy(function($t) {
                    return $t['category'] ?? 'uncategorized';
                });
            @endphp
            @foreach($groupedTools as $category => $categoryTools)
                @php
                    $isActiveCategory = collect($categoryTools)->contains(function($t) {
                        return request()->segment(3) == $t['slug'];
                    });
                @endphp
                <div class="nav-tools-group {{ $isActiveCategory ? 'is-open' : '' }}">
                    <button type="button" class="nav-tools-toggle" data-tools-toggle>
                        <span>{{ str_replace('_', ' ', $category) }}</span>
                        <i class="fas fa-chevron-down toggle-icon"></i>
                    </button>
                    <div class="nav-tools-content">
                        @foreach($categoryTools as $t)
                            <a href="{{ route('admin.horizon.show', $t['slug']) }}" class="nav-link {{ request()->segment(3) == $t['slug'] ? 'active' : '' }}" style="padding-left: 2rem;">
                                <i class="fas fa-circle-notch" style="font-size: 0.6rem; color: {{ $t['color'] ?? 'inherit' }};"></i> {{ $t['name'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: auto;">
            <a href="/dashboard" class="nav-link">
                <i class="fas fa-arrow-left"></i> Exit to Site
            </a>
        </div>
    </aside>

    <main class="horizon-main">
        <div class="horizon-header">
            <div class="horizon-header-left">
                <button type="button" class="sidebar-toggle-btn" onclick="toggleSidebar(true)" aria-label="Open menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="horizon-header-meta">
                    <h1 style="font-family: 'Space+Grotesk', sans-serif; font-size: 1.8rem; margin: 0; color: var(--text-main);">@yield('title', 'Horizon Dashboard')</h1>
                    <p>Super Admin Management Interface</p>
                </div>
            </div>
            <div class="flex items-center gap-6" style="display: flex; align-items: center; gap: 1.5rem;">
                <!-- Theme Toggle -->
                <label class="theme-switch-dribbble" title="Toggle Dark/Light Mode">
                    <input type="checkbox" onchange="handleThemeChange(event)" id="theme-toggle-checkbox">
                    <div class="dribbble-slider">
                        <div class="star star-1"></div>
                        <div class="star star-2"></div>
                        <div class="star star-3"></div>
                        <div class="cloud cloud-1"></div>
                        <div class="cloud cloud-2"></div>
                        <div class="crater crater-1"></div>
                        <div class="crater crater-2"></div>
                        <div class="crater crater-3"></div>
                    </div>
                </label>
                
                <div style="display: flex; align-items: center; gap: 0.75rem; background: var(--horizon-nav-hover); padding: 0.35rem 1.25rem 0.35rem 0.35rem; border-radius: 64px; border: 1px solid var(--horizon-border);">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--secondary-admin), var(--primary-admin)); display: flex; align-items: center; justify-content: center; color: #000; font-weight: 800; font-size: 0.9rem; flex-shrink: 0;">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div style="display: flex; flex-direction: column; line-height: 1.2; padding-right: 0.5rem;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-main);">{{ auth()->user()->name ?? 'Administrator' }}</span>
                        <span style="font-size: 0.65rem; color: var(--primary-admin); text-transform: uppercase; letter-spacing: 1px; font-weight: 800;">Super Admin</span>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Ensure the toggle matches the current theme on load
            document.addEventListener('DOMContentLoaded', () => {
                const isDark = (document.documentElement.getAttribute('data-theme') || 'dark') === 'dark';
                const toggle = document.getElementById('theme-toggle-checkbox');
                if (toggle) toggle.checked = isDark;
            });
        </script>

        @if(session('success'))
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #00A58B; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2.5rem; display: flex; align-items: center; gap: 1rem;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2.5rem; display: flex; align-items: center; gap: 1rem;">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <meta name="credits-balance-url" content="{{ route('dashboard.credits.balance') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('script.js') }}"></script>
    <script src="{{ asset('credits-live.js') }}?v={{ config('vidanexus.style_css_version', '2') }}" defer></script>
    <script src="{{ asset('admin-ajax-save.js') }}?v={{ config('vidanexus.style_css_version', '2') }}.2" defer></script>
    <script>
        function toggleSidebar(open) {
            document.body.classList.toggle('sidebar-open', open);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const isDesktop = window.matchMedia('(min-width: 993px)');
            const closeSidebarOnDesktop = () => {
                if (isDesktop.matches) {
                    document.body.classList.remove('sidebar-open');
                }
            };

            closeSidebarOnDesktop();
            isDesktop.addEventListener('change', closeSidebarOnDesktop);

            document.querySelectorAll('[data-tools-toggle]').forEach((trigger) => {
                trigger.addEventListener('click', () => {
                    trigger.closest('.nav-tools-group')?.classList.toggle('is-open');
                });
            });

            document.querySelectorAll('.horizon-sidebar .nav-link').forEach((link) => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 992) {
                        document.body.classList.remove('sidebar-open');
                    }
                });
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
