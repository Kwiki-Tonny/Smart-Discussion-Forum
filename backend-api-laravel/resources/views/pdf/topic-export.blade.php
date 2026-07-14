<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>                                                                                                                                              
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            margin: 40px;
        }
        .header {
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .header h1 {
            font-size: 22px;
            font-weight: bold;
            margin: 0 0 6px;
        }
        .header p {
            font-size: 12px;
            color: #777;
            margin: 0;
        }
        .topic-info {
            background: #f9f9f9;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .topic-info h2 {
            font-size: 16px;
            margin: 0 0 8px;
        }
        .topic-info p {
            font-size: 12px;
            color: #555;
            margin: 0;
        }
        .post {
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            padding: 14px;
            margin-bottom: 12px;
        }
        .post-author {
            font-size: 13px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 4px;
        }
        .post-time {
            font-size: 11px;
            color: #999;
            margin-bottom: 8px;
        }
        .post-content {
            font-size: 13px;
            color: #333;
            line-height: 1.6;
        }
        .footer {
            border-top: 1px solid #e5e5e5;
            padding-top: 12px;
            margin-top: 24px;
            font-size: 11px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <h1>Smart Discussion Forum</h1>
        <p>Topic Export — Generated on {{ now()->format('D, d M Y h:i A') }}</p>
    </div>

    {{-- TOPIC INFO --}}
    <div class="topic-info">
        <h2>{{ $topic->title }}</h2>
        <p>Group: {{ $group->name }}</p>
        @if($topic->description)
        <p style="margin-top:8px;">{{ $topic->description }}</p>
        @endif
        @if($topic->ml_category)
        <p style="margin-top:6px;">Category: {{ $topic->ml_category }}</p>
        @endif
    </div>

    {{-- POSTS --}}
    <h3 style="font-size:14px; margin-bottom:12px;">
        {{ $posts->count() }} {{ $posts->count() == 1 ? 'Reply' : 'Replies' }}
    </h3>

    @forelse($posts as $post)
    <div class="post">
        <div class="post-author">{{ $post->user->name ?? 'Unknown User' }}</div>
        <div class="post-time">{{ $post->created_at->format('D, d M Y h:i A') }}</div>
        <div class="post-content">{{ $post->content }}</div>
    </div>
    @empty
    <p style="color:#999;">No replies yet.</p>
    @endforelse

    {{-- FOOTER --}}
    <div class="footer">
        Smart Discussion Forum — Exported from {{ $group->name }} | {{ now()->format('Y') }}
    </div>

</body>
</html>