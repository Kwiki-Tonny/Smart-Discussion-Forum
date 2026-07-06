{{-- resources/views/partials/_post.blade.php --}}
<div class="bg-white border border-[#E5E5E5] p-4" id="post-{{ $post->id }}" data-post-id="{{ $post->id }}">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center space-x-3 min-w-0">
            <span class="text-sm font-bold text-[#000000]">{{ $post->author->name ?? 'Unknown' }}</span>
            <span class="text-[10px] text-[#666666] flex-shrink-0">{{ $post->created_at->diffForHumans() }}</span>
            @if($post->is_private)
                <span class="text-[8px] font-bold uppercase tracking-wider text-[#DC2626] border border-[#DC2626] px-1.5 py-0.5 flex-shrink-0">Private</span>
            @endif
            @if($post->parent_id)
                <span class="text-[8px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-1.5 py-0.5 flex-shrink-0">Reply</span>
            @endif
        </div>
        <div class="flex items-center space-x-3 flex-shrink-0">
            {{-- Like Button (AJAX) --}}
            <button class="like-btn text-xs text-[#666666] hover:text-[#000000] transition-colors flex items-center space-x-1" data-post-id="{{ $post->id }}">
                <span class="like-icon">{{ $post->isLikedByUser(Auth::id()) ? '❤️' : '🤍' }}</span>
                <span class="like-count">{{ $post->likes_count ?? 0 }}</span>
            </button>

            {{-- Reply Button (toggles reply form) --}}
            <button class="reply-toggle text-xs text-[#666666] hover:text-[#000000] transition-colors" data-post-id="{{ $post->id }}">
                💬 Reply
            </button>
        </div>
    </div>
    <p class="text-sm text-[#000000] leading-relaxed">{{ $post->content }}</p>

    {{-- Reply Form (hidden by default) --}}
    <div class="mt-3 hidden reply-form" id="reply-form-{{ $post->id }}">
        <form class="reply-form-ajax" data-parent-id="{{ $post->id }}" data-topic-id="{{ $post->topic_id }}">
            @csrf
            <div class="border-l-2 border-[#E5E5E5] pl-4 space-y-2">
                <textarea name="content" rows="2" required 
                          class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors"
                          placeholder="Write a reply..."></textarea>
                <div class="flex items-center justify-between">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_private" value="1" class="accent-black">
                        <span class="text-xs text-[#666666]">Private</span>
                    </label>
                    <button type="submit" 
                            class="reply-submit-btn bg-[#000000] text-white px-4 py-1.5 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                        Post Reply
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Nested replies (recursive) --}}
    @if($post->children && $post->children->count())
        <div class="ml-6 mt-3 space-y-3 border-l-2 border-[#E5E5E5] pl-4">
            @foreach($post->children as $child)
                @include('partials._post', ['post' => $child])
            @endforeach
        </div>
    @endif
</div>