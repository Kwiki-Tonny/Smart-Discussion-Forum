@extends('layouts.workspace')

@section('title', 'Performance Report - ' . $quiz->title)

@section('context_panel')
    @php
        $backRoute = request()->query('from') === 'profile' 
            ? route('profile') . '?tab=quizzes' 
            : route('student.quizzes');
        // ─── COMPUTE ANSWERED COUNT FROM QUESTION DETAILS ──────
        $answeredCount = collect($questionDetails)->filter(fn($q) => $q['user_answer'] !== 'Not answered')->count();
        $totalQuestions = count($questionDetails);
    @endphp
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ $backRoute }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity flex items-center gap-1">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] truncate">{{ $quiz->title }}</h2>
    </div>

    {{-- Score Summary (Context Panel) --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="text-center">
            <p class="text-4xl font-bold {{ $submission->score >= 70 ? 'text-[#16A34A]' : ($submission->score >= 50 ? 'text-[#D97706]' : 'text-[#DC2626]') }}">
                {{ $submission->score ?? 0 }}%
            </p>
            <p class="text-[10px] text-[#666666] uppercase tracking-wider flex items-center justify-center gap-1">
                <i data-lucide="award" style="width:12px;height:12px;"></i>
                Your Score
            </p>
        </div>
    </div>

    {{-- Quick Stats (Context Panel) --}}
    <div class="p-3 bg-[#FAFAFA] border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-2 text-center">
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-2 hover:shadow-sm transition">
                <p class="text-sm font-bold text-[#000000]">#{{ $rank }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider flex items-center justify-center gap-1">
                    <i data-lucide="trophy" style="width:10px;height:10px;color:#D97706;"></i>
                    Rank
                </p>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-2 hover:shadow-sm transition">
                <p class="text-sm font-bold text-[#000000]">{{ number_format($averageScore, 1) }}%</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider flex items-center justify-center gap-1">
                    <i data-lucide="users" style="width:10px;height:10px;color:#2563EB;"></i>
                    Class Avg
                </p>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-2 hover:shadow-sm transition">
                <p class="text-sm font-bold text-[#000000]">{{ $passRate }}%</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider flex items-center justify-center gap-1">
                    <i data-lucide="check-circle" style="width:10px;height:10px;color:#16A34A;"></i>
                    Pass Rate
                </p>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-2 hover:shadow-sm transition">
                <p class="text-sm font-bold text-[#000000]">{{ $answeredCount }}/{{ $totalQuestions }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider flex items-center justify-center gap-1">
                    <i data-lucide="check-square" style="width:10px;height:10px;color:#2563EB;"></i>
                    Answered
                </p>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-[#000000] flex items-center gap-2">
                        <i data-lucide="clipboard-list" style="width:24px;height:24px;color:#0A574F;"></i>
                        {{ $quiz->title }}
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-2 flex-wrap">
                        <span class="flex items-center gap-1">
                            <i data-lucide="users" style="width:14px;height:14px;color:#0A574F;"></i>
                            {{ $quiz->group->name ?? 'N/A' }}
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="clock" style="width:14px;height:14px;color:#0A574F;"></i>
                            {{ $quiz->duration }} min
                        </span>
                        @if($submission->is_auto_submitted)
                            <span class="text-[#D97706] flex items-center gap-1">
                                <i data-lucide="clock" style="width:12px;height:12px;"></i>
                                Auto-submitted
                            </span>
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar" style="width:14px;height:14px;color:#666666;"></i>
                    <span class="text-xs text-[#666666]">
                        {{ $submission->created_at->format('M d, Y h:i A') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Main Content – 2‑column top row + full‑width bottom --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar flex flex-col gap-6">

            {{-- TOP ROW: Round Score Chart + Distribution Chart (side by side) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 flex-1">

                {{-- Round Score Chart --}}
                <div class="bg-white rounded-xl border border-[#E5E5E5] shadow-sm p-6 hover:shadow-md transition flex flex-col items-center justify-center">
                    <div class="relative inline-block">
                        <svg class="w-48 h-48 transform -rotate-90">
                            <circle cx="96" cy="96" r="84" fill="none" stroke="#E5E5E5" stroke-width="14"/>
                            @php
                                $circumference = 2 * pi() * 84;
                                $dashOffset = $circumference - (($submission->score / 100) * $circumference);
                            @endphp
                            <circle cx="96" cy="96" r="84" fill="none"
                                    stroke="{{ $submission->score >= 70 ? '#16A34A' : ($submission->score >= 50 ? '#D97706' : '#DC2626') }}"
                                    stroke-width="14"
                                    stroke-dasharray="{{ $circumference }}"
                                    stroke-dashoffset="{{ $dashOffset }}"
                                    stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <p class="text-4xl font-bold {{ $submission->score >= 70 ? 'text-[#16A34A]' : ($submission->score >= 50 ? 'text-[#D97706]' : 'text-[#DC2626]') }}">
                                    {{ $submission->score ?? 0 }}%
                                </p>
                                <p class="text-[10px] text-[#666666] uppercase tracking-wider flex items-center justify-center gap-1">
                                    <i data-lucide="award" style="width:12px;height:12px;"></i>
                                    Score
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Quick stats below the circle --}}
                    <div class="mt-4 grid grid-cols-2 gap-3 w-full">
                        <div class="bg-[#F9F9F9] rounded-lg p-2 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <i data-lucide="trophy" style="width:14px;height:14px;color:#D97706;"></i>
                                <p class="text-sm font-bold text-[#000000]">#{{ $rank }}</p>
                            </div>
                            <p class="text-[8px] text-[#666666] uppercase tracking-wider">Rank</p>
                        </div>
                        <div class="bg-[#F9F9F9] rounded-lg p-2 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <i data-lucide="users" style="width:14px;height:14px;color:#2563EB;"></i>
                                <p class="text-sm font-bold text-[#000000]">{{ number_format($averageScore, 1) }}%</p>
                            </div>
                            <p class="text-[8px] text-[#666666] uppercase tracking-wider">Avg</p>
                        </div>
                        <div class="bg-[#F9F9F9] rounded-lg p-2 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <i data-lucide="check-circle" style="width:14px;height:14px;color:#16A34A;"></i>
                                <p class="text-sm font-bold text-[#000000]">{{ $passRate }}%</p>
                            </div>
                            <p class="text-[8px] text-[#666666] uppercase tracking-wider">Pass</p>
                        </div>
                        <div class="bg-[#F9F9F9] rounded-lg p-2 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <i data-lucide="check-square" style="width:14px;height:14px;color:#2563EB;"></i>
                                <p class="text-sm font-bold text-[#000000]">{{ $answeredCount }}/{{ $totalQuestions }}</p>
                            </div>
                            <p class="text-[8px] text-[#666666] uppercase tracking-wider">Answered</p>
                        </div>
                    </div>
                </div>

                {{-- Score Distribution Chart (using $allSubmissions) --}}
                <div class="bg-white rounded-xl border border-[#E5E5E5] shadow-sm p-4 hover:shadow-md transition flex flex-col">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="bar-chart-2" style="width:18px;height:18px;color:#0A574F;"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666]">Score Distribution</h3>
                    </div>
                    <div class="flex-1" style="min-height: 0;">
                        @php
                            $scores = $allSubmissions->pluck('score')->filter();
                        @endphp
                        @if($scores->isEmpty())
                            <div class="flex items-center justify-center h-full text-[#999999] text-sm flex-col gap-2">
                                <i data-lucide="inbox" style="width:32px;height:32px;"></i>
                                <p>No submissions yet</p>
                                <p class="text-xs">The chart will appear once students submit the quiz.</p>
                            </div>
                        @else
                            <canvas id="scoreDistribution" style="width:100%; height:100%;"></canvas>
                        @endif
                    </div>
                </div>

            </div>

            {{-- BOTTOM ROW: Merged Question Summary + Answer Breakdown --}}
            <div class="bg-white rounded-xl border border-[#E5E5E5] shadow-sm p-4 hover:shadow-md transition">
                <div class="flex items-center gap-2 mb-3">
                    <i data-lucide="list" style="width:18px;height:18px;color:#0A574F;"></i>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666]">Question Details</h3>
                </div>
                <div class="divide-y divide-[#E5E5E5] max-h-64 overflow-y-auto custom-scrollbar">
                    @foreach($questionDetails as $q)
                        <div class="py-3 first:pt-0 last:pb-0">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-[#000000] flex items-center gap-1">
                                            <i data-lucide="help-circle" style="width:12px;height:12px;color:#0A574F;"></i>
                                            Q{{ $q['number'] }}
                                        </span>
                                        @if($q['is_correct'])
                                            <span class="text-[#16A34A] text-[10px] flex items-center gap-0.5">
                                                <i data-lucide="check-circle" style="width:10px;height:10px;"></i>
                                            </span>
                                        @else
                                            <span class="text-[#DC2626] text-[10px] flex items-center gap-0.5">
                                                <i data-lucide="x-circle" style="width:10px;height:10px;"></i>
                                            </span>
                                        @endif
                                        <span class="text-[10px] text-[#666666] truncate">{{ $q['question'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 mt-1 flex-wrap text-[10px]">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="user" style="width:12px;height:12px;color:#2563EB;"></i>
                                            <span class="text-[#666666]">Your answer:</span>
                                            <span class="font-bold text-[#000000]">{{ $q['user_answer'] ?: 'Not answered' }}</span>
                                        </span>
                                        @if(!$q['is_correct'] && $q['correct_answer'])
                                            <span class="flex items-center gap-1">
                                                <i data-lucide="check" style="width:12px;height:12px;color:#16A34A;"></i>
                                                <span class="text-[#16A34A]">Correct: {{ is_array($q['correct_answer']) ? implode(', ', $q['correct_answer']) : $q['correct_answer'] }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0 ml-4">
                                    <span class="text-xs font-bold {{ $q['is_correct'] ? 'text-[#16A34A]' : 'text-[#DC2626]' }}">
                                        {{ $q['points_earned'] }}/{{ $q['points'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
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

        // ─── SCORE DISTRIBUTION CHART ──────────────────────────
        const scores = {!! json_encode($allSubmissions->pluck('score')->filter()) !!};
        if (scores.length > 0) {
            const bins = [0,10,20,30,40,50,60,70,80,90,100];
            const binLabels = bins.map((b, i) => {
                if (i === bins.length - 1) return b + '+';
                return b + '-' + (bins[i+1] - 1);
            });

            const counts = bins.map((b, i) => {
                if (i === bins.length - 1) return scores.filter(s => s >= b).length;
                return scores.filter(s => s >= b && s < bins[i+1]).length;
            });

            const maxCount = Math.max(...counts, 1);
            const backgroundColors = counts.map(count => {
                const opacity = 0.2 + 0.7 * (count / maxCount);
                return `rgba(10, 87, 79, ${opacity})`;
            });
            const borderColors = counts.map(() => '#0A574F');
            const borderWidths = counts.map(() => 1.5);

            new Chart(document.getElementById('scoreDistribution'), {
                type: 'bar',
                data: {
                    labels: binLabels,
                    datasets: [{
                        label: 'Students',
                        data: counts,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: borderWidths,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush