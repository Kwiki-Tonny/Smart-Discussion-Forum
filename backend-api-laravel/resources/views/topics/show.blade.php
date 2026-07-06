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
                    <button class="text-xs text-[#666666] border border-[#E5E5E5] px-3 py-1 hover:bg-[#F5F5F5] transition-colors">
                        📄 Export PDF
                    </button>
                    <button class="text-xs text-[#666666] border border-[#E5E5E5] px-3 py-1 hover:bg-[#F5F5F5] transition-colors">
                        🔗 Share
                    </button>
                </div>
            </div>
        </div>

        {{-- Posts --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-4">
            @forelse($posts as $post)
                <div class="bg-white border border-[#E5E5E5] p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-bold text-[#000000]">{{ $post->author->name ?? 'Unknown' }}</span>
                            <span class="text-[10px] text-[#666666]">{{ $post->created_at->diffForHumans() }}</span>
                            @if($post->is_private)
                                <span class="text-[8px] font-bold uppercase tracking-wider text-[#DC2626] border border-[#DC2626] px-1.5 py-0.5">Private</span>
                            @endif
                        </div>
                        <div class="flex items-center space-x-3">
                            <button class="text-xs text-[#666666] hover:text-[#000000] transition-colors">
                                Likes {{ $post->likes_count ?? 0 }}
                            </button>
                            <button class="text-xs text-[#666666] hover:text-[#000000] transition-colors">
                                Reply
                            </button>
                        </div>
                    </div>
                    <p class="text-sm text-[#000000] leading-relaxed">{{ $post->content }}</p>
                </div>
            @empty
                <div class="bg-white border border-[#E5E5E5] p-12 text-center">
                    <p class="text-sm text-[#666666]">No posts yet. Be the first to reply!</p>
                </div>
            @endforelse

            {{-- Reply Form --}}
            <div class="bg-white border border-[#E5E5E5] p-4">
                <form method="POST" action="{{ route('posts.store') }}">
                    @csrf
                    <input type="hidden" name="topic_id" value="{{ $topic->id }}">
                    <div class="space-y-3">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Write a Reply</label>
                        <textarea name="content" rows="3" required 
                                  class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors"
                                  placeholder="Share your thoughts..."></textarea>
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