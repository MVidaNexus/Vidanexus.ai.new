@extends('layouts.marketing')

@section('title', 'Complete Your Order — VidaNexus')

@push('styles')
    @include('partials.payment-styles')
@endpush

@section('content')
    @include('partials.payment-content')
@endsection

@push('after_main')
    @include('partials.footer')
@endpush

@push('scripts')
    <script>
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            var btn = document.getElementById('payBtn');
            if (btn.disabled) {
                e.preventDefault();
                return;
            }
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 0.75rem;"></i> Processing...';
        });
    </script>
@endpush
