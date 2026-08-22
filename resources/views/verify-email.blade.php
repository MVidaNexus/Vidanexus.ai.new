@extends('layouts.marketing')

@section('title', 'VidaNexus AI — Verify Your Email')

@push('meta')
    <meta name="description" content="Verify your email address to activate your VidaNexus AI account.">
@endpush

@push('styles')
    @include('partials.verify-email-styles')
@endpush

@section('content')
    @include('partials.verify-email-content')
@endsection
