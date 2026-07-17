@extends('layouts.workspace')

@section('title', $topic->title)

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('groups.topics', $topic->group_id) }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] truncate">{{ $topic->group->name }}</h2>
    </div>
    <div class="p-2 space-y-1">
        <div class="p-2 text-xs font-bold bg-[#F5F5F5] border border-black">• {{ $posts->count() }} replies</div>
        <div class="p-2 text-xs text-[#666666]">• by {{ $topic->creator->name ?? 'Unknown' }}</div>
        <div class="p-2 text-xs text-[#666666]">• {{ $topic->created_at->format('M d, Y') }}</div>
        @if($topic->ml_category)
            <div class="p-2 text-[8px] font-bold uppercase tracking-wider border border-[#000000]">{{ $topic->ml_category }}</div>
        @endif
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        {{-- Topic Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-[#000000]">{{ $topic->title }}</h1>
                    @if($topic->description)
                        <p class="text-sm text-[#666666] mt-1">{{ $topic->description }}</p>
                    @endif
                    <div class="flex items-center space-x-3 mt-2">
                        <span class="text-xs text-[#666666]">by {{ $topic->creator->name ?? 'Unknown' }}</span>
                        <span class="text-[10px] text-[#666666]">•</span>
                        <span class="text-[10px] text-[#666666]">{{ $topic->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    {{-- Export PDF Button --}}
                    <a href="{{ route('topics.export', $topic->id) }}" 
                       class="text-xs text-[#666666] border border-[#E5E5E5] px-3 py-1 hover:bg-[#F5F5F5] transition-colors">
                        📄 Export PDF
                    </a>
                    {{-- Share Button --}}
                    <button onclick="copyLink()" 
                            class="text-xs text-[#666666] border border-[#E5E5E5] px-3 py-1 hover:bg-[#F5F5F5] transition-colors">
                        🔗 Share
                    </button>
                </div>
            </div>
        </div>

        {{-- Posts Container --}}
        <div id="posts-container" class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-4">
            {{-- Split posts into pinned and normal --}}
            @php
                $pinnedPosts = $posts->where('is_pinned', true);
                $normalPosts = $posts->where('is_pinned', false);
            @endphp

            {{-- Pinned Section --}}
            @if($pinnedPosts->count() > 0)
                <div class="mb-4 border-l-4 border-[#000000] bg-[#FAFAFA] p-4">
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-[#666666] mb-2">📌 Pinned Posts</h4>
                    @foreach($pinnedPosts as $post)
                        @include('partials._post', ['post' => $post, 'inPinned' => true])
                    @endforeach
                </div>
            @endif

            {{-- Normal Posts --}}
            @forelse($normalPosts as $post)
                @include('partials._post', ['post' => $post, 'inPinned' => false])
            @empty
                <div class="bg-white border border-[#E5E5E5] p-12 text-center" id="empty-state">
                    <p class="text-sm text-[#666666]">No posts yet. Be the first to reply!</p>
                </div>
            @endforelse

            {{-- Reply Form (main) --}}
            <div class="bg-white border border-[#E5E5E5] p-4" id="main-reply-form">
                <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="topic_id" value="{{ $topic->id }}">
                    <div class="space-y-3">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Write a Reply</label>
                        <textarea name="content" rows="3" required 
                                  class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors"
                                  placeholder="Share your thoughts..."></textarea>

                        {{-- File attachment input --}}
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
                                <span class="text-xs text-[#666666]">Make this post private</span>
                            </label>
                            <button type="submit" 
                                    class="bg-[#000000] text-white px-6 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                                Post Reply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================================
    // 1. BIND REPLY TOGGLES
    // ============================================================
    function bindReplyToggles() {
        document.querySelectorAll('.reply-toggle').forEach(button => {
            button.removeEventListener('click', replyToggleHandler);
            button.addEventListener('click', replyToggleHandler);
        });
    }

    function replyToggleHandler(e) {
        const postId = this.dataset.postId;
        const form = document.getElementById('reply-form-' + postId);
        if (form) {
            form.classList.toggle('hidden');
        }
    }

    // ============================================================
    // 2. BIND REPLY FORMS (AJAX)
    // ============================================================
    function bindReplyForms() {
        document.querySelectorAll('.reply-form-ajax').forEach(form => {
            form.removeEventListener('submit', replyFormHandler);
            form.addEventListener('submit', replyFormHandler);
        });
    }

    function replyFormHandler(e) {
        e.preventDefault();

        const parentId = this.dataset.parentId;
        const topicId = this.dataset.topicId;
        const content = this.querySelector('textarea[name="content"]').value.trim();
        const isPrivate = this.querySelector('input[name="is_private"]')?.checked ? 1 : 0;
        const submitBtn = this.querySelector('.reply-submit-btn');
        const originalText = submitBtn.textContent;

        if (!content) {
            alert('Please enter a reply.');
            return;
        }

        submitBtn.textContent = 'Posting...';
        submitBtn.disabled = true;

        // Build FormData for file uploads
        const formData = new FormData(this);
        formData.append('topic_id', topicId);
        formData.append('parent_id', parentId);
        formData.append('content', content);
        formData.append('is_private', isPrivate);

        fetch('{{ route("posts.reply") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const newPost = createPostHTML(data.post);
                const parentPost = document.getElementById('post-' + parentId);
                let childrenContainer = parentPost.querySelector('.children-container');
                if (!childrenContainer) {
                    childrenContainer = document.createElement('div');
                    childrenContainer.className = 'ml-6 mt-3 space-y-3 border-l-2 border-[#E5E5E5] pl-4 children-container';
                    parentPost.appendChild(childrenContainer);
                }
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = newPost;
                childrenContainer.appendChild(tempDiv.firstElementChild);

                this.querySelector('textarea[name="content"]').value = '';
                this.closest('.reply-form').classList.add('hidden');

                bindLikeEvents();
                bindReplyToggles();
                bindReplyForms();
            }
        })
        .catch(error => {
            console.error('Error posting reply:', error);
            alert('Failed to post reply. Please try again.');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }

    // ============================================================
    // 3. BIND MAIN POST FORM (AJAX)
    // ============================================================
    const mainForm = document.getElementById('main-reply-form')?.querySelector('form');
    if (mainForm) {
        mainForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;

            submitBtn.textContent = 'Posting...';
            submitBtn.disabled = true;

            fetch('{{ route("posts.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const newPost = createPostHTML(data.post);
                    const postsContainer = document.getElementById('posts-container');
                    const mainReplyForm = document.getElementById('main-reply-form');
                    
                    // 🆕 Remove empty state if present
                    const emptyState = postsContainer.querySelector('.bg-white.border.border-\\[\\#E5E5E5\\]\\.p-12\\.text-center');
                    if (emptyState) emptyState.remove();

                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = newPost;
                    postsContainer.insertBefore(tempDiv.firstElementChild, mainReplyForm);
                    this.querySelector('textarea[name="content"]').value = '';
                    updateReplyCount();
                    bindLikeEvents();
                    bindReplyToggles();
                    bindReplyForms();
                }
            })
            .catch(error => {
                console.error('Error posting:', error);
                alert('Failed to post. Please try again.');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    // ============================================================
    // 4. BIND LIKE EVENTS
    // ============================================================
    function bindLikeEvents() {
        document.querySelectorAll('.like-btn').forEach(button => {
            button.removeEventListener('click', likeHandler);
            button.addEventListener('click', likeHandler);
        });
    }

    function likeHandler(e) {
        const button = e.currentTarget;
        const postId = button.dataset.postId;
        const likeIcon = button.querySelector('.like-icon');
        const likeCount = button.querySelector('.like-count');

        button.disabled = true;

        fetch('{{ url("/posts") }}/' + postId + '/like', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                likeIcon.textContent = data.liked ? '❤️' : '🤍';
                likeCount.textContent = data.count;
            }
        })
        .catch(error => {
            console.error('Error toggling like:', error);
        })
        .finally(() => {
            button.disabled = false;
        });
    }

    // ============================================================
    // 5. HELPER FUNCTIONS
    // ============================================================
    function createPostHTML(post) {
        const isLiked = post.is_liked ? '❤️' : '🤍';

        // Build attachments HTML
        let attachmentsHtml = '';
        if (post.attachments && post.attachments.length > 0) {
            attachmentsHtml = '<div class="mt-3 flex flex-wrap gap-2">';
            post.attachments.forEach(file => {
                if (file.is_image) {
                    attachmentsHtml += `
                        <a href="${file.url}" target="_blank" class="block border">
                            <img src="${file.url}" class="max-w-xs max-h-48 object-contain">
                        </a>
                    `;
                } else {
                    attachmentsHtml += `
                        <a href="${file.url}" target="_blank" class="text-xs text-[#2563EB] border border-[#E5E5E5] px-3 py-1 hover:bg-[#F5F5F5] transition-colors">
                            📎 ${file.name}
                        </a>
                    `;
                }
            });
            attachmentsHtml += '</div>';
        }

        return `
            <div class="bg-white border border-[#E5E5E5] p-4" id="post-${post.id}" data-post-id="${post.id}">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-3 min-w-0">
                        <span class="text-sm font-bold text-[#000000]">${post.author.name}</span>
                        <span class="text-[10px] text-[#666666] flex-shrink-0">${post.created_at}</span>
                        ${post.is_private ? '<span class="text-[8px] font-bold uppercase tracking-wider text-[#DC2626] border border-[#DC2626] px-1.5 py-0.5 flex-shrink-0">Private</span>' : ''}
                    </div>
                    <div class="flex items-center space-x-3 flex-shrink-0">
                        <button class="like-btn text-xs text-[#666666] hover:text-[#000000] transition-colors flex items-center space-x-1" data-post-id="${post.id}">
                            <span class="like-icon">${isLiked}</span>
                            <span class="like-count">${post.likes_count || 0}</span>
                        </button>
                        <button class="reply-toggle text-xs text-[#666666] hover:text-[#000000] transition-colors" data-post-id="${post.id}">
                            💬 Reply
                        </button>
                    </div>
                </div>
                <p class="text-sm text-[#000000] leading-relaxed">${post.content}</p>
                ${attachmentsHtml}
                <div class="mt-3 hidden reply-form" id="reply-form-${post.id}">
                    <form class="reply-form-ajax" data-parent-id="${post.id}" data-topic-id="{{ $topic->id }}">
                        @csrf
                        <div class="border-l-2 border-[#E5E5E5] pl-4 space-y-2">
                            <textarea name="content" rows="2" required 
                                      class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors"
                                      placeholder="Write a reply..."></textarea>
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
            </div>
        `;
    }

    function updateReplyCount() {
        const posts = document.querySelectorAll('#posts-container > .bg-white.border');
        const count = posts.length - 1;
        const replySpan = document.querySelector('.context_panel .p-2.text-xs.font-bold');
        if (replySpan) {
            replySpan.textContent = `• ${count} replies`;
        }
    }

    // ============================================================
    // 6. LONG POLLING - STATELESS (no session blocking)
    // ============================================================
    let lastPostId = {{ $posts->last()->id ?? 0 }};
    let isPolling = false;
    let longPollTimeout = null;
    const userId = {{ Auth::id() }};

    function startLongPoll() {
        if (isPolling) return;
        isPolling = true;

        fetch('{{ route("topics.poll", $topic->id) }}?last_post_id=' + lastPostId + '&user_id=' + userId, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            isPolling = false;
            if (data.has_updates && data.post) {
                if (data.post.author_id != userId) {
                    showNewPostNotification(data.post);
                    lastPostId = data.post.id;
                } else {
                    lastPostId = Math.max(lastPostId, data.post.id);
                }
            }
            longPollTimeout = setTimeout(startLongPoll, 2000);
        })
        .catch(error => {
            isPolling = false;
            console.log('Long poll error:', error);
            longPollTimeout = setTimeout(startLongPoll, 5000);
        });
    }

    function showNewPostNotification(post) {
        dismissNotification();

        const banner = document.createElement('div');
        banner.id = 'new-post-notification';
        banner.className = 'fixed bottom-24 left-1/2 transform -translate-x-1/2 bg-[#000000] text-white px-6 py-4 z-50 border border-[#E5E5E5] shadow-xl max-w-lg w-full';
        banner.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4 min-w-0">
                    <span class="text-lg font-bold text-white flex-shrink-0">1</span>
                    <span class="text-sm font-medium text-white truncate">new reply</span>
                    <span class="text-xs text-[#999999] truncate hidden sm:inline">
                        by ${post.author}
                    </span>
                </div>
                <div class="flex items-center space-x-3 flex-shrink-0">
                    <button onclick="dismissNotification()" 
                            class="text-xs text-[#999999] hover:text-white transition-colors">
                        Later
                    </button>
                    <button onclick="reloadPage()" 
                            class="bg-white text-[#000000] px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#E5E5E5] transition-colors">
                        View Now
                    </button>
                </div>
            </div>
            <div class="mt-2 text-xs text-[#999999] border-t border-[#333333] pt-2">
                Auto-refreshing in 5 seconds...
            </div>
        `;
        document.body.appendChild(banner);

        window.reloadTimer = setTimeout(() => {
            reloadPage();
        }, 5000);
    }

    function dismissNotification() {
        const existing = document.getElementById('new-post-notification');
        if (existing) {
            existing.remove();
        }
        if (window.reloadTimer) {
            clearTimeout(window.reloadTimer);
            window.reloadTimer = null;
        }
    }

    function reloadPage() {
        dismissNotification();
        window.location.reload();
    }

    // ─── SHARE: Copy Link to Clipboard ───
    window.copyLink = function() {
        const url = window.location.href;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url)
                .then(() => alert('Link copied to clipboard!'))
                .catch(() => fallbackCopy(url));
        } else {
            fallbackCopy(url);
        }
    };

    function fallbackCopy(url) {
        const input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        try {
            document.execCommand('copy');
            alert('Link copied to clipboard!');
        } catch (e) {
            alert('Failed to copy link. Please copy manually.');
        }
        input.remove();
    }

    // ============================================================
    // 7. CLEANUP ON PAGE UNLOAD
    // ============================================================
    window.addEventListener('beforeunload', function() {
        if (longPollTimeout) {
            clearTimeout(longPollTimeout);
            longPollTimeout = null;
        }
        isPolling = false;
    });

    // ============================================================
    // 8. INITIALIZATION
    // ============================================================
    bindReplyToggles();
    bindReplyForms();
    bindLikeEvents();

    // Start long polling after 3 seconds
    setTimeout(startLongPoll, 3000);
});
</script>
@endpush