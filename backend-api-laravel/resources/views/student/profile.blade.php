@extends('layouts.workspace')

@section('title', 'My Profile')

@section('context_panel')
    {{-- User Info --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-[#0A574F] to-[#16A34A] text-white flex items-center justify-center text-2xl font-bold uppercase rounded-lg flex-shrink-0 shadow-sm">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-bold text-[#000000] truncate flex items-center gap-2">
                    {{ Auth::user()->name }}
                    <i data-lucide="badge-check" style="width:14px;height:14px;color:#0A574F;"></i>
                </h3>
                <p class="text-xs text-[#666666] truncate flex items-center gap-1">
                    <i data-lucide="mail" style="width:12px;height:12px;color:#2563EB;"></i>
                    {{ Auth::user()->email }}
                </p>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                    <span class="text-[8px] font-bold uppercase tracking-wider text-[#0A574F] border border-[#0A574F] px-2 py-0.5 rounded-full">
                        {{ Auth::user()->role }}
                    </span>
                    @if(Auth::user()->status === 'active')
                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-2 py-0.5 rounded-full flex items-center gap-1">
                            <i data-lucide="circle" style="width:6px;height:6px;fill:#16A34A;color:#16A34A;"></i>
                            Active
                        </span>
                    @elseif(Auth::user()->status === 'warned_once' || Auth::user()->status === 'warned_twice')
                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-2 py-0.5 rounded-full">
                             {{ str_replace('_', ' ', Auth::user()->status) }}
                        </span>
                    @elseif(Auth::user()->status === 'blacklisted')
                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#DC2626] border border-[#DC2626] px-2 py-0.5 rounded-full">
                             Blacklisted
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @if(Auth::user()->blacklist_expires_at && Auth::user()->status === 'blacklisted')
            <p class="text-[10px] text-[#DC2626] mt-2 flex items-center gap-1">
                <i data-lucide="clock" style="width:12px;height:12px;"></i>
                Blacklisted until {{ Auth::user()->blacklist_expires_at->format('M d, Y') }}
            </p>
        @endif
    </div>

   >
{{-- Quick Stats --}}
<div class="p-4 bg-white border-b border-[#E5E5E5]">
    <div class="grid grid-cols-4 gap-3">
        <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
            <p class="text-xl font-bold text-[#000000]">{{ $totalTopics }}</p>
            <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Topics</p>
        </div>
        <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
            <p class="text-xl font-bold text-[#000000]">{{ $totalPosts }}</p>
            <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Posts</p>
        </div>
        <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
            <p class="text-xl font-bold text-[#000000]">{{ $totalLikes }}</p>
            <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Likes</p>
        </div>
        <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
            <p class="text-xl font-bold text-[#000000]">{{ $totalQuizzesTaken }}</p>
            <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Quizzes</p>
        </div>
    </div>
</div>

    {{-- Vertical Navigation Tabs with Colored Icons --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1 bg-[#F9F9F9]">
        <button class="tab-btn w-full text-left px-4 py-3 text-xs font-bold uppercase tracking-wider rounded-lg border-l-2 border-[#0A574F] bg-white text-[#000000] shadow-sm transition-all flex items-center gap-3" data-tab="activity">
            <div class="w-7 h-7 bg-[#0A574F] rounded-lg flex items-center justify-center">
                <i data-lucide="activity" style="width:14px;height:14px;color:white;"></i>
            </div>
            Activity
        </button>
        <button class="tab-btn w-full text-left px-4 py-3 text-xs font-bold uppercase tracking-wider rounded-lg border-l-2 border-transparent text-[#666666] hover:bg-[#F0F0F0] hover:text-[#000000] transition-all flex items-center gap-3" data-tab="quizzes">
            <div class="w-7 h-7 bg-[#D97706] rounded-lg flex items-center justify-center">
                <i data-lucide="file-question" style="width:14px;height:14px;color:white;"></i>
            </div>
            Quizzes
        </button>
        <button class="tab-btn w-full text-left px-4 py-3 text-xs font-bold uppercase tracking-wider rounded-lg border-l-2 border-transparent text-[#666666] hover:bg-[#F0F0F0] hover:text-[#000000] transition-all flex items-center gap-3" data-tab="warnings">
            <div class="w-7 h-7 bg-[#DC2626] rounded-lg flex items-center justify-center">
                <i data-lucide="alert-triangle" style="width:14px;height:14px;color:white;"></i>
            </div>
            Warnings
        </button>
        <button class="tab-btn w-full text-left px-4 py-3 text-xs font-bold uppercase tracking-wider rounded-lg border-l-2 border-transparent text-[#666666] hover:bg-[#F0F0F0] hover:text-[#000000] transition-all flex items-center gap-3" data-tab="insights">
            <div class="w-7 h-7 bg-[#2563EB] rounded-lg flex items-center justify-center">
                <i data-lucide="brain" style="width:14px;height:14px;color:white;"></i>
            </div>
            Insights
        </button>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Tab Content: Activity --}}
        <div class="tab-content flex-1 overflow-y-auto p-6 custom-scrollbar" id="tab-activity">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 bg-[#0A574F] rounded-lg flex items-center justify-center">
                    <i data-lucide="activity" style="width:16px;height:16px;color:white;"></i>
                </div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Recent Activity</h2>
                <span class="text-[10px] text-[#666666] bg-[#F9F9F9] px-2 py-0.5 rounded-full ml-auto">{{ $recentActivity->count() }} items</span>
            </div>

            @if($recentActivity->isEmpty())
                <div class="bg-white rounded-lg border border-dashed border-[#E5E5E5] p-12 text-center">
                    <i data-lucide="activity" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                    <p class="text-sm text-[#666666]">No recent activity yet.</p>
                    <p class="text-xs text-[#94A3B8]">Start engaging with topics to see your activity here.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($recentActivity as $activity)
                        <div class="bg-white rounded-lg border-l-4 border-[#0A574F] shadow-sm hover:shadow-md transition p-4">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    @if($activity->type === 'topic')
                                        <span class="text-xs font-bold text-[#0A574F] flex items-center gap-1">
                                            <i data-lucide="message-circle" style="width:12px;height:12px;"></i>
                                            Created topic
                                        </span>
                                        <a href="{{ route('topics.show', [$activity->group_id, $activity->topic_id]) }}" 
                                           class="text-sm font-semibold text-[#000000] hover:text-[#0A574F] block mt-1 truncate">
                                            {{ $activity->title }}
                                        </a>
                                    @elseif($activity->type === 'post')
                                        <span class="text-xs font-bold text-[#2563EB] flex items-center gap-1">
                                            <i data-lucide="message-square" style="width:12px;height:12px;"></i>
                                            Posted reply
                                        </span>
                                        <a href="{{ route('topics.show', [$activity->group_id, $activity->topic_id]) }}#post-{{ $activity->post_id }}" 
                                           class="text-sm text-[#666666] block mt-1 line-clamp-2 hover:text-[#000000]">
                                            {{ $activity->content }}
                                        </a>
                                    @elseif($activity->type === 'like')
                                        <span class="text-xs font-bold text-[#DC2626] flex items-center gap-1">
                                            <i data-lucide="heart" style="width:12px;height:12px;"></i>
                                            Liked a post
                                        </span>
                                        <a href="{{ route('topics.show', [$activity->group_id, $activity->topic_id]) }}#post-{{ $activity->post_id }}" 
                                           class="text-sm text-[#666666] block mt-1 line-clamp-2 hover:text-[#000000]">
                                            {{ $activity->content }}
                                        </a>
                                    @endif
                                </div>
                                <span class="text-[10px] text-[#666666] flex-shrink-0 ml-4 flex items-center gap-1">
                                    <i data-lucide="clock" style="width:10px;height:10px;"></i>
                                    {{ $activity->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Tab Content: Quizzes --}}
        <div class="tab-content flex-1 overflow-y-auto p-6 custom-scrollbar hidden" id="tab-quizzes">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 bg-[#D97706] rounded-lg flex items-center justify-center">
                    <i data-lucide="file-question" style="width:16px;height:16px;color:white;"></i>
                </div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Quiz Performance</h2>
                <span class="text-[10px] text-[#666666] bg-[#F9F9F9] px-2 py-0.5 rounded-full ml-auto">{{ $quizSubmissions->count() }} attempts</span>
            </div>

            @if($quizSubmissions->isEmpty())
                <div class="bg-white rounded-lg border border-dashed border-[#E5E5E5] p-12 text-center">
                    <i data-lucide="file-question" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                    <p class="text-sm text-[#666666]">You haven't taken any quizzes yet.</p>
                    <p class="text-xs text-[#94A3B8]">Check your groups for available quizzes.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($quizSubmissions as $submission)
                        <div class="bg-white rounded-lg border-l-4 border-[#D97706] shadow-sm hover:shadow-md transition p-4">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm font-bold text-[#000000] flex items-center gap-2">
                                        <i data-lucide="clipboard-list" style="width:14px;height:14px;color:#D97706;"></i>
                                        {{ $submission->quiz->title ?? 'Quiz' }}
                                    </h3>
                                    <div class="flex items-center gap-3 mt-1 flex-wrap">
                                        <span class="text-xs text-[#666666] flex items-center gap-1">
                                            <i data-lucide="users" style="width:10px;height:10px;color:#2563EB;"></i>
                                            {{ $submission->quiz->group->name ?? 'N/A' }}
                                        </span>
                                        <span class="text-[10px] text-[#666666]">•</span>
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $submission->score >= 70 ? 'bg-[#F0FDF4] text-[#16A34A]' : ($submission->score >= 50 ? 'bg-[#FEF3C7] text-[#D97706]' : 'bg-[#FEF2F2] text-[#DC2626]') }}">
                                            Score: {{ $submission->score ?? 'N/A' }}%
                                        </span>
                                        @if($submission->is_auto_submitted)
                                            <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-2 py-0.5 rounded-full flex items-center gap-1">
                                                <i data-lucide="clock" style="width:8px;height:8px;"></i>
                                                Auto-submitted
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-[10px] text-[#666666] flex-shrink-0 ml-4 flex items-center gap-1">
                                    <i data-lucide="calendar" style="width:10px;height:10px;color:#2563EB;"></i>
                                    {{ $submission->created_at->format('M d, Y') }}
                                </span>
                            </div>
                            @if($submission->answers_payload)
                                <details class="mt-2">
                                    <summary class="text-[10px] text-[#666666] cursor-pointer hover:text-[#0A574F] flex items-center gap-1">
                                        <i data-lucide="eye" style="width:10px;height:10px;"></i>
                                        View Answers
                                    </summary>
                                    <div class="mt-2 p-3 bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg text-xs text-[#666666] overflow-x-auto">
                                        <pre class="whitespace-pre-wrap">{{ json_encode($submission->answers_payload, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </details>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Tab Content: Warnings --}}
        <div class="tab-content flex-1 overflow-y-auto p-6 custom-scrollbar hidden" id="tab-warnings">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 bg-[#DC2626] rounded-lg flex items-center justify-center">
                    <i data-lucide="alert-triangle" style="width:16px;height:16px;color:white;"></i>
                </div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Warning History</h2>
                <span class="text-[10px] text-[#666666] bg-[#F9F9F9] px-2 py-0.5 rounded-full ml-auto">{{ $warningLogs->count() }} records</span>
            </div>

            @if($warningLogs->isEmpty())
                <div class="bg-white rounded-lg border border-dashed border-[#16A34A] p-12 text-center">
                    <i data-lucide="shield-check" style="width:48px;height:48px;color:#16A34A;margin:0 auto 0.75rem;display:block;"></i>
                    <p class="text-sm font-medium text-[#16A34A]">No warnings on your account</p>
                    <p class="text-xs text-[#666666] mt-1">You're in good standing!</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($warningLogs as $log)
                        <div class="bg-white rounded-lg border-l-4 border-[#DC2626] shadow-sm hover:shadow-md transition p-4">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-3 flex-wrap">
                                        @if($log->action_type === 'issue_warning_1')
                                            <span class="text-xs font-bold text-[#D97706] flex items-center gap-1">
                                                <i data-lucide="alert-triangle" style="width:12px;height:12px;"></i>
                                                First Warning
                                            </span>
                                        @elseif($log->action_type === 'issue_warning_2')
                                            <span class="text-xs font-bold text-[#D97706] flex items-center gap-1">
                                                <i data-lucide="alert-octagon" style="width:12px;height:12px;"></i>
                                                Second Warning
                                            </span>
                                        @elseif($log->action_type === 'hard_blacklist')
                                            <span class="text-xs font-bold text-[#DC2626] flex items-center gap-1">
                                                <i data-lucide="ban" style="width:12px;height:12px;"></i>
                                                Blacklisted
                                            </span>
                                        @else
                                            <span class="text-xs font-bold text-[#666666]">{{ $log->action_type }}</span>
                                        @endif
                                        @if($log->expires_at)
                                            <span class="text-[8px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-2 py-0.5 rounded-full flex items-center gap-1">
                                                <i data-lucide="clock" style="width:8px;height:8px;"></i>
                                                Expires: {{ $log->expires_at->format('M d, Y') }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-[#666666] mt-1">{{ $log->reason }}</p>
                                </div>
                                <span class="text-[10px] text-[#666666] flex-shrink-0 ml-4 flex items-center gap-1">
                                    <i data-lucide="calendar" style="width:10px;height:10px;"></i>
                                    {{ $log->created_at->format('M d, Y') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Tab Content: Insights --}}
        <div class="tab-content flex-1 overflow-y-auto p-6 custom-scrollbar hidden" id="tab-insights">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 bg-[#2563EB] rounded-lg flex items-center justify-center">
                    <i data-lucide="brain" style="width:16px;height:16px;color:white;"></i>
                </div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">ML-Powered Insights</h2>
                <span class="text-[8px] text-[#0A574F] bg-[#ECFDF5] px-2 py-0.5 rounded-full ml-auto">Beta</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Chart --}}
                <div class="bg-white rounded-xl border border-[#E5E5E5] shadow-sm p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] flex items-center gap-1">
                            <i data-lucide="bar-chart-2" style="width:14px;height:14px;color:#0A574F;"></i>
                            Interest Categories
                        </h3>
                        <span class="text-[8px] text-[#0A574F] bg-[#ECFDF5] px-2 py-0.5 rounded-full border border-[#0A574F]">ML</span>
                    </div>
                    <div class="w-full" style="height: 180px;">
                        <canvas id="profileAffinityChart"></canvas>
                    </div>
                    <p class="text-[9px] text-[#666666] mt-2 flex items-center gap-1">
                        <i data-lucide="info" style="width:10px;height:10px;"></i>
                        Based on your interactions (views, likes, comments)
                    </p>
                </div>

                {{-- Detailed Breakdown --}}
                <div class="bg-white rounded-xl border border-[#E5E5E5] shadow-sm p-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3 flex items-center gap-1">
                        <i data-lucide="list" style="width:14px;height:14px;color:#0A574F;"></i>
                        Detailed Breakdown
                    </h3>
                    @if(isset($affinityScores) && count($affinityScores) > 0)
                        <div class="space-y-3 max-h-40 overflow-y-auto custom-scrollbar pr-1">
                            @foreach($affinityScores as $category => $score)
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-medium text-[#000000] flex items-center gap-1">
                                            <i data-lucide="tag" style="width:10px;height:10px;color:#0A574F;"></i>
                                            {{ $category }}
                                        </span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full 
                                            {{ $score >= 70 ? 'bg-[#F0FDF4] text-[#16A34A]' : ($score >= 40 ? 'bg-[#FEF3C7] text-[#D97706]' : 'bg-[#FEF2F2] text-[#DC2626]') }}">
                                            {{ $score }}%
                                        </span>
                                    </div>
                                    <div class="w-full h-1.5 bg-[#E5E5E5] rounded-full mt-0.5 overflow-hidden">
                                        <div class="h-full rounded-full bg-[#0A574F]" 
                                             style="width: {{ $score }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <i data-lucide="inbox" style="width:24px;height:24px;color:#94A3B8;margin:0 auto 0.25rem;display:block;"></i>
                            <p class="text-sm text-[#666666]">Not enough data yet.</p>
                            <p class="text-xs text-[#94A3B8]">Interact with more topics to build your profile.</p>
                        </div>
                    @endif
                </div>

                {{-- Recommendations --}}
                <div class="bg-white rounded-xl border border-[#E5E5E5] shadow-sm p-4 lg:col-span-2">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] flex items-center gap-1">
                            <i data-lucide="thumbs-up" style="width:14px;height:14px;color:#0A574F;"></i>
                            Recommended For You
                        </h3>
                        <span class="text-[8px] text-[#0A574F] bg-[#ECFDF5] px-2 py-0.5 rounded-full">ML</span>
                    </div>
                    @if($recommendations->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($recommendations->take(4) as $topic)
                                <a href="{{ route('topics.show', [$topic->group_id, $topic->id]) }}" 
                                   class="block hover:bg-[#F9F9F9] rounded-lg p-3 border border-[#E5E5E5] hover:border-[#0A574F] transition">
                                    <p class="text-sm font-medium text-[#000000] flex items-center gap-2">
                                        <i data-lucide="message-circle" style="width:14px;height:14px;color:#0A574F;"></i>
                                        {{ $topic->title }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-0.5">
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
                            @endforeach
                        </div>
                        @if($recommendations->count() > 4)
                            <p class="text-[9px] text-[#666666] text-center mt-2 flex items-center gap-1 justify-center">
                                <i data-lucide="plus" style="width:10px;height:10px;"></i>
                                {{ $recommendations->count() - 4 }} more recommendations available
                            </p>
                        @endif
                    @else
                        <div class="text-center py-6">
                            <i data-lucide="thumbs-up" style="width:24px;height:24px;color:#94A3B8;margin:0 auto 0.25rem;display:block;"></i>
                            <p class="text-sm text-[#666666]">No recommendations available.</p>
                            <p class="text-xs text-[#94A3B8]">Keep exploring to get personalized suggestions.</p>
                        </div>
                    @endif
                </div>

            </div>

            {{-- Interaction Summary --}}
            <div class="mt-6 bg-white rounded-xl border border-[#E5E5E5] shadow-sm p-4">
                <div class="flex items-center gap-2 mb-3">
                    <i data-lucide="bar-chart-3" style="width:16px;height:16px;color:#0A574F;"></i>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666]">Interaction Breakdown</h3>
                </div>
                <div class="grid grid-cols-4 gap-4 text-center">
                    <div class="bg-[#F9F9F9] rounded-lg p-3 hover:bg-[#E0F2FE] transition">
                        <p class="text-2xl font-bold text-[#2563EB]">{{ $interactionCounts['views'] ?? 0 }}</p>
                        <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Views</p>
                    </div>
                    <div class="bg-[#F9F9F9] rounded-lg p-3 hover:bg-[#FEF2F2] transition">
                        <p class="text-2xl font-bold text-[#DC2626]">{{ $interactionCounts['likes'] ?? 0 }}</p>
                        <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Likes</p>
                    </div>
                    <div class="bg-[#F9F9F9] rounded-lg p-3 hover:bg-[#FEF3C7] transition">
                        <p class="text-2xl font-bold text-[#D97706]">{{ $interactionCounts['comments'] ?? 0 }}</p>
                        <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Comments</p>
                    </div>
                    <div class="bg-[#F9F9F9] rounded-lg p-3 hover:bg-[#ECFDF5] transition">
                        <p class="text-2xl font-bold text-[#0A574F]">{{ $interactionCounts['downloads'] ?? 0 }}</p>
                        <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Downloads</p>
                    </div>
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

    // ─── TAB SWITCHING ──────────────────────────────────────────
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = {
        activity: document.getElementById('tab-activity'),
        quizzes: document.getElementById('tab-quizzes'),
        warnings: document.getElementById('tab-warnings'),
        insights: document.getElementById('tab-insights'),
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => {
                t.className = 'tab-btn w-full text-left px-4 py-3 text-xs font-bold uppercase tracking-wider rounded-lg border-l-2 border-transparent text-[#666666] hover:bg-[#F0F0F0] hover:text-[#000000] transition-all flex items-center gap-3';
            });
            Object.values(contents).forEach(c => c.classList.add('hidden'));
            this.className = 'tab-btn w-full text-left px-4 py-3 text-xs font-bold uppercase tracking-wider rounded-lg border-l-2 border-[#0A574F] bg-white text-[#000000] shadow-sm transition-all flex items-center gap-3';
            const tabName = this.dataset.tab;
            if (contents[tabName]) {
                contents[tabName].classList.remove('hidden');
            }
        });
    });

    const firstTab = document.querySelector('.tab-btn');
    if (firstTab) firstTab.click();

    // ─── AFFINITY CHART ──────────────────────────────────────────
    const ctx = document.getElementById('profileAffinityChart');
    if (ctx) {
        const scores = {!! json_encode($affinityScores) !!};
        const labels = Object.keys(scores);
        const data = Object.values(scores);

        const colors = [
            '#16A34A', '#2563EB', '#D97706', '#DC2626', '#0D9488', '#7C3AED', '#EC4899', '#F59E0B'
        ];

        const backgroundColors = data.map((_, i) => colors[i % colors.length]);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
                    borderColor: backgroundColors.map(c => c),
                    borderWidth: 1,
                    borderRadius: 2,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.x + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        max: 100,
                        grid: { display: false },
                        ticks: { font: { size: 9 } }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 9 } }
                    }
                }
            }
        });
    }
});
</script>
@endpush