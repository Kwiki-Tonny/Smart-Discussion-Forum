{{-- resources/views/partials/_post.blade.php --}}
<div class="bg-white border border-[#E5E5E5] p-4 {{ isset($inPinned) && $inPinned ? 'border-l-4 border-l-[#000000]' : '' }}" id="post-{{ $post->id }}" data-post-id="{{ $post->id }}">
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
            {{-- 📌 Pinned Badge --}}
            @if($post->is_pinned)
                <span class="text-[8px] font-bold uppercase tracking-wider text-[#000000] border border-[#000000] px-1.5 py-0.5 flex-shrink-0">📌 Pinned</span>
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

            {{-- Pin Button (only for lecturers/admins) --}}
            @if(in_array(Auth::user()->role, ['lecturer', 'admin']))
                <button class="pin-btn text-xs text-[#666666] hover:text-[#000000] transition-colors flex items-center space-x-1" data-post-id="{{ $post->id }}">
                    {{ $post->is_pinned ? '📌 Unpin' : '📌 Pin' }}
                </button>
            @endif

            {{-- Jump to thread (only visible when this post is displayed in the pinned section) --}}
            @if(isset($inPinned) && $inPinned)
                <button class="jump-to-post text-xs text-[#2563EB] hover:underline transition-colors" data-post-id="{{ $post->id }}">
                    ↳ Jump to thread
                </button>
            @endif
        </div>
    </div>

    {{-- Post Content --}}
    <p class="text-sm text-[#000000] leading-relaxed">{{ $post->content }}</p>

    {{-- 📎 Attachments --}}
    @if($post->attachments && count($post->attachments) > 0)
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach($post->attachments as $file)
                @php
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
                    $url = asset('storage/' . $file);
                @endphp
                @if($isImage)
                    <a href="{{ $url }}" target="_blank" class="block border">
                        <img src="{{ $url }}" class="max-w-xs max-h-48 object-contain">
                    </a>
                @else
                    <a href="{{ $url }}" target="_blank" class="text-xs text-[#2563EB] border border-[#E5E5E5] px-3 py-1 hover:bg-[#F5F5F5] transition-colors">
                        📎 {{ basename($file) }}
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    {{-- Reply Form (hidden by default) --}}
    <div class="mt-3 hidden reply-form" id="reply-form-{{ $post->id }}">
        <form class="reply-form-ajax" data-parent-id="{{ $post->id }}" data-topic-id="{{ $post->topic_id }}">
            @csrf
            <div class="border-l-2 border-[#E5E5E5] pl-4 space-y-2">
                <textarea name="content" rows="2" required 
                          class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors"
                          placeholder="Write a reply..."></textarea>

                {{-- File attachment input for replies --}}
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wide text-[#666666]">Attach Files</label>
                    <input type="file" name="attachments[]" multiple
                           accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                           class="w-full bg-white border border-[#E5E5E5] px-3 py-1 text-sm">
                    <p class="text-[9px] text-[#666666]">Max 5MB per file. Supported: images, PDF, Word, Excel, TXT</p>
                </div>

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