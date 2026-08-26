                <div class="content-panel" id="settings" style="display: none;">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fas fa-cog"></i> Account Settings</h2>
                    </div>

                    <form action="/dashboard/settings" method="POST" enctype="multipart/form-data" style="max-width: 600px;" onsubmit="return combinePhoneNumber()">
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

                        <!-- Profile Picture Upload Section -->
                        <div style="margin-bottom: 2rem; padding: 1.5rem; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--glass-border); border-radius: 14px; display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
                            <div style="position: relative;">
                                <div id="avatarPreviewContainer" style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; border: 2.5px solid var(--primary-cyan); background: linear-gradient(135deg, var(--primary-cyan), #0070e0); display: flex; align-items: center; justify-content: center; font-size: 2.2rem; font-family: var(--font-heading); font-weight: 800; color: #000; box-shadow: 0 0 15px rgba(0, 168, 230, 0.25);">
                                    @if($user->avatar_url)
                                        <img id="avatarPreviewImg" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <span id="avatarPreviewInitial">{{ substr($user->name, 0, 1) }}</span>
                                        <img id="avatarPreviewImg" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                    @endif
                                </div>
                            </div>
                            <div style="flex: 1; min-width: 200px;">
                                <label style="display: block; color: var(--text-main); font-weight: 700; font-size: 0.95rem; margin-bottom: 0.25rem;">Profile Picture</label>
                                <p style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.8rem;">Upload a custom avatar (JPG, PNG, or WEBP - Max 3MB).</p>
                                
                                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                                    <label for="avatarInput" style="display: inline-flex; align-items: center; gap: 0.5rem; background: var(--primary-cyan); color: #000; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.82rem; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                                        <i class="fas fa-camera"></i>
                                        <span>Change Photo</span>
                                    </label>
                                    <input type="file" name="avatar" id="avatarInput" accept="image/png, image/jpeg, image/webp" style="display: none;" onchange="handleAvatarPreview(this)">
                                    
                                    @if($user->avatar_url)
                                        <label style="display: inline-flex; align-items: center; gap: 0.4rem; color: #ff6b6b; font-size: 0.82rem; font-weight: 600; cursor: pointer; padding: 0.5rem 0.75rem; background: rgba(255, 107, 107, 0.1); border-radius: 8px;">
                                            <input type="checkbox" name="remove_avatar" value="1" id="removeAvatarCheck" onchange="toggleRemoveAvatar(this)">
                                            <i class="fas fa-trash-alt"></i>
                                            <span>Remove</span>
                                        </label>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <script>
                        function handleAvatarPreview(input) {
                            if (input.files && input.files[0]) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const img = document.getElementById('avatarPreviewImg');
                                    const initial = document.getElementById('avatarPreviewInitial');
                                    if (img) {
                                        img.src = e.target.result;
                                        img.style.display = 'block';
                                    }
                                    if (initial) {
                                        initial.style.display = 'none';
                                    }
                                };
                                reader.readAsDataURL(input.files[0]);
                            }
                        }

                        function toggleRemoveAvatar(checkbox) {
                            const img = document.getElementById('avatarPreviewImg');
                            const initial = document.getElementById('avatarPreviewInitial');
                            if (checkbox.checked) {
                                if (img) img.style.opacity = '0.3';
                            } else {
                                if (img) img.style.opacity = '1';
                            }
                        }
                        </script>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text-main); padding: 1rem; border-radius: 8px; font-family: inherit;">
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Country</label>
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

                        @php
                            $rawPhone = (string) old('phone', $user->phone ?? '');
                            $localPhone = $rawPhone;
                            $countryDialMap = [
                                'Egypt' => '+20', 'Saudi Arabia' => '+966', 'United Arab Emirates' => '+971',
                                'Kuwait' => '+965', 'Qatar' => '+974', 'Bahrain' => '+973', 'Oman' => '+968',
                                'Jordan' => '+962', 'Iraq' => '+964', 'Lebanon' => '+961', 'Palestine' => '+970',
                                'Syria' => '+963', 'Libya' => '+218', 'Tunisia' => '+216', 'Algeria' => '+213',
                                'Morocco' => '+212', 'Sudan' => '+249', 'Yemen' => '+967', 'Turkey' => '+90',
                                'United States' => '+1', 'United Kingdom' => '+44', 'Germany' => '+49',
                                'France' => '+33', 'India' => '+91', 'Pakistan' => '+92', 'Nigeria' => '+234',
                                'South Africa' => '+27', 'Brazil' => '+55', 'Canada' => '+1', 'Australia' => '+61',
                                'Malaysia' => '+60', 'Indonesia' => '+62',
                            ];
                            $selectedCountry = old('country', $user->country ?? 'Egypt');
                            $currentDial = $countryDialMap[$selectedCountry] ?? '';
                            if ($currentDial && str_starts_with($localPhone, $currentDial)) {
                                $localPhone = substr($localPhone, strlen($currentDial));
                            }
                        @endphp

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Phone Number</label>
                            <div style="display: flex; align-items: center; background: rgba(255,255,255,0.05); border: 1px solid {{ $user->phone ? 'var(--glass-border)' : 'rgba(255, 170, 0, 0.5)' }}; border-radius: 8px; overflow: hidden;">
                                <span id="phonePrefix" style="padding: 0.75rem 0.5rem 0.75rem 1rem; color: var(--text-main); font-size: 1.1rem; white-space: nowrap; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; min-width: 100px;">
                                    <span id="phoneFlagEmoji" style="font-size: 1.3rem;">🌍</span>
                                    <span id="phoneDialCode" style="color: var(--primary-cyan);">+00</span>
                                </span>
                                <div style="width: 1px; height: 28px; background: var(--glass-border);"></div>
                                <input type="tel" name="phone" id="phoneInput" value="{{ $localPhone }}" required placeholder="1012345678" style="flex: 1; background: none; border: none; color: var(--text-main); padding: 1rem; font-family: inherit; outline: none; font-size: 1rem; letter-spacing: 0.5px;">
                            </div>
                            <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.3rem; display: block;">Enter your local number without the country code</small>
                        </div>

                        <div style="margin-bottom: 2rem;">
                            <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Email Address (Cannot be changed)</label>
                            <input type="email" value="{{ $user->email }}" disabled style="width: 100%; background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); color: var(--text-muted); padding: 1rem; border-radius: 8px; font-family: inherit; cursor: not-allowed;">
                        </div>

                        <div style="border-top: 1px solid var(--glass-border); padding-top: 2rem; margin-top: 2rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem;">
                                <h3 style="font-family: var(--font-heading); font-size: 1.1rem; margin: 0; color: var(--primary-cyan); display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-lock text-sm"></i> Security & Password
                                </h3>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">Leave blank if you do not wish to change your password</span>
                            </div>
                            
                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600;">Current Password <span style="font-size: 0.75rem; color: var(--text-dim);">(Required only when changing password)</span></label>
                                <div style="position: relative;">
                                    <input type="password" id="userCurrentPassword" name="current_password" autocomplete="current-password" placeholder="Enter your current password" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text-main); padding: 0.9rem 3rem 0.9rem 1rem; border-radius: 10px; font-family: inherit; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary-cyan)'" onblur="this.style.borderColor='var(--glass-border)'">
                                    <button type="button" onclick="togglePasswordVisibility('userCurrentPassword', 'eyeCurrentPassword')" aria-label="Toggle password visibility" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px;">
                                        <i class="fas fa-eye" id="eyeCurrentPassword"></i>
                                    </button>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
                                <div>
                                    <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600;">New Password <span style="font-size: 0.75rem; color: var(--text-dim);">(Min. 8 characters)</span></label>
                                    <div style="position: relative;">
                                        <input type="password" id="userNewPassword" name="password" autocomplete="new-password" minlength="8" placeholder="Enter new strong password" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text-main); padding: 0.9rem 3rem 0.9rem 1rem; border-radius: 10px; font-family: inherit; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary-cyan)'" onblur="this.style.borderColor='var(--glass-border)'">
                                        <button type="button" onclick="togglePasswordVisibility('userNewPassword', 'eyeNewPassword')" aria-label="Toggle password visibility" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px;">
                                            <i class="fas fa-eye" id="eyeNewPassword"></i>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600;">Confirm New Password</label>
                                    <div style="position: relative;">
                                        <input type="password" id="userConfirmPassword" name="password_confirmation" autocomplete="new-password" placeholder="Re-type new password" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text-main); padding: 0.9rem 3rem 0.9rem 1rem; border-radius: 10px; font-family: inherit; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary-cyan)'" onblur="this.style.borderColor='var(--glass-border)'">
                                        <button type="button" onclick="togglePasswordVisibility('userConfirmPassword', 'eyeConfirmPassword')" aria-label="Toggle password visibility" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px;">
                                            <i class="fas fa-eye" id="eyeConfirmPassword"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="vn-btn vn-btn-primary" style="margin-top: 1rem; width: 100%; max-width: 280px; padding: 0.9rem 2rem; border-radius: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                            <i class="fas fa-save"></i> Save Account Changes
                        </button>
                    </form>
                </div>

                <script>
                    function togglePasswordVisibility(inputId, iconId) {
                        const input = document.getElementById(inputId);
                        const icon = document.getElementById(iconId);
                        if (!input || !icon) return;
                        if (input.type === 'password') {
                            input.type = 'text';
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                            icon.style.color = 'var(--primary-cyan)';
                        } else {
                            input.type = 'password';
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                            icon.style.color = 'var(--text-muted)';
                        }
                    }
                </script>
