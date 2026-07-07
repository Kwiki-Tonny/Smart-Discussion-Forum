<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $topic->title }} - Discussion Thread</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; line-height: 1.5; }
        .header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; margin: 0; }
        .header .meta { color: #666; font-size: 10px; }
        .post { border-bottom: 1px solid #ddd; padding: 12px 0; }
        .post .author { font-weight: bold; }
        .post .timestamp { color: #999; font-size: 10px; }
        .post .content { margin-top: 5px; }
        .footer { margin-top: 30px; font-size: 10px; text-align: center; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $topic->title }}</h1>
        <div class="meta">
            <strong>Group:</strong> {{ $group->name ?? 'N/A' }} &bull;
            <strong>Created by:</strong> {{ $author->name ?? 'Unknown' }} &bull;
            <strong>Date:</strong> {{ $topic->created_at->format('M d, Y h:i A') }}
        </div>
        @if($topic->description)
            <p style="margin-top: 8px;">{{ $topic->description }}</p>
        @endif
    </div>

    <h3 style="margin-bottom: 10px;">Discussion Thread ({{ $posts->count() }} replies)</h3>

    @forelse($posts as $post)
        <div class="post">
            <div class="author">{{ $post->author->name ?? 'Unknown' }}</div>
            <div class="timestamp">{{ $post->created_at->format('M d, Y h:i A') }}</div>
            <div class="content">{{ $post->content }}</div>
            @if($post->is_private)
                <div style="color: #DC2626; font-size: 9px;">[Private]</div>
            @endif
        </div>
    @empty
        <p>No replies yet.</p>
    @endforelse

    <div class="footer">
        Generated from Smart Discussion Forum &bull; {{ now()->format('M d, Y h:i A') }}
    </div>

</body>
</html>