@php
    $isChild = isset($depth) && $depth > 0;
    $isPinned = $post->is_pinned ?? false;
    $isPrivate = $post->is_private ?? false;
    $isReply = $post->parent_id ? true : false;

    // Determine classes for styling
    $classes = 'post';
    if ($isReply) $classes .= ' post-reply';
    if ($isPinned) $classes .= ' post-pinned';

    // Avatar initial
    $avatar = $post->author->name ?? 'U';
    $avatar = substr($avatar, 0, 1);
@endphp

<div class="{{ $classes }}" style="{{ isset($depth) && $depth > 0 ? 'margin-left: ' . ($depth * 20) . 'px;' : '' }}">
    <div class="post-header">
        <span class="avatar">{{ $avatar }}</span>
        <span class="author">{{ $post->author->name ?? 'Unknown' }}</span>
        <span class="timestamp">{{ $post->created_at->format('M d, Y h:i A') }}</span>

        @if($isPrivate)
            <span class="badge badge-private">Private</span>
        @endif

        @if($isReply)
            <span class="badge badge-reply">Reply</span>
        @endif

        @if($isPinned)
            <span class="badge badge-pinned">Pinned</span>
        @endif
    </div>

    <div class="post-content">
        {!! nl2br(e($post->content)) !!}
    </div>

    {{-- Attachments (if any) --}}
    @if($post->attachments && count($post->attachments) > 0)
        <div class="attachments">
            @foreach($post->attachments as $file)
                @php
                    $path = is_array($file) ? ($file['path'] ?? $file) : $file;
                    $url = is_array($file) ? ($file['url'] ?? asset('storage/' . $path)) : asset('storage/' . $path);
                    $name = is_array($file) ? ($file['name'] ?? basename($path)) : basename($path);
                    $isImage = is_array($file) ? ($file['is_image'] ?? false) : in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','svg','webp']);
                @endphp
                @if($isImage)
                    <a href="{{ $url }}" target="_blank"><img src="{{ $url }}" alt="{{ $name }}"></a>
                @else
                    <a href="{{ $url }}" target="_blank">📎 {{ $name }}</a>
                @endif
            @endforeach
        </div>
    @endif

    {{-- Nested replies (recursive) --}}
    @if($post->children && $post->children->count())
        <div class="replies">
            @php $newDepth = ($depth ?? 0) + 1; @endphp
            @foreach($post->children as $child)
                @include('pdf._post', ['post' => $child, 'depth' => $newDepth])
            @endforeach
        </div>
    @endif
</div>