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
                <p class="text-[12px] font-bold text-[#2563EB]">{{ $topic->creator->name ?? 'Unknown' }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Author</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-[12px] font-bold text-[#D97706]">{{ $topic->created_at->format('M d, Y') }}</p>
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
        <div id="posts-container" class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-4 pb-32">

            {{-- ========== PINNED SECTION ========== --}}
            @php
                $pinnedPosts = $posts->where('is_pinned', true);
                $regularPosts = $posts->where('is_pinned', false);
                $lastPostId = $posts->last()->id ?? 0;
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

            {{-- ========== REGULAR POSTS (non‑pinned) ========== --}}
            @forelse($regularPosts as $post)
                @include('partials._post', ['post' => $post, 'inPinned' => false])
            @empty
                <div class="bg-white rounded-lg border border-dashed border-[#E5E5E5] p-12 text-center" id="empty-state">
                    <i data-lucide="message-circle" style="width:48px;height:48px;color:#94A3B8;margin:0 auto 0.75rem;display:block;"></i>
                    <p class="text-sm font-medium text-[#000000]">No posts yet</p>
                    <p class="text-xs text-[#666666] mt-1">Be the first to reply to this topic!</p>
                </div>
            @endforelse

            <div class="h-4"></div>
        </div>

        {{-- ========== STICKY REPLY BAR ========== --}}
        <div class="sticky bottom-0 bg-white border-t border-[#E5E5E5] p-3 shadow-lg" id="main-reply-form">
            <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" id="main-form">
                @csrf
                <input type="hidden" name="topic_id" value="{{ $topic->id }}">
                <div class="flex items-end gap-2">
                    {{-- Paperclip (left) --}}
                    <label for="main-file-input" class="cursor-pointer text-[#666666] hover:text-[#0A574F] transition p-2 rounded-full hover:bg-[#F0F0F0] flex-shrink-0">
                        <i data-lucide="paperclip" style="width:20px;height:20px;"></i>
                    </label>
                    <input type="file" name="attachments[]" multiple id="main-file-input"
                           accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                           class="hidden">
                    <span id="main-file-names" class="text-[10px] text-[#666666] truncate max-w-[100px] hidden"></span>

                    {{-- Text input --}}
                    <div class="flex-1 relative">
                        <textarea name="content" rows="1" required
                                  class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition resize-none"
                                  placeholder="Write a reply..." style="min-height:40px; max-height:120px;"
                                  oninput="this.style.height = 'auto'; this.style.height = Math.min(this.scrollHeight, 120) + 'px';"></textarea>
                    </div>

                    {{-- Send --}}
                    <button type="submit"
                            class="flex items-center justify-center bg-[#0A574F] text-white p-2 rounded-full hover:bg-[#08443e] transition hover:shadow-sm w-10 h-10 flex-shrink-0">
                        <i data-lucide="send" style="width:18px;height:18px;"></i>
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ============================================================
    // 1. FILE INPUT – main form
    // ============================================================
    const mainFileInput = document.getElementById('main-file-input');
    const mainFileNames = document.getElementById('main-file-names');
    if (mainFileInput) {
        mainFileInput.addEventListener('change', function() {
            const names = Array.from(this.files).map(f => f.name).join(', ');
            if (names) {
                mainFileNames.textContent = names;
                mainFileNames.classList.remove('hidden');
            } else {
                mainFileNames.classList.add('hidden');
            }
        });
    }

    // ============================================================
    // 2. REPLY TOGGLES
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
    // 3. REPLY FORMS (AJAX)
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
        const originalHTML = submitBtn.innerHTML;

        if (!content) {
            alert('Please enter a reply.');
            return;
        }

        submitBtn.innerHTML = '<i data-lucide="loader-circle" style="width:14px;height:14px;animation:spin 1s linear infinite;"></i>';
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
                    childrenContainer.className = 'children-container mt-3 space-y-3 hidden';
                    childrenContainer.id = 'children-container-' + parentId;
                    parentPost.appendChild(childrenContainer);
                }
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = newPost;
                childrenContainer.appendChild(tempDiv.firstElementChild);

                // Update toggle button if it exists
                const toggleBtn = parentPost.querySelector('.toggle-replies-btn');
                if (toggleBtn) {
                    const count = childrenContainer.querySelectorAll('.post-bubble').length;
                    toggleBtn.innerHTML = `<i data-lucide="chevron-down" style="width:12px;height:12px;"></i> Show ${count} repl${count > 1 ? 'ies' : 'y'}`;
                    if (childrenContainer.classList.contains('hidden')) {
                        // keep hidden, no change
                    } else {
                        // if visible, keep visible
                    }
                } else {
                    // if no toggle button yet (first reply), create one
                    const newToggle = document.createElement('button');
                    newToggle.className = 'toggle-replies-btn text-xs text-[#2563EB] hover:underline mt-2 flex items-center gap-1';
                    newToggle.dataset.postId = parentId;
                    newToggle.innerHTML = `<i data-lucide="chevron-down" style="width:12px;height:12px;"></i> Show 1 reply`;
                    parentPost.insertBefore(newToggle, childrenContainer);
                    // bind toggle event
                    newToggle.addEventListener('click', toggleRepliesHandler);
                }

                this.querySelector('textarea[name="content"]').value = '';
                this.closest('.reply-form').classList.add('hidden');

                bindAllEvents();
                lucide.createIcons();
            }
        })
        .catch(error => {
            console.error('Error posting reply:', error);
            alert('Failed to post reply. Please try again.');
        })
        .finally(() => {
            submitBtn.innerHTML = '<i data-lucide="send" style="width:14px;height:14px;"></i>';
            submitBtn.disabled = false;
            lucide.createIcons();
        });
    }

    // ============================================================
    // 4. MAIN POST FORM (AJAX)
    // ============================================================
    const mainForm = document.getElementById('main-form');
    if (mainForm) {
        mainForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHTML = submitBtn.innerHTML;

            submitBtn.innerHTML = '<i data-lucide="loader-circle" style="width:18px;height:18px;animation:spin 1s linear infinite;"></i>';
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
                    const fileInput = document.getElementById('main-file-input');
                    if (fileInput) fileInput.value = '';
                    document.getElementById('main-file-names').textContent = '';
                    document.getElementById('main-file-names').classList.add('hidden');

                    updateReplyCount();
                    bindAllEvents();
                    lucide.createIcons();
                }
            })
            .catch(error => {
                console.error('Error posting:', error);
                alert('Failed to post. Please try again.');
            })
            .finally(() => {
                submitBtn.innerHTML = originalHTML;
                submitBtn.disabled = false;
                lucide.createIcons();
            });
        });
    }

    // ============================================================
    // 5. LIKE EVENTS
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
    // 6. PIN EVENTS
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
                window.location.reload(); // Pins affect ordering, simple reload
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
    // 7. JUMP TO POST
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
    // 8. TOGGLE REPLIES (collapsible nested replies)
    // ============================================================
    function bindToggleReplies() {
        document.querySelectorAll('.toggle-replies-btn').forEach(button => {
            button.removeEventListener('click', toggleRepliesHandler);
            button.addEventListener('click', toggleRepliesHandler);
        });
    }

    function toggleRepliesHandler(e) {
        const postId = this.dataset.postId;
        const container = document.getElementById('children-container-' + postId);
        if (!container) return;

        const isHidden = container.classList.contains('hidden');
        if (isHidden) {
            container.classList.remove('hidden');
            this.innerHTML = '<i data-lucide="chevron-up" style="width:12px;height:12px;"></i> Hide replies';
        } else {
            container.classList.add('hidden');
            const count = container.querySelectorAll('.post-bubble').length;
            this.innerHTML = `<i data-lucide="chevron-down" style="width:12px;height:12px;"></i> Show ${count} repl${count > 1 ? 'ies' : 'y'}`;
        }
        lucide.createIcons();
    }

    // ============================================================
    // 9. BIND ALL EVENTS
    // ============================================================
    function bindAllEvents() {
        bindReplyToggles();
        bindReplyForms();
        bindLikeEvents();
        bindPinEvents();
        bindJumpToPost();
        bindToggleReplies();
        // file inputs for reply forms
        document.querySelectorAll('.reply-file-input').forEach(input => {
            input.removeEventListener('change', replyFileChangeHandler);
            input.addEventListener('change', replyFileChangeHandler);
        });
    }

    function replyFileChangeHandler(e) {
        const input = e.target;
        const namesSpan = document.getElementById(input.id.replace('file-input', 'file-names'));
        if (namesSpan) {
            const names = Array.from(input.files).map(f => f.name).join(', ');
            namesSpan.textContent = names;
            if (names) {
                namesSpan.classList.remove('hidden');
            } else {
                namesSpan.classList.add('hidden');
            }
        }
    }

    // ============================================================
    // 10. createPostHTML (WhatsApp style, with collapsible replies)
    // ============================================================
    function createPostHTML(post) {
        const isLiked = post.is_liked
            ? '<i data-lucide="heart" style="width:14px;height:14px;fill:#DC2626;color:#DC2626;"></i>'
            : '<i data-lucide="heart" style="width:14px;height:14px;"></i>';

        let attachmentsHtml = '';
        if (post.attachments && post.attachments.length > 0) {
            attachmentsHtml = '<div class="mt-2 flex flex-wrap gap-2">';
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

        const fileInputId = 'reply-file-' + post.id;
        const fileNamesId = 'reply-file-names-' + post.id;

        // Build nested children HTML recursively
        let childrenHtml = '';
        let toggleHtml = '';
        if (post.children && post.children.length > 0) {
            const childHtml = post.children.map(child => createPostHTML(child)).join('');
            childrenHtml = `
                <div class="children-container mt-3 space-y-3 hidden" id="children-container-${post.id}">
                    ${childHtml}
                </div>
            `;
            toggleHtml = `
                <button class="toggle-replies-btn text-xs text-[#2563EB] hover:underline mt-2 flex items-center gap-1" data-post-id="${post.id}">
                    <i data-lucide="chevron-down" style="width:12px;height:12px;"></i>
                    Show ${post.children.length} repl${post.children.length > 1 ? 'ies' : 'y'}
                </button>
            `;
        }

        return `
            <div class="post-bubble bg-white rounded-2xl shadow-sm border border-[#E5E5E5] p-3 transition hover:shadow-md" id="post-${post.id}" data-post-id="${post.id}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <div class="w-7 h-7 bg-[#ECFDF5] rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="user" style="width:14px;height:14px;color:#0A574F;"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center flex-wrap gap-1">
                                <span class="text-sm font-bold text-[#000000]">${post.author.name}</span>
                                <span class="text-[10px] text-[#666666]">${post.created_at}</span>
                                ${post.is_private ? '<span class="text-[8px] font-bold uppercase tracking-wider text-[#DC2626] border border-[#DC2626] px-1.5 py-0.5 rounded-full">Private</span>' : ''}
                                ${post.parent_id ? '<span class="text-[8px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-1.5 py-0.5 rounded-full">Reply</span>' : ''}
                                ${post.is_pinned ? '<span class="text-[8px] font-bold uppercase tracking-wider text-[#000000] border border-[#000000] px-1.5 py-0.5 rounded-full flex items-center gap-1"><i data-lucide="pin" style="width:10px;height:10px;"></i> Pinned</span>' : ''}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-0.5 flex-shrink-0 ml-1">
                        <button class="like-btn text-xs text-[#666666] hover:text-[#000000] transition-colors flex items-center gap-1 px-1.5 py-1 rounded-lg hover:bg-[#F0F0F0]" data-post-id="${post.id}">
                            <span class="like-icon">${isLiked}</span>
                            <span class="like-count text-sm font-medium">${post.likes_count || 0}</span>
                        </button>
                        <button class="reply-toggle text-xs text-[#666666] hover:text-[#000000] transition-colors flex items-center gap-1 px-1.5 py-1 rounded-lg hover:bg-[#F0F0F0]" data-post-id="${post.id}">
                            <i data-lucide="reply" style="width:14px;height:14px;"></i>
                        </button>
                        <button class="pin-btn text-xs text-[#666666] hover:text-[#000000] transition-colors flex items-center gap-1 px-1.5 py-1 rounded-lg hover:bg-[#F0F0F0]" data-post-id="${post.id}">
                            <i data-lucide="${post.is_pinned ? 'pin-off' : 'pin'}" style="width:14px;height:14px;"></i>
                        </button>
                    </div>
                </div>
                <p class="text-sm text-[#000000] leading-relaxed mt-1">${post.content}</p>
                ${attachmentsHtml}
                ${toggleHtml}
                <div class="mt-2 hidden reply-form" id="reply-form-${post.id}">
                    <form class="reply-form-ajax" data-parent-id="${post.id}" data-topic-id="{{ $topic->id }}">
                        @csrf
                        <div class="flex items-end gap-2">
                            <label for="${fileInputId}" class="cursor-pointer text-[#666666] hover:text-[#0A574F] transition p-1.5 rounded-full hover:bg-[#F0F0F0] flex-shrink-0">
                                <i data-lucide="paperclip" style="width:16px;height:16px;"></i>
                            </label>
                            <input type="file" name="attachments[]" multiple id="${fileInputId}"
                                   accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                                   class="hidden reply-file-input">
                            <span id="${fileNamesId}" class="text-[9px] text-[#666666] truncate max-w-[80px] hidden"></span>
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
                ${childrenHtml}
            </div>
        `;
    }

    // ============================================================
    // 11. UPDATE REPLY COUNT
    // ============================================================
    function updateReplyCount() {
        const totalPosts = document.querySelectorAll('#posts-container > .post-bubble').length;
        const replySpan = document.querySelector('.context_panel .p-2.text-xs.font-bold');
        if (replySpan) {
            replySpan.textContent = `• ${totalPosts} replies`;
        }
    }

    // ============================================================
    // 12. LONG POLLING
    // ============================================================
    let lastPostId = {{ $lastPostId }};
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
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            isPolling = false;
            if (data.has_updates && data.posts && data.posts.length) {
                const newPosts = data.posts.filter(p => p.author_id != userId);
                if (newPosts.length > 0) {
                    const container = document.getElementById('posts-container');
                    const mainForm = document.getElementById('main-reply-form');
                    newPosts.forEach(post => {
                        const html = createPostHTML(post);
                        const temp = document.createElement('div');
                        temp.innerHTML = html;
                        container.insertBefore(temp.firstElementChild, mainForm);
                        if (post.id > lastPostId) lastPostId = post.id;
                    });
                    bindAllEvents();
                    lucide.createIcons();
                    showNewPostNotification(newPosts.length);
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

    // ============================================================
    // 13. NEW POST NOTIFICATION
    // ============================================================
    let notificationTimer = null;

    function showNewPostNotification(count) {
        dismissNotification();
        const banner = document.createElement('div');
        banner.id = 'new-post-notification';
        banner.className = 'fixed bottom-24 left-1/2 transform -translate-x-1/2 bg-[#0A574F] text-white px-6 py-4 z-50 rounded-lg shadow-xl max-w-lg w-full border border-[#08443e]';
        banner.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4 min-w-0">
                    <i data-lucide="message-circle" style="width:20px;height:20px;flex-shrink:0;"></i>
                    <span class="text-sm font-medium text-white truncate">${count} new repl${count > 1 ? 'ies' : 'y'}</span>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <button onclick="dismissNotification()" class="text-xs text-white/70 hover:text-white transition">Dismiss</button>
                </div>
            </div>
        `;
        document.body.appendChild(banner);
        lucide.createIcons();
        notificationTimer = setTimeout(dismissNotification, 5000);
    }

    function dismissNotification() {
        const existing = document.getElementById('new-post-notification');
        if (existing) existing.remove();
        if (notificationTimer) {
            clearTimeout(notificationTimer);
            notificationTimer = null;
        }
    }
    window.dismissNotification = dismissNotification;

    // ============================================================
    // 14. SHARE LINK
    // ============================================================
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
    // 15. STYLES
    // ============================================================
    if (!document.getElementById('topic-styles')) {
        const style = document.createElement('style');
        style.id = 'topic-styles';
        style.textContent = `
            .highlight-flash { animation: flashBg 2s ease; }
            @keyframes flashBg { 0% { background-color: #fef3c7; } 100% { background-color: transparent; } }
            .children-container { margin-left: 1.5rem; padding-left: 1rem; border-left: 2px solid #E5E5E5; }
            .children-container .post-bubble { background-color: #F9F9F9; border-radius: 1.25rem 1.25rem 1.25rem 0.5rem; }
            .children-container .post-bubble:last-child { border-bottom-left-radius: 1.25rem; }
            .post-bubble { transition: all 0.15s ease; }
            .reply-form textarea { resize: none; }
        `;
        document.head.appendChild(style);
    }

    // ============================================================
    // 16. INIT
    // ============================================================
    bindAllEvents();
    lucide.createIcons();

    setTimeout(startLongPoll, 3000);

    window.addEventListener('beforeunload', function() {
        if (longPollTimeout) {
            clearTimeout(longPollTimeout);
            longPollTimeout = null;
        }
        isPolling = false;
    });

});
</script>
@endpush