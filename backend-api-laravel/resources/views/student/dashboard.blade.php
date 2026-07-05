@extends('layouts.workspace')

@section('title', 'Dashboard')

@section('context_panel')
    {{-- Groups Navigation --}}
    <div class="p-4 border-b border-[#E5E5E5] flex items-center justify-between bg-white sticky top-0">
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Your Groups</h2>
        <button class="text-sm font-bold hover:opacity-60">+</button>
    </div>

    <div class="divide-y divide-[#E5E5E5]">
        @forelse($groups as $group)
            <a href="{{ route('groups.topics', $group->id) }}" 
               class="block p-4 bg-white hover:bg-[#F5F5F5] cursor-pointer transition-colors space-y-1 group">
                <div class="flex justify-between items-baseline">
                    <h3 class="text-sm font-bold text-[#000000]">{{ $group->name }}</h3>
                    <span class="text-[10px] text-[#666666]">{{ $group->topics_count ?? 0 }} topics</span>
                </div>
                @if($group->description)
                    <p class="text-xs text-[#666666] line-clamp-1">{{ $group->description }}</p>
                @endif
                @if($group->latest_topic)
                    <p class="text-[10px] text-[#666666] mt-1">
                        Latest: {{ $group->latest_topic->title }}
                    </p>
                @endif
            </a>
        @empty
            <div class="p-8 text-center">
                <p class="text-sm text-[#666666]">You haven't joined any groups yet.</p>
                <a href="{{ route('groups.index') }}" class="text-sm text-[#000000] underline mt-2 inline-block">
                    Browse available groups
                </a>
            </div>
        @endforelse
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">

        {{-- Welcome Section --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-[#000000]">
                        Welcome back, {{ Auth::user()->name }}
                    </h1>
                    <p class="text-sm text-[#666666] mt-1">
                        Here's what's happening in your groups
                    </p>
                </div>
                <a href="{{ route('topics.create') }}" 
                   class="bg-[#000000] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                    + New Topic
                </a>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Recent Topics (Left Column - 2/3) --}}
                <div class="lg:col-span-2">
                    <div class="bg-white border border-[#E5E5E5]">
                        <div class="border-b border-[#E5E5E5] px-4 py-3">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Recent Topics</h2>
                        </div>
                        <div class="divide-y divide-[#E5E5E5]">
                            @forelse($recentTopics as $topic)
                                <a href="{{ route('topics.show', [$topic->group_id, $topic->id]) }}" 
                                   class="block px-4 py-3 hover:bg-[#F5F5F5] transition-colors">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="text-sm font-semibold text-[#000000]">{{ $topic->title }}</h3>
                                            <div class="flex items-center space-x-3 mt-1">
                                                <span class="text-xs text-[#666666]">
                                                    {{ $topic->group->name }}
                                                </span>
                                                <span class="text-[10px] text-[#666666]">
                                                    by {{ $topic->creator->name }}
                                                </span>
                                                <span class="text-[10px] text-[#666666]">
                                                    {{ $topic->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        </div>
                                        <span class="text-[10px] text-[#666666] flex-shrink-0">
                                            {{ $topic->posts_count ?? 0 }} replies
                                        </span>
                                    </div>
                                    @if($topic->ml_category)
                                        <span class="inline-block mt-1 text-[8px] font-bold uppercase tracking-wider border border-[#000000] px-1.5 py-0.5">
                                            {{ $topic->ml_category }}
                                        </span>
                                    @endif
                                </a>
                            @empty
                                <div class="px-4 py-6 text-center">
                                    <p class="text-sm text-[#666666]">No recent topics in your groups.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Right Sidebar (1/3) --}}
                <div class="space-y-6">

                    {{-- Recommendations Widget --}}
                    <div class="bg-white border border-[#E5E5E5]">
                        <div class="border-b border-[#E5E5E5] px-4 py-3">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Recommended Topics</h2>
                        </div>
                        <div class="p-4 space-y-3">
                            @forelse($recommendations as $topic)
                                <a href="{{ route('topics.show', [$topic->group_id, $topic->id]) }}" 
                                   class="block hover:bg-[#F5F5F5] transition-colors p-2 -mx-2">
                                    <p class="text-sm text-[#000000]">{{ $topic->title }}</p>
                                    <p class="text-[10px] text-[#666666]">{{ $topic->group->name }}</p>
                                    @if($topic->ml_category)
                                        <span class="inline-block mt-1 text-[8px] font-bold uppercase tracking-wider border border-[#E5E5E5] px-1.5 py-0.5">
                                            {{ $topic->ml_category }}
                                        </span>
                                    @endif
                                </a>
                            @empty
                                <p class="text-sm text-[#666666]">No recommendations available.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Upcoming Quizzes Widget --}}
                    <div class="bg-white border border-[#E5E5E5]">
                        <div class="border-b border-[#E5E5E5] px-4 py-3">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Upcoming Quizzes</h2>
                        </div>
                        <div class="p-4 space-y-3">
                            @forelse($upcomingQuizzes as $quiz)
                                <div class="border border-[#E5E5E5] p-3">
                                    <p class="text-sm font-semibold text-[#000000]">{{ $quiz->title }}</p>
                                    <div class="flex justify-between mt-1">
                                        <span class="text-[10px] text-[#666666]">{{ $quiz->group->name }}</span>
                                        <span class="text-[10px] text-[#666666]">{{ $quiz->duration }} min</span>
                                    </div>
                                    @if($quiz->starts_at)
                                        <p class="text-[9px] text-[#666666] mt-1">
                                            Starts: {{ $quiz->starts_at->format('M d, Y h:i A') }}
                                        </p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-[#666666]">No upcoming quizzes.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Quick Stats Widget --}}
                    <div class="bg-white border border-[#E5E5E5]">
                        <div class="border-b border-[#E5E5E5] px-4 py-3">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Your Activity</h2>
                        </div>
                        <div class="p-4 grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-2xl font-bold text-[#000000]">{{ $totalTopics ?? 0 }}</p>
                                <p class="text-[10px] text-[#666666]">Topics Created</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-[#000000]">{{ $totalPosts ?? 0 }}</p>
                                <p class="text-[10px] text-[#666666]">Posts Made</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-[#000000]">{{ $totalLikes ?? 0 }}</p>
                                <p class="text-[10px] text-[#666666]">Likes Received</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-[#000000]">{{ $totalQuizzesTaken ?? 0 }}</p>
                                <p class="text-[10px] text-[#666666]">Quizzes Taken</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection