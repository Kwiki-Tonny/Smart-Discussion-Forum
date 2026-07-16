@extends('layouts.workspace')

@section('title', 'My Profile')

@section('context_panel')
    {{-- User Info --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-[#000000] text-white flex items-center justify-center text-xl font-bold uppercase flex-shrink-0">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-bold text-[#000000] truncate">{{ Auth::user()->name }}</h3>
                <p class="text-xs text-[#666666] truncate">{{ Auth::user()->email }}</p>
                <div class="flex items-center space-x-2 mt-1 flex-wrap">
                    <span class="text-[8px] font-bold uppercase tracking-wider border border-[#E5E5E5] px-1.5 py-0.5">
                        {{ Auth::user()->role }}
                    </span>
                    @if(Auth::user()->status === 'active')
                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-1.5 py-0.5">
                            Active
                        </span>
                    @elseif(Auth::user()->status === 'warned_once' || Auth::user()->status === 'warned_twice')
                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-1.5 py-0.5">
                            {{ str_replace('_', ' ', Auth::user()->status) }}
                        </span>
                    @elseif(Auth::user()->status === 'blacklisted')
                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#DC2626] border border-[#DC2626] px-1.5 py-0.5">
                            Blacklisted
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @if(Auth::user()->blacklist_expires_at && Auth::user()->status === 'blacklisted')
            <p class="text-[10px] text-[#DC2626] mt-2">
                ⚠️ Account blacklisted until {{ Auth::user()->blacklist_expires_at->format('M d, Y') }}
            </p>
        @endif
    </div>

    {{-- Quick Stats --}}
    <div class="p-3 bg-[#FAFAFA] border-b border-[#E5E5E5]">
        <div class="grid grid-cols-4 gap-1 text-center">
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $totalTopics }}</p>
                <p class="text-[7px] text-[#666666] uppercase tracking-wider">Topics</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $totalPosts }}</p>
                <p class="text-[7px] text-[#666666] uppercase tracking-wider">Posts</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $totalLikes }}</p>
                <p class="text-[7px] text-[#666666] uppercase tracking-wider">Likes</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $totalQuizzesTaken }}</p>
                <p class="text-[7px] text-[#666666] uppercase tracking-wider">Quizzes</p>
            </div>
        </div>
    </div>

    {{-- Vertical Navigation Tabs --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1 bg-[#FAFAFA]">
        <button class="tab-btn w-full text-left px-4 py-3 text-xs font-bold uppercase tracking-wider border-l-2 border-[#000000] bg-white text-[#000000] transition-colors" data-tab="activity">
            Activity
        </button>
        <button class="tab-btn w-full text-left px-4 py-3 text-xs font-bold uppercase tracking-wider border-l-2 border-transparent text-[#666666] hover:bg-[#F0F0F0] hover:text-[#000000] transition-colors" data-tab="quizzes">
            Quizzes
        </button>
        <button class="tab-btn w-full text-left px-4 py-3 text-xs font-bold uppercase tracking-wider border-l-2 border-transparent text-[#666666] hover:bg-[#F0F0F0] hover:text-[#000000] transition-colors" data-tab="warnings">
            Warnings
        </button>
        <button class="tab-btn w-full text-left px-4 py-3 text-xs font-bold uppercase tracking-wider border-l-2 border-transparent text-[#666666] hover:bg-[#F0F0F0] hover:text-[#000000] transition-colors" data-tab="insights">
            Insights
        </button>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">

        {{-- Tab Content: Activity --}}
        <div class="tab-content flex-1 overflow-y-auto p-6 custom-scrollbar" id="tab-activity">
            <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] mb-4">Recent Activity</h2>

            @if($recentActivity->isEmpty())
                <div class="bg-white border border-[#E5E5E5] p-8 text-center">
                    <p class="text-sm text-[#666666]">No recent activity yet.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($recentActivity as $activity)
                        <div class="bg-white border border-[#E5E5E5] p-4">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    @if($activity->type === 'topic')
                                        <span class="text-xs font-bold text-[#000000]">Created topic</span>
                                        <a href="{{ route('topics.show', [$activity->group_id, $activity->topic_id]) }}" 
                                           class="text-sm font-semibold text-[#000000] hover:underline block mt-1 truncate">
                                            {{ $activity->title }}
                                        </a>
                                    @elseif($activity->type === 'post')
                                        <span class="text-xs font-bold text-[#000000]">Posted reply</span>
                                        <a href="{{ route('topics.show', [$activity->group_id, $activity->topic_id]) }}#post-{{ $activity->post_id }}" 
                                           class="text-sm text-[#666666] block mt-1 line-clamp-2 hover:text-[#000000]">
                                            {{ $activity->content }}
                                        </a>
                                    @elseif($activity->type === 'like')
                                        <span class="text-xs font-bold text-[#000000]">Liked a post</span>
                                        <a href="{{ route('topics.show', [$activity->group_id, $activity->topic_id]) }}#post-{{ $activity->post_id }}" 
                                           class="text-sm text-[#666666] block mt-1 line-clamp-2 hover:text-[#000000]">
                                            {{ $activity->content }}
                                        </a>
                                    @endif
                                </div>
                                <span class="text-[10px] text-[#666666] flex-shrink-0 ml-4">
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
            <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] mb-4">Quiz Performance</h2>

            @if($quizSubmissions->isEmpty())
                <div class="bg-white border border-[#E5E5E5] p-8 text-center">
                    <p class="text-sm text-[#666666]">You haven't taken any quizzes yet.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($quizSubmissions as $submission)
                        <div class="bg-white border border-[#E5E5E5] p-4">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm font-bold text-[#000000]">{{ $submission->quiz->title ?? 'Quiz' }}</h3>
                                    <div class="flex items-center space-x-3 mt-1 flex-wrap">
                                        <span class="text-xs text-[#666666]">{{ $submission->quiz->group->name ?? 'N/A' }}</span>
                                        <span class="text-[10px] text-[#666666]">•</span>
                                        <span class="text-xs font-bold {{ $submission->score >= 70 ? 'text-[#16A34A]' : ($submission->score >= 50 ? 'text-[#D97706]' : 'text-[#DC2626]') }}">
                                            Score: {{ $submission->score ?? 'N/A' }}%
                                        </span>
                                        @if($submission->is_auto_submitted)
                                            <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-1.5 py-0.5">
                                                Auto-submitted
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-[10px] text-[#666666] flex-shrink-0 ml-4">
                                    {{ $submission->created_at->format('M d, Y') }}
                                </span>
                            </div>
                            @if($submission->answers_payload)
                                <details class="mt-2">
                                    <summary class="text-[10px] text-[#666666] cursor-pointer hover:text-[#000000]">
                                        View Answers
                                    </summary>
                                    <div class="mt-2 p-3 bg-[#FAFAFA] border border-[#E5E5E5] text-xs text-[#666666] overflow-x-auto">
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
            <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] mb-4">Warning History</h2>

            @if($warningLogs->isEmpty())
                <div class="bg-white border border-[#E5E5E5] p-8 text-center">
                    <p class="text-sm text-[#666666]">No warnings on your account.</p>
                    <p class="text-xs text-[#16A34A] mt-2">✅ You're in good standing!</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($warningLogs as $log)
                        <div class="bg-white border border-[#E5E5E5] p-4">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center space-x-3 flex-wrap">
                                        @if($log->action_type === 'issue_warning_1')
                                            <span class="text-xs font-bold text-[#D97706]">⚠️ First Warning</span>
                                        @elseif($log->action_type === 'issue_warning_2')
                                            <span class="text-xs font-bold text-[#D97706]">⚠️ Second Warning</span>
                                        @elseif($log->action_type === 'hard_blacklist')
                                            <span class="text-xs font-bold text-[#DC2626]">🚫 Blacklisted</span>
                                        @else
                                            <span class="text-xs font-bold text-[#666666]">{{ $log->action_type }}</span>
                                        @endif
                                        @if($log->expires_at)
                                            <span class="text-[8px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-1.5 py-0.5">
                                                Expires: {{ $log->expires_at->format('M d, Y') }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-[#666666] mt-1">{{ $log->reason }}</p>
                                </div>
                                <span class="text-[10px] text-[#666666] flex-shrink-0 ml-4">
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
    <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] mb-4">ML-Powered Insights</h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Chart (Graphical) --}}
        <div class="bg-white border border-[#E5E5E5] p-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Interest Categories</h3>
            <canvas id="profileAffinityChart" height="200"></canvas>
            <p class="text-[9px] text-[#666666] mt-2">* Based on your interactions (views, likes, comments)</p>
        </div>

        {{-- Text Breakdown --}}
        <div class="bg-white border border-[#E5E5E5] p-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Detailed Breakdown</h3>
            @if(isset($affinityScores) && count($affinityScores) > 0)
                <div class="space-y-2">
                    @foreach($affinityScores as $category => $score)
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-[#000000]">{{ $category }}</span>
                                <span class="text-[10px] text-[#666666]">{{ $score }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-[#E5E5E5] mt-0.5">
                                <div class="h-full bg-[#000000]" style="width: {{ $score }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-[#666666]">Not enough data yet.</p>
            @endif
        </div>

        {{-- Recommendations --}}
        <div class="bg-white border border-[#E5E5E5] p-4 lg:col-span-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Recommended For You</h3>
            @if($recommendations->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($recommendations->take(4) as $topic)
                        <a href="{{ route('topics.show', [$topic->group_id, $topic->id]) }}" 
                           class="block hover:bg-[#F5F5F5] transition-colors p-2 border border-[#E5E5E5]">
                            <p class="text-sm text-[#000000]">{{ $topic->title }}</p>
                            <div class="flex items-center space-x-2 mt-0.5">
                                <span class="text-[10px] text-[#666666]">{{ $topic->group->name }}</span>
                                @if($topic->ml_category)
                                    <span class="text-[8px] font-bold uppercase tracking-wider border border-[#E5E5E5] px-1.5 py-0.5">
                                        {{ $topic->ml_category }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
                @if($recommendations->count() > 4)
                    <p class="text-[9px] text-[#666666] text-center mt-2">
                        + {{ $recommendations->count() - 4 }} more
                    </p>
                @endif
            @else
                <p class="text-sm text-[#666666]">No recommendations available.</p>
            @endif
        </div>

    </div>

    {{-- Interaction Summary --}}
    <div class="mt-4 bg-white border border-[#E5E5E5] p-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Interaction Breakdown</h3>
        <div class="grid grid-cols-4 gap-4 text-center">
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $interactionCounts['views'] ?? 0 }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Views</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $interactionCounts['likes'] ?? 0 }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Likes</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $interactionCounts['comments'] ?? 0 }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Comments</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $interactionCounts['downloads'] ?? 0 }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Downloads</p>
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
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = {
        activity: document.getElementById('tab-activity'),
        quizzes: document.getElementById('tab-quizzes'),
        warnings: document.getElementById('tab-warnings'),
        insights: document.getElementById('tab-insights'),
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Reset all tabs
            tabs.forEach(t => {
                t.className = 'tab-btn w-full text-left px-4 py-3 text-xs font-bold uppercase tracking-wider border-l-2 border-transparent text-[#666666] hover:bg-[#F0F0F0] hover:text-[#000000] transition-colors';
            });

            // Hide all contents
            Object.values(contents).forEach(c => c.classList.add('hidden'));

            // Activate clicked tab
            this.className = 'tab-btn w-full text-left px-4 py-3 text-xs font-bold uppercase tracking-wider border-l-2 border-[#000000] bg-white text-[#000000] transition-colors';

            // Show corresponding content
            const tabName = this.dataset.tab;
            if (contents[tabName]) {
                contents[tabName].classList.remove('hidden');
            }
        });
    });

    // Activate first tab by default
    const firstTab = document.querySelector('.tab-btn');
    if (firstTab) {
        firstTab.click();
    }

    // Chart.js for Affinity Scores

    document.addEventListener('DOMContentLoaded', function() {
        // Profile Affinity Chart
        const ctx = document.getElementById('profileAffinityChart');
        if (ctx) {
            const scores = {!! json_encode($affinityScores) !!};
            const labels = Object.keys(scores);
            const data = Object.values(scores);
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: '#000000',
                        borderColor: '#000000',
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
});
</script>
@endpush