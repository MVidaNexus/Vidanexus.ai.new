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
