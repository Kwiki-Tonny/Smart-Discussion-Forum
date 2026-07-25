@php
    $isLiked = $post->isLikedByUser(Auth::id());
    $likeIcon = $isLiked
        ? '<i data-lucide="heart" style="width:14px;height:14px;fill:#DC2626;color:#DC2626;"></i>'
        : '<i data-lucide="heart" style="width:14px;height:14px;"></i>';
    $fileInputId = 'reply-file-' . $post->id;
    $fileNamesId = 'reply-file-names-' . $post->id;
    $isChild = isset($inPinned) && $inPinned ? false : ($post->parent_id ? true : false);
    $childrenCount = $post->children ? count($post->children) : 0;
@endphp

<div class="post-bubble {{ $isChild ? 'bg-[#F9F9F9]' : 'bg-white' }} rounded-2xl shadow-sm border border-[#E5E5E5] p-3 transition hover:shadow-md" id="post-{{ $post->id }}" data-post-id="{{ $post->id }}">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 min-w-0 flex-1">
            <div class="w-7 h-7 bg-[#ECFDF5] rounded-full flex items-center justify-center flex-shrink-0">
                <i data-lucide="user" style="width:14px;height:14px;color:#0A574F;"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center flex-wrap gap-1">
                    <span class="text-sm font-bold text-[#000000]">{{ $post->author->name ?? 'Unknown' }}</span>
                    <span class="text-[10px] text-[#666666]">{{ $post->created_at->diffForHumans() }}</span>
                    @if($post->is_private)
                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#DC2626] border border-[#DC2626] px-1.5 py-0.5 rounded-full">Private</span>
                    @endif
                    @if($post->parent_id)
                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-1.5 py-0.5 rounded-full">Reply</span>
                    @endif
                    @if($post->is_pinned)
                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#000000] border border-[#000000] px-1.5 py-0.5 rounded-full flex items-center gap-1">
                            <i data-lucide="pin" style="width:10px;height:10px;"></i> Pinned
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-0.5 flex-shrink-0 ml-1">
            <button class="like-btn text-xs text-[#666666] hover:text-[#000000] transition-colors flex items-center gap-1 px-1.5 py-1 rounded-lg hover:bg-[#F0F0F0]" data-post-id="{{ $post->id }}">
                <span class="like-icon">{!! $likeIcon !!}</span>
                <span class="like-count text-sm font-medium">{{ $post->likes_count ?? 0 }}</span>
            </button>
            <button class="reply-toggle text-xs text-[#666666] hover:text-[#000000] transition-colors flex items-center gap-1 px-1.5 py-1 rounded-lg hover:bg-[#F0F0F0]" data-post-id="{{ $post->id }}">
                <i data-lucide="reply" style="width:14px;height:14px;"></i>
            </button>
            @auth
                <button class="pin-btn text-xs text-[#666666] hover:text-[#000000] transition-colors flex items-center gap-1 px-1.5 py-1 rounded-lg hover:bg-[#F0F0F0]" data-post-id="{{ $post->id }}">
                    <i data-lucide="{{ $post->is_pinned ? 'pin-off' : 'pin' }}" style="width:14px;height:14px;"></i>
                </button>
            @endauth
        </div>
    </div>

    <p class="text-sm text-[#000000] leading-relaxed mt-1">{{ $post->content }}</p>

    {{-- Attachments – images open in new tab, others download --}}
    @if($post->attachments && count($post->attachments) > 0)
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach($post->attachments as $file)
                @php
                    // If $file is an object (from formatted attachments) use it, else treat as string path
                    $path = is_array($file) ? ($file['path'] ?? $file) : $file;
                    $url = is_array($file) ? ($file['url'] ?? asset('storage/' . $path)) : asset('storage/' . $path);
                    $name = is_array($file) ? ($file['name'] ?? basename($path)) : basename($path);
                    $isImage = is_array($file) ? ($file['is_image'] ?? false) : in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','svg','webp']);
                @endphp
                @if($isImage)
                    <a href="{{ $url }}" target="_blank" class="block border rounded-lg overflow-hidden">
                        <img src="{{ $url }}" class="max-w-xs max-h-48 object-contain" alt="{{ $name }}">
                    </a>
                @else
                    <a href="{{ $url }}" download="{{ $name }}" class="flex items-center gap-1 text-xs text-[#2563EB] border border-[#E5E5E5] rounded-lg px-3 py-1.5 hover:bg-[#F9F9F9] transition">
                        <i data-lucide="file" style="width:12px;height:12px;"></i>
                        {{ $name }}
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    {{-- Toggle button for nested replies --}}
    @if($childrenCount > 0)
        <button class="toggle-replies-btn text-xs text-[#2563EB] hover:underline mt-2 flex items-center gap-1" data-post-id="{{ $post->id }}">
            <i data-lucide="chevron-down" style="width:12px;height:12px;"></i>
            Show {{ $childrenCount }} repl{{ $childrenCount > 1 ? 'ies' : 'y' }}
        </button>
    @endif

    {{-- Reply form (hidden by default) --}}
    <div class="mt-2 hidden reply-form" id="reply-form-{{ $post->id }}">
        <form class="reply-form-ajax" data-parent-id="{{ $post->id }}" data-topic-id="{{ $post->topic_id }}">
            @csrf
            <div class="flex items-end gap-2">
                <label for="{{ $fileInputId }}" class="cursor-pointer text-[#666666] hover:text-[#0A574F] transition p-1.5 rounded-full hover:bg-[#F0F0F0] flex-shrink-0">
                    <i data-lucide="paperclip" style="width:16px;height:16px;"></i>
                </label>
                <input type="file" name="attachments[]" multiple id="{{ $fileInputId }}"
                       accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                       class="hidden reply-file-input">
                <span id="{{ $fileNamesId }}" class="text-[9px] text-[#666666] truncate max-w-[80px] hidden"></span>
                <div class="flex-1 relative">
                    <textarea name="content" rows="1" required
                              class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-1.5 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition resize-none"
                              placeholder="Write a reply..." style="min-height:36px; max-height:100px;"
                              oninput="this.style.height = 'auto'; this.style.height = Math.min(this.scrollHeight, 100) + 'px';"></textarea>
                </div>
                <button type="submit"
                        class="reply-submit-btn flex items-center justify-center bg-[#0A574F] text-white p-1.5 rounded-full hover:bg-[#08443e] transition w-8 h-8 flex-shrink-0">
                    <i data-lucide="send" style="width:14px;height:14px;"></i>
                </button>
            </div>
        </form>
    </div>

    {{-- Nested replies – initially hidden --}}
    @if($childrenCount > 0)
        <div class="children-container mt-3 space-y-3 hidden" id="children-container-{{ $post->id }}">
            @foreach($post->children as $child)
                @include('partials._post', ['post' => $child])
            @endforeach
        </div>
    @endif
</div>