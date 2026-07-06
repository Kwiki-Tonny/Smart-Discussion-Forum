<div class="bg-white border border-[#E5E5E5] p-4" id="post-{{ $post->id }}">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center space-x-3">
            <span class="text-sm font-bold text-[#000000]">{{ $post->author->name ?? 'Unknown' }}</span>
            <span class="text-[10px] text-[#666666]">{{ $post->created_at->diffForHumans() }}</span>
            @if($post->is_private)
                <span class="text-[8px] font-bold uppercase tracking-wider text-[#DC2626] border border-[#DC2626] px-1.5 py-0.5">Private</span>
            @endif
            @if($post->parent_id)
                <span class="text-[8px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-1.5 py-0.5">Reply</span>
            @endif
        </div>
        <div class="flex items-center space-x-3">
            {{-- Like Button --}}
            <form method="POST" action="{{ route('posts.like', $post->id) }}" class="inline">
                @csrf
                <button type="submit" class="text-xs text-[#666666] hover:text-[#000000] transition-colors flex items-center space-x-1">
                    @if($post->isLikedByUser(Auth::id()))
                        <span class="text-[#DC2626]">❤️</span>
                    @else
                        <span>🤍</span>
                    @endif
                    <span>{{ $post->likes_count }}</span>
                </button>
            </form>

            {{-- Reply Button (toggles reply form) --}}
            <button class="text-xs text-[#666666] hover:text-[#000000] transition-colors reply-toggle" data-post-id="{{ $post->id }}">
                Reply
            </button>
        </div>
    </div>
    <p class="text-sm text-[#000000] leading-relaxed">{{ $post->content }}</p>

    {{-- Reply Form (hidden by default) --}}
    <div class="mt-3 hidden reply-form" id="reply-form-{{ $post->id }}">
        <form method="POST" action="{{ route('posts.reply') }}" class="border-l-2 border-[#E5E5E5] pl-4">
            @csrf
            <input type="hidden" name="topic_id" value="{{ $post->topic_id }}">
            <input type="hidden" name="parent_id" value="{{ $post->id }}">
            <div class="space-y-2">
                <textarea name="content" rows="2" required 
                          class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors"
                          placeholder="Write a reply..."></textarea>
                <div class="flex items-center justify-between">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_private" value="1" class="accent-black">
                        <span class="text-xs text-[#666666]">Private</span>
                    </label>
                    <button type="submit" 
                            class="bg-[#000000] text-white px-4 py-1.5 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                        Post Reply
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Nested replies (recursive) --}}
    @if($post->children->count())
        <div class="ml-6 mt-3 space-y-3 border-l-2 border-[#E5E5E5] pl-4">
            @foreach($post->children as $child)
                @include('partials._post', ['post' => $child])
            @endforeach
        </div>
    @endif
</div>