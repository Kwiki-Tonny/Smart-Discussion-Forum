@extends('layouts.workspace')

@section('title', $topic->title)

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('groups.topics', $topic->group_id) }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] truncate">{{ $topic->group->name }}</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-3 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#0A574F]">{{ $posts->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Replies</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#2563EB]">{{ $topic->creator->name ?? 'Unknown' }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Author</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#D97706]">{{ $topic->created_at->format('M d, Y') }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Created</p>
            </div>
        </div>
        @if($topic->ml_category)
            <div class="mt-2 text-center">
                <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-3 py-1 rounded-full inline-flex items-center gap-1">
                    <i data-lucide="tag" style="width:10px;height:10px;"></i>
                    {{ $topic->ml_category }}
                </span>
            </div>
        @endif
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Topic Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3">
                        <i data-lucide="message-circle" style="width:28px;height:28px;color:#0A574F;"></i>
                        <h1 class="text-2xl font-bold text-[#000000] truncate">{{ $topic->title }}</h1>
                    </div>
                    @if($topic->description)
                        <p class="text-sm text-[#666666] mt-1 pl-9">{{ $topic->description }}</p>
                    @endif
                    <div class="flex items-center flex-wrap gap-3 mt-2 pl-9">
                        <span class="text-xs text-[#666666] flex items-center gap-1">
                            <i data-lucide="user" style="width:12px;height:12px;"></i>
                            by {{ $topic->creator->name ?? 'Unknown' }}
                        </span>
                        <span class="text-[10px] text-[#666666]">•</span>
                        <span class="text-[10px] text-[#666666] flex items-center gap-1">
                            <i data-lucide="clock" style="width:12px;height:12px;"></i>
                            {{ $topic->created_at->diffForHumans() }}
                        </span>
                        <span class="text-[10px] text-[#666666]">•</span>
                        <span class="text-[10px] text-[#2563EB] flex items-center gap-1">
                            <i data-lucide="message-square" style="width:12px;height:12px;"></i>
                            {{ $posts->count() }} replies
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                    <a href="{{ route('topics.export', $topic->id) }}"
                       class="flex items-center gap-1 text-xs font-bold uppercase tracking-wider bg-[#0A574F] text-white px-4 py-1.5 rounded-lg hover:bg-[#08443e] transition hover:shadow-md">
                        <i data-lucide="file-text" style="width:14px;height:14px;"></i>
                        Export PDF
                    </a>
                    <button onclick="copyLink()"
                            class="flex items-center gap-1 text-xs font-bold uppercase tracking-wider bg-[#2563EB] text-white px-4 py-1.5 rounded-lg hover:bg-[#1d4ed8] transition hover:shadow-md">
                        <i data-lucide="share-2" style="width:14px;height:14px;"></i>
                        Share
                    </button>
                </div>
            </div>
        </div>

        {{-- Posts Container --}}
        <div id="posts-container" class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-4">

            {{-- Compact Pinned Section --}}
            @php
                $pinnedPosts = $posts->where('is_pinned', true);
            @endphp

            @if($pinnedPosts->count() > 0)
                <div class="sticky top-0 z-10 bg-[#FAFAFA] border-l-4 border-[#0A574F] p-4 shadow-sm -mx-6 px-6 rounded-lg">
                    <div class="flex items-center gap-2 mb-2">
                        <i data-lucide="pin" style="width:14px;height:14px;color:#0A574F;"></i>
                        <h4 class="text-[10px] font-bold uppercase tracking-wider text-[#666666]">Pinned Messages</h4>
                    </div>
                    <div class="space-y-2">
                        @foreach($pinnedPosts as $post)
                            <div class="flex items-center justify-between bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 hover:bg-[#F9F9F9] transition">
                                <div class="flex items-center gap-3 min-w-0 cursor-pointer jump-to-post flex-1" data-post-id="{{ $post->id }}">
                                    <div class="w-6 h-6 bg-[#ECFDF5] rounded-full flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="user" style="width:12px;height:12px;color:#0A574F;"></i>
                                    </div>
                                    <span class="text-sm font-bold text-[#000000]">{{ $post->author->name ?? 'Unknown' }}</span>
                                    <span class="text-xs text-[#666666] truncate">{{ Str::limit($post->content, 60) }}</span>
                                    <span class="text-[10px] text-[#2563EB] ml-2 flex items-center gap-1">
                                        <i data-lucide="arrow-right" style="width:10px;height:10px;"></i>
                                        Jump
                                    </span>
                                </div>
                                <button class="text-[10px] text-[#DC2626] hover:underline pin-btn flex-shrink-0 ml-2" data-post-id="{{ $post->id }}">
                                    <i data-lucide="pin-off" style="width:12px;height:12px;"></i>
                                    Unpin
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- All Posts --}}
            @forelse($posts as $post)
                @include('partials._post', ['post' => $post, 'inPinned' => false])
            @empty
                <div class="bg-white rounded-lg border border-dashed border-[#E5E5E5] p-12 text-center" id="empty-state">
                    <i data-lucide="message-circle" style="width:48px;height:48px;color:#94A3B8;margin:0 auto 0.75rem;display:block;"></i>
                    <p class="text-sm font-medium text-[#000000]">No posts yet</p>
                    <p class="text-xs text-[#666666] mt-1">Be the first to reply to this topic!</p>
                </div>
            @endforelse

            {{-- Reply Form --}}
            <div class="bg-white rounded-lg border-2 border-[#0A574F] shadow-sm p-5" id="main-reply-form">
                <div class="flex items-center gap-2 border-b border-[#E5E5E5] pb-3 mb-4">
                    <i data-lucide="message-square" style="width:18px;height:18px;color:#0A574F;"></i>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#000000]">Write a Reply</h3>
                </div>
                <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="topic_id" value="{{ $topic->id }}">
                    <div class="space-y-3">
                        <textarea name="content" rows="3" required
                                  class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition"
                                  placeholder="Share your thoughts..."></textarea>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wide text-[#666666] flex items-center gap-1">
                                <i data-lucide="paperclip" style="width:12px;height:12px;"></i>
                                Attach Files
                            </label>
                            <input type="file" name="attachments[]" multiple
                                   accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                                   class="w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-1.5 text-sm file:mr-3 file:py-1 file:px-3 file:text-xs file:font-bold file:border-0 file:bg-[#0A574F] file:text-white hover:file:bg-[#08443e] transition">
                            <p class="text-[9px] text-[#666666] mt-1 flex items-center gap-1">
                                <i data-lucide="info" style="width:10px;height:10px;"></i>
                                Max 5MB per file. Supported: images, PDF, Word, Excel, TXT
                            </p>
                        </div>

                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-[#666666]">
                                <input type="checkbox" name="is_private" value="1" class="accent-[#0A574F] w-4 h-4 rounded">
                                <span>Make this post private</span>
                            </label>
                            <button type="submit"
                                    class="flex items-center gap-2 bg-[#0A574F] text-white px-6 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition hover:shadow-sm">
                                <i data-lucide="send" style="width:14px;height:14px;"></i>
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
                bindPinEvents();
                bindReplyToggles();
                bindReplyForms();
                lucide.createIcons();
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

                    const emptyState = postsContainer.querySelector('#empty-state');
                    if (emptyState) emptyState.remove();

                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = newPost;
                    postsContainer.insertBefore(tempDiv.firstElementChild, mainReplyForm);
                    this.querySelector('textarea[name="content"]').value = '';
                    updateReplyCount();
                    bindLikeEvents();
                    bindPinEvents();
                    bindReplyToggles();
                    bindReplyForms();
                    lucide.createIcons();
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
                likeIcon.innerHTML = data.liked
                    ? '<i data-lucide="heart" style="width:14px;height:14px;fill:#DC2626;color:#DC2626;"></i>'
                    : '<i data-lucide="heart" style="width:14px;height:14px;"></i>';
                likeCount.textContent = data.count;
                lucide.createIcons();
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
    // 5. BIND PIN EVENTS
    // ============================================================
    function bindPinEvents() {
        document.querySelectorAll('.pin-btn').forEach(button => {
            button.removeEventListener('click', pinHandler);
            button.addEventListener('click', pinHandler);
        });
    }

    function pinHandler(e) {
        const button = e.currentTarget;
        const postId = button.dataset.postId;

        button.disabled = true;

        fetch('{{ url("/posts") }}/' + postId + '/pin', {
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
                window.location.reload();
            } else {
                alert(data.message || 'Failed to pin/unpin.');
            }
        })
        .catch(error => {
            console.error('Error toggling pin:', error);
            alert('An error occurred. Please try again.');
        })
        .finally(() => {
            button.disabled = false;
        });
    }

    // ============================================================
    // 6. BIND JUMP TO POST
    // ============================================================
    function bindJumpToPost() {
        document.querySelectorAll('.jump-to-post').forEach(el => {
            el.removeEventListener('click', jumpToPostHandler);
            el.addEventListener('click', jumpToPostHandler);
        });
    }

    function jumpToPostHandler(e) {
        const postId = this.dataset.postId;
        const target = document.getElementById('post-' + postId);
        if (target) {
            document.querySelectorAll('.highlight-flash').forEach(el => el.classList.remove('highlight-flash'));
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            target.classList.add('highlight-flash');
            setTimeout(() => target.classList.remove('highlight-flash'), 2000);
        }
    }

    // ============================================================
    // 7. HELPER FUNCTIONS
    // ============================================================
    function createPostHTML(post) {
        const isLiked = post.is_liked
            ? '<i data-lucide="heart" style="width:14px;height:14px;fill:#DC2626;color:#DC2626;"></i>'
            : '<i data-lucide="heart" style="width:14px;height:14px;"></i>';

        let attachmentsHtml = '';
        if (post.attachments && post.attachments.length > 0) {
            attachmentsHtml = '<div class="mt-3 flex flex-wrap gap-2">';
            post.attachments.forEach(file => {
                if (file.is_image) {
                    attachmentsHtml += `
                        <a href="${file.url}" target="_blank" class="block border rounded-lg overflow-hidden">
                            <img src="${file.url}" class="max-w-xs max-h-48 object-contain">
                        </a>
                    `;
                } else {
                    attachmentsHtml += `
                        <a href="${file.url}" target="_blank" class="flex items-center gap-1 text-xs text-[#2563EB] border border-[#E5E5E5] rounded-lg px-3 py-1.5 hover:bg-[#F9F9F9] transition">
                            <i data-lucide="file" style="width:12px;height:12px;"></i>
                            ${file.name}
                        </a>
                    `;
                }
            });
            attachmentsHtml += '</div>';
        }

        return `
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm hover:shadow-md transition p-5" id="post-${post.id}" data-post-id="${post.id}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="w-8 h-8 bg-[#ECFDF5] rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="user" style="width:14px;height:14px;color:#0A574F;"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center flex-wrap gap-2">
                                <span class="text-sm font-bold text-[#000000]">${post.author.name}</span>
                                <span class="text-[10px] text-[#666666]">${post.created_at}</span>
                                ${post.is_private ? '<span class="text-[8px] font-bold uppercase tracking-wider text-[#DC2626] border border-[#DC2626] px-1.5 py-0.5 rounded-full">Private</span>' : ''}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                        <button class="like-btn text-xs text-[#666666] hover:text-[#000000] transition-colors flex items-center gap-1 px-2 py-1 rounded-lg hover:bg-[#F9F9F9]" data-post-id="${post.id}">
                            <span class="like-icon">${isLiked}</span>
                            <span class="like-count text-sm font-medium">${post.likes_count || 0}</span>
                        </button>
                        <button class="reply-toggle text-xs text-[#666666] hover:text-[#000000] transition-colors flex items-center gap-1 px-2 py-1 rounded-lg hover:bg-[#F9F9F9]" data-post-id="${post.id}">
                            <i data-lucide="reply" style="width:14px;height:14px;"></i>
                            Reply
                        </button>
                        <button class="pin-btn text-xs text-[#666666] hover:text-[#000000] transition-colors flex items-center gap-1 px-2 py-1 rounded-lg hover:bg-[#F9F9F9]" data-post-id="${post.id}">
                            <i data-lucide="${post.is_pinned ? 'pin-off' : 'pin'}" style="width:14px;height:14px;"></i>
                            ${post.is_pinned ? 'Unpin' : 'Pin'}
                        </button>
                    </div>
                </div>
                <p class="text-sm text-[#000000] leading-relaxed mt-2">${post.content}</p>
                ${attachmentsHtml}
                <div class="mt-3 hidden reply-form" id="reply-form-${post.id}">
                    <form class="reply-form-ajax" data-parent-id="${post.id}" data-topic-id="{{ $topic->id }}">
                        @csrf
                        <div class="border-l-2 border-[#0A574F] pl-4 space-y-2">
                            <textarea name="content" rows="2" required
                                      class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition"
                                      placeholder="Write a reply..."></textarea>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wide text-[#666666] flex items-center gap-1">
                                    <i data-lucide="paperclip" style="width:12px;height:12px;"></i>
                                    Attach Files
                                </label>
                                <input type="file" name="attachments[]" multiple
                                       accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                                       class="w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-1.5 text-sm">
                                <p class="text-[9px] text-[#666666] mt-1">Max 5MB per file. Supported: images, PDF, Word, Excel, TXT</p>
                            </div>
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <label class="flex items-center gap-2 cursor-pointer text-xs text-[#666666]">
                                    <input type="checkbox" name="is_private" value="1" class="accent-[#0A574F] w-4 h-4 rounded">
                                    Private
                                </label>
                                <button type="submit"
                                        class="reply-submit-btn flex items-center gap-1 bg-[#0A574F] text-white px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition">
                                    <i data-lucide="send" style="width:12px;height:12px;"></i>
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
        const posts = document.querySelectorAll('#posts-container > .bg-white.rounded-lg');
        const count = posts.length - 1;
        const replySpan = document.querySelector('.context_panel .p-2.text-xs.font-bold');
        if (replySpan) {
            replySpan.textContent = `• ${count} replies`;
        }
    }

    // ============================================================
    // 8. LONG POLLING
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
        banner.className = 'fixed bottom-24 left-1/2 transform -translate-x-1/2 bg-[#0A574F] text-white px-6 py-4 z-50 rounded-lg shadow-xl max-w-lg w-full border border-[#08443e]';
        banner.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4 min-w-0">
                    <i data-lucide="message-circle" style="width:20px;height:20px;flex-shrink:0;"></i>
                    <span class="text-sm font-medium text-white truncate">new reply from ${post.author}</span>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <button onclick="dismissNotification()"
                            class="text-xs text-white/70 hover:text-white transition">
                        Later
                    </button>
                    <button onclick="reloadPage()"
                            class="bg-white text-[#0A574F] px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#F9F9F9] transition">
                        View Now
                    </button>
                </div>
            </div>
            <div class="mt-2 text-xs text-white/50 border-t border-white/10 pt-2 flex items-center gap-1">
                <i data-lucide="clock" style="width:12px;height:12px;"></i>
                Auto-refreshing in 5 seconds...
            </div>
        `;
        document.body.appendChild(banner);
        lucide.createIcons();

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

    // ─── SHARE ──────────────────────────────────────────────────
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

    // ─── CSS for Highlight Flash ──────────────────────────────
    const style = document.createElement('style');
    style.textContent = `
        .highlight-flash {
            animation: flashBg 2s ease;
        }
        @keyframes flashBg {
            0% { background-color: #fef3c7; }
            100% { background-color: transparent; }
        }
    `;
    document.head.appendChild(style);

    // ============================================================
    // 9. CLEANUP
    // ============================================================
    window.addEventListener('beforeunload', function() {
        if (longPollTimeout) {
            clearTimeout(longPollTimeout);
            longPollTimeout = null;
        }
        isPolling = false;
    });

    // ============================================================
    // 10. INITIALIZATION
    // ============================================================
    bindReplyToggles();
    bindReplyForms();
    bindLikeEvents();
    bindPinEvents();
    bindJumpToPost();

    setTimeout(startLongPoll, 3000);
});
</script>
@endpush
