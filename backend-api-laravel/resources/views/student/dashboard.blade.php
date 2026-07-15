@extends('layouts.workspace')

@section('title', 'Dashboard')


                                @csrf
                                <button type="submit" 
    @section('context_panel')
    {{-- Section: Your Groups Header --}}
    <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Your Groups</h2>
        {{-- Dynamic Count Badge --}}
        <span class="flex items-center justify-center min-w-5 h-5 px-1.5 text-[10px] font-bold text-emerald-600 bg-emerald-50 rounded-full">
            {{ $groups->count() }}
        </span>
    </div>

    {{-- Your Dynamic Joined Groups List --}}
    <div class="p-4 space-y-3">
        @forelse($groups as $group)
            <a href="{{ route('groups.topics', $group->id) }}" 
               class="block p-4 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-100 cursor-pointer transition-all duration-200 space-y-1.5 group">
                <div class="flex justify-between items-baseline gap-2">
                    <h3 class="text-sm font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">
                        {{ $group->name }}
                    </h3>
                    <span class="text-[10px] font-semibold text-slate-400 shrink-0">
                        {{ $group->topics_count ?? 0 }} topics
                    </span>
                </div>
                
                @if($group->description)
                    <p class="text-xs text-slate-500 line-clamp-1">
                        {{ $group->description }}
                    </p>
                @endif
                
                @if($group->latest_topic)
                    <p class="text-[10px] text-emerald-600 font-medium mt-1 flex items-center">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span>
                        Latest: {{ $group->latest_topic->title }}
                    </p>
                @endif
            </a>
        @empty
            {{-- Dynamic Empty State --}}
            <div class="p-6 text-center border-2 border-dashed border-slate-100 rounded-2xl">
                <p class="text-sm font-semibold text-slate-600">You haven't joined any groups yet.</p>
                <p class="text-xs text-slate-400 mt-1">Browse available groups below.</p>
            </div>
        @endforelse
    </div>

    {{-- Section: Available Groups --}}
    @if(isset($availableGroups) && $availableGroups->isNotEmpty())
        <div class="border-t border-slate-100 mt-2">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Available Groups</h2>
                <a href="{{ route('groups.index') }}" class="text-[10px] font-bold text-slate-500 hover:text-slate-800 transition-colors">
                    View All
                </a>
            </div>
            
            <div class="p-4 space-y-3">
                @foreach($availableGroups->take(3) as $group)
                    <div class="p-4 bg-white rounded-xl border border-slate-100/80 hover:border-slate-200 hover:shadow-sm transition-all duration-150 space-y-3">
                        <div class="flex justify-between items-start gap-3">
                            <div class="space-y-1">
                                <h3 class="text-sm font-bold text-slate-800">{{ $group->name }}</h3>
                                @if($group->description)
                                    <p class="text-xs text-slate-500 line-clamp-1">{{ $group->description }}</p>
                                @endif
                                
                                <div class="flex items-center space-x-2 text-[10px] text-slate-400 pt-1">
                                    <span class="font-medium text-slate-500">{{ $group->topics_count ?? 0 }} topics</span>
                                    <span>•</span>
                                    <span class="font-medium text-slate-500">{{ $group->users_count ?? 0 }} members</span>
                                </div>
                            </div>
                            
                            {{-- Your CSRF Form is safe and styled --}}
                            <form action="{{ route('groups.join', $group->id) }}" method="POST" class="shrink-0">
                                @csrf
                                <button type="submit" 
                                        class="bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-bold px-3 py-1.5 rounded-lg transition-all duration-150 active:scale-95 shadow-sm">
                                    Join
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
                
                @if($availableGroups->count() > 3)
                    <div class="pt-2 text-center">
                        <a href="{{ route('groups.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                            + {{ $availableGroups->count() - 3 }} more groups
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
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
                <div class="flex items-center space-x-3">
    <a href="{{ route('topics.create') }}" 
       class="flex items-center bg-emerald-600 text-white px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-emerald-700 transition-colors rounded-md shadow-sm">
        <i data-lucide="plus-circle" class="w-4 h-4 mr-1.5"></i>
        New Topic
    </a>
    <a href="{{ route('student.quizzes') }}" 
       class="flex items-center bg-white text-emerald-700 border border-emerald-200 px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-emerald-50 transition-colors rounded-md shadow-sm">
        <i data-lucide="award" class="w-4 h-4 mr-1.5"></i>
        Quizzes
    </a>
</div>
            </div>
        </div>

        {{-- Main Content --}}
<div class="flex-grow p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

               <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
        <h2 class="text-emerald-950 font-bold text-sm tracking-wide uppercase flex items-center">
            <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl mr-3">
                <i data-lucide="message-square-more" class="w-5 h-5"></i>
            </span>
            Recent Discussions
        </h2>
        <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">
            Live Feed
        </span>
    </div>

    <!-- Feed List -->
    <div class="space-y-4">
        <!-- Topic 1: Secant Method -->
        <div class="p-4 rounded-xl bg-slate-50 hover:bg-emerald-50/40 transition-all duration-200 border border-transparent hover:border-emerald-100 flex justify-between items-start group">
            <div class="space-y-2">
                <span class="inline-flex items-center bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                    General Discussion
                </span>
                <h3 class="text-base font-bold text-gray-900 group-hover:text-emerald-600 transition duration-150 cursor-pointer">
                    Secant Method
                </h3>
                <div class="flex items-center space-x-3 text-xs text-gray-500">
                    <span class="font-medium text-gray-700">Software Engineering Year 1</span>
                    <span>•</span>
                    <span>by Nakasi Trina</span>
                    <span>•</span>
                    <span>1 day ago</span>
                </div>
            </div>
            <span class="flex items-center bg-white text-gray-400 group-hover:text-emerald-600 group-hover:border-emerald-200 border border-slate-100 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm transition-all duration-200">
                <i data-lucide="message-circle" class="w-3.5 h-3.5 mr-1 text-slate-400 group-hover:text-emerald-500"></i>
                0 replies
            </span>
        </div>

        <!-- Topic 2: Polymorphism -->
        <div class="p-4 rounded-xl bg-slate-50 hover:bg-emerald-50/40 transition-all duration-200 border border-transparent hover:border-emerald-100 flex justify-between items-start group">
            <div class="space-y-2">
                <span class="inline-flex items-center bg-teal-100 text-teal-800 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                    Java Fundamentals
                </span>
                <h3 class="text-base font-bold text-gray-900 group-hover:text-emerald-600 transition duration-150 cursor-pointer">
                    Understanding Object Polymorphism and Java Constructors
                </h3>
                <div class="flex items-center space-x-3 text-xs text-gray-500">
                    <span class="font-medium text-gray-700">Software Engineering Year 1</span>
                    <span>•</span>
                    <span>by Dr. Mary Nsabagwa</span>
                    <span>•</span>
                    <span>1 week ago</span>
                </div>
            </div>
            <span class="flex items-center bg-emerald-600 text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm animate-pulse">
                <i data-lucide="message-circle" class="w-3.5 h-3.5 mr-1 text-white"></i>
                1 reply
            </span>
        </div>
    </div>
</div>

                {{-- Right Sidebar (1/3) --}}
                <div class="space-y-6">

                   {{-- ML: Recommendations Widget (Instagram/Premium Inspired) --}}
<div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
        <h2 class="text-emerald-950 font-bold text-sm tracking-wide uppercase flex items-center">
            <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl mr-3">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </span>
            Recommended for You
        </h2>
        <a href="{{ route('recommendations.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition duration-150 flex items-center">
            View All 
            <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
        </a>
    </div>

    <!-- Recommendations List -->
    <div class="space-y-4">
        @forelse($recommendations as $topic)
            <a href="{{ route('topics.show', [$topic->group_id, $topic->id]) }}" 
               class="block p-4 rounded-xl bg-gradient-to-br from-white to-slate-50/50 border border-slate-100 hover:border-emerald-100 hover:shadow-sm transition-all duration-200 group">
                
                <!-- Topic Title -->
                <p class="text-sm font-bold text-gray-950 group-hover:text-emerald-700 transition-colors duration-150 line-clamp-2">
                    {{ $topic->title }}
                </p>
                
                <!-- Topic Metadata -->
                <div class="flex items-center gap-2 mt-3 flex-wrap">
                    <span class="text-[11px] font-semibold text-slate-500">
                        {{ $topic->group->name }}
                    </span>
                    
                    @if($topic->ml_category)
                        <span class="text-slate-300 text-[10px]">•</span>
                        <span class="text-[9px] font-extrabold uppercase tracking-widest bg-gradient-to-r from-emerald-500/10 to-teal-500/10 text-emerald-700 px-2.5 py-0.5 rounded-full">
                            {{ $topic->ml_category }}
                        </span>
                    @endif
                </div>
            </a>
        @empty
            <div class="text-center py-6 bg-slate-50 rounded-xl border border-dashed border-slate-200 p-4">
                <i data-lucide="compass" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                <p class="text-sm font-bold text-slate-700">No recommendations yet</p>
                <p class="text-xs text-slate-400 mt-1">Start interacting with topics to get personalized suggestions.</p>
            </div>
        @endforelse
    </div>
</div>

                {{-- ML: Affinity Scores Widget --}}
@if(isset($affinityScores) && count($affinityScores) > 0)
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
            <h2 class="text-slate-800 font-bold text-xs uppercase tracking-wider flex items-center">
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl mr-3">
                    <i data-lucide="compass" class="w-4 h-4"></i>
                </span>
                Your Interests
            </h2>
            <span class="text-[9px] font-extrabold uppercase tracking-widest bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full">
                ML Engine
            </span>
        </div>
        
        <div class="space-y-4"> 
            @php $displayCategories = array_slice($affinityScores, 0, 5); @endphp
            @foreach($displayCategories as $category => $score)
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-semibold text-slate-700">{{ $category }}</span>
                        <span class="text-xs font-bold text-indigo-600">{{ $score }}%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        {{-- This inner bar dynamically fills up based on the score! --}}
                        <div class="h-full bg-gradient-to-r from-indigo-500 to-violet-600 rounded-full transition-all duration-500" style="width: {{ $score }}%"></div>
                    </div>
                </div>
            @endforeach

            <p class="text-[10px] text-slate-400 mt-2 flex items-center">
                <i data-lucide="info" class="w-3.5 h-3.5 mr-1 text-slate-300"></i> 
                Dynamically calculated based on your platform interactions
            </p>
        </div>
    </div>
@endif   
                    {{-- Upcoming Quizzes Widget --}}
                    @if($upcomingQuizzes->isNotEmpty())
                    <div class="bg-white border border-[#E5E5E5]">
                        <div class="border-b border-[#E5E5E5] px-4 py-3">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Upcoming Quizzes</h2>
                        </div>
                        <div class="p-4 space-y-3">
                            @foreach($upcomingQuizzes->take(3) as $quiz)
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
                            @endforeach
                            @if($upcomingQuizzes->count() > 3)
                                <p class="text-[9px] text-[#666666] text-center">
                                    + {{ $upcomingQuizzes->count() - 3 }} more
                                </p>
                            @endif
                        </div>
                    </div>
                    @endif

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