@extends('layouts.workspace')

@section('title', 'Dashboard')

@section('context_panel')
    {{-- Your Groups --}}
    <div class="p-4 border-b border-[#E5E5E5] flex items-center justify-between bg-white sticky top-0">
        <div class="flex items-center gap-2">
            <i data-lucide="users" style="width:18px;height:18px;color:#0A574F;"></i>
            <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Your Groups</h2>
        </div>
        <span class="text-[10px] text-[#0A574F] bg-[#ECFDF5] px-2 py-0.5 rounded-full">{{ $groups->count() }}</span>
    </div>

    <div class="divide-y divide-[#E5E5E5]">
        @forelse($groups as $group)
            <div class="block p-4 bg-white hover:bg-[#F9F9F9] transition-colors space-y-1 border-l-2 border-transparent hover:border-[#0A574F]">
                <div class="flex justify-between items-start">
                    <a href="{{ route('groups.topics', $group->id) }}" class="flex-1 min-w-0">
                        <div>
                            <h3 class="text-sm font-bold text-[#000000] flex items-center gap-2">
                                <i data-lucide="folder" style="width:14px;height:14px;color:#0A574F;"></i>
                                {{ $group->name }}
                            </h3>
                            <span class="text-[10px] text-[#2563EB] border border-[#2563EB] px-2 py-0.5 rounded-full">{{ $group->topics_count ?? 0 }} topics</span>
                            @if($group->description)
                                <p class="text-xs text-[#666666] line-clamp-1 mt-1">{{ $group->description }}</p>
                            @endif
                            @if($group->latest_topic)
                                <p class="text-[10px] text-[#666666] mt-1 flex items-center gap-1">
                                    <i data-lucide="clock" style="width:10px;height:10px;"></i>
                                    Latest: {{ $group->latest_topic->title }}
                                </p>
                            @endif
                        </div>
                    </a>
                    <div class="flex-shrink-0 ml-4">
                        <form action="{{ route('groups.leave', $group->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to leave this group?')">
                            @csrf
                            <button type="submit" 
                                    class="flex items-center gap-1 text-[9px] font-bold uppercase tracking-wider text-[#DC2626] border border-[#DC2626] px-2 py-0.5 rounded-lg hover:bg-[#DC2626] hover:text-white transition">
                                <i data-lucide="log-out" style="width:10px;height:10px;"></i>
                                Leave
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-6 text-center">
                <i data-lucide="users" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                <p class="text-sm text-[#666666]">You haven't joined any groups yet.</p>
                <p class="text-xs text-[#666666] mt-1">Browse available groups below.</p>
            </div>
        @endforelse
    </div>

    {{-- Available Groups --}}
    @if(isset($availableGroups) && $availableGroups->isNotEmpty())
        <div class="border-t border-[#E5E5E5] mt-2">
            <div class="p-4 border-b border-[#E5E5E5] flex items-center justify-between bg-[#FAFAFA]">
                <div class="flex items-center gap-2">
                    <i data-lucide="compass" style="width:14px;height:14px;color:#0A574F;"></i>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[#666666]">Available Groups</h2>
                </div>
                <a href="{{ route('groups.index') }}" class="text-[10px] text-[#0A574F] hover:text-[#08443e] font-medium">
                    View All →
                </a>
            </div>
            <div class="divide-y divide-[#E5E5E5]">
                @foreach($availableGroups->take(3) as $group)
                    <div class="p-4 bg-white hover:bg-[#F9F9F9] transition-colors space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-sm font-bold text-[#000000] flex items-center gap-2">
                                    <i data-lucide="folder" style="width:14px;height:14px;color:#0A574F;"></i>
                                    {{ $group->name }}
                                </h3>
                                @if($group->description)
                                    <p class="text-xs text-[#666666] line-clamp-1">{{ $group->description }}</p>
                                @endif
                                <div class="flex items-center gap-3 mt-1">
                                    <span class="text-[10px] text-[#666666] flex items-center gap-1">
                                        <i data-lucide="message-circle" style="width:10px;height:10px;"></i>
                                        {{ $group->topics_count ?? 0 }} topics
                                    </span>
                                    <span>•</span>
                                    <span class="text-[10px] text-[#666666] flex items-center gap-1">
                                        <i data-lucide="users" style="width:10px;height:10px;"></i>
                                        {{ $group->users_count ?? 0 }} members
                                    </span>
                                </div>
                            </div>
                            <form action="{{ route('groups.join', $group->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-3 py-1 rounded-lg hover:bg-[#16A34A] hover:text-white transition">
                                    <i data-lucide="log-in" style="width:10px;height:10px;"></i>
                                    Join
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
                @if($availableGroups->count() > 3)
                    <div class="p-3 text-center bg-[#FAFAFA]">
                        <a href="{{ route('groups.index') }}" class="text-xs text-[#0A574F] hover:text-[#08443e] font-medium">
                            + {{ $availableGroups->count() - 3 }} more groups
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Welcome Section --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-3">
                        <i data-lucide="graduation-cap" style="width:28px;height:28px;color:#0A574F;"></i>
                        Welcome back, <span class="text-[#0A574F]">{{ Auth::user()->name }}</span>
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="activity" style="width:14px;height:14px;color:#0A574F;"></i>
                        Here's what's happening in your groups
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4]">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        {{ $groups->count() }} groups
                    </span>
                    <a href="{{ route('topics.create') }}" 
                       class="flex items-center gap-2 bg-[#0A574F] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition hover:shadow-sm">
                        <i data-lucide="plus-circle" style="width:14px;height:14px;"></i>
                        New Topic
                    </a>
                    <a href="{{ route('student.quizzes') }}" 
                       class="flex items-center gap-2 bg-[#2563EB] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#1d4ed8] transition hover:shadow-sm">
                        <i data-lucide="file-question" style="width:14px;height:14px;"></i>
                        Quizzes
                    </a>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Recent Topics (Left Column - 2/3) --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                        <div class="border-b border-[#E5E5E5] px-5 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="message-circle" style="width:18px;height:18px;color:#0A574F;"></i>
                                <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Recent Topics</h2>
                            </div>
                            @if($recentTopics->count() > 5)
                                <span class="text-[10px] text-[#666666] bg-[#F9F9F9] px-2 py-0.5 rounded-full">Latest 5</span>
                            @endif
                        </div>
                        <div class="divide-y divide-[#F5F5F5] max-h-[420px] overflow-y-auto">
                            @forelse($recentTopics->take(5) as $topic)
                                <a href="{{ route('topics.show', [$topic->group_id, $topic->id]) }}" 
                                   class="block px-5 py-4 hover:bg-[#F9F9F9] transition">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <i data-lucide="message-square" style="width:14px;height:14px;color:#0A574F;"></i>
                                                <h3 class="text-sm font-semibold text-[#000000]">{{ $topic->title }}</h3>
                                            </div>
                                            <div class="flex items-center flex-wrap gap-2 mt-1">
                                                <span class="text-xs text-[#666666] flex items-center gap-1">
                                                    <i data-lucide="folder" style="width:10px;height:10px;"></i>
                                                    {{ $topic->group->name }}
                                                </span>
                                                <span class="text-[10px] text-[#666666]">•</span>
                                                <span class="text-[10px] text-[#666666] flex items-center gap-1">
                                                    <i data-lucide="user" style="width:10px;height:10px;"></i>
                                                    {{ $topic->creator->name }}
                                                </span>
                                                <span class="text-[10px] text-[#666666]">•</span>
                                                <span class="text-[10px] text-[#666666] flex items-center gap-1">
                                                    <i data-lucide="clock" style="width:10px;height:10px;"></i>
                                                    {{ $topic->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        </div>
                                        <span class="text-[10px] text-[#2563EB] flex items-center gap-1 flex-shrink-0 ml-2">
                                            <i data-lucide="message-square" style="width:12px;height:12px;"></i>
                                            {{ $topic->posts_count ?? 0 }} replies
                                        </span>
                                    </div>
                                    @if($topic->ml_category)
                                        <span class="inline-block mt-1 text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-2 py-0.5 rounded-full">
                                            <i data-lucide="tag" style="width:8px;height:8px;display:inline;"></i>
                                            {{ $topic->ml_category }}
                                        </span>
                                    @endif
                                </a>
                            @empty
                                <div class="px-5 py-8 text-center">
                                    <i data-lucide="inbox" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                                    <p class="text-sm text-[#666666]">No recent topics in your groups.</p>
                                    <p class="text-xs text-[#94A3B8]">Join more groups to see topics here.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Right Sidebar (1/3) --}}
                <div class="space-y-6">

                    {{-- Your Activity Stats --}}
                    <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                        <div class="border-b border-[#E5E5E5] px-4 py-3 flex items-center gap-2">
                            <i data-lucide="activity" style="width:16px;height:16px;color:#0A574F;"></i>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Your Activity</h2>
                        </div>
                        <div class="p-4 grid grid-cols-2 gap-3">
                            <div class="bg-[#F9F9F9] rounded-lg p-3 text-center hover:bg-[#ECFDF5] transition">
                                <p class="text-2xl font-bold text-[#0A574F]">{{ $totalTopics ?? 0 }}</p>
                                <p class="text-[9px] text-[#666666] uppercase tracking-wider font-medium">Topics</p>
                            </div>
                            <div class="bg-[#F9F9F9] rounded-lg p-3 text-center hover:bg-[#E0F2FE] transition">
                                <p class="text-2xl font-bold text-[#2563EB]">{{ $totalPosts ?? 0 }}</p>
                                <p class="text-[9px] text-[#666666] uppercase tracking-wider font-medium">Posts</p>
                            </div>
                            <div class="bg-[#F9F9F9] rounded-lg p-3 text-center hover:bg-[#FEF3C7] transition">
                                <p class="text-2xl font-bold text-[#D97706]">{{ $totalLikes ?? 0 }}</p>
                                <p class="text-[9px] text-[#666666] uppercase tracking-wider font-medium">Likes</p>
                            </div>
                            <div class="bg-[#F9F9F9] rounded-lg p-3 text-center hover:bg-[#FEE2E2] transition">
                                <p class="text-2xl font-bold text-[#DC2626]">{{ $totalQuizzesTaken ?? 0 }}</p>
                                <p class="text-[9px] text-[#666666] uppercase tracking-wider font-medium">Quizzes</p>
                            </div>
                        </div>
                    </div>

                    {{-- ML: Recommendations Widget --}}
                    <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                        <div class="border-b border-[#E5E5E5] px-4 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="thumbs-up" style="width:16px;height:16px;color:#0A574F;"></i>
                                <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Recommended</h2>
                            </div>
                            <a href="{{ route('recommendations.index') }}" class="text-[9px] text-[#0A574F] hover:text-[#08443e] font-medium">
                                View All →
                            </a>
                        </div>
                        <div class="p-4 space-y-3">
                            @forelse($recommendations as $topic)
                                <a href="{{ route('topics.show', [$topic->group_id, $topic->id]) }}" 
                                   class="block hover:bg-[#F9F9F9] rounded-lg p-2 -mx-2 transition">
                                    <p class="text-sm font-medium text-[#000000] flex items-center gap-2">
                                        <i data-lucide="message-circle" style="width:14px;height:14px;color:#0A574F;"></i>
                                        {{ $topic->title }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] text-[#666666] flex items-center gap-1">
                                            <i data-lucide="folder" style="width:10px;height:10px;"></i>
                                            {{ $topic->group->name }}
                                        </span>
                                        @if($topic->ml_category)
                                            <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-1.5 py-0.5 rounded-full">
                                                {{ $topic->ml_category }}
                                            </span>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="text-center py-4">
                                    <i data-lucide="thumbs-up" style="width:24px;height:24px;color:#94A3B8;margin:0 auto 0.25rem;display:block;"></i>
                                    <p class="text-sm text-[#666666]">No recommendations</p>
                                    <p class="text-[10px] text-[#94A3B8]">Start interacting to get suggestions.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- ML: Affinity Scores Widget --}}
                    @if(isset($affinityScores) && count($affinityScores) > 0)
                        <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                            <div class="border-b border-[#E5E5E5] px-4 py-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="brain" style="width:16px;height:16px;color:#0A574F;"></i>
                                    <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Your Interests</h2>
                                </div>
                                <span class="text-[8px] text-[#0A574F] bg-[#ECFDF5] px-2 py-0.5 rounded-full">ML</span>
                            </div>
                            <div class="p-4 space-y-3">
                                @php $displayCategories = array_slice($affinityScores, 0, 5); @endphp
                                @foreach($displayCategories as $category => $score)
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-[#666666] flex items-center gap-1">
                                                <i data-lucide="tag" style="width:10px;height:10px;color:#0A574F;"></i>
                                                {{ $category }}
                                            </span>
                                            <span class="text-[10px] font-bold text-[#0A574F]">{{ $score }}%</span>
                                        </div>
                                        <div class="w-full h-1.5 bg-[#E5E5E5] rounded-full mt-0.5 overflow-hidden">
                                            <div class="h-full bg-[#0A574F] rounded-full transition-all" style="width: {{ $score }}%;"></div>
                                        </div>
                                    </div>
                                @endforeach
                                <p class="text-[9px] text-[#666666] flex items-center gap-1 mt-1">
                                    <i data-lucide="info" style="width:10px;height:10px;"></i>
                                    Based on your interactions
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Upcoming Quizzes Widget --}}
                    @if($upcomingQuizzes->isNotEmpty())
                        <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                            <div class="border-b border-[#E5E5E5] px-4 py-3 flex items-center gap-2">
                                <i data-lucide="file-question" style="width:16px;height:16px;color:#D97706;"></i>
                                <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Upcoming Quizzes</h2>
                            </div>
                            <div class="p-4 space-y-3">
                                @foreach($upcomingQuizzes->take(3) as $quiz)
                                    <div class="border border-[#E5E5E5] rounded-lg p-3 hover:border-[#D97706] transition">
                                        <p class="text-sm font-semibold text-[#000000] flex items-center gap-2">
                                            <i data-lucide="clipboard-list" style="width:14px;height:14px;color:#D97706;"></i>
                                            {{ $quiz->title }}
                                        </p>
                                        <div class="flex items-center gap-3 mt-1 text-[10px] text-[#666666]">
                                            <span class="flex items-center gap-1">
                                                <i data-lucide="users" style="width:10px;height:10px;"></i>
                                                {{ $quiz->group->name }}
                                            </span>
                                            <span>•</span>
                                            <span class="flex items-center gap-1">
                                                <i data-lucide="clock" style="width:10px;height:10px;"></i>
                                                {{ $quiz->duration }} min
                                            </span>
                                        </div>
                                        @if($quiz->starts_at)
                                            <p class="text-[9px] text-[#666666] mt-1 flex items-center gap-1">
                                                <i data-lucide="calendar" style="width:10px;height:10px;"></i>
                                                Starts: {{ $quiz->starts_at->format('M d, Y h:i A') }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                                @if($upcomingQuizzes->count() > 3)
                                    <p class="text-[9px] text-[#666666] text-center font-medium">
                                        + {{ $upcomingQuizzes->count() - 3 }} more quizzes
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();

        const ctx = document.getElementById('affinityChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($affinityScores)) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($affinityScores)) !!},
                        backgroundColor: '#0A574F',
                        borderColor: '#0A574F',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { max: 100 }
                    }
                }
            });
        }
    });
</script>
@endpush