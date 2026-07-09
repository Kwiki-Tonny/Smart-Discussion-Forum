{{-- resources/views/pdf/_post.blade.php --}}
<div class="post" style="{{ isset($depth) ? 'margin-left: ' . ($depth * 20) . 'px;' : '' }}">
    <div>
        <span class="author">{{ $post->author->name ?? 'Unknown' }}</span>
        <span class="timestamp">{{ $post->created_at->format('M d, Y h:i A') }}</span>

        @if($post->is_private)
            <span class="badge badge-private">Private</span>
        @endif

        @if($post->parent_id)
            <span class="badge badge-reply">Reply</span>
        @endif
    </div>

    <div class="content">
        {!! nl2br(e($post->content)) !!}
    </div>

    {{-- Nested replies (recursive) --}}
    @if($post->children && $post->children->count())
        <div class="replies">
            @php $depth = ($depth ?? 0) + 1; @endphp
            @foreach($post->children as $child)
                @include('pdf._post', ['post' => $child, 'depth' => $depth])
            @endforeach
        </div>
    @endif
</div>