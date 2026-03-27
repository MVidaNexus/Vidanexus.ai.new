<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VidaNexus AI — Join the Future</title>
    <meta name="description" content="Join the future of intelligent automation. Register for VidaNexus AI.">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@700;800&family=Noto+Sans+Arabic:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style.v2.css?v=30') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    
    <style>
        :root {
            --font-arabic: 'Noto Sans Arabic', sans-serif;
        }
        body { font-family: 'Inter', var(--font-arabic), sans-serif; }
        
        .reg-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            max-width: 1100px;
            width: 100%;
            margin: 0 auto;
        }
        
        @media (min-width: 992px) {
            .reg-container { grid-template-columns: 1.2fr 1fr; align-items: start; }
        }

        .mkt-panel {
            padding: 2rem;
            color: #fff;
            text-align: left;
        }
        
        .mkt-title {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #fff 30%, var(--primary-cyan) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .benefit-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: flex-start;
        }
        .benefit-icon {
            width: 40px;
            height: 40px;
            background: rgba(14, 165, 233, 0.1);
            border: 1px solid rgba(14, 165, 233, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-cyan);
            flex-shrink: 0;
        }
        .benefit-text h4 { margin: 0 0 0.25rem 0; font-size: 1.1rem; color: var(--text-main); }
        .benefit-text p { margin: 0; font-size: 0.9rem; color: var(--text-muted); line-height: 1.5; }

        .reg-steps { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem; }
        .reg-step { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-muted); }
        .reg-step.active { color: var(--primary-cyan); font-weight: 700; }
        .reg-step .step-num { width: 24px; height: 24px; border-radius: 50%; border: 2px solid var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; }
        .reg-step.active .step-num { border-color: var(--primary-cyan); background: rgba(14, 165, 233, 0.1); }
        .reg-step.done { color: var(--accent-success); }
        .reg-step.done .step-num { border-color: var(--accent-success); background: rgba(0, 255, 170, 0.1); }

        .plan-selector { display: grid; grid-template-columns: 1fr; gap: 0.75rem; margin-bottom: 1.5rem; }
        @media (min-width: 640px) { .plan-selector { grid-template-columns: 1fr 1fr; } }
        
        .plan-option {
            position: relative; cursor: pointer;
            border: 1px solid rgba(255,255,255,0.1); border-radius: 12px;
            padding: 1rem; transition: all 0.2s ease;
            background: rgba(255,255,255,0.03);
        }
        .plan-option:hover { border-color: rgba(14, 165, 233, 0.3); background: rgba(14, 165, 233, 0.05); }
        .plan-option.selected { border-color: var(--primary-cyan); background: rgba(14, 165, 233, 0.1); }
        .plan-option.recommended { border: 1px solid rgba(191, 0, 255, 0.4); }
        .plan-option.recommended.selected { border-color: #bf00ff; background: rgba(191, 0, 255, 0.1); }
        
        .po-name { font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem; }
        .po-price { font-size: 0.85rem; color: var(--primary-cyan); }
        .po-desc { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; line-height: 1.3; }
        
        .step-hidden { display: none !important; }
        
        .arabic-hint {
            display: block;
            font-family: var(--font-arabic);
            font-size: 0.8rem;
            margin-top: 0.2rem;
            opacity: 0.7;
        }

        .pricing-hero {
            text-align: center;
            padding: clamp(3rem, 10vh, 6rem) 1.5rem clamp(2rem, 5vh, 4rem);
        }
        .pricing-title {
            font-family: var(--font-heading);
            font-size: clamp(2.2rem, 8vw, 4rem);
            margin-bottom: 1rem;
            color: var(--text-main);
            text-shadow: 0 0 30px var(--title-glow);
            line-height: 1.1;
        }
        .pricing-subtitle {
            color: var(--text-muted);
            font-size: clamp(1rem, 2vw, 1.25rem);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Custom Searchable Country Picker */
        .country-picker { position: relative; width: 100%; }
        .country-picker-trigger {
            display: flex; align-items: center; gap: 0.75rem;
            width: 100%; background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border); padding: 1rem 1.25rem;
            border-radius: 12px; color: var(--text-main); font-size: 1rem;
            font-family: inherit; cursor: pointer; transition: border-color 0.3s;
        }
        .country-picker-trigger:hover { border-color: var(--primary-cyan); }
        .country-picker-trigger .cp-flag { font-size: 1.3rem; }
        .country-picker-trigger .cp-name { flex: 1; }
        .country-picker-trigger .cp-arrow { color: var(--text-muted); font-size: 0.7rem; transition: transform 0.2s; }
        .country-picker.open .cp-arrow { transform: rotate(180deg); }
        .country-picker-dropdown {
            display: none; position: absolute; top: calc(100% + 6px); left: 0; right: 0;
            background: rgba(12, 17, 28, 0.97); border: 1px solid var(--glass-border);
            border-radius: 12px; box-shadow: 0 12px 40px rgba(0,0,0,0.5);
            backdrop-filter: blur(20px); z-index: 1000; overflow: hidden;
        }
        .country-picker.open .country-picker-dropdown { display: block; }
        .cp-search-wrap {
            padding: 0.75rem; border-bottom: 1px solid var(--glass-border);
        }
        .cp-search-wrap input {
            width: 100%; background: rgba(255,255,255,0.06); border: 1px solid var(--glass-border);
            border-radius: 8px; padding: 0.7rem 1rem 0.7rem 2.5rem; color: var(--text-main);
            font-size: 0.95rem; font-family: inherit; outline: none; transition: border-color 0.3s;
        }
        .cp-search-wrap input:focus { border-color: var(--primary-cyan); }
        .cp-search-wrap { position: relative; }
        .cp-search-wrap i { position: absolute; left: 1.5rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem; }
        .cp-options {
            max-height: 250px; overflow-y: auto; padding: 0.4rem;
        }
        .cp-options::-webkit-scrollbar { width: 5px; }
        .cp-options::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .cp-option {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.65rem 1rem; border-radius: 8px; cursor: pointer;
            color: var(--text-muted); transition: all 0.15s; font-size: 0.95rem;
        }
        .cp-option:hover, .cp-option.active {
            background: rgba(14,165,233,0.12); color: var(--primary-cyan);
        }
        .cp-option .cp-opt-flag { font-size: 1.2rem; }
        .cp-option .cp-opt-dial { margin-left: auto; font-size: 0.8rem; opacity: 0.5; }
        .cp-no-results { padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.9rem; }
    </style>
    <script>(function(){const t=localStorage.getItem("theme")||"dark";document.documentElement.setAttribute("data-theme",t);})();</script>
</head>
<body>
    <canvas id="techCanvas"></canvas>
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>

    <div class="main-container">
        @include('partials.header')

        <main class="hero" style="padding-top: 2rem;">
            <div class="reg-container">
                
                {{-- Left: Marketing Panel --}}
                <div class="mkt-panel">
                    <h1 class="mkt-title">Scale Your Vision with VidaNexus<br><span style="font-size: 1.4rem; font-weight: 400; opacity: 0.8;">The Premium AI Command Center</span></h1>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-satellite-dish"></i></div>
                        <div class="benefit-text">
                            <h4>Intelligent Market Radar</h4>
                            <p>Monitor competitors, scrape real-time trends, and dominate your niche with fully automated daily intelligence sweeps.</p>
                        </div>
                    </div>

                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-microchip"></i></div>
                        <div class="benefit-text">
                            <h4>Full-Stack Automation Hub</h4>
                            <p>Deploy our powerful suite of SEO auditors, OCR vision models, image compression, and AI content factories in one seamless workspace.</p>
                        </div>
                    </div>

                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-coins"></i></div>
                        <div class="benefit-text">
                            <h4>Pay-As-You-Grow Credits</h4>
                            <p>No rigid subscriptions. Our flexible credit packages ensure you only pay for exactly the compute power your enterprise consumes.</p>
                        </div>
                    </div>
                </div>

                {{-- Right: Form Panel --}}
                <div class="glass-panel" style="padding: 2.5rem; width: 100%;">
                    
                    {{-- Progress (Simplified) --}}
                    <div class="reg-steps">
                        <div class="reg-step active">
                            <span class="step-num">1</span>
                            <span>Initialize Workspace</span>
                        </div>
                    </div>

                    <form action="/register" method="POST" id="registerForm">
                        @csrf
                        <input type="hidden" name="selected_plan" value="beginner">
                        @if(request()->has('redirect'))
                            <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                        @endif

                        {{-- STEP 1: Account Info --}}
                        <div id="step1">
                            <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; color: var(--text-main);">Forge Your Administrator Profile</h2>
                            
                            <div style="margin-bottom: 1.25rem; text-align: left;">
                                <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Full Name</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="name" value="{{ old('name') }}" placeholder="John Doe" required>
                                </div>
                            </div>

                            <div style="margin-bottom: 1.25rem; text-align: left;">
                                <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Email Address</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required>
                                </div>
                            </div>

                            <div style="margin-bottom: 1.25rem; text-align: left;">
                                <div class="input-group">
                                    <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Country</label>
                                        <select name="country" id="regCountry" required onchange="syncPhoneDialCode()">
                                                                                                                                    <option value="Egypt" data-dial="+20">🇪🇬 Egypt</option>
                                            <option value="Afghanistan" data-dial="+93">🇦🇫 Afghanistan</option>
                                            <option value="Albania" data-dial="+355">🇦🇱 Albania</option>
                                            <option value="Algeria" data-dial="+213">🇩🇿 Algeria</option>
                                            <option value="American Samoa" data-dial="+1">🇦🇸 American Samoa</option>
                                            <option value="Andorra" data-dial="+376">🇦🇩 Andorra</option>
                                            <option value="Angola" data-dial="+244">🇦🇴 Angola</option>
                                            <option value="Anguilla" data-dial="+1">🇦🇮 Anguilla</option>
                                            <option value="Antigua and Barbuda" data-dial="+1">🇦🇬 Antigua and Barbuda</option>
                                            <option value="Argentina" data-dial="+54">🇦🇷 Argentina</option>
                                            <option value="Armenia" data-dial="+374">🇦🇲 Armenia</option>
                                            <option value="Aruba" data-dial="+297">🇦🇼 Aruba</option>
                                            <option value="Australia" data-dial="+61">🇦🇺 Australia</option>
                                            <option value="Austria" data-dial="+43">🇦🇹 Austria</option>
                                            <option value="Azerbaijan" data-dial="+994">🇦🇿 Azerbaijan</option>
                                            <option value="Bahamas" data-dial="+1">🇧🇸 Bahamas</option>
                                            <option value="Bahrain" data-dial="+973">🇧🇭 Bahrain</option>
                                            <option value="Bangladesh" data-dial="+880">🇧🇩 Bangladesh</option>
                                            <option value="Barbados" data-dial="+1">🇧🇧 Barbados</option>
                                            <option value="Belarus" data-dial="+375">🇧🇾 Belarus</option>
                                            <option value="Belgium" data-dial="+32">🇧🇪 Belgium</option>
                                            <option value="Belize" data-dial="+501">🇧🇿 Belize</option>
                                            <option value="Benin" data-dial="+229">🇧🇯 Benin</option>
                                            <option value="Bermuda" data-dial="+1">🇧🇲 Bermuda</option>
                                            <option value="Bhutan" data-dial="+975">🇧🇹 Bhutan</option>
                                            <option value="Bolivia" data-dial="+591">🇧🇴 Bolivia</option>
                                            <option value="Bosnia and Herzegovina" data-dial="+387">🇧🇦 Bosnia and Herzegovina</option>
                                            <option value="Botswana" data-dial="+267">🇧🇼 Botswana</option>
                                            <option value="Brazil" data-dial="+55">🇧🇷 Brazil</option>
                                            <option value="British Indian Ocean Territory" data-dial="+246">🇮🇴 British Indian Ocean Territory</option>
                                            <option value="British Virgin Islands" data-dial="+1">🇻🇬 British Virgin Islands</option>
                                            <option value="Brunei" data-dial="+673">🇧🇳 Brunei</option>
                                            <option value="Bulgaria" data-dial="+359">🇧🇬 Bulgaria</option>
                                            <option value="Burkina Faso" data-dial="+226">🇧🇫 Burkina Faso</option>
                                            <option value="Burundi" data-dial="+257">🇧🇮 Burundi</option>
                                            <option value="Cambodia" data-dial="+855">🇰🇭 Cambodia</option>
                                            <option value="Cameroon" data-dial="+237">🇨🇲 Cameroon</option>
                                            <option value="Canada" data-dial="+1">🇨🇦 Canada</option>
                                            <option value="Cape Verde" data-dial="+238">🇨🇻 Cape Verde</option>
                                            <option value="Cayman Islands" data-dial="+1">🇰🇾 Cayman Islands</option>
                                            <option value="Central African Republic" data-dial="+236">🇨🇫 Central African Republic</option>
                                            <option value="Chad" data-dial="+235">🇹🇩 Chad</option>
                                            <option value="Chile" data-dial="+56">🇨🇱 Chile</option>
                                            <option value="China" data-dial="+86">🇨🇳 China</option>
                                            <option value="Christmas Island" data-dial="+61">🇨🇽 Christmas Island</option>
                                            <option value="Cocos Islands" data-dial="+61">🇨🇨 Cocos Islands</option>
                                            <option value="Colombia" data-dial="+57">🇨🇴 Colombia</option>
                                            <option value="Comoros" data-dial="+269">🇰🇲 Comoros</option>
                                            <option value="Cook Islands" data-dial="+682">🇨🇰 Cook Islands</option>
                                            <option value="Costa Rica" data-dial="+506">🇨🇷 Costa Rica</option>
                                            <option value="Croatia" data-dial="+385">🇭🇷 Croatia</option>
                                            <option value="Cuba" data-dial="+53">🇨🇺 Cuba</option>
                                            <option value="Curacao" data-dial="+599">🇨🇼 Curacao</option>
                                            <option value="Cyprus" data-dial="+357">🇨🇾 Cyprus</option>
                                            <option value="Czech Republic" data-dial="+420">🇨🇿 Czech Republic</option>
                                            <option value="Democratic Republic of the Congo" data-dial="+243">🇨🇩 Democratic Republic of the Congo</option>
                                            <option value="Denmark" data-dial="+45">🇩🇰 Denmark</option>
                                            <option value="Djibouti" data-dial="+253">🇩🇯 Djibouti</option>
                                            <option value="Dominica" data-dial="+1">🇩🇲 Dominica</option>
                                            <option value="Dominican Republic" data-dial="+1">🇩🇴 Dominican Republic</option>
                                            <option value="East Timor" data-dial="+670">🇹🇱 East Timor</option>
                                            <option value="Ecuador" data-dial="+593">🇪🇨 Ecuador</option>
                                            <option value="El Salvador" data-dial="+503">🇸🇻 El Salvador</option>
                                            <option value="Equatorial Guinea" data-dial="+240">🇬🇶 Equatorial Guinea</option>
                                            <option value="Eritrea" data-dial="+291">🇪🇷 Eritrea</option>
                                            <option value="Estonia" data-dial="+372">🇪🇪 Estonia</option>
                                            <option value="Ethiopia" data-dial="+251">🇪🇹 Ethiopia</option>
                                            <option value="Falkland Islands" data-dial="+500">🇫🇰 Falkland Islands</option>
                                            <option value="Faroe Islands" data-dial="+298">🇫🇴 Faroe Islands</option>
                                            <option value="Fiji" data-dial="+679">🇫🇯 Fiji</option>
                                            <option value="Finland" data-dial="+358">🇫🇮 Finland</option>
                                            <option value="France" data-dial="+33">🇫🇷 France</option>
                                            <option value="French Polynesia" data-dial="+689">🇵🇫 French Polynesia</option>
                                            <option value="Gabon" data-dial="+241">🇬🇦 Gabon</option>
                                            <option value="Gambia" data-dial="+220">🇬🇲 Gambia</option>
                                            <option value="Georgia" data-dial="+995">🇬🇪 Georgia</option>
                                            <option value="Germany" data-dial="+49">🇩🇪 Germany</option>
                                            <option value="Ghana" data-dial="+233">🇬🇭 Ghana</option>
                                            <option value="Gibraltar" data-dial="+350">🇬🇮 Gibraltar</option>
                                            <option value="Greece" data-dial="+30">🇬🇷 Greece</option>
                                            <option value="Greenland" data-dial="+299">🇬🇱 Greenland</option>
                                            <option value="Grenada" data-dial="+1">🇬🇩 Grenada</option>
                                            <option value="Guam" data-dial="+1">🇬🇺 Guam</option>
                                            <option value="Guatemala" data-dial="+502">🇬🇹 Guatemala</option>
                                            <option value="Guernsey" data-dial="+44">🇬🇬 Guernsey</option>
                                            <option value="Guinea" data-dial="+224">🇬🇳 Guinea</option>
                                            <option value="Guinea-Bissau" data-dial="+245">🇬🇼 Guinea-Bissau</option>
                                            <option value="Guyana" data-dial="+592">🇬🇾 Guyana</option>
                                            <option value="Haiti" data-dial="+509">🇭🇹 Haiti</option>
                                            <option value="Honduras" data-dial="+504">🇭🇳 Honduras</option>
                                            <option value="Hong Kong" data-dial="+852">🇭🇰 Hong Kong</option>
                                            <option value="Hungary" data-dial="+36">🇭🇺 Hungary</option>
                                            <option value="Iceland" data-dial="+354">🇮🇸 Iceland</option>
                                            <option value="India" data-dial="+91">🇮🇳 India</option>
                                            <option value="Indonesia" data-dial="+62">🇮🇩 Indonesia</option>
                                            <option value="Iran" data-dial="+98">🇮🇷 Iran</option>
                                            <option value="Iraq" data-dial="+964">🇮🇶 Iraq</option>
                                            <option value="Ireland" data-dial="+353">🇮🇪 Ireland</option>
                                            <option value="Isle of Man" data-dial="+44">🇮🇲 Isle of Man</option>
                                            <option value="Israel" data-dial="+972">🇮🇱 Israel</option>
                                            <option value="Italy" data-dial="+39">🇮🇹 Italy</option>
                                            <option value="Ivory Coast" data-dial="+225">🇨🇮 Ivory Coast</option>
                                            <option value="Jamaica" data-dial="+1">🇯🇲 Jamaica</option>
                                            <option value="Japan" data-dial="+81">🇯🇵 Japan</option>
                                            <option value="Jersey" data-dial="+44">🇯🇪 Jersey</option>
                                            <option value="Jordan" data-dial="+962">🇯🇴 Jordan</option>
                                            <option value="Kazakhstan" data-dial="+7">🇰🇿 Kazakhstan</option>
                                            <option value="Kenya" data-dial="+254">🇰🇪 Kenya</option>
                                            <option value="Kiribati" data-dial="+686">🇰🇮 Kiribati</option>
                                            <option value="Kosovo" data-dial="+383">🇽🇰 Kosovo</option>
                                            <option value="Kuwait" data-dial="+965">🇰🇼 Kuwait</option>
                                            <option value="Kyrgyzstan" data-dial="+996">🇰🇬 Kyrgyzstan</option>
                                            <option value="Laos" data-dial="+856">🇱🇦 Laos</option>
                                            <option value="Latvia" data-dial="+371">🇱🇻 Latvia</option>
                                            <option value="Lebanon" data-dial="+961">🇱🇧 Lebanon</option>
                                            <option value="Lesotho" data-dial="+266">🇱🇸 Lesotho</option>
                                            <option value="Liberia" data-dial="+231">🇱🇷 Liberia</option>
                                            <option value="Libya" data-dial="+218">🇱🇾 Libya</option>
                                            <option value="Liechtenstein" data-dial="+423">🇱🇮 Liechtenstein</option>
                                            <option value="Lithuania" data-dial="+370">🇱🇹 Lithuania</option>
                                            <option value="Luxembourg" data-dial="+352">🇱🇺 Luxembourg</option>
                                            <option value="Macau" data-dial="+853">🇲🇴 Macau</option>
                                            <option value="Macedonia" data-dial="+389">🇲🇰 Macedonia</option>
                                            <option value="Madagascar" data-dial="+261">🇲🇬 Madagascar</option>
                                            <option value="Malawi" data-dial="+265">🇲🇼 Malawi</option>
                                            <option value="Malaysia" data-dial="+60">🇲🇾 Malaysia</option>
                                            <option value="Maldives" data-dial="+960">🇲🇻 Maldives</option>
                                            <option value="Mali" data-dial="+223">🇲🇱 Mali</option>
                                            <option value="Malta" data-dial="+356">🇲🇹 Malta</option>
                                            <option value="Marshall Islands" data-dial="+692">🇲🇭 Marshall Islands</option>
                                            <option value="Mauritania" data-dial="+222">🇲🇷 Mauritania</option>
                                            <option value="Mauritius" data-dial="+230">🇲🇺 Mauritius</option>
                                            <option value="Mayotte" data-dial="+262">🇾🇹 Mayotte</option>
                                            <option value="Mexico" data-dial="+52">🇲🇽 Mexico</option>
                                            <option value="Micronesia" data-dial="+691">🇫🇲 Micronesia</option>
                                            <option value="Moldova" data-dial="+373">🇲🇩 Moldova</option>
                                            <option value="Monaco" data-dial="+377">🇲🇨 Monaco</option>
                                            <option value="Mongolia" data-dial="+976">🇲🇳 Mongolia</option>
                                            <option value="Montenegro" data-dial="+382">🇲🇪 Montenegro</option>
                                            <option value="Montserrat" data-dial="+1">🇲🇸 Montserrat</option>
                                            <option value="Morocco" data-dial="+212">🇲🇦 Morocco</option>
                                            <option value="Mozambique" data-dial="+258">🇲🇿 Mozambique</option>
                                            <option value="Myanmar" data-dial="+95">🇲🇲 Myanmar</option>
                                            <option value="Namibia" data-dial="+264">🇳🇦 Namibia</option>
                                            <option value="Nauru" data-dial="+674">🇳🇷 Nauru</option>
                                            <option value="Nepal" data-dial="+977">🇳🇵 Nepal</option>
                                            <option value="Netherlands" data-dial="+31">🇳🇱 Netherlands</option>
                                            <option value="Netherlands Antilles" data-dial="+599">🇦🇳 Netherlands Antilles</option>
                                            <option value="New Caledonia" data-dial="+687">🇳🇨 New Caledonia</option>
                                            <option value="New Zealand" data-dial="+64">🇳🇿 New Zealand</option>
                                            <option value="Nicaragua" data-dial="+505">🇳🇮 Nicaragua</option>
                                            <option value="Niger" data-dial="+227">🇳🇪 Niger</option>
                                            <option value="Nigeria" data-dial="+234">🇳🇬 Nigeria</option>
                                            <option value="Niue" data-dial="+683">🇳🇺 Niue</option>
                                            <option value="North Korea" data-dial="+850">🇰🇵 North Korea</option>
                                            <option value="Northern Mariana Islands" data-dial="+1">🇲🇵 Northern Mariana Islands</option>
                                            <option value="Norway" data-dial="+47">🇳🇴 Norway</option>
                                            <option value="Oman" data-dial="+968">🇴🇲 Oman</option>
                                            <option value="Pakistan" data-dial="+92">🇵🇰 Pakistan</option>
                                            <option value="Palau" data-dial="+680">🇵🇼 Palau</option>
                                            <option value="Palestine" data-dial="+970">🇵🇸 Palestine</option>
                                            <option value="Panama" data-dial="+507">🇵🇦 Panama</option>
                                            <option value="Papua New Guinea" data-dial="+675">🇵🇬 Papua New Guinea</option>
                                            <option value="Paraguay" data-dial="+595">🇵🇾 Paraguay</option>
                                            <option value="Peru" data-dial="+51">🇵🇪 Peru</option>
                                            <option value="Philippines" data-dial="+63">🇵🇭 Philippines</option>
                                            <option value="Pitcairn" data-dial="+64">🇵🇳 Pitcairn</option>
                                            <option value="Poland" data-dial="+48">🇵🇱 Poland</option>
                                            <option value="Portugal" data-dial="+351">🇵🇹 Portugal</option>
                                            <option value="Puerto Rico" data-dial="+1">🇵🇷 Puerto Rico</option>
                                            <option value="Qatar" data-dial="+974">🇶🇦 Qatar</option>
                                            <option value="Republic of the Congo" data-dial="+242">🇨🇬 Republic of the Congo</option>
                                            <option value="Reunion" data-dial="+262">🇷🇪 Reunion</option>
                                            <option value="Romania" data-dial="+40">🇷🇴 Romania</option>
                                            <option value="Russia" data-dial="+7">🇷🇺 Russia</option>
                                            <option value="Rwanda" data-dial="+250">🇷🇼 Rwanda</option>
                                            <option value="Saint Barthelemy" data-dial="+590">🇧🇱 Saint Barthelemy</option>
                                            <option value="Saint Helena" data-dial="+290">🇸🇭 Saint Helena</option>
                                            <option value="Saint Kitts and Nevis" data-dial="+1">🇰🇳 Saint Kitts and Nevis</option>
                                            <option value="Saint Lucia" data-dial="+1">🇱🇨 Saint Lucia</option>
                                            <option value="Saint Martin" data-dial="+590">🇲🇫 Saint Martin</option>
                                            <option value="Saint Pierre and Miquelon" data-dial="+508">🇵🇲 Saint Pierre and Miquelon</option>
                                            <option value="Saint Vincent and the Grenadines" data-dial="+1">🇻🇨 Saint Vincent and the Grenadines</option>
                                            <option value="Samoa" data-dial="+685">🇼🇸 Samoa</option>
                                            <option value="San Marino" data-dial="+378">🇸🇲 San Marino</option>
                                            <option value="Sao Tome and Principe" data-dial="+239">🇸🇹 Sao Tome and Principe</option>
                                            <option value="Saudi Arabia" data-dial="+966">🇸🇦 Saudi Arabia</option>
                                            <option value="Senegal" data-dial="+221">🇸🇳 Senegal</option>
                                            <option value="Serbia" data-dial="+381">🇷🇸 Serbia</option>
                                            <option value="Seychelles" data-dial="+248">🇸🇨 Seychelles</option>
                                            <option value="Sierra Leone" data-dial="+232">🇸🇱 Sierra Leone</option>
                                            <option value="Singapore" data-dial="+65">🇸🇬 Singapore</option>
                                            <option value="Sint Maarten" data-dial="+1">🇸🇽 Sint Maarten</option>
                                            <option value="Slovakia" data-dial="+421">🇸🇰 Slovakia</option>
                                            <option value="Slovenia" data-dial="+386">🇸🇮 Slovenia</option>
                                            <option value="Solomon Islands" data-dial="+677">🇸🇧 Solomon Islands</option>
                                            <option value="Somalia" data-dial="+252">🇸🇴 Somalia</option>
                                            <option value="South Africa" data-dial="+27">🇿🇦 South Africa</option>
                                            <option value="South Korea" data-dial="+82">🇰🇷 South Korea</option>
                                            <option value="South Sudan" data-dial="+211">🇸🇸 South Sudan</option>
                                            <option value="Spain" data-dial="+34">🇪🇸 Spain</option>
                                            <option value="Sri Lanka" data-dial="+94">🇱🇰 Sri Lanka</option>
                                            <option value="Sudan" data-dial="+249">🇸🇩 Sudan</option>
                                            <option value="Suriname" data-dial="+597">🇸🇷 Suriname</option>
                                            <option value="Svalbard and Jan Mayen" data-dial="+47">🇸🇯 Svalbard and Jan Mayen</option>
                                            <option value="Swaziland" data-dial="+268">🇸🇿 Swaziland</option>
                                            <option value="Sweden" data-dial="+46">🇸🇪 Sweden</option>
                                            <option value="Switzerland" data-dial="+41">🇨🇭 Switzerland</option>
                                            <option value="Syria" data-dial="+963">🇸🇾 Syria</option>
                                            <option value="Taiwan" data-dial="+886">🇹🇼 Taiwan</option>
                                            <option value="Tajikistan" data-dial="+992">🇹🇯 Tajikistan</option>
                                            <option value="Tanzania" data-dial="+255">🇹🇿 Tanzania</option>
                                            <option value="Thailand" data-dial="+66">🇹🇭 Thailand</option>
                                            <option value="Togo" data-dial="+228">🇹🇬 Togo</option>
                                            <option value="Tokelau" data-dial="+690">🇹🇰 Tokelau</option>
                                            <option value="Tonga" data-dial="+676">🇹🇴 Tonga</option>
                                            <option value="Trinidad and Tobago" data-dial="+1">🇹🇹 Trinidad and Tobago</option>
                                            <option value="Tunisia" data-dial="+216">🇹🇳 Tunisia</option>
                                            <option value="Turkey" data-dial="+90">🇹🇷 Turkey</option>
                                            <option value="Turkmenistan" data-dial="+993">🇹🇲 Turkmenistan</option>
                                            <option value="Turks and Caicos Islands" data-dial="+1">🇹🇨 Turks and Caicos Islands</option>
                                            <option value="Tuvalu" data-dial="+688">🇹🇻 Tuvalu</option>
                                            <option value="U.S. Virgin Islands" data-dial="+1">🇻🇮 U.S. Virgin Islands</option>
                                            <option value="Uganda" data-dial="+256">🇺🇬 Uganda</option>
                                            <option value="Ukraine" data-dial="+380">🇺🇦 Ukraine</option>
                                            <option value="United Arab Emirates" data-dial="+971">🇦🇪 United Arab Emirates</option>
                                            <option value="United Kingdom" data-dial="+44">🇬🇧 United Kingdom</option>
                                            <option value="United States" data-dial="+1">🇺🇸 United States</option>
                                            <option value="Uruguay" data-dial="+598">🇺🇾 Uruguay</option>
                                            <option value="Uzbekistan" data-dial="+998">🇺🇿 Uzbekistan</option>
                                            <option value="Vanuatu" data-dial="+678">🇻🇺 Vanuatu</option>
                                            <option value="Vatican" data-dial="+379">🇻🇦 Vatican</option>
                                            <option value="Venezuela" data-dial="+58">🇻🇪 Venezuela</option>
                                            <option value="Vietnam" data-dial="+84">🇻🇳 Vietnam</option>
                                            <option value="Wallis and Futuna" data-dial="+681">🇼🇫 Wallis and Futuna</option>
                                            <option value="Western Sahara" data-dial="+212">🇪🇭 Western Sahara</option>
                                            <option value="Yemen" data-dial="+967">🇾🇪 Yemen</option>
                                            <option value="Zambia" data-dial="+260">🇿🇲 Zambia</option>
                                            <option value="Zimbabwe" data-dial="+263">🇿🇼 Zimbabwe</option>
                                            <option value="Other" data-dial="+">🌍 Other</option>
                                        </select>
                                </div>

                                <div class="input-group" style="margin-top: 1.25rem;">
                                    <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Phone Number</label>
                                    <div class="input-wrapper" style="display: flex; align-items: center; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: 12px; overflow: hidden; transition: border-color 0.3s ease;">
                                        <input type="text" name="dial_code" id="regPhonePrefix" value="+20" style="width: 80px; background: rgba(0,0,0,0.1); border: none; border-right: 1px solid var(--glass-border); color: var(--primary-cyan); font-weight: 700; font-size: 1.05rem; text-align: center; padding: 1.25rem 0.5rem; outline: none; transition: background 0.3s ease;">
                                        <input type="tel" name="phone" id="regPhoneInput" required placeholder="10XXXXXXXX" style="flex: 1; background: none; border: none; padding: 1.25rem 1.25rem 1.25rem 1rem; outline: none; color: var(--text-main); font-size: 1rem;">
                                    </div>
                                </div>
                            </div>

                            <div style="margin-bottom: 2rem; text-align: left;">
                                <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Create Password</label>
                                <div class="input-wrapper" style="position: relative;">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" id="registerPassword" name="password" placeholder="••••••••" required minlength="8" style="padding-right: 2.5rem;">
                                    <i class="fas fa-eye" id="toggleRegisterPassword" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer; transition: color 0.2s;" onclick="togglePassword('registerPassword', 'toggleRegisterPassword')"></i>
                                </div>
                            </div>

                            <button type="submit" class="notify-btn" style="width: 100%; justify-content: center;">
                                <span>Initialize Account</span>
                                <i class="fas fa-rocket"></i>
                            </button>
                        </div>
                    </form>

                    @if ($errors->any())
                        <div style="margin-top: 1rem; padding: 0.75rem; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 8px; color: #ef4444; font-size: 0.85rem; text-align: left;">
                            @foreach ($errors->all() as $error)
                                <p style="margin: 0;">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div style="margin-top: 2rem; color: var(--text-muted); font-size: 0.85rem;">
                        Already have an account? <a href="/login" style="color: var(--primary-cyan); text-decoration: none; font-weight: 600;">Secure Login</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="{{ asset('script.js?v=14') }}"></script>
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
</body>
</html>
