@extends('layouts.workspace')

@section('title', 'Performance Report - ' . $quiz->title)

@section('context_panel')
    @php
        $backRoute = request()->query('from') === 'profile' 
            ? route('profile') . '?tab=quizzes' 
            : route('student.quizzes');
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

    {{-- Quick Stats --}}
    <div class="p-3 bg-[#FAFAFA] border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-2 text-center">
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-2 hover:shadow-sm transition">
                <p class="text-sm font-bold text-[#000000]">#{{ $rank }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider flex items-center justify-center gap-1">
                    <i data-lucide="trophy" style="width:10px;height:10px;"></i>
                    Rank
                </p>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-2 hover:shadow-sm transition">
                <p class="text-sm font-bold text-[#000000]">{{ number_format($averageScore, 1) }}%</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider flex items-center justify-center gap-1">
                    <i data-lucide="users" style="width:10px;height:10px;"></i>
                    Class Avg
                </p>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-2 hover:shadow-sm transition">
                <p class="text-sm font-bold text-[#000000]">{{ $passRate }}%</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider flex items-center justify-center gap-1">
                    <i data-lucide="check-circle" style="width:10px;height:10px;"></i>
                    Pass Rate
                </p>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-2 hover:shadow-sm transition">
                <p class="text-sm font-bold text-[#000000]">{{ $submission->answers->count() }}/{{ $quiz->questions->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider flex items-center justify-center gap-1">
                    <i data-lucide="check-square" style="width:10px;height:10px;"></i>
                    Answered
                </p>
            </div>
        </div>
    </div>

    {{-- Question Summary List --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-2">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1 flex items-center gap-1">
            <i data-lucide="list" style="width:12px;height:12px;"></i>
            Question Summary
        </p>
        @foreach($questionDetails as $q)
            <div class="bg-white border border-[#E5E5E5] rounded-lg p-3 hover:border-[#0A574F] hover:shadow-sm transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#000000] flex items-center gap-1">
                        <i data-lucide="help-circle" style="width:12px;height:12px;color:#0A574F;"></i>
                        Q{{ $q['number'] }}
                    </span>
                    @if($q['is_correct'])
                        <span class="text-[#16A34A] flex items-center gap-1">
                            <i data-lucide="check-circle" style="width:12px;height:12px;"></i>
                            Correct (+{{ $q['points_earned'] }} pts)
                        </span>
                    @else
                        <span class="text-[#DC2626] flex items-center gap-1">
                            <i data-lucide="x-circle" style="width:12px;height:12px;"></i>
                            Incorrect (0 pts)
                        </span>
                    @endif
                </div>
                <p class="text-[10px] text-[#666666] mt-1 line-clamp-2">{{ $q['question'] }}</p>
                <div class="flex items-center gap-4 mt-2 text-[9px]">
                    <span class="flex items-center gap-1">
                        <i data-lucide="user" style="width:10px;height:10px;color:#2563EB;"></i>
                        <span class="text-[#666666]">Your answer:</span>
                        <span class="font-bold text-[#000000]">{{ $q['user_answer'] ?: 'Not answered' }}</span>
                    </span>
                    @if(!$q['is_correct'] && $q['correct_answer'])
                        <span class="flex items-center gap-1">
                            <i data-lucide="check" style="width:10px;height:10px;color:#16A34A;"></i>
                            <span class="text-[#16A34A]">Correct: {{ is_array($q['correct_answer']) ? implode(', ', $q['correct_answer']) : $q['correct_answer'] }}</span>
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
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

        {{-- Main Content --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Score Summary (Left) – enlarged --}}
                <div class="lg:col-span-1 space-y-4">

                    {{-- Score Circle (larger, more padding) --}}
                    <div class="bg-white rounded-xl border border-[#E5E5E5] shadow-sm p-8 text-center hover:shadow-md transition">
                        <div class="relative inline-block">
                            {{-- SVG circle with increased radius and thinner stroke to give text breathing room --}}
                            <svg class="w-56 h-56 transform -rotate-90">
                                {{-- Background circle --}}
                                <circle cx="112" cy="112" r="96" fill="none" stroke="#E5E5E5" stroke-width="12"/>
                                {{-- Progress circle --}}
                                @php
                                    $circumference = 2 * pi() * 96; // ≈ 603.19
                                    $dashOffset = $circumference - (($submission->score / 100) * $circumference);
                                @endphp
                                <circle cx="112" cy="112" r="96" fill="none"
                                        stroke="{{ $submission->score >= 70 ? '#16A34A' : ($submission->score >= 50 ? '#D97706' : '#DC2626') }}"
                                        stroke-width="12"
                                        stroke-dasharray="{{ $circumference }}"
                                        stroke-dashoffset="{{ $dashOffset }}"
                                        stroke-linecap="round"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div>
                                    <p class="text-5xl font-bold {{ $submission->score >= 70 ? 'text-[#16A34A]' : ($submission->score >= 50 ? 'text-[#D97706]' : 'text-[#DC2626]') }}">
                                        {{ $submission->score ?? 0 }}%
                                    </p>
                                    <p class="text-[10px] text-[#666666] uppercase tracking-wider flex items-center justify-center gap-1">
                                        <i data-lucide="award" style="width:12px;height:12px;"></i>
                                        Score
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <div class="bg-white border border-[#E5E5E5] rounded-lg p-3 hover:border-[#0A574F] transition">
                                <p class="text-lg font-bold text-[#000000] flex items-center justify-center gap-1">
                                    <i data-lucide="trophy" style="width:16px;height:16px;color:#D97706;"></i>
                                    #{{ $rank }}
                                </p>
                                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Rank</p>
                            </div>
                            <div class="bg-white border border-[#E5E5E5] rounded-lg p-3 hover:border-[#0A574F] transition">
                                <p class="text-lg font-bold text-[#000000] flex items-center justify-center gap-1">
                                    <i data-lucide="users" style="width:16px;height:16px;color:#2563EB;"></i>
                                    {{ number_format($averageScore, 1) }}%
                                </p>
                                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Average</p>
                            </div>
                            <div class="bg-white border border-[#E5E5E5] rounded-lg p-3 hover:border-[#0A574F] transition">
                                <p class="text-lg font-bold text-[#000000] flex items-center justify-center gap-1">
                                    <i data-lucide="check-circle" style="width:16px;height:16px;color:#16A34A;"></i>
                                    {{ $passRate }}%
                                </p>
                                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Pass Rate</p>
                            </div>
                            <div class="bg-white border border-[#E5E5E5] rounded-lg p-3 hover:border-[#0A574F] transition">
                                <p class="text-lg font-bold text-[#000000] flex items-center justify-center gap-1">
                                    <i data-lucide="check-square" style="width:16px;height:16px;color:#2563EB;"></i>
                                    {{ $submission->answers->count() }}/{{ $quiz->questions->count() }}
                                </p>
                                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Answered</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Score Distribution & Charts (Center) --}}
                <div class="lg:col-span-2 space-y-4">

                    {{-- Score Distribution Chart --}}
                    <div class="bg-white rounded-xl border border-[#E5E5E5] shadow-sm p-4 hover:shadow-md transition">
                        <div class="flex items-center gap-2 mb-3">
                            <i data-lucide="bar-chart-2" style="width:18px;height:18px;color:#0A574F;"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666]">Score Distribution</h3>
                        </div>
                        {{-- Fixed height container to prevent uncontrolled growth --}}
                        <div style="height: 200px;">
                            <canvas id="scoreDistribution"></canvas>
                        </div>
                    </div>

                    {{-- Question Breakdown --}}
                    <div class="bg-white rounded-xl border border-[#E5E5E5] shadow-sm p-4 hover:shadow-md transition">
                        <div class="flex items-center gap-2 mb-3">
                            <i data-lucide="list" style="width:18px;height:18px;color:#0A574F;"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666]">Answer Breakdown</h3>
                        </div>
                        <div class="divide-y divide-[#E5E5E5]">
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
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();

        const scores = {!! json_encode($allSubmissions->pluck('score')->filter()) !!};
        if (scores.length > 0) {
            const bins = [0,10,20,30,40,50,60,70,80,90,100];
            const counts = bins.map((b, i) => {
                if (i === bins.length-1) return scores.filter(s => s >= b).length;
                return scores.filter(s => s >= b && s < bins[i+1]).length;
            });
            new Chart(document.getElementById('scoreDistribution'), {
                type: 'bar',
                data: {
                    labels: bins.map((b,i) => i < bins.length-1 ? b+'-'+(bins[i+1]-1) : b+'+'),
                    datasets: [{
                        label: 'Students',
                        data: counts,
                        backgroundColor: 'rgba(0,0,0,0.7)',
                        borderColor: '#000',
                        borderWidth: 1,
                        borderRadius: 2,
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