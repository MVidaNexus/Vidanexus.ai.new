<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #111;">
    <h2 style="margin-bottom: 0.5rem;">User feedback</h2>
    <p><strong>From:</strong> {{ $feedback->name }} &lt;{{ $feedback->email }}&gt;</p>
    @if($feedback->subject)
        <p><strong>Subject:</strong> {{ $feedback->subject }}</p>
    @endif
    <p><strong>Time:</strong> {{ $feedback->created_at->toIso8601String() }}</p>
    @if($feedback->ip_address)
        <p><strong>IP:</strong> {{ $feedback->ip_address }}</p>
    @endif
    <hr style="margin: 1rem 0;">
    <p style="white-space: pre-wrap;">{{ $feedback->message }}</p>
</body>
</html>
