    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navItems = document.querySelectorAll('.dash-nav-item');
            const panels = {
                '#overview': document.getElementById('overview'),
                '#subscriptions': document.getElementById('subscriptions'),
                '#billing': document.getElementById('billing'),
                '#credits': document.getElementById('credits'),
                '#feedback': document.getElementById('feedback'),
                '#settings': document.getElementById('settings')
            };

            function showPanel(hash) {
                let targetHash = hash || '#overview';
                if (!panels[targetHash]) {
                    if (targetHash.includes('billing')) targetHash = '#billing';
                    else if (targetHash.includes('subscriptions')) targetHash = '#subscriptions';
                    else if (targetHash.includes('credits')) targetHash = '#credits';
                    else if (targetHash.includes('feedback')) targetHash = '#feedback';
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
            
            if (dialHidden && phoneInput) {
                let local = phoneInput.value.trim();
                const dial = dialHidden.value || '+20';
                
                if (local.startsWith('+')) {
                    return true;
                }
                
                // Remove leading zero if present
                if (local.startsWith('0')) {
                    local = local.substring(1);
                }
                
                const codeNoPlus = dial.replace('+', '');
                if (local.startsWith(codeNoPlus)) {
                    phoneInput.value = '+' + local;
                    return true;
                }

                phoneInput.value = dial + local;
            }
            return true;
        }

        // Initialize phone prefix on page load if country is already set
        document.addEventListener('DOMContentLoaded', function() {
            updatePhonePrefix();
        });
    </script>
