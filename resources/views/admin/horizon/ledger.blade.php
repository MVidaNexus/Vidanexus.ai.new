@extends('admin.horizon.layout')

@section('title', 'Financial Ledger')

@section('content')
    <div style="max-width: 1200px; margin: 0 auto;">
        <h2 style="font-family: var(--font-heading, sans-serif); margin-bottom: 1rem;">Financial ledger</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem; font-size: 0.95rem;">
            Append-only log of wallet and per-tool bonus movements (tool AI usage, coupons, welcome credits, purchases).
        </p>

        <div style="overflow-x: auto; background: rgba(0,0,0,0.2); border: 1px solid var(--horizon-border, #333); border-radius: 12px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--horizon-border, #333);">
                        <th style="padding: 0.75rem 1rem; text-align: left;">When</th>
                        <th style="padding: 0.75rem 1rem; text-align: left;">User</th>
                        <th style="padding: 0.75rem 1rem; text-align: left;">Event</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Wallet Δ</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Bonus Δ</th>
                        <th style="padding: 0.75rem 1rem; text-align: left;">Tool</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $row)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                            <td style="padding: 0.65rem 1rem; white-space: nowrap;">{{ $row->created_at->format('Y-m-d H:i') }}</td>
                            <td style="padding: 0.65rem 1rem;">
                                @if($row->user)
                                    <div style="font-weight: 600;">{{ $row->user->name }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $row->user->email }}</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td style="padding: 0.65rem 1rem; font-family: monospace; font-size: 0.8rem;">{{ $row->event_type }}</td>
                            <td style="padding: 0.65rem 1rem; text-align: right;">{{ $row->wallet_delta }}</td>
                            <td style="padding: 0.65rem 1rem; text-align: right;">{{ $row->bonus_delta }}</td>
                            <td style="padding: 0.65rem 1rem;">{{ $row->tool_slug ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-muted);">No entries yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $entries->links('admin.horizon.partials._pagination') }}
        </div>
    </div>
@endsection
