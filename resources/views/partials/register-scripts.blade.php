    <script>
        function syncPhoneDialCode() {
            const select = document.getElementById('regCountry');
            const prefix = document.getElementById('regPhonePrefix');
            const selectedOption = select.options[select.selectedIndex];
            if(selectedOption && selectedOption.dataset.dial) {
                const dial = selectedOption.dataset.dial;
                prefix.value = dial; // Updated to .value since it's an input now

                const input = document.getElementById('regPhoneInput');
                if (dial === '+20') input.placeholder = '10XXXXXXXX';
                else if (dial === '+966' || dial === '+971') input.placeholder = '5XXXXXXXX';
                else if (dial === '+965') input.placeholder = 'XXXXXXXX';
                else input.placeholder = 'Phone Number';
            } else {
                prefix.value = '+';
            }
        }

        function validatePhoneLength() {
            const select = document.getElementById('regCountry');
            const input = document.getElementById('regPhoneInput');
            const dial = select.options[select.selectedIndex].getAttribute('data-dial');
            let val = input.value.trim();
            if (val.startsWith('0')) val = val.substring(1);
            
            const lengths = { '+20': 10, '+966': 9, '+971': 9, '+965': 8 };
            if (lengths[dial] && val.length !== lengths[dial]) {
                alert(`Please enter a valid ${lengths[dial]}-digit number for ${select.value}.`);
                return false;
            }
            return true;
        }

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            if (!validatePhoneLength()) {
                e.preventDefault();
                return;
            }
            const dial = document.getElementById('regPhonePrefix').value;
            const input = document.getElementById('regPhoneInput');
            let val = input.value.trim();
            if (val.startsWith('0')) val = val.substring(1);
        });

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

        document.addEventListener('DOMContentLoaded', function() {
            // ── Custom Searchable Country Picker ──
            const select = document.getElementById('regCountry');
            const wrapper = document.createElement('div');
            wrapper.className = 'country-picker';

            // Build options data from the hidden select
            const options = Array.from(select.options).map(o => ({
                value: o.value, text: o.textContent, dial: o.dataset.dial,
                flag: o.textContent.split(' ')[0], name: o.textContent.split(' ').slice(1).join(' ')
            }));

            const first = options[0];

            wrapper.innerHTML = `
                <div class="country-picker-trigger" tabindex="0">
                    <span class="cp-flag">${first.flag}</span>
                    <span class="cp-name">${first.name}</span>
                    <i class="fas fa-chevron-down cp-arrow"></i>
                </div>
                <div class="country-picker-dropdown">
                    <div class="cp-search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search country..." autocomplete="off">
                    </div>
                    <div class="cp-options"></div>
                </div>
            `;

            select.style.display = 'none';
            select.parentNode.insertBefore(wrapper, select);

            const trigger = wrapper.querySelector('.country-picker-trigger');
            const dropdown = wrapper.querySelector('.country-picker-dropdown');
            const searchInput = wrapper.querySelector('.cp-search-wrap input');
            const optionsContainer = wrapper.querySelector('.cp-options');

            function renderOptions(filter = '') {
                const f = filter.toLowerCase();
                const filtered = options.filter(o => o.name.toLowerCase().includes(f) || o.dial.includes(f));
                if (!filtered.length) {
                    optionsContainer.innerHTML = '<div class="cp-no-results">No countries found</div>';
                    return;
                }
                optionsContainer.innerHTML = filtered.map(o =>
                    `<div class="cp-option" data-value="${o.value}" data-dial="${o.dial}" data-flag="${o.flag}" data-name="${o.name}">
                        <span class="cp-opt-flag">${o.flag}</span>
                        <span>${o.name}</span>
                        <span class="cp-opt-dial">${o.dial}</span>
                    </div>`
                ).join('');
            }

            function selectOption(value, dial, flag, name) {
                select.value = value;
                trigger.querySelector('.cp-flag').textContent = flag;
                trigger.querySelector('.cp-name').textContent = name;
                wrapper.classList.remove('open');
                searchInput.value = '';
                syncPhoneDialCode();
            }

            trigger.addEventListener('click', () => {
                wrapper.classList.toggle('open');
                if (wrapper.classList.contains('open')) {
                    renderOptions();
                    setTimeout(() => searchInput.focus(), 50);
                }
            });

            searchInput.addEventListener('input', () => renderOptions(searchInput.value));

            optionsContainer.addEventListener('click', (e) => {
                const opt = e.target.closest('.cp-option');
                if (opt) selectOption(opt.dataset.value, opt.dataset.dial, opt.dataset.flag, opt.dataset.name);
            });

            document.addEventListener('click', (e) => {
                if (!wrapper.contains(e.target)) wrapper.classList.remove('open');
            });

            // Handle keyboard nav
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') wrapper.classList.remove('open');
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const first = optionsContainer.querySelector('.cp-option');
                    if (first) selectOption(first.dataset.value, first.dataset.dial, first.dataset.flag, first.dataset.name);
                }
            });

            renderOptions();
            syncPhoneDialCode();
        });
    </script>
