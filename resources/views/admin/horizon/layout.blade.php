<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horizon Super Admin | VidaNexus</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@700&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style.v2.css?v=30') }}">
    <script>(function(){const t=localStorage.getItem("theme")||"dark";document.documentElement.setAttribute("data-theme",t);})();</script>
    <style>
        :root {
            --horizon-bg: #050505;
            --horizon-card: rgba(20, 20, 30, 0.4);
            --horizon-border: rgba(255, 255, 255, 0.1);
            --horizon-nav-hover: rgba(255, 255, 255, 0.05);
            --horizon-icon-bg: rgba(255, 255, 255, 0.05);
            --primary-admin: var(--primary-cyan);
            --secondary-admin: var(--neon-purple);
            --horizon-primary-bg: rgba(14, 165, 233, 0.1);
            --horizon-secondary-bg: rgba(191, 0, 255, 0.1);
            --horizon-success: #10b981;
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
            font-family: 'Inter', sans-serif;
            margin: 0;
            display: flex;
            min-height: 100vh;
            transition: background-color 0.3s ease, color 0.3s ease;
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
            background: linear-gradient(90deg, rgba(14, 165, 233, 0.1), transparent);
        }

        .horizon-main {
            flex: 1;
            margin-left: 280px;
            padding: 2.5rem;
        }

        .horizon-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
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
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.2);
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
            box-shadow: 0 5px 15px rgba(14, 165, 233, 0.4);
        }

        pre, code, textarea.mono {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
    @yield('styles')
</head>
<body>
    <aside class="horizon-sidebar">
        <a href="{{ route('admin.horizon.index') }}" class="horizon-logo" style="text-decoration: none;">
            <img src="{{ asset('assets/logo.png') }}" alt="VidaNexus" style="height: 32px; width: auto;">
            <div style="display: flex; flex-direction: column; line-height: 1.1; margin-left: 0.25rem;">
                <span class="logo-text-vida" style="font-family: 'Space Grotesk', sans-serif; font-weight: 800; font-size: 1.1rem; letter-spacing: 1px;">VIDA</span>
                <span style="font-family: 'Space Grotesk', sans-serif; font-weight: 800; color: var(--primary-admin); font-size: 0.7rem; letter-spacing: 3px; margin-top: -2px;">NEXUS</span>
            </div>
        </a>

        <div class="nav-group">
            <div class="nav-label">Main System</div>
            <a href="{{ route('admin.horizon.index') }}" class="nav-link {{ request()->routeIs('admin.horizon.index') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                <i class="fas fa-users-cog"></i> Global Users
            </a>
            <a href="{{ route('admin.horizon.settings.index') }}" class="nav-link {{ request()->routeIs('admin.horizon.settings.index') ? 'active' : '' }}">
                <i class="fas fa-sliders-h"></i> System Settings
            </a>
            <a href="{{ route('admin.horizon.api-keys.index') }}" class="nav-link {{ request()->routeIs('admin.horizon.api-keys.index') ? 'active' : '' }}">
                <i class="fas fa-network-wired"></i> API & Connectivity
            </a>
            <a href="{{ route('admin.horizon.roadmap') }}" class="nav-link {{ request()->routeIs('admin.horizon.roadmap') ? 'active' : '' }}">
                <i class="fas fa-rocket" style="color: #0ea5e9;"></i> Phase: v2.0 To do
            </a>
        </div>

        <div class="nav-group">
            <div class="nav-label">AI Tool Control</div>
            @php
                $tools = config('tools.all_tools', []);
            @endphp
            @foreach($tools as $t)
                <a href="{{ route('admin.horizon.show', $t['slug']) }}" class="nav-link {{ request()->segment(3) == $t['slug'] ? 'active' : '' }}">
                    <i class="fas fa-circle-notch" style="font-size: 0.6rem; color: {{ $t['color'] ?? 'inherit' }};"></i> {{ $t['name'] }}
                </a>
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
            <div>
                <h1 style="font-family: 'Space+Grotesk', sans-serif; font-size: 1.8rem; margin: 0; color: var(--text-main);">@yield('title', 'Horizon Dashboard')</h1>
                <p style="color: var(--text-muted); margin-top: 0.5rem;">Super Admin Management Interface</p>
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
                const isLight = document.documentElement.getAttribute('data-theme') === 'light';
                const toggle = document.getElementById('theme-toggle-checkbox');
                if (toggle) toggle.checked = isLight;
            });
        </script>

        @if(session('success'))
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2.5rem; display: flex; align-items: center; gap: 1rem;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

    <script src="{{ asset('script.js') }}"></script>
    @yield('scripts')
</body>
</html>
