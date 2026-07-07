<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $topic->title }} - Discussion Thread</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            padding: 20px;
            color: #1a1a1a;
        }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 22px;
            margin: 0 0 4px 0;
        }
        .header .meta {
            color: #666;
            font-size: 10px;
        }
        .header .description {
            margin-top: 8px;
            font-size: 13px;
            color: #333;
        }
        .post {
            border-bottom: 1px solid #e5e5e5;
            padding: 12px 0;
        }
        .post:last-child {
            border-bottom: none;
        }
        .post .author {
            font-weight: bold;
            font-size: 13px;
        }
        .post .timestamp {
            color: #999;
            font-size: 10px;
            margin-left: 8px;
        }
        .post .badge {
            display: inline-block;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1px 6px;
            margin-left: 6px;
            border: 1px solid #ddd;
            color: #666;
        }
        .post .badge-private {
            border-color: #DC2626;
            color: #DC2626;
        }
        .post .badge-reply {
            border-color: #999;
            color: #999;
        }
        .post .content {
            margin-top: 4px;
            font-size: 13px;
        }
        .post .content p {
            margin: 4px 0;
        }
        .replies {
            margin-left: 30px;
            padding-left: 15px;
            border-left: 2px solid #e5e5e5;
        }
        .replies .post {
            border-bottom: 1px solid #f0f0f0;
            padding: 10px 0;
        }
        .replies .post:last-child {
            border-bottom: none;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: center;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <h1>{{ $topic->title }}</h1>
        <div class="meta">
            <strong>Group:</strong> {{ $group->name ?? 'N/A' }}
            &bull; <strong>Created by:</strong> {{ $author->name ?? 'Unknown' }}
            &bull; <strong>Date:</strong> {{ $topic->created_at->format('M d, Y h:i A') }}
        </div>
        @if($topic->description)
            <div class="description">{{ $topic->description }}</div>
        @endif
    </div>

    {{-- Stats --}}
    <p style="font-size: 11px; color: #666; margin-bottom: 16px;">
        <strong>{{ $posts->count() }}</strong> replies in this thread
    </p>

    {{-- Posts --}}
    @forelse($posts as $post)
        @include('pdf._post', ['post' => $post])
    @empty
        <p style="color: #999; font-style: italic;">No replies yet.</p>
    @endforelse

    {{-- Footer --}}
    <div class="footer">
        Generated from Smart Discussion Forum &bull; {{ now()->format('M d, Y h:i A') }}
        &bull; Page {PAGE_NUM} of {PAGE_COUNT}
    </div>

</body>
</html>