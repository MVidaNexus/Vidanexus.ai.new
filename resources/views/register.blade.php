@extends('layouts.marketing')

@section('title', 'Create Account | VidaNexus AI')

@push('meta')
    <meta name="description" content="Create your free VidaNexus AI account to start discovering keywords, monitoring trends, and writing high-ranking content.">
@endpush

@push('head')
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;700&display=swap" rel="stylesheet">
@endpush

@push('styles')
    @include('partials.register-styles')
@endpush

@section('content')
    @include('partials.register-content-inner')
@endsection

@push('scripts')
    @include('partials.register-scripts')
@endpush
