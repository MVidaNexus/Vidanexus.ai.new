@extends('admin.horizon.layout')

@section('title', 'API & Connectivity Management')

@section('content')
<div style="max-width: 1000px; margin: 0 auto;">
    
    <!-- Tab Navigation -->
    <div style="display: flex; gap: 1rem; margin-bottom: 2rem; background: var(--horizon-card); padding: 0.5rem; border-radius: 16px; border: 1px solid var(--horizon-border);">
        <button onclick="switchTab('global-config')" id="tab-global-config" class="tab-btn active">
            <i class="fas fa-globe"></i> Global Infrastructure (.env)
        </button>
        <button onclick="switchTab('ai-routing')" id="tab-ai-routing" class="tab-btn">
            <i class="fas fa-microchip"></i> AI Routing Matrix
        </button>
    </div>

    <!-- Tab 1: Global Config -->
    <div id="content-global-config" class="tab-panel active">
        <div class="card-admin">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--horizon-border);">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--horizon-primary-bg); display: flex; align-items: center; justify-content: center; color: var(--primary-admin); font-size: 1.2rem;">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h2 style="margin: 0; font-family: 'Space+Grotesk', sans-serif;">Platform-Wide General Keys</h2>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">These keys are stored in the <code>.env</code> file and serve as the default for all tools.</p>
                </div>
            </div>

            @if(session('error'))
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem;">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('admin.horizon.api-keys.update') }}" method="POST">
                @csrf
                <div style="display: flex; flex-direction: column; gap: 2rem; margin-bottom: 3rem;">
                    
                    <!-- Payment Gateway (Fawaterk) -->
                    <div style="background: var(--horizon-nav-hover); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--horizon-border); border-left: 4px solid #f68b1e;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem;">
                            <div style="width: 32px; height: 32px; background: #f68b1e; color: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div>
                                <h3 style="margin: 0; font-size: 1rem; color: var(--text-main);">Payment Gateway: Fawaterk</h3>
                                <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted);">Main payment processing credentials used across the platform.</p>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div class="input-container">
                                <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.75rem;">Fawaterk API Key</label>
                                <div style="position: relative; display: flex; align-items: center;">
                                    <i class="fas fa-plug" style="position: absolute; left: 1rem; color: var(--text-muted); font-size: 0.8rem;"></i>
                                    <input type="password" name="FAWATERK_API_KEY" value="{{ $keys['FAWATERK_API_KEY'] }}" class="api-input" style="width: 100%; padding: 0.85rem 1rem 0.85rem 2.75rem; background: var(--vn-input-bg); border: 1px solid var(--vn-input-border); border-radius: 12px; color: var(--text-main); font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; outline: none; transition: all 0.3s ease;">
                                    <button type="button" onclick="toggleVisibility(this)" style="position: absolute; right: 1rem; background: none; border: none; color: var(--text-muted); cursor: pointer;"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="input-container">
                                <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.75rem;">Fawaterk Vendor Key</label>
                                <div style="position: relative; display: flex; align-items: center;">
                                    <i class="fas fa-store" style="position: absolute; left: 1rem; color: var(--text-muted); font-size: 0.8rem;"></i>
                                    <input type="text" name="FAWATERK_VENDOR_KEY" value="{{ $keys['FAWATERK_VENDOR_KEY'] }}" class="api-input" style="width: 100%; padding: 0.85rem 1rem 0.85rem 2.75rem; background: var(--vn-input-bg); border: 1px solid var(--vn-input-border); border-radius: 12px; color: var(--text-main); font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; outline: none; transition: all 0.3s ease;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="padding-left: 1rem; border-left: 3px solid var(--primary-admin);">
                        <h4 style="margin: 0; font-size: 0.9rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1.5px;">AI Infrastructure Keys</h4>
                    </div>

                    @foreach(['OPENAI_API_KEY' => ['#10a37f', 'fas fa-robot', 'OpenAI'], 'GEMINI_API_KEY' => ['#4285f4', 'fas fa-bolt', 'Google Gemini'], 'OPENROUTER_API_KEY' => ['#7c3aed', 'fas fa-network-wired', 'OpenRouter']] as $id => $meta)
                    <div style="background: var(--horizon-nav-hover); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--horizon-border);">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem;">
                            <div style="width: 32px; height: 32px; background: {{ $meta[0] }}; color: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                                <i class="{{ $meta[1] }}"></i>
                            </div>
                            <h3 style="margin: 0; font-size: 1rem; color: var(--text-main);">{{ $meta[2] }} API Key</h3>
                        </div>
                        <div class="input-container">
                            <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.75rem;">Global Default Key</label>
                            <div style="position: relative; display: flex; align-items: center;">
                                <i class="fas fa-key" style="position: absolute; left: 1rem; color: var(--text-muted); font-size: 0.8rem;"></i>
                                <input type="password" name="{{ $id }}" value="{{ $keys[$id] }}" class="api-input" style="width: 100%; padding: 0.85rem 1rem 0.85rem 2.75rem; background: var(--vn-input-bg); border: 1px solid var(--vn-input-border); border-radius: 12px; color: var(--text-main); font-family: 'JetBrains Mono', monospace; font-size: 0.9rem; outline: none; transition: all 0.3s ease;">
                                <button type="button" onclick="toggleVisibility(this)" style="position: absolute; right: 1rem; background: none; border: none; color: var(--text-muted); cursor: pointer;"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn-save" style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem 3rem;">
                        <i class="fas fa-save"></i> Synchronize & Save .env
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab 2: AI Routing Table -->
    <div id="content-ai-routing" class="tab-panel">
        <div class="card-admin">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--horizon-secondary-bg); display: flex; align-items: center; justify-content: center; color: var(--secondary-admin); font-size: 1.2rem;">
                        <i class="fas fa-microchip"></i>
                    </div>
                    <div>
                        <h2 style="margin: 0; font-family: 'Space+Grotesk', sans-serif;">AI Intelligence Routing Matrix</h2>
                        <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Master overview of current Provider & Model mappings for each tool.</p>
                    </div>
                </div>
                <div style="position: relative; width: 300px;">
                    <i class="fas fa-search" style="position: absolute; left: 1rem; top: 1rem; color: var(--text-muted); font-size: 0.8rem;"></i>
                    <input type="text" id="toolSearch" onkeyup="filterTools()" placeholder="Search tools..." style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; background: var(--vn-input-bg); border: 1px solid var(--vn-input-border); border-radius: 10px; color: var(--text-main); font-size: 0.85rem; outline: none;">
                </div>
            </div>

            <div style="background: var(--horizon-primary-bg); border: 1px dashed rgba(14, 165, 233, 0.3); padding: 0.75rem 1.25rem; border-radius: 12px; margin-bottom: 2rem; font-size: 0.75rem; color: var(--primary-admin);">
                <i class="fas fa-info-circle mr-2"></i> To modify AI configurations or private API keys, navigate to the <strong>Individual Tool Control</strong> pages in the sidebar.
            </div>

            <!-- AI Model Reference Section -->
            <div style="margin-bottom: 2.5rem;">
                <h4 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1.5px; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-book"></i> Latest Model ID Reference
                </h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <!-- OpenAI -->
                    <div style="background: rgba(16, 163, 127, 0.05); border: 1px solid rgba(16, 163, 127, 0.1); padding: 1rem; border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="color: #10a37f; font-weight: 700; font-size: 0.75rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-robot"></i> OpenAI
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2); padding: 0.4rem 0.6rem; border-radius: 6px;">
                                    <code style="font-size: 0.7rem; color: #fff;">gpt-4o</code>
                                    <i class="fas fa-copy" style="cursor: pointer; font-size: 0.6rem; opacity: 0.5;" onclick="copyToClipboard('gpt-4o')"></i>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2); padding: 0.4rem 0.6rem; border-radius: 6px;">
                                    <code style="font-size: 0.7rem; color: #fff;">gpt-4o-mini</code>
                                    <i class="fas fa-copy" style="cursor: pointer; font-size: 0.6rem; opacity: 0.5;" onclick="copyToClipboard('gpt-4o-mini')"></i>
                                </div>
                            </div>
                        </div>
                        <a href="https://platform.openai.com/docs/models" target="_blank" style="font-size: 0.65rem; color: #10a37f; text-decoration: none; display: flex; align-items: center; gap: 0.35rem; opacity: 0.8;">
                            <i class="fas fa-external-link-alt"></i> View all OpenAI models
                        </a>
                    </div>

                    <!-- Gemini -->
                    <div style="background: rgba(66, 133, 244, 0.05); border: 1px solid rgba(66, 133, 244, 0.1); padding: 1rem; border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="color: #4285f4; font-weight: 700; font-size: 0.75rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-bolt"></i> Google Gemini
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2); padding: 0.4rem 0.6rem; border-radius: 6px;">
                                    <code style="font-size: 0.7rem; color: #fff;">gemini-1.5-pro</code>
                                    <i class="fas fa-copy" style="cursor: pointer; font-size: 0.6rem; opacity: 0.5;" onclick="copyToClipboard('gemini-1.5-pro')"></i>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2); padding: 0.4rem 0.6rem; border-radius: 6px;">
                                    <code style="font-size: 0.7rem; color: #fff;">gemini-1.5-flash</code>
                                    <i class="fas fa-copy" style="cursor: pointer; font-size: 0.6rem; opacity: 0.5;" onclick="copyToClipboard('gemini-1.5-flash')"></i>
                                </div>
                            </div>
                        </div>
                        <a href="https://ai.google.dev/gemini-api/docs/models/gemini" target="_blank" style="font-size: 0.65rem; color: #4285f4; text-decoration: none; display: flex; align-items: center; gap: 0.35rem; opacity: 0.8;">
                            <i class="fas fa-external-link-alt"></i> View all Gemini models
                        </a>
                    </div>

                    <!-- OpenRouter -->
                    <div style="background: rgba(124, 58, 237, 0.05); border: 1px solid rgba(124, 58, 237, 0.1); padding: 1rem; border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="color: #7c3aed; font-weight: 700; font-size: 0.75rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-network-wired"></i> OpenRouter
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2); padding: 0.4rem 0.6rem; border-radius: 6px;">
                                    <code style="font-size: 0.7rem; color: #fff;">openai/gpt-4o</code>
                                    <i class="fas fa-copy" style="cursor: pointer; font-size: 0.6rem; opacity: 0.5;" onclick="copyToClipboard('openai/gpt-4o')"></i>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2); padding: 0.4rem 0.6rem; border-radius: 6px;">
                                    <code style="font-size: 0.7rem; color: #fff;">anthropic/claude-3.5-sonnet</code>
                                    <i class="fas fa-copy" style="cursor: pointer; font-size: 0.6rem; opacity: 0.5;" onclick="copyToClipboard('anthropic/claude-3.5-sonnet')"></i>
                                </div>
                            </div>
                        </div>
                        <a href="https://openrouter.ai/models" target="_blank" style="font-size: 0.65rem; color: #7c3aed; text-decoration: none; display: flex; align-items: center; gap: 0.35rem; opacity: 0.8;">
                            <i class="fas fa-external-link-alt"></i> View all OpenRouter models
                        </a>
                    </div>
                </div>
            </div>

            <div style="border: 1px solid var(--horizon-border); border-radius: 16px; overflow: hidden; background: var(--horizon-nav-hover);">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--horizon-border); background: var(--horizon-nav-hover);">
                            <th style="padding: 1.25rem; text-align: left; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Tool / Service</th>
                            <th style="padding: 1.25rem; text-align: left; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Key Source</th>
                            <th style="padding: 1.25rem; text-align: left; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">AI Provider</th>
                            <th style="padding: 1.25rem; text-align: left; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Model Override</th>
                        </tr>
                    </thead>
                    <tbody id="toolTableBody">
                        @foreach($tools as $tool)
                            @php
                                $slug = $tool['slug'];
                                $config = $toolConfig[$slug] ?? [];
                                $hasChain = !empty($config['ai_chain']);
                                $hasPrivateSetting = !empty($config['api_key']);
                                
                                $currentProv = $config['provider'] ?? 'env-default';
                                $currentMod = $config['model'] ?? '-';
                                
                                $hasChainKey = false;
                                if ($hasChain && !empty($config['ai_chain'][0]['provider'])) {
                                    $currentProv = $config['ai_chain'][0]['provider'];
                                    $currentMod = $config['ai_chain'][0]['model'] ?? $currentMod;
                                    $hasChainKey = !empty($config['ai_chain'][0]['api_key']);
                                }

                                // It's only a "PRIVATE KEY" source if an actual key is provided at the tool level
                                $isPrivateKey = $hasPrivateSetting || $hasChainKey;
                            @endphp
                            <tr class="tool-row" style="border-bottom: 1px solid var(--horizon-border); transition: 0.2s;">
                                <td style="padding: 1.25rem;">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--horizon-icon-bg); display: flex; align-items: center; justify-content: center; color: {{ $tool['color'] ?? 'var(--text-muted)' }}; font-size: 0.9rem;">
                                            <i class="fas {{ $tool['icon'] ?? 'fa-cube' }}"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $tool['name'] ?? $slug }}</div>
                                            <div style="font-size: 0.65rem; color: var(--text-muted); font-family: monospace;">{{ $slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1.25rem;">
                                    @if($isPrivateKey)
                                        <span style="background: rgba(191, 0, 255, 0.1); color: #bf00ff; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.7rem; font-weight: 700; border: 1px solid rgba(191, 0, 255, 0.2);">
                                            <i class="fas fa-shield-alt mr-1"></i> PRIVATE KEY
                                        </span>
                                    @else
                                        <span style="background: rgba(14, 165, 233, 0.1); color: var(--primary-admin); padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.7rem; font-weight: 700; border: 1px solid rgba(14, 165, 233, 0.2);">
                                            <i class="fas fa-globe mr-1"></i> GLOBAL (.env)
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 1.25rem;">
                                    <div style="font-size: 0.85rem; color: var(--text-main); font-weight: 600; text-transform: uppercase;">
                                        {{ $currentProv }}
                                    </div>
                                </td>
                                <td style="padding: 1.25rem;">
                                    <code style="font-size: 0.8rem; background: rgba(255,255,255,0.05); padding: 0.4rem 0.8rem; border-radius: 8px; color: var(--primary-admin);">
                                        {{ $currentMod }}
                                    </code>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .tab-btn {
        flex: 1;
        padding: 0.75rem 1.5rem;
        border: none;
        background: none;
        color: var(--text-muted);
        font-weight: 600;
        cursor: pointer;
        border-radius: 12px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        font-family: 'Space+Grotesk', sans-serif;
    }
    .tab-btn.active {
        background: var(--horizon-primary-bg);
        color: var(--primary-admin);
        box-shadow: 0 4px 15px rgba(14, 165, 233, 0.1);
    }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; animation: fadeIn 0.4s ease; }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .api-input:focus { border-color: var(--primary-admin) !important; box-shadow: 0 0 15px rgba(14, 165, 233, 0.1) !important; }
    .tool-row:hover { background: var(--horizon-nav-hover); }
</style>

<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('tab-' + tabId).classList.add('active');
        document.getElementById('content-' + tabId).classList.add('active');
    }

    function toggleVisibility(btn) {
        const input = btn.parentElement.querySelector('input');
        const icon = btn.querySelector('i');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    function filterTools() {
        const query = document.getElementById('toolSearch').value.toLowerCase();
        document.querySelectorAll('.tool-row').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
        });
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            // Optional: Subtle toast could go here
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }
</script>
@endsection
