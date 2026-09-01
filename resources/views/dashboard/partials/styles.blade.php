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

        .dash-nav-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
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
            background: linear-gradient(135deg, rgba(0, 168, 230, 0.1) 0%, rgba(0, 102, 255, 0.1) 100%);
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

        .stat-icon.cyan { background: rgba(0, 168, 230, 0.1); color: var(--primary-cyan); }
        .stat-icon.purple { background: rgba(176, 38, 255, 0.1); color: var(--accent); }
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

        /* Heavy Blur on Coming Soon Tool Cards */
        .dash-tool-card.is-coming-soon {
            position: relative !important;
            overflow: hidden !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            cursor: default !important;
        }

        .dash-tool-card.is-coming-soon:hover {
            transform: none !important;
            box-shadow: none !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            background: var(--card-bg) !important;
        }

        .dash-tool-card.is-coming-soon .tool-card-content {
            filter: blur(10px) grayscale(60%) opacity(0.3);
            pointer-events: none !important;
            user-select: none !important;
            transform: scale(0.97);
            transition: all 0.3s ease;
        }

        .coming-soon-overlay {
            position: absolute;
            inset: 0;
            z-index: 25;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at center, rgba(10, 15, 29, 0.72) 0%, rgba(5, 8, 16, 0.9) 100%);
            backdrop-filter: blur(16px) saturate(130%);
            -webkit-backdrop-filter: blur(16px) saturate(130%);
            border-radius: inherit;
            padding: 1.5rem;
            pointer-events: all;
        }

        .coming-soon-badge-glow {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 1.1rem 1.6rem;
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(0, 168, 230, 0.45);
            border-radius: 18px;
            box-shadow: 0 0 30px rgba(0, 168, 230, 0.22), inset 0 0 18px rgba(0, 168, 230, 0.1);
            animation: csBadgePulse 3.5s ease-in-out infinite alternate;
        }

        .coming-soon-badge-glow .cs-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(0, 168, 230, 0.15);
            border: 1px solid rgba(0, 168, 230, 0.5);
            color: var(--primary-cyan, #00A8E6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 0.6rem;
            box-shadow: 0 0 15px rgba(0, 168, 230, 0.35);
        }

        .coming-soon-badge-glow .cs-title {
            font-size: 0.82rem;
            font-weight: 900;
            letter-spacing: 2.5px;
            color: #ffffff;
            text-transform: uppercase;
            font-family: var(--font-heading, inherit);
            margin-bottom: 0.25rem;
            text-shadow: 0 0 12px rgba(0, 168, 230, 0.6);
        }

        .coming-soon-badge-glow .cs-subtitle {
            font-size: 0.7rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
            letter-spacing: 0.5px;
        }

        @keyframes csBadgePulse {
            0% {
                box-shadow: 0 0 20px rgba(0, 168, 230, 0.15), inset 0 0 10px rgba(0, 168, 230, 0.05);
                border-color: rgba(0, 168, 230, 0.35);
            }
            100% {
                box-shadow: 0 0 38px rgba(0, 168, 230, 0.35), inset 0 0 22px rgba(0, 168, 230, 0.18);
                border-color: rgba(0, 168, 230, 0.7);
            }
        }

        html[data-theme="light"] .coming-soon-overlay {
            background: radial-gradient(circle at center, rgba(248, 250, 252, 0.75) 0%, rgba(241, 245, 249, 0.92) 100%);
        }
        html[data-theme="light"] .coming-soon-badge-glow {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(0, 168, 230, 0.45);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        html[data-theme="light"] .coming-soon-badge-glow .cs-title {
            color: #0f172a;
            text-shadow: none;
        }
        html[data-theme="light"] .coming-soon-badge-glow .cs-subtitle {
            color: #64748b;
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
            background: linear-gradient(90deg, var(--primary-cyan), var(--primary));
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
            box-shadow: 0 0 30px rgba(0, 168, 230, 0.1);
        }

        .upgrade-card.recommended {
            border-color: var(--accent);
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

        .badge-current { background: rgba(0, 168, 230, 0.1); color: var(--primary-cyan); border: 1px solid rgba(0, 168, 230, 0.3); }
        .badge-popular { background: linear-gradient(135deg, var(--accent), var(--primary-cyan)); color: var(--text-main); }

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
            background: rgba(0, 168, 230, 0.1);
            color: var(--primary-cyan);
            border: 1px solid rgba(0, 168, 230, 0.3);
            cursor: default;
        }

        .btn-plan-upgrade {
            background: linear-gradient(135deg, var(--accent), var(--primary-cyan));
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
            background: rgba(0, 168, 230, 0.05);
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
            background: rgba(0, 168, 230, 0.1);
            color: var(--primary-cyan);
            border: 1px solid rgba(0, 168, 230, 0.2);
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
                position: static !important;
                top: auto !important;
                z-index: 10;
                display: block;
                padding: 0.75rem !important;
                background: rgba(10, 14, 23, 0.95);
                backdrop-filter: blur(20px);
                border: 1px solid var(--glass-border);
                border-radius: 18px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            }
            .dash-sidebar .user-profile-widget {
                display: none;
            }
            .dash-nav-list {
                display: grid !important;
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 0.5rem !important;
                width: 100% !important;
                overflow-x: visible !important;
                padding: 0 !important;
            }
            .dash-nav-list::-webkit-scrollbar { display: none; }
            .dash-nav-item {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                text-align: center !important;
                white-space: normal !important;
                margin-bottom: 0 !important;
                padding: 0.65rem 0.35rem !important;
                font-size: 0.78rem !important;
                font-weight: 600 !important;
                border-radius: 12px !important;
                background: rgba(255, 255, 255, 0.03) !important;
                border: 1px solid rgba(255, 255, 255, 0.08) !important;
                color: var(--text-muted, #94a3b8) !important;
                transition: all 0.2s ease !important;
            }
            .dash-nav-item i {
                font-size: 1.15rem !important;
                margin-bottom: 0.35rem !important;
                margin-right: 0 !important;
                width: auto !important;
            }
            .dash-nav-item span {
                font-size: 0.75rem !important;
                line-height: 1.2 !important;
            }
            .dash-nav-item.active {
                background: rgba(0, 168, 230, 0.15) !important;
                border: 1px solid var(--primary-cyan) !important;
                color: var(--primary-cyan) !important;
                box-shadow: 0 0 12px rgba(0, 168, 230, 0.2) !important;
                border-bottom: 1px solid var(--primary-cyan) !important;
                border-left: 1px solid var(--primary-cyan) !important;
            }
            .content-panel { padding: 1.5rem; border-radius: 20px; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .billing-summary { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 600px) {
            .billing-summary { grid-template-columns: 1fr; }
            .dashboard-container { margin-top: 1rem; }
            .upgrade-grid, .billing-grid { grid-template-columns: 1fr !important; gap: 1rem; }
            .premium-coupon-block { padding: 1.25rem 1rem !important; }
            .panel-title { font-size: 1.15rem !important; }
            .stat-info .value { font-size: 1.4rem !important; }
            .stats-grid { grid-template-columns: 1fr; gap: 0.75rem; }
            .content-panel { padding: 1.25rem 1rem; }
        }

        /* ========== MOBILE RESPONSIVE ========== */
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .dash-sidebar {
                position: static !important;
                top: auto !important;
                border-radius: 16px;
                padding: 0.65rem !important;
                display: block !important;
            }
            .dash-sidebar .sidebar-header {
                display: none;
            }

            .dash-nav-list {
                display: grid !important;
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 0.4rem !important;
            }

            .dash-nav-item {
                padding: 0.6rem 0.25rem !important;
                font-size: 0.72rem !important;
            }
            .dash-nav-item i { font-size: 1.05rem !important; margin-bottom: 0.25rem !important; }
            .dash-nav-item span { font-size: 0.72rem !important; }

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
            background: rgba(0, 168, 230, 0.08);
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

        /* ========== PREMIUM POLISH UI ========== */
        .premium-profile-widget {
            padding: 1.5rem; background: linear-gradient(145deg, rgba(255,255,255,0.05), rgba(255,255,255,0.01)); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; display: flex; flex-direction: column; align-items: center; text-align: center; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .premium-profile-avatar {
            border: 3px solid rgba(255,255,255,0.1); box-shadow: 0 0 20px rgba(0, 168, 230,0.4); color: #fff;
        }
        .premium-stat-card {
            background: linear-gradient(145deg, rgba(15,23,42,0.6), rgba(15,23,42,0.9)); box-shadow: 0 10px 30px rgba(0,0,0,0.3); position: relative; overflow: hidden;
        }
        .premium-stat-value-unit {
            color: rgba(255,255,255,0.5); font-weight: 500; text-transform: uppercase;
        }
        .premium-stat-value-slash {
            color: rgba(255,255,255,0.3);
        }
        .premium-account-model-text {
            background: linear-gradient(90deg, #fff, rgba(255,255,255,0.7)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .premium-info-group {
            background: rgba(15,23,42,0.4); border: 1px solid rgba(255,255,255,0.05); padding: 1.25rem 1.5rem; border-radius: 16px; display: flex; flex-direction: column; gap: 0.25rem;
        }
        .premium-tool-card {
            background: rgba(15,23,42,0.4); border: 1px solid rgba(255,255,255,0.1); box-shadow: none; position: relative; overflow: hidden; display: flex; flex-direction: column;
        }
        .premium-tool-card.unlocked {
            border-color: rgba(16,185,129,0.4); box-shadow: 0 0 20px rgba(16,185,129,0.05);
        }
        .premium-tool-icon {
            background: linear-gradient(135deg, rgba(255,255,255,0.05), rgba(255,255,255,0.01)); border: 1px solid rgba(255,255,255,0.1); box-shadow: inset 0 0 10px rgba(255,255,255,0.02);
        }
        .premium-action-cost {
            background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05);
        }
        .premium-unlock-btn {
            background: linear-gradient(135deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02)); border: 1px solid rgba(255,255,255,0.1); color: var(--text-main); font-size: 0.85rem; padding: 0.8rem 1rem; width: 100%; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.3s;
        }
        .premium-unlock-btn:hover {
            background: linear-gradient(135deg, #a855f7, #6366f1); border-color: transparent;
        }
        .premium-coupon-block {
            background: linear-gradient(145deg, rgba(245,158,11,0.05), rgba(245,158,11,0.01)); border: 1px solid rgba(245,158,11,0.3); padding: 2rem; border-radius: 20px; position: relative; overflow: hidden; margin-bottom: 2rem; box-shadow: 0 10px 30px rgba(245,158,11,0.05);
        }
        .premium-coupon-input {
            background: rgba(15,23,42,0.6); padding: 1rem 1.25rem; border-radius: 12px; color: var(--text-main); font-family: monospace; font-size: 1.1rem; letter-spacing: 2px; text-transform: uppercase; outline: none; transition: all 0.3s; box-shadow: inset 0 2px 10px rgba(0,0,0,0.2); width: 100%;
        }
        .premium-locked-badge {
            background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); color: var(--text-muted);
        }

        /* GENERIC TOOL DASHBOARD UI CLASSES */
        .premium-generic-input {
            width: 100%; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); 
            border-radius: 1rem; padding: 1rem 1.5rem; color: var(--text-main); transition: all 0.3s ease; resize: none;
        }
        .premium-generic-input:focus {
            outline: none; border-color: rgba(0, 168, 230, 0.5); box-shadow: 0 0 20px rgba(0, 168, 230, 0.1);
        }
        .premium-generic-output {
            background: rgba(0, 0, 0, 0.4); border: 1px solid rgba(255, 255, 255, 0.05); 
            border-radius: 1rem; padding: 1.5rem; color: var(--text-main); font-weight: 500;
            white-space: pre-wrap; line-height: 1.6;
        }
        .premium-generic-feature {
            background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1rem; transition: all 0.3s ease;
        }
        .premium-generic-feature:hover {
            border-color: rgba(255, 255, 255, 0.2); transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        /* LIGHT MODE OVERRIDES FOR PREMIUM UI */
        html[data-theme="light"] .premium-profile-widget {
            background: linear-gradient(145deg, rgba(255,255,255,0.9), rgba(255,255,255,0.6)); border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        html[data-theme="light"] .premium-profile-avatar {
            border: 3px solid rgba(255,255,255,0.8); box-shadow: 0 0 20px rgba(0, 168, 230,0.2);
        }
        html[data-theme="light"] .premium-stat-card {
            background: linear-gradient(145deg, rgba(255,255,255,0.9), rgba(255,255,255,0.6)); box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        html[data-theme="light"] .premium-stat-value-unit {
            color: rgba(15,23,42,0.5);
        }
        html[data-theme="light"] .premium-stat-value-slash {
            color: rgba(15,23,42,0.3);
        }
        html[data-theme="light"] .premium-account-model-text {
            background: linear-gradient(90deg, #0f172a, rgba(15,23,42,0.7)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        html[data-theme="light"] .premium-info-group {
            background: rgba(255,255,255,0.6); border: 1px solid rgba(0,0,0,0.05);
        }
        html[data-theme="light"] .premium-tool-card {
            background: rgba(255,255,255,0.6);
        }
        html[data-theme="light"] .premium-tool-card.unlocked {
            border-color: rgba(16,185,129,0.3) !important; background: rgba(255,255,255,0.9);
        }
        html[data-theme="light"] .premium-tool-icon {
            background: linear-gradient(135deg, rgba(255,255,255,0.8), rgba(255,255,255,0.4)); border: 1px solid rgba(0,0,0,0.05); text-shadow: none !important; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        html[data-theme="light"] .premium-action-cost {
            background: rgba(15,23,42,0.05); border: 1px solid rgba(0,0,0,0.05);
        }
        html[data-theme="light"] .premium-unlock-btn {
            background: linear-gradient(135deg, rgba(0,0,0,0.03), rgba(0,0,0,0.01)); border: 1px solid rgba(0,0,0,0.1); color: var(--text-main);
        }
        html[data-theme="light"] .premium-unlock-btn:hover {
            color: #fff;
        }
        html[data-theme="light"] .premium-coupon-block {
            background: linear-gradient(145deg, rgba(245,158,11,0.1), rgba(245,158,11,0.02)); border-color: rgba(245,158,11,0.2);
        }
        html[data-theme="light"] .premium-coupon-input {
            background: rgba(255,255,255,0.8); box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);
        }
        html[data-theme="light"] .premium-locked-badge {
            background: rgba(15,23,42,0.05); border-color: rgba(15,23,42,0.1); color: var(--text-muted);
        }
        html[data-theme="light"] .premium-generic-input {
            background: rgba(255, 255, 255, 0.8); border-color: rgba(15, 23, 42, 0.1); color: var(--text-main);
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.02);
        }
        html[data-theme="light"] .premium-generic-output {
            background: rgba(255, 255, 255, 0.9); border-color: rgba(15, 23, 42, 0.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        html[data-theme="light"] .premium-generic-feature {
            background: rgba(255, 255, 255, 0.8); border-color: rgba(15, 23, 42, 0.1);
        }
        html[data-theme="light"] .premium-generic-feature:hover {
            border-color: rgba(0, 168, 230, 0.3); box-shadow: 0 10px 20px rgba(0, 168, 230, 0.1);
        }
    </style>
