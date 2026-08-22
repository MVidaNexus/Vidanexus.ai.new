{{--
    Shared "Credit System" pane used by every tool admin page so the
    look-and-feel matches the AI Keyword Radar reference design exactly.

    Required variables:
        - $paneId        string  — DOM id suffix; the wrapping div becomes
                                   `pane-{$paneId}`. Defaults to "credits".
        - $cards         array   — one or more cost cards. Each card:
                                       [
                                         'name'      => 'credit_cost',   // form input name
                                         'value'     => 1,               // current value
                                         'label'     => 'Action Cost',   // small label above the input
                                         'helper'    => '…',             // helper paragraph
                                         'badge'     => 'Manual Sync',   // optional pill label
                                       ]
        - $subtitle      string  — sentence below the title.
        - $saveLabel     string  — label of the save button (default: "Confirm Credit Settings").

    Notes:
        - Every form input keeps its real `name=` so the existing
          HorizonController handlers continue to work without changes.
        - The wrapping <div id="pane-…"> is rendered by the partial; tools
          should NOT wrap it again.
--}}
@php
    $paneId   = $paneId   ?? 'credits';
    $subtitle = $subtitle ?? 'Set the operational cost for each tool action.';
    $saveLabel = $saveLabel ?? 'Confirm Credit Settings';
    $cards    = $cards    ?? [];
@endphp

<div id="pane-{{ $paneId }}" class="horizon-tab-pane">
    <div style="max-width: 720px; margin: 0 auto; padding: 1rem 0;">
        <div style="text-align: center; margin-bottom: 3rem;">
            <div style="width: 90px; height: 90px; border-radius: 24px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.05)); border: 1px solid rgba(245, 158, 11, 0.2); display: flex; align-items: center; justify-content: center; color: #f59e0b; font-size: 3rem; margin: 0 auto 1.5rem; transform: rotate(-5deg);">
                <i class="fas fa-coins"></i>
            </div>
            <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 1.75rem; margin: 0 0 0.5rem; color: var(--text-main);">Financial Unit Calibration</h3>
            <p style="color: var(--text-muted); font-size: 1rem; margin: 0;">{{ $subtitle }}</p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            @foreach($cards as $card)
                @php
                    $cardName   = $card['name']   ?? 'credit_cost';
                    $cardValue  = $card['value']  ?? 0;
                    $cardLabel  = $card['label']  ?? 'Action Cost';
                    $cardHelper = $card['helper'] ?? 'Credits deducted from the wallet on each action.';
                    $cardBadge  = $card['badge']  ?? null;
                @endphp
                <div style="background: linear-gradient(180deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%); border: 1px solid var(--horizon-border); border-radius: 28px; padding: 2.5rem; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.2); position: relative;">
                    @if($cardBadge)
                        <span style="position: absolute; top: -14px; left: 50%; transform: translateX(-50%); background: var(--primary-admin); color: #000; font-size: 0.65rem; font-weight: 900; padding: 4px 14px; border-radius: 7px; text-transform: uppercase; letter-spacing: 1.5px; box-shadow: 0 4px 10px rgba(14, 165, 233, 0.3); white-space: nowrap;">
                            {{ $cardBadge }}
                        </span>
                    @endif

                    <label style="display: block; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 1.5rem;">{{ $cardLabel }}</label>

                    <div style="display: flex; align-items: center; justify-content: center; gap: 1.5rem; margin-bottom: 2rem;">
                        <input type="number"
                               name="{{ $cardName }}"
                               value="{{ $cardValue }}"
                               min="0"
                               step="1"
                               style="width: 160px; background: #000; border: 2px solid var(--primary-admin); color: var(--primary-admin); padding: 1.25rem; border-radius: 20px; font-size: 3rem; font-weight: 800; text-align: center; outline: none; font-family: 'Space Grotesk'; box-shadow: 0 0 30px rgba(14, 165, 233, 0.2);">
                        <span style="font-size: 1.5rem; font-weight: 600; color: var(--text-muted); opacity: 0.6;">CREDITS</span>
                    </div>

                    <div style="background: rgba(14, 165, 233, 0.05); border-radius: 16px; padding: 1.1rem 1.25rem; border: 1px solid rgba(14, 165, 233, 0.1); display: flex; align-items: flex-start; gap: 1rem; text-align: left;">
                        <i class="fas fa-info-circle" style="color: var(--primary-admin); margin-top: 3px;"></i>
                        <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); line-height: 1.6;">{{ $cardHelper }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="submit" class="btn-save" style="width: 100%; margin-top: 2.5rem; height: 60px; font-size: 1.1rem;">
            <i class="fas fa-check-circle"></i> {{ $saveLabel }}
        </button>
    </div>
</div>
