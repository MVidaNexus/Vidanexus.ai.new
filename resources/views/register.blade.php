@extends('layouts.marketing')

@section('title', 'VidaNexus AI — Join the Future')

@push('meta')
    <meta name="description" content="Join the future of intelligent automation. Register for VidaNexus AI.">
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
