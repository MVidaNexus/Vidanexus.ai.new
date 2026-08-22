@extends('dashboard.layouts.app')

@section('title', 'Cart — VidaNexus')

@section('content')
<div class="py-2" style="max-width: 900px; margin: 0 auto;">
    <h1 class="mb-4" style="font-weight: 800;">Subscription cart</h1>

    @if (session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
    @endif

    @forelse ($cart->items as $item)
        <div class="card mb-3 border-secondary bg-dark text-light">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="fw-bold">{{ $item->package_name_snapshot }}</div>
                    <div class="small text-muted">
                        {{ $item->billing_interval === 'yearly' ? 'Yearly' : 'Monthly' }}
                        · {{ number_format((float) $item->unit_price_snapshot, 2) }} {{ $item->currency_snapshot }} each
                    </div>
                    @if ($item->subscriptionPackage && $item->subscriptionPackage->tools->isNotEmpty())
                        <ul class="small mb-0 mt-2 ps-3 text-muted">
                            @foreach ($item->subscriptionPackage->tools as $pt)
                                <li>{{ $pt->tool_slug }} — {{ $pt->credits_per_cycle }} credits / cycle</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2">
                    <form method="post" action="{{ route('cart.items.update', $item) }}" class="d-flex align-items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <label class="small text-muted mb-0">Qty</label>
                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="0" max="99" class="form-control form-control-sm" style="width: 5rem;">
                        <button type="submit" class="btn btn-sm btn-outline-light">Update</button>
                    </form>
                    <form method="post" action="{{ route('cart.items.destroy', $item) }}" onsubmit="return confirm('Remove this package?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted">Your cart is empty. Add SaaS packages from the catalog once they are published in the database.</p>
    @endforelse

    @if ($cart->items->isNotEmpty())
        <div class="d-flex justify-content-between align-items-center mt-4 p-3 rounded border border-secondary">
            <span class="fw-bold">Subtotal</span>
            <span class="fw-bold">{{ number_format($summary['subtotal'], 2) }} {{ $summary['currency'] }}</span>
        </div>
        <p class="small text-muted mt-2 mb-0">
            Checkout with your payment provider should call validation (<code>CartService::validateCheckout</code>) then entitlements (<code>PackageEntitlementService::activatePaidCart</code>) after payment succeeds.
        </p>
    @endif
</div>
@endsection
