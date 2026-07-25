<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $topic->title }} - Discussion Thread</title>
    <style>
        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            padding: 25px 20px;
            color: #1a1a1a;
            background: #f9f9f9;
        }
        .container {
            max-width: 100%;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        /* Header */
        .header {
            border-bottom: 2px solid #0A574F;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin: 0 0 6px 0;
            color: #0A574F;
        }
        .header .meta {
            color: #666;
            font-size: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .header .meta strong {
            font-weight: bold;
        }
        .header .description {
            margin-top: 8px;
            font-size: 13px;
            color: #333;
            background: #f0f0f0;
            padding: 8px 12px;
            border-radius: 6px;
        }
        .stats {
            font-size: 11px;
            color: #666;
            margin-bottom: 16px;
            padding: 8px 12px;
            background: #f5f5f5;
            border-radius: 6px;
            display: inline-block;
        }

        /* Post Bubble */
        .post {
            margin-bottom: 12px;
            padding: 12px 16px;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e5e5e5;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .post:last-child {
            margin-bottom: 0;
        }
        /* Child / Reply posts get a slightly different background */
        .post-reply {
            background: #f9f9f9;
            border-left: 3px solid #0A574F;
            border-radius: 12px 12px 12px 4px;
        }
        /* Pinned post style */
        .post-pinned {
            border-left: 4px solid #0A574F;
            background: #f0faf8;
        }

        /* Author & Meta */
        .post-header {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 4px;
        }
        .post-header .avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #ECFDF5;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            color: #0A574F;
            flex-shrink: 0;
        }
        .post-header .author {
            font-weight: bold;
            font-size: 13px;
            color: #000;
        }
        .post-header .timestamp {
            color: #999;
            font-size: 10px;
            margin-left: 2px;
        }
        .post-header .badge {
            display: inline-block;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1px 6px;
            border-radius: 10px;
            border: 1px solid #ddd;
            color: #666;
            background: #fff;
        }
        .post-header .badge-private {
            border-color: #DC2626;
            color: #DC2626;
            background: #fee2e2;
        }
        .post-header .badge-reply {
            border-color: #999;
            color: #999;
            background: #f0f0f0;
        }
        .post-header .badge-pinned {
            border-color: #0A574F;
            color: #0A574F;
            background: #d1fae5;
        }

        /* Content */
        .post-content {
            margin-top: 4px;
            font-size: 13px;
            color: #1a1a1a;
            line-height: 1.6;
        }
        .post-content p {
            margin: 4px 0;
        }

        /* Attachments (if any) */
        .attachments {
            margin-top: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .attachments a {
            display: inline-block;
            font-size: 10px;
            color: #2563EB;
            text-decoration: none;
            background: #f0f4ff;
            padding: 2px 10px;
            border-radius: 12px;
            border: 1px solid #dbeafe;
        }
        .attachments a img {
            max-width: 120px;
            max-height: 120px;
            border-radius: 4px;
            border: 1px solid #e5e5e5;
        }

        /* Nested Replies */
        .replies {
            margin-left: 30px;
            padding-left: 16px;
            border-left: 2px solid #e5e5e5;
            margin-top: 10px;
        }
        .replies .post {
            margin-bottom: 10px;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: center;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 12px;
        }

        /* Page numbering via CSS */
        .page-number:before {
            content: counter(page);
        }
        .page-count:before {
            content: counter(pages);
        }

        /* Optional print settings */
        @page {
            margin: 1cm;
            size: A4;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">

        {{-- Header --}}
        <div class="header">
            <h1>{{ $topic->title }}</h1>
            <div class="meta">
                <span><strong>Group:</strong> {{ $group->name ?? 'N/A' }}</span>
                <span><strong>Author:</strong> {{ $author->name ?? 'Unknown' }}</span>
                <span><strong>Created:</strong> {{ $topic->created_at->format('M d, Y h:i A') }}</span>
                @if($topic->ml_category)
                    <span><strong>Category:</strong> {{ $topic->ml_category }}</span>
                @endif
            </div>
            @if($topic->description)
                <div class="description">{{ $topic->description }}</div>
            @endif
        </div>

        {{-- Stats --}}
        <div class="stats">
            <strong>{{ $posts->count() }}</strong> replies in this thread
        </div>

        {{-- Posts --}}
        @forelse($posts as $post)
            @include('pdf._post', ['post' => $post])
        @empty
            <p style="color: #999; font-style: italic; text-align: center; padding: 20px;">No replies yet.</p>
        @endforelse

        {{-- Footer --}}
        <div class="footer">
            Generated from Smart Discussion Forum &bull; {{ now()->format('M d, Y h:i A') }}
            &bull; Page <span class="page-number"></span> of <span class="page-count"></span>
        </div>
    </div>
</body>
</html>