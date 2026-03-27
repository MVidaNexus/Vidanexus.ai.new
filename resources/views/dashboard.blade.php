<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Dashboard — Vida Nexus</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Core Styles -->
    <link rel="stylesheet" href="{{ asset('style.v2.css?v=31') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">

    <style>
        .dashboard-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 1.5rem;
            width: 100%;
            margin-top: 2rem;
        }

        /* Sidebar Navigation */
        .dash-sidebar {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem 1.25rem;
            backdrop-filter: blur(20px);
            height: fit-content;
            position: sticky;
            top: 120px;
        }

        .dash-nav-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
            white-space: nowrap;
        }

        .dash-nav-item i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .dash-nav-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-main);
        }

        .dash-nav-item.active {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, rgba(0, 102, 255, 0.1) 100%);
            border-left: 3px solid var(--primary-cyan);
            color: var(--primary-cyan);
        }

        /* Top Stats Row */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            backdrop-filter: blur(20px);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            flex-shrink: 0;
        }

        .stat-icon.cyan { background: rgba(14, 165, 233, 0.1); color: var(--primary-cyan); }
        .stat-icon.purple { background: rgba(176, 38, 255, 0.1); color: var(--neon-purple); }
        .stat-icon.green { background: var(--success-bg); color: var(--accent-success); }

        .stat-info h4 {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-info .value {
            font-family: var(--font-heading);
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.1;
        }

        /* Main Content Panels */
        .dash-content {
            min-height: 800px;
        }

        .content-panel {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2.5rem 1.25rem;
            backdrop-filter: blur(20px);
            margin-bottom: 2rem;
            overflow-x: auto;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--header-border);
        }

        .panel-title {
            font-family: var(--font-heading);
            font-size: clamp(1.2rem, 4vw, 1.5rem);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .panel-title i { color: var(--primary-cyan); }

        /* User Profile Info */
        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 2rem;
        }

        .info-group label {
            display: block;
            color: var(--text-muted);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .info-group p {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--text-main);
            word-break: break-all;
            overflow-wrap: break-word;
        }

        /* Subscribed Tools Grid */
        .my-tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .dash-tool-card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .dash-tool-card:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .tool-status {
            display: inline-block;
            align-self: flex-start;
            font-size: 0.7rem;
            padding: 0.25rem 0.75rem;
            border-radius: 100px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }

        .status-active { background: var(--success-bg); color: var(--accent-success); border: 1px solid var(--success-border); }
        .status-locked { background: rgba(255, 75, 75, 0.1); color: #ff4b4b; border: 1px solid rgba(255, 75, 75, 0.2); }

        .dash-tool-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .dash-tool-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            background: rgba(255, 255, 255, 0.05);
            flex-shrink: 0;
        }

        .dash-tool-title {
            font-family: var(--font-heading);
            font-size: 1.1rem;
            font-weight: 700;
        }

        .dash-tool-desc {
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }

        .dash-btn {
            display: block;
            width: 100%;
            padding: 0.8rem;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-start {
            background: var(--success-bg);
            color: var(--accent-success);
            border: 1px solid var(--success-border);
        }

        .btn-start:hover {
            background: var(--accent-success);
            color: #000;
        }

        .usage-container {
            margin-bottom: 1.5rem;
        }

        .usage-bar-bg {
            height: 6px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .usage-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-cyan), var(--electric-blue));
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .usage-text {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .btn-upgrade {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--glass-border);
        }

        .btn-upgrade:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-main);
        }

        /* Billing & Invoice Styles */
        .billing-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .billing-item {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 1.5rem;
        }

        .billing-item label {
            display: block;
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .billing-item .val {
            font-family: var(--font-heading);
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-table th {
            text-align: left;
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 1rem 0.75rem;
            border-bottom: 1px solid var(--header-border);
        }

        .invoice-table td {
            padding: 1rem 0.75rem;
            color: var(--text-main);
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }

        .invoice-status {
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .inv-paid { background: var(--success-bg); color: var(--accent-success); }
        .inv-pending { background: rgba(255, 204, 0, 0.1); color: #ffcc00; }

        /* Upgrade Plan Cards */
        .upgrade-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .upgrade-card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .upgrade-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .upgrade-card.current {
            border-color: var(--primary-cyan);
            box-shadow: 0 0 30px rgba(14, 165, 233, 0.1);
        }

        .upgrade-card.recommended {
            border-color: var(--neon-purple);
            box-shadow: 0 0 30px rgba(191, 0, 255, 0.15);
        }

        .plan-badge {
            display: inline-block;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1rem;
        }

        .badge-current { background: rgba(14, 165, 233, 0.1); color: var(--primary-cyan); border: 1px solid rgba(14, 165, 233, 0.3); }
        .badge-popular { background: linear-gradient(135deg, var(--neon-purple), var(--primary-cyan)); color: var(--text-main); }

        .plan-name-lg {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .plan-price-lg {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.25rem;
        }

        .plan-price-lg span { font-size: 0.9rem; color: var(--text-muted); font-weight: 400; }

        .plan-credits-lg {
            color: var(--primary-cyan);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .plan-features {
            list-style: none;
            padding: 0;
            text-align: left;
            margin-bottom: 2rem;
            flex-grow: 1;
        }

        .plan-features li {
            color: var(--text-muted);
            padding: 0.5rem 0;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .plan-features li i { color: var(--primary-cyan); font-size: 0.8rem; }

        .btn-plan {
            display: block;
            width: 100%;
            padding: 1rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            border: none;
            font-family: var(--font-heading);
            letter-spacing: 1px;
        }

        .btn-plan-current {
            background: rgba(14, 165, 233, 0.1);
            color: var(--primary-cyan);
            border: 1px solid rgba(14, 165, 233, 0.3);
            cursor: default;
        }

        .btn-plan-upgrade {
            background: linear-gradient(135deg, var(--neon-purple), var(--primary-cyan));
            color: var(--text-main);
            box-shadow: 0 10px 20px rgba(191, 0, 255, 0.3);
        }

        .btn-plan-upgrade:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(191, 0, 255, 0.5);
        }

        /* Billing Packages */
        .billing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
            margin-bottom: 3rem;
        }

        .pkg-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .pkg-card:hover {
            border-color: var(--primary-cyan);
            background: rgba(14, 165, 233, 0.05);
            transform: translateY(-5px);
        }

        .pkg-name {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.25rem;
        }

        .pkg-desc {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
            line-height: 1.4;
            height: 2rem;
            overflow: hidden;
        }

        .pkg-ribbon {
            position: absolute;
            top: 10px;
            right: -30px;
            background: #ef4444;
            color: #fff;
            font-size: 0.6rem;
            font-weight: 800;
            padding: 4px 30px;
            transform: rotate(45deg);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
            z-index: 5;
        }

        .pkg-credits {
            font-family: var(--font-heading);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.25rem;
        }

        .pkg-price {
            color: var(--primary-cyan);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .btn-buy-sm {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: rgba(14, 165, 233, 0.1);
            color: var(--primary-cyan);
            border: 1px solid rgba(14, 165, 233, 0.2);
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-buy-sm:hover {
            background: var(--primary-cyan);
            color: #000;
        }

        /* Responsive Layout */
        @media (max-width: 992px) {
            .dashboard-container {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            .dash-sidebar {
                position: sticky;
                top: 80px;
                z-index: 100;
                display: flex;
                overflow-x: auto;
                padding: 0.75rem;
                background: var(--header-bg);
                backdrop-filter: blur(20px);
                border-radius: 16px;
                gap: 0.5rem;
                scrollbar-width: none;
            }
            .dash-sidebar::-webkit-scrollbar { display: none; }
            .dash-sidebar > div:first-child { display: none; }
            .dash-nav-item {
                white-space: nowrap;
                margin-bottom: 0;
                padding: 0.75rem 1.25rem;
                font-size: 0.9rem;
            }
            .content-panel { padding: 1.5rem; border-radius: 20px; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .billing-summary { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 600px) {
            .billing-summary { grid-template-columns: 1fr; }
        }

        /* ========== MOBILE RESPONSIVE ========== */
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .dash-sidebar {
                position: relative;
                top: 0;
                border-radius: 16px;
                padding: 1rem;
                display: flex;
                flex-direction: row;
                overflow-x: auto;
                gap: 0.25rem;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
            }
            .dash-sidebar::-webkit-scrollbar { display: none; }

            .dash-sidebar .sidebar-header {
                display: none;
            }

            .dash-nav-item {
                white-space: nowrap;
                padding: 0.7rem 1rem;
                margin-bottom: 0;
                font-size: 0.85rem;
                border-radius: 10px;
                flex-shrink: 0;
            }
            .dash-nav-item i { font-size: 1rem; width: 18px; }
            .dash-nav-item.active { border-left: none; border-bottom: 3px solid var(--primary-cyan); }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stat-card {
                padding: 1.25rem;
                border-radius: 16px;
            }
            .stat-icon { width: 48px; height: 48px; font-size: 1.4rem; border-radius: 12px; }

            .content-panel {
                padding: 1.5rem 1rem;
                border-radius: 16px;
            }

            .dash-content { min-height: auto; }

            .my-tools-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .billing-summary {
                grid-template-columns: 1fr;
            }

            .profile-info {
                grid-template-columns: 1fr;
            }

            .invoice-table th,
            .invoice-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.8rem;
            }

            .panel-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }

        @media (max-width: 500px) {
            .stat-card { flex-direction: column; text-align: center; gap: 0.75rem; }
        }

        /* ========== LIGHT MODE DASHBOARD FIXES ========== */
        html[data-theme="light"] .dash-sidebar {
            background: rgba(255, 255, 255, 0.85);
            border-color: rgba(15, 23, 42, 0.1);
        }
        html[data-theme="light"] .stat-card {
            background: rgba(255, 255, 255, 0.85);
            border-color: rgba(15, 23, 42, 0.1);
        }
        html[data-theme="light"] .stat-card:hover {
            border-color: rgba(15, 23, 42, 0.2);
        }
        html[data-theme="light"] .content-panel {
            background: rgba(255, 255, 255, 0.85);
            border-color: rgba(15, 23, 42, 0.1);
        }
        html[data-theme="light"] .dash-tool-card {
            background: #ffffff;
            border-color: rgba(15, 23, 42, 0.1);
        }
        html[data-theme="light"] .dash-tool-card:hover {
            background: rgba(15, 23, 42, 0.02);
            border-color: rgba(15, 23, 42, 0.2);
        }
        html[data-theme="light"] .dash-nav-item:hover {
            background: rgba(15, 23, 42, 0.05);
        }
        html[data-theme="light"] .dash-nav-item.active {
            background: rgba(14, 165, 233, 0.08);
        }
        html[data-theme="light"] .dash-tool-icon {
            background: rgba(15, 23, 42, 0.04);
        }
        html[data-theme="light"] .billing-item {
            background: #ffffff;
            border-color: rgba(15, 23, 42, 0.1);
        }
        html[data-theme="light"] .upgrade-card {
            background: #ffffff;
            border-color: rgba(15, 23, 42, 0.1);
        }
        html[data-theme="light"] .upgrade-card:hover {
            border-color: rgba(15, 23, 42, 0.2);
        }
        html[data-theme="light"] .usage-bar-bg {
            background: rgba(15, 23, 42, 0.06);
        }
        html[data-theme="light"] .pkg-card {
            background: rgba(15, 23, 42, 0.02);
            border-color: rgba(15, 23, 42, 0.1);
        }
        html[data-theme="light"] .invoice-table td {
            border-bottom-color: rgba(15, 23, 42, 0.06);
        }
    </style>
    <script>(function(){const t=localStorage.getItem("theme")||"dark";document.documentElement.setAttribute("data-theme",t);})();</script>
</head>
<body>
    <canvas id="techCanvas"></canvas>
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>
    
    <div class="main-container">
        @include('partials.header')

        <!-- Dashboard Layout -->
        <div class="dashboard-container">
            <!-- Sidebar Menu -->
            <aside class="dash-sidebar">
                <div style="margin-bottom: 2rem; padding: 0 1rem;">
                    <h3 style="font-family: var(--font-heading); font-size: 1.2rem; margin-bottom: 0.5rem;">Welcome back,</h3>
                    <p style="color: var(--primary-cyan); font-weight: 600; font-size: 1.1rem; word-break: break-all;">{{ $user->name }}</p>
                </div>

                <a href="#overview" class="dash-nav-item active">
                    <i class="fas fa-chart-pie"></i>
                    <span>Overview</span>
                </a>
                <a href="#subscriptions" class="dash-nav-item">
                    <i class="fas fa-layer-group"></i>
                    <span>My Tools</span>
                </a>
                <a href="#billing" class="dash-nav-item">
                    <i class="fas fa-wallet"></i>
                    <span>Wallet & Credits</span>
                </a>
                <a href="#settings" class="dash-nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </aside>

            <!-- Main Content Area -->
            <div class="dash-content">
                
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

                <!-- Quick Stats (Persistent) -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon cyan">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-info">
                            <h4>Balance</h4>
                            <div class="value">{{ number_format($walletBalance, 2) }} <span style="font-size: 0.9rem; color: var(--primary-cyan);">Credits</span></div>
                        </div>
                    </div>
                    

                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="stat-info">
                            <h4>Tools Unlocked</h4>
                            <div class="value">{{ $accessibleCount }} <span style="font-size: 0.9rem; color: var(--text-muted);">/ {{ $totalTools }}</span></div>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="content-panel" id="overview">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fas fa-id-card"></i> Account Details</h2>
                    </div>
                    <div class="profile-info">
                        <div class="info-group">
                            <label>Full Name</label>
                            <p>{{ $user->name }}</p>
                        </div>
                        <div class="info-group">
                            <label>Email Address</label>
                            <p>{{ $user->email }}</p>
                        </div>
                        <div class="info-group">
                            <label>Phone Number</label>
                            <p>{{ $user->phone ?? '⚠️ Not set' }}</p>
                        </div>
                        <div class="info-group">
                            <label>Country</label>
                            <p>{{ $user->country ?? '⚠️ Not set' }}</p>
                        </div>
                        <div class="info-group">
                            <label>Member Since</label>
                            <p>{{ $user->created_at->format('F j, Y') }}</p>
                        </div>
                        <div class="info-group">
                            <label>Account Model</label>
                            <p style="color: var(--primary-cyan);">Modular AI Marketplace</p>
                        </div>
                    </div>
                </div>

                <!-- My Tools / Subscriptions -->
                <div class="content-panel" id="subscriptions" style="display: none;">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fas fa-box-open"></i> Tool Access Hub</h2>
                        <a href="#billing" class="dash-nav-link" style="color: var(--primary-cyan); text-decoration: none; font-size: 0.9rem; font-weight: 500;">Purchase Credits <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
                    </div>
                    
                    <div class="my-tools-grid">
                        @foreach($tools as $tool)
                            <div class="dash-tool-card">
                                @if($tool['accessible'])
                                    <div class="tool-status status-active">Unlocked</div>
                                @elseif(!$tool['is_available'])
                                    <div class="tool-status" style="background: rgba(14, 165, 233, 0.1); color: var(--primary-cyan); border: 1px solid rgba(14, 165, 233, 0.2);">
                                        <i class="fas fa-clock" style="margin-right: 4px;"></i> Coming Soon
                                    </div>
                                @else
                                    <div class="tool-status status-locked"><i class="fas fa-lock" style="margin-right: 4px;"></i> Marketplace</div>
                                @endif
                                
                                <div class="dash-tool-header">
                                    <div class="dash-tool-icon" style="color: {{ $tool['color'] }}">
                                        <i class="fas {{ $tool['icon'] }}"></i>
                                    </div>
                                    <h3 class="dash-tool-title">{{ $tool['name'] }}</h3>
                                </div>
                                
                                <p class="dash-tool-desc">{{ $tool['description'] }}</p>
                                
                                @if($tool['accessible'])
                                    <div style="margin-bottom: 1.5rem;">
                                        <span style="font-size: 0.75rem; color: var(--accent-success); font-weight: 600;"><i class="fas fa-infinity"></i> Unlimited Access</span>
                                    </div>
                                @endif
                                
                                @if($tool['accessible'])
                                    @if($tool['slug'] === 'discover-headlines')
                                        <a href="{{ route('headlines.index') }}" class="vn-btn vn-btn-primary dash-action-btn">Start Tool</a>
                                    @elseif($tool['slug'] === 'seo-analyzer')
                                        <a href="{{ route('dashboard.seo-analyzer.index') }}" class="vn-btn vn-btn-primary dash-action-btn">Start Tool</a>
                                    @elseif($tool['slug'] === 'drama-trends')
                                        <a href="{{ route('dashboard.drama-trends.index') }}" class="vn-btn vn-btn-primary dash-action-btn">Start Tool</a>
                                    @elseif($tool['slug'] === 'ai-keyword-radar')
                                        <a href="{{ route('dashboard.ai-keyword-radar.index') }}" class="vn-btn vn-btn-primary dash-action-btn">Start Tool</a>
                                    @elseif($tool['slug'] === 'global-news-monitor')
                                        <a href="{{ route('dashboard.global-news-monitor.index') }}" class="vn-btn vn-btn-primary dash-action-btn">Start Tool</a>
                                    @elseif($tool['slug'] === 'trending-search-monitor')
                                        <a href="{{ route('dashboard.trending-searches.index') }}" class="vn-btn vn-btn-primary dash-action-btn">Start Tool</a>
                                    @elseif($tool['slug'] === 'seo-auditor')
                                        <a href="{{ route('dashboard.seo-auditor.index') }}" class="vn-btn vn-btn-primary dash-action-btn">Start Tool</a>
                                    @elseif($tool['slug'] === 'audit-x')
                                        <a href="{{ route('dashboard.audit-x.index') }}" class="vn-btn vn-btn-primary dash-action-btn">Start Tool</a>
                                    @elseif($tool['slug'] === 'folio-ocr')
                                        <a href="{{ route('dashboard.folio-ocr.index') }}" class="vn-btn vn-btn-primary dash-action-btn">Start Tool</a>
                                    @elseif($tool['slug'] === 'img-compress')
                                        <a href="{{ route('dashboard.img-compress.index') }}" class="vn-btn vn-btn-primary dash-action-btn">Start Tool</a>
                                    @elseif($tool['slug'] === 'web-to-app')
                                        <a href="{{ route('dashboard.web-to-app.index') }}" class="vn-btn vn-btn-primary dash-action-btn">Start Tool</a>
                                    @elseif($tool['slug'] === 'money-printer')
                                        <a href="{{ route('dashboard.money-printer.index') }}" class="vn-btn vn-btn-primary dash-action-btn">Start Tool</a>
                                    @else
                                        <a href="/tools/{{ $tool['slug'] }}" class="vn-btn vn-btn-primary dash-action-btn">Start Tool</a>
                                    @endif
                                @elseif(!$tool['is_available'])
                                    <button class="vn-btn vn-btn-outline dash-action-btn" style="cursor: not-allowed; opacity: 0.6;" disabled>
                                        <i class="fas fa-lock mr-2"></i> Coming Soon
                                    </button>
                                @else
                                    <a href="/payment?type=tool&id={{ $tool['slug'] }}" class="vn-btn" style="background: linear-gradient(135deg, #a855f7, #6366f1); color: white; border: none; font-size: 0.85rem; padding: 0.8rem 1rem; width: 100%; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                        <span>Subscribe for {{ number_format($tool['unlock_price']) }} EGP / Month</span>
                                        <i class="fas fa-calendar-alt"></i>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Billing & Invoices Panel -->
                <div class="content-panel" id="billing" style="display: none;">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fas fa-file-invoice-dollar"></i> Billing & Invoices</h2>
                    </div>

                    <div class="billing-summary">
                        <div class="billing-item">
                            <label>Wallet Status</label>
                            <div class="val" style="color: var(--primary-cyan);">Active</div>
                        </div>
                        <div class="billing-item">
                            <label>Current Balance</label>
                            <div class="val">{{ number_format($walletBalance, 2) }} Credits</div>
                        </div>
                        <div class="billing-item">
                            <label>Account Type</label>
                            <div class="val">Pay-per-Action</div>
                        </div>
                        <div class="billing-item">
                            <label>Last Activity</label>
                            <div class="val" style="font-size: 1.1rem;">
                                {{ now()->format('M j, Y') }}
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--glass-border);">
                        <h3 style="font-family: var(--font-heading); font-size: 1.25rem; margin-bottom: 0.5rem;"><i class="fas fa-plus-circle" style="color: var(--primary-cyan); margin-right: 0.5rem;"></i> Purchase Extra Credits</h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem;">Need more points? Top up your wallet with supplemental credit packages.</p>
                        
                        @php
                            $defaultPackages = [
                                'lite' => [ 'name' => 'Lite Dash', 'credits' => '100', 'price' => '35', 'desc' => 'Perfect for testing a single tool.', 'icon' => 'fa-seedling', 'color' => '#00ffaa' ],
                                'standard' => [ 'name' => 'Creator Pack', 'credits' => '500', 'price' => '150', 'desc' => 'Best for social media managers.', 'icon' => 'fa-rocket', 'color' => 'var(--primary-cyan)', 'popular' => true ],
                                'pro' => [ 'name' => 'Agency Pro', 'credits' => '2,500', 'price' => '650', 'desc' => 'High-volume SEO & Content.', 'icon' => 'fa-bolt-lightning', 'color' => 'var(--neon-purple)' ],
                                'enterprise' => [ 'name' => 'Power Node', 'credits' => '10,000', 'price' => '2,250', 'desc' => 'Infrastructure level usage.', 'icon' => 'fa-crown', 'color' => '#ffcc00' ]
                            ];
                            $savedPackagesJson = \App\Models\Setting::get('marketplace_packages');
                            $packages = is_string($savedPackagesJson) ? json_decode($savedPackagesJson, true) : ($savedPackagesJson ?: $defaultPackages);
                        @endphp

                        <div class="billing-grid">
                            @foreach($packages as $id => $pkg)
                                @php
                                    $discount = isset($pkg['discount']) && is_numeric($pkg['discount']) ? (float)$pkg['discount'] : 0;
                                    $basePrice = (float)str_replace(',', '', $pkg['price']);
                                    $finalPrice = $discount > 0 ? $basePrice - ($basePrice * ($discount / 100)) : $basePrice;
                                @endphp
                                <div class="pkg-card" style="position: relative; overflow: hidden; border-color: {{ !empty($pkg['popular']) ? 'var(--primary-cyan)' : 'var(--glass-border)' }};">
                                    @if($discount > 0)
                                        <div class="pkg-ribbon">SAVE {{ (int)$discount }}%</div>
                                    @endif

                                    <div style="font-size: 1.5rem; color: {{ $pkg['color'] ?? 'var(--primary-cyan)' }}; margin-bottom: 1rem;">
                                        <i class="fas {{ $pkg['icon'] ?? 'fa-box' }}"></i>
                                    </div>
                                    <div class="pkg-name">{{ $pkg['name'] }}</div>
                                    <div class="pkg-desc">{{ $pkg['desc'] }}</div>
                                    <div class="pkg-credits">{{ $pkg['credits'] }} Credits</div>
                                    <div class="pkg-price">
                                        @if($discount > 0)
                                            <span style="text-decoration: line-through; font-size: 0.75rem; opacity: 0.5; margin-right: 5px;">{{ number_format($basePrice) }}</span>
                                        @endif
                                        {{ number_format($finalPrice) }} EGP
                                    </div>
                                    <a href="/payment?type=package&id={{ $id }}" class="vn-btn {{ !empty($pkg['popular']) ? 'vn-btn-primary' : 'vn-btn-outline' }}" style="padding: 0.5rem 1rem; font-size: 0.85rem; width: 100%;">Buy Now</a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($invoices->count())
                        <h3 style="font-family: var(--font-heading); font-size: 1.2rem; margin-bottom: 1rem;">Invoice History</h3>
                        <div style="overflow-x: auto;">
                            <table class="invoice-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Credits</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        <tr>
                                            <td style="color: var(--text-muted);">INV-{{ str_pad($invoice->id, 4, '0', STR_PAD_LEFT) }}</td>
                                            <td>{{ $invoice->created_at->format('M j, Y') }}</td>
                                            <td>{{ $invoice->description }}</td>
                                            <td style="font-weight: 600;">{{ number_format($invoice->amount, 0) }} EGP</td>
                                            <td style="color: var(--primary-cyan);">+{{ number_format($invoice->credits_granted) }} Credits</td>
                                            <td><span class="invoice-status {{ $invoice->status === 'paid' ? 'inv-paid' : 'inv-pending' }}">{{ $invoice->status }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div style="text-align: center; padding: 3rem 0; color: var(--text-muted);">
                            <i class="fas fa-receipt" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p>No transactions found in your history.</p>
                        </div>
                    @endif
                </div>

                <!-- Settings Panel -->
                <div class="content-panel" id="settings" style="display: none;">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fas fa-cog"></i> Account Settings</h2>
                    </div>

                    <form action="/dashboard/settings" method="POST" style="max-width: 600px;" onsubmit="return combinePhoneNumber()">
                        @csrf
                        <input type="hidden" name="dial_code" id="dialCodeHidden" value="">
                        
                        @if($errors->any())
                            <div style="background: rgba(255, 70, 70, 0.1); border: 1px solid #ff4646; color: #ff4646; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                                <ul style="margin: 0; padding-left: 1.2rem;">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text-main); padding: 1rem; border-radius: 8px; font-family: inherit;">
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Country <span style="color: #ff4b4b;">*</span></label>
                            <div style="display: flex; align-items: center; background: rgba(255,255,255,0.05); border: 1px solid {{ $user->country ? 'var(--glass-border)' : 'rgba(255, 170, 0, 0.5)' }}; border-radius: 8px; overflow: hidden;">
                                <span style="padding: 1rem; color: var(--primary-cyan); font-size: 1rem;"><i class="fas fa-globe"></i></span>
                                <select name="country" id="countrySelect" required onchange="updatePhonePrefix()" style="flex: 1; background: transparent; border: none; color: var(--text-main); padding: 1rem 1rem 1rem 0; font-family: inherit; outline: none; font-size: 1rem; cursor: pointer; -webkit-appearance: none; appearance: none;">
                                    <option value="" style="background: #1a1a2e;">-- Select Country --</option>
                                    <option value="Egypt" data-dial="+20" data-flag="🇪🇬" {{ (old('country', $user->country) == 'Egypt' || empty(old('country', $user->country))) ? 'selected' : '' }} style="background: #1a1a2e;">🇪🇬 Egypt</option>
                                    <option value="Saudi Arabia" data-dial="+966" data-flag="🇸🇦" {{ old('country', $user->country) == 'Saudi Arabia' ? 'selected' : '' }} style="background: #1a1a2e;">🇸🇦 Saudi Arabia</option>
                                    <option value="United Arab Emirates" data-dial="+971" data-flag="🇦🇪" {{ old('country', $user->country) == 'United Arab Emirates' ? 'selected' : '' }} style="background: #1a1a2e;">🇦🇪 United Arab Emirates</option>
                                    <option value="Kuwait" data-dial="+965" data-flag="🇰🇼" {{ old('country', $user->country) == 'Kuwait' ? 'selected' : '' }} style="background: #1a1a2e;">🇰🇼 Kuwait</option>
                                    <option value="Qatar" data-dial="+974" data-flag="🇶🇦" {{ old('country', $user->country) == 'Qatar' ? 'selected' : '' }} style="background: #1a1a2e;">🇶🇦 Qatar</option>
                                    <option value="Bahrain" data-dial="+973" data-flag="🇧🇭" {{ old('country', $user->country) == 'Bahrain' ? 'selected' : '' }} style="background: #1a1a2e;">🇧🇭 Bahrain</option>
                                    <option value="Oman" data-dial="+968" data-flag="🇴🇲" {{ old('country', $user->country) == 'Oman' ? 'selected' : '' }} style="background: #1a1a2e;">🇴🇲 Oman</option>
                                    <option value="Jordan" data-dial="+962" data-flag="🇯🇴" {{ old('country', $user->country) == 'Jordan' ? 'selected' : '' }} style="background: #1a1a2e;">🇯🇴 Jordan</option>
                                    <option value="Iraq" data-dial="+964" data-flag="🇮🇶" {{ old('country', $user->country) == 'Iraq' ? 'selected' : '' }} style="background: #1a1a2e;">🇮🇶 Iraq</option>
                                    <option value="Lebanon" data-dial="+961" data-flag="🇱🇧" {{ old('country', $user->country) == 'Lebanon' ? 'selected' : '' }} style="background: #1a1a2e;">🇱🇧 Lebanon</option>
                                    <option value="Palestine" data-dial="+970" data-flag="🇵🇸" {{ old('country', $user->country) == 'Palestine' ? 'selected' : '' }} style="background: #1a1a2e;">🇵🇸 Palestine</option>
                                    <option value="Syria" data-dial="+963" data-flag="🇸🇾" {{ old('country', $user->country) == 'Syria' ? 'selected' : '' }} style="background: #1a1a2e;">🇸🇾 Syria</option>
                                    <option value="Libya" data-dial="+218" data-flag="🇱🇾" {{ old('country', $user->country) == 'Libya' ? 'selected' : '' }} style="background: #1a1a2e;">🇱🇾 Libya</option>
                                    <option value="Tunisia" data-dial="+216" data-flag="🇹🇳" {{ old('country', $user->country) == 'Tunisia' ? 'selected' : '' }} style="background: #1a1a2e;">🇹🇳 Tunisia</option>
                                    <option value="Algeria" data-dial="+213" data-flag="🇩🇿" {{ old('country', $user->country) == 'Algeria' ? 'selected' : '' }} style="background: #1a1a2e;">🇩🇿 Algeria</option>
                                    <option value="Morocco" data-dial="+212" data-flag="🇲🇦" {{ old('country', $user->country) == 'Morocco' ? 'selected' : '' }} style="background: #1a1a2e;">🇲🇦 Morocco</option>
                                    <option value="Sudan" data-dial="+249" data-flag="🇸🇩" {{ old('country', $user->country) == 'Sudan' ? 'selected' : '' }} style="background: #1a1a2e;">🇸🇩 Sudan</option>
                                    <option value="Yemen" data-dial="+967" data-flag="🇾🇪" {{ old('country', $user->country) == 'Yemen' ? 'selected' : '' }} style="background: #1a1a2e;">🇾🇪 Yemen</option>
                                    <option value="Turkey" data-dial="+90" data-flag="🇹🇷" {{ old('country', $user->country) == 'Turkey' ? 'selected' : '' }} style="background: #1a1a2e;">🇹🇷 Turkey</option>
                                    <option value="United States" data-dial="+1" data-flag="🇺🇸" {{ old('country', $user->country) == 'United States' ? 'selected' : '' }} style="background: #1a1a2e;">🇺🇸 United States</option>
                                    <option value="United Kingdom" data-dial="+44" data-flag="🇬🇧" {{ old('country', $user->country) == 'United Kingdom' ? 'selected' : '' }} style="background: #1a1a2e;">🇬🇧 United Kingdom</option>
                                    <option value="Germany" data-dial="+49" data-flag="🇩🇪" {{ old('country', $user->country) == 'Germany' ? 'selected' : '' }} style="background: #1a1a2e;">🇩🇪 Germany</option>
                                    <option value="France" data-dial="+33" data-flag="🇫🇷" {{ old('country', $user->country) == 'France' ? 'selected' : '' }} style="background: #1a1a2e;">🇫🇷 France</option>
                                    <option value="India" data-dial="+91" data-flag="🇮🇳" {{ old('country', $user->country) == 'India' ? 'selected' : '' }} style="background: #1a1a2e;">🇮🇳 India</option>
                                    <option value="Pakistan" data-dial="+92" data-flag="🇵🇰" {{ old('country', $user->country) == 'Pakistan' ? 'selected' : '' }} style="background: #1a1a2e;">🇵🇰 Pakistan</option>
                                    <option value="Nigeria" data-dial="+234" data-flag="🇳🇬" {{ old('country', $user->country) == 'Nigeria' ? 'selected' : '' }} style="background: #1a1a2e;">🇳🇬 Nigeria</option>
                                    <option value="South Africa" data-dial="+27" data-flag="🇿🇦" {{ old('country', $user->country) == 'South Africa' ? 'selected' : '' }} style="background: #1a1a2e;">🇿🇦 South Africa</option>
                                    <option value="Brazil" data-dial="+55" data-flag="🇧🇷" {{ old('country', $user->country) == 'Brazil' ? 'selected' : '' }} style="background: #1a1a2e;">🇧🇷 Brazil</option>
                                    <option value="Canada" data-dial="+1" data-flag="🇨🇦" {{ old('country', $user->country) == 'Canada' ? 'selected' : '' }} style="background: #1a1a2e;">🇨🇦 Canada</option>
                                    <option value="Australia" data-dial="+61" data-flag="🇦🇺" {{ old('country', $user->country) == 'Australia' ? 'selected' : '' }} style="background: #1a1a2e;">🇦🇺 Australia</option>
                                    <option value="Malaysia" data-dial="+60" data-flag="🇲🇾" {{ old('country', $user->country) == 'Malaysia' ? 'selected' : '' }} style="background: #1a1a2e;">🇲🇾 Malaysia</option>
                                    <option value="Indonesia" data-dial="+62" data-flag="🇮🇩" {{ old('country', $user->country) == 'Indonesia' ? 'selected' : '' }} style="background: #1a1a2e;">🇮🇩 Indonesia</option>
                                </select>
                                <i class="fas fa-chevron-down" style="padding-right: 1rem; color: var(--text-muted); font-size: 0.8rem;"></i>
                            </div>
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Phone Number <span style="color: #ff4b4b;">*</span></label>
                            <div style="display: flex; align-items: center; background: rgba(255,255,255,0.05); border: 1px solid {{ $user->phone ? 'var(--glass-border)' : 'rgba(255, 170, 0, 0.5)' }}; border-radius: 8px; overflow: hidden;">
                                <span id="phonePrefix" style="padding: 0.75rem 0.5rem 0.75rem 1rem; color: var(--text-main); font-size: 1.1rem; white-space: nowrap; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; min-width: 100px;">
                                    <span id="phoneFlagEmoji" style="font-size: 1.3rem;">🌍</span>
                                    <span id="phoneDialCode" style="color: var(--primary-cyan);">+00</span>
                                </span>
                                <div style="width: 1px; height: 28px; background: var(--glass-border);"></div>
                                <input type="tel" name="phone" id="phoneInput" value="{{ old('phone', $user->phone) }}" required placeholder="1234567890" style="flex: 1; background: none; border: none; color: var(--text-main); padding: 1rem; font-family: inherit; outline: none; font-size: 1rem; letter-spacing: 0.5px;">
                            </div>
                            <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.3rem; display: block;">Enter your local number without the country code</small>
                        </div>

                        <div style="margin-bottom: 2rem;">
                            <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Email Address (Cannot be changed)</label>
                            <input type="email" value="{{ $user->email }}" disabled style="width: 100%; background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); color: var(--text-muted); padding: 1rem; border-radius: 8px; font-family: inherit; cursor: not-allowed;">
                        </div>

                        <div style="border-top: 1px solid var(--glass-border); padding-top: 2rem; margin-top: 2rem;">
                            <h3 style="font-family: var(--font-heading); font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--primary-cyan);">Change Password</h3>
                            
                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Current Password (required to change)</label>
                                <input type="password" name="current_password" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text-main); padding: 1rem; border-radius: 8px; font-family: inherit;">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div style="margin-bottom: 1.5rem;">
                                    <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">New Password</label>
                                    <input type="password" name="password" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text-main); padding: 1rem; border-radius: 8px; font-family: inherit;">
                                </div>

                                <div style="margin-bottom: 1.5rem;">
                                    <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text-main); padding: 1rem; border-radius: 8px; font-family: inherit;">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="vn-btn vn-btn-primary" style="margin-top: 1rem; width: fit-content; padding-left: 3rem; padding-right: 3rem;">
                            Save Account Changes
                        </button>
                    </form>
                </div>

            </div>
        </div>

        @include('partials.footer')
    </div>
    <script src="{{ asset('script.js?v=14') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navItems = document.querySelectorAll('.dash-nav-item');
            const panels = {
                '#overview': document.getElementById('overview'),
                '#subscriptions': document.getElementById('subscriptions'),
                '#billing': document.getElementById('billing'),
                '#settings': document.getElementById('settings')
            };

            function showPanel(hash) {
                let targetHash = hash || '#overview';
                if (!panels[targetHash]) {
                    if (targetHash.includes('billing')) targetHash = '#billing';
                    else if (targetHash.includes('subscriptions')) targetHash = '#subscriptions';
                    else if (targetHash.includes('settings')) targetHash = '#settings';
                    else targetHash = '#overview';
                }

                // Toggle visibility in one pass to prevent layout flicker
                Object.keys(panels).forEach(key => {
                    if (panels[key]) {
                        panels[key].style.display = (key === targetHash) ? 'block' : 'none';
                    }
                });

                // Update active nav state
                navItems.forEach(item => {
                    item.classList.remove('active');
                    if (item.getAttribute('href') === targetHash) {
                        item.classList.add('active');
                    }
                });
            }

            // Handle nav clicks (sidebar and inline links)
            document.querySelectorAll('.dash-nav-item, .dash-nav-link').forEach(item => {
                item.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href && href.startsWith('#')) {
                        e.preventDefault();
                        
                        // Update hash without jumping
                        if (history.pushState) {
                            history.pushState(null, null, href);
                        } else {
                            window.location.hash = href;
                        }
                        
                        showPanel(href);
                    }
                });
            });

            // Initial load check
            showPanel(window.location.hash || '#overview');
            
            // Handle hash changes
            window.addEventListener('hashchange', function() {
                showPanel(window.location.hash || '#overview');
            });
        });

        // ── Country → Phone Prefix Sync ──
        function updatePhonePrefix() {
            const select = document.getElementById('countrySelect');
            const flagEl = document.getElementById('phoneFlagEmoji');
            const dialEl = document.getElementById('phoneDialCode');
            const dialHidden = document.getElementById('dialCodeHidden');
            
            if (!select || !flagEl || !dialEl) return;
            
            const selected = select.options[select.selectedIndex];
            if (selected && selected.value) {
                const flag = selected.getAttribute('data-flag') || '🌍';
                const dial = selected.getAttribute('data-dial') || '+00';
                flagEl.textContent = flag;
                dialEl.textContent = dial;
                if (dialHidden) dialHidden.value = dial;
            } else {
                flagEl.textContent = '🌍';
                dialEl.textContent = '+00';
                if (dialHidden) dialHidden.value = '';
            }
        }

        // Combine dial code + local number before form submission
        function combinePhoneNumber() {
            const select = document.getElementById('countrySelect');
            const dialHidden = document.getElementById('dialCodeHidden');
            const phoneInput = document.getElementById('phoneInput');
            
            if (dialHidden && phoneInput && dialHidden.value) {
                let local = phoneInput.value.trim();
                // Remove leading zero if present
                if (local.startsWith('0')) local = local.substring(1);
                
                // Validate Length
                const dial = dialHidden.value;
                const lengths = { '+20': 10, '+966': 9, '+971': 9, '+965': 8 };
                if (lengths[dial] && local.length !== lengths[dial]) {
                    alert(`Please enter a valid ${lengths[dial]}-digit number for ${select.value}.`);
                    return false;
                }

                // Only prepend if not already starts with the dial code
                if (!local.startsWith(dial)) {
                    phoneInput.value = dial + local;
                }
            }
            return true; // Allow form submission
        }

        // Initialize phone prefix on page load if country is already set
        document.addEventListener('DOMContentLoaded', function() {
            updatePhonePrefix();
        });
    </script>
</body>
</html>
