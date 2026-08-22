<style>
        :root {
            --font-arabic: 'Noto Sans Arabic', sans-serif;
        }
        body { font-family: 'Poppins', var(--font-arabic), sans-serif; }
        
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
            background: rgba(0, 168, 230, 0.1);
            border: 1px solid rgba(0, 168, 230, 0.2);
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
        .reg-step.active .step-num { border-color: var(--primary-cyan); background: rgba(0, 168, 230, 0.1); }
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
        .plan-option:hover { border-color: rgba(0, 168, 230, 0.3); background: rgba(0, 168, 230, 0.05); }
        .plan-option.selected { border-color: var(--primary-cyan); background: rgba(0, 168, 230, 0.1); }
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
            background: rgba(0, 168, 230,0.12); color: var(--primary-cyan);
        }
        .cp-option .cp-opt-flag { font-size: 1.2rem; }
        .cp-option .cp-opt-dial { margin-left: auto; font-size: 0.8rem; opacity: 0.5; }
        .cp-no-results { padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.9rem; }
</style>
