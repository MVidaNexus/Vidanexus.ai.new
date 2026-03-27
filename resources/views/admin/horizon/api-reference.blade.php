@extends('admin.horizon.layout')

@section('title', 'API Key Reference')

@section('content')
<div style="max-width: 1100px; margin: 0 auto; color: var(--text-main);">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--horizon-border);">
        <div>
            <h2 style="margin: 0; font-family: 'Space+Grotesk', sans-serif;">Site-Wide API Reference</h2>
            <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Comprehensive view of all credentials stored in <code>.env</code> and Database.</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <span style="padding: 0.4rem 0.8rem; border-radius: 8px; font-size: 0.75rem; background: var(--horizon-primary-bg); color: var(--primary-admin);">
                <i class="fas fa-database"></i> {{ count($databaseKeys) + count($toolConfig) }} Database Entries
            </span>
            <span style="padding: 0.4rem 0.8rem; border-radius: 8px; font-size: 0.75rem; background: var(--horizon-secondary-bg); color: var(--secondary-admin);">
                <i class="fas fa-file-code"></i> {{ count($envKeys) }} .env Keys
            </span>
        </div>
    </div>

    <!-- Search Bar -->
    <div style="margin-bottom: 2rem; position: relative;">
        <i class="fas fa-search" style="position: absolute; left: 1rem; top: 1rem; color: var(--text-muted);"></i>
        <input type="text" id="apiSearch" placeholder="Search by key name, provider, or tool slug..." onkeyup="filterReference()" style="width: 100%; padding: 0.85rem 1rem 0.85rem 3rem; background: var(--horizon-card); border: 1px solid var(--horizon-border); border-radius: 12px; color: var(--text-main); font-size: 0.9rem; outline: none; transition: all 0.3s ease;">
    </div>

    <!-- 1. Global Infrastructure (.env) -->
    <div class="reference-section" style="margin-bottom: 3rem;">
        <h3 style="font-size: 0.9rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1.5px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-globe"></i> Global Infrastructure (.env)
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem;">
            @foreach($envKeys as $key => $value)
                <div class="ref-card" style="background: var(--horizon-card); border: 1px solid var(--horizon-border); border-radius: 16px; padding: 1.25rem; position: relative; overflow: hidden;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">{{ $key }}</div>
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                        <code style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; color: var(--primary-admin);">{{ !empty($value) ? substr($value, 0, 8) . '...' . substr($value, -4) : 'NOT SET' }}</code>
                        <div style="display: flex; gap: 0.5rem;">
                            <button onclick="copyToClipboard('{{ $value }}')" class="icon-btn" title="Copy Key"><i class="fas fa-copy"></i></button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 2. Tool AI Configurations (Database) -->
    <div class="reference-section" style="margin-bottom: 3rem;">
        <h3 style="font-size: 0.9rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1.5px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-robot"></i> Tool AI Routing (Database Override)
        </h3>
        <div style="background: var(--horizon-card); border: 1px solid var(--horizon-border); border-radius: 16px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead style="background: var(--horizon-nav-hover);">
                    <tr>
                        <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--horizon-border);">Tool Name / Slug</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--horizon-border);">Status</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--horizon-border);">Key Source</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--horizon-border);">Provider</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--horizon-border);">Model</th>
                        <th style="padding: 1rem; text-align: right; border-bottom: 1px solid var(--horizon-border);">Configuration</th>
                    </tr>
                </thead>
                <tbody id="toolTableBody">
                    @foreach($toolConfig as $slug => $config)
                        @php
                            $toolData = collect($tools)->where('slug', $slug)->first();
                            $toolName = $toolData['name'] ?? $slug;
                            $toolIcon = $toolData['icon'] ?? 'fa-cube';
                            $hasChain = !empty($config['ai_chain']);
                        @endphp
                        <tr class="tool-row" style="border-bottom: 1px solid var(--horizon-border); transition: 0.2s;">
                            <td style="padding: 1rem;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 28px; height: 28px; border-radius: 6px; background: var(--horizon-icon-bg); display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 0.8rem;">
                                        <i class="fas {{ $toolIcon }}"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-main);">{{ $toolName }}</div>
                                        <div style="font-size: 0.65rem; color: var(--text-muted); font-family: monospace;">{{ $slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 1rem;">
                                <span style="color: {{ ($config['is_active'] ?? true) ? 'var(--horizon-success)' : '#ff4b4b' }}">
                                    {{ ($config['is_active'] ?? true) ? 'Active' : 'Maintenance' }}
                                </span>
                            </td>
                            <td style="padding: 1rem;">
                                @if($hasChain)
                                    <span style="background: var(--horizon-primary-bg); color: var(--primary-admin); padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.7rem; font-weight: 600;">
                                        <i class="fas fa-link"></i> Custom (Chain)
                                    </span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.75rem;">
                                        <i class="fas fa-globe"></i> Global (.env)
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 1rem;">{{ $config['provider'] ?? 'env-default' }}</td>
                            <td style="padding: 1rem;"><code style="font-size: 0.75rem;">{{ $config['model'] ?? '-' }}</code></td>
                            <td style="padding: 1rem; text-align: right;">
                                @if($hasChain)
                                    <span style="background: #e1f5fe; color: #01579b; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.7rem;">MULTI-FAILOVER</span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.7rem;">Single Strategy</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Miscellaneous API Keys (Database) -->
    <div class="reference-section" style="margin-bottom: 3rem;">
        <h3 style="font-size: 0.9rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1.5px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-key"></i> Other API Keys & Secrets (Database)
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem;" id="miscKeys">
            @foreach($databaseKeys as $key => $value)
                <div class="ref-card misc-row" style="background: var(--horizon-card); border: 1px solid var(--horizon-border); border-radius: 16px; padding: 1.25rem;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">{{ $key }}</div>
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                        <code style="font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; color: var(--text-main); break-all;">{{ is_array($value) ? '[Array/JSON]' : (strlen($value) > 20 ? substr($value, 0, 10) . '...' : $value) }}</code>
                        <button onclick="copyToClipboard('{{ $value }}')" class="icon-btn"><i class="fas fa-copy"></i></button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
    .icon-btn {
        background: var(--horizon-nav-hover);
        border: 1px solid var(--horizon-border);
        color: var(--text-main);
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }
    .icon-btn:hover {
        background: var(--primary-admin);
        color: #fff;
        border-color: var(--primary-admin);
    }
    .ref-card:hover {
        border-color: var(--primary-admin) !important;
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .tool-row:hover {
        background: var(--horizon-nav-hover);
    }
</style>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Copied to clipboard');
        });
    }

    function filterReference() {
        const query = document.getElementById('apiSearch').value.toLowerCase();
        
        // Filter Tool Table
        const toolRows = document.querySelectorAll('.tool-row');
        toolRows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });

        // Filter Misc Cards
        const miscRows = document.querySelectorAll('.misc-row');
        miscRows.forEach(card => {
            const text = card.innerText.toLowerCase();
            card.style.display = text.includes(query) ? '' : 'none';
        });
    }
</script>
@endsection
