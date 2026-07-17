@extends('layouts.workspace')

@section('title', 'Performance Report - ' . $quiz->title)

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('student.quizzes') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] truncate">{{ $quiz->title }}</h2>
    </div>

    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="text-center">
            <p class="text-3xl font-bold {{ $submission->score >= 70 ? 'text-[#16A34A]' : ($submission->score >= 50 ? 'text-[#D97706]' : 'text-[#DC2626]') }}">
                {{ $submission->score ?? 0 }}%
            </p>
            <p class="text-[10px] text-[#666666] uppercase tracking-wider">Your Score</p>
        </div>
    </div>

    <div class="p-3 bg-[#FAFAFA] border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-2 text-center">
            <div>
                <p class="text-sm font-bold text-[#000000]">#{{ $rank }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Rank</p>
            </div>
            <div>
                <p class="text-sm font-bold text-[#000000]">{{ number_format($averageScore, 1) }}%</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Class Avg</p>
            </div>
            <div>
                <p class="text-sm font-bold text-[#000000]">{{ $passRate }}%</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Pass Rate</p>
            </div>
            <div>
                <p class="text-sm font-bold text-[#000000]">{{ $submission->answers->count() }}/{{ $quiz->questions->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Answered</p>
            </div>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-2">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1">Question Summary</p>
        @foreach($questionDetails as $q)
            <div class="bg-white border border-[#E5E5E5] p-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#000000]">Q{{ $q['number'] }}</span>
                    @if($q['is_correct'])
                        <span class="text-[#16A34A]">✅ Correct (+{{ $q['points_earned'] }} pts)</span>
                    @else
                        <span class="text-[#DC2626]">❌ Incorrect (0 pts)</span>
                    @endif
                </div>
                <p class="text-[10px] text-[#666666] mt-1 line-clamp-2">{{ $q['question'] }}</p>
            </div>
        @endforeach
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-[#000000]">{{ $quiz->title }}</h1>
                    <p class="text-sm text-[#666666] mt-1">
                        {{ $quiz->group->name ?? 'N/A' }} • {{ $quiz->duration }} min
                        @if($submission->is_auto_submitted)
                            <span class="text-[#D97706]">• Auto-submitted</span>
                        @endif
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-[#666666]">
                        {{ $submission->created_at->format('M d, Y h:i A') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Score Summary (Left) --}}
                <div class="lg:col-span-1 space-y-4">
                    {{-- Score Circle --}}
                    <div class="bg-white border border-[#E5E5E5] p-6 text-center">
                        <div class="relative inline-block">
                            <svg class="w-32 h-32 transform -rotate-90">
                                <circle cx="64" cy="64" r="56" fill="none" stroke="#E5E5E5" stroke-width="12"/>
                                <circle cx="64" cy="64" r="56" fill="none"
                                        stroke="{{ $submission->score >= 70 ? '#16A34A' : ($submission->score >= 50 ? '#D97706' : '#DC2626') }}"
                                        stroke-width="12"
                                        stroke-dasharray="{{ ($submission->score / 100) * 351.86 }} 351.86"
                                        stroke-linecap="round"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div>
                                    <p class="text-3xl font-bold {{ $submission->score >= 70 ? 'text-[#16A34A]' : ($submission->score >= 50 ? 'text-[#D97706]' : 'text-[#DC2626]') }}">
                                        {{ $submission->score ?? 0 }}%
                                    </p>
                                    <p class="text-[10px] text-[#666666] uppercase tracking-wider">Score</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <div class="border border-[#E5E5E5] p-2">
                                <p class="text-lg font-bold text-[#000000]">#{{ $rank }}</p>
                                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Rank</p>
                            </div>
                            <div class="border border-[#E5E5E5] p-2">
                                <p class="text-lg font-bold text-[#000000]">{{ number_format($averageScore, 1) }}%</p>
                                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Average</p>
                            </div>
                            <div class="border border-[#E5E5E5] p-2">
                                <p class="text-lg font-bold text-[#000000]">{{ $passRate }}%</p>
                                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Pass Rate</p>
                            </div>
                            <div class="border border-[#E5E5E5] p-2">
                                <p class="text-lg font-bold text-[#000000]">{{ $submission->answers->count() }}/{{ $quiz->questions->count() }}</p>
                                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Answered</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Score Distribution & Charts (Center) --}}
                <div class="lg:col-span-2 space-y-4">
                    {{-- Score Distribution Chart --}}
                    <div class="bg-white border border-[#E5E5E5] p-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Score Distribution</h3>
                        <canvas id="scoreDistribution" height="150"></canvas>
                    </div>

                    {{-- Question Breakdown --}}
                    <div class="bg-white border border-[#E5E5E5] p-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Answer Breakdown</h3>
                        <div class="divide-y divide-[#E5E5E5]">
                            @foreach($questionDetails as $q)
                                <div class="py-3 first:pt-0 last:pb-0">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs font-bold text-[#000000]">Q{{ $q['number'] }}</span>
                                                @if($q['is_correct'])
                                                    <span class="text-[#16A34A] text-[10px]">✅</span>
                                                @else
                                                    <span class="text-[#DC2626] text-[10px]">❌</span>
                                                @endif
                                                <span class="text-[10px] text-[#666666] truncate">{{ $q['question'] }}</span>
                                            </div>
                                            <div class="flex items-center space-x-2 mt-0.5">
                                                <span class="text-[9px] text-[#666666]">
                                                    Your answer: <span class="font-bold">{{ $q['user_answer'] ?: 'Not answered' }}</span>
                                                </span>
                                                @if(!$q['is_correct'] && $q['correct_answer'])
                                                    <span class="text-[9px] text-[#16A34A]">
                                                        Correct: {{ is_array($q['correct_answer']) ? implode(', ', $q['correct_answer']) : $q['correct_answer'] }}
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
                        borderWidth: 1
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