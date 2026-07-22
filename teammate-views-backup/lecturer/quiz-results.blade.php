@extends('layouts.workspace')

@section('title', $quiz->title . ' - Results')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.quizzes') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] truncate">Results</h2>
    </div>
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-2 text-center">
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ number_format($averageScore, 0) }}%</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Average</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ number_format($passRate, 0) }}%</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Pass Rate</p>
            </div>
        </div>
    </div>
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1">{{ $submissions->count() }} Submissions</p>
        @foreach($submissions->take(10) as $submission)
            <div class="px-3 py-2 bg-white border border-[#E5E5E5]">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-[#000000] truncate">{{ $submission->user->name ?? 'Unknown' }}</span>
                    <span class="text-sm font-bold {{ $submission->score >= 70 ? 'text-[#16A34A]' : ($submission->score >= 50 ? 'text-[#D97706]' : 'text-[#DC2626]') }}">
                        {{ $submission->score ?? 'N/A' }}%
                    </span>
                </div>
                @if($submission->is_auto_submitted)
                    <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706]">Auto-submitted</span>
                @endif
            </div>
        @endforeach
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">{{ $quiz->title }} – Results</h1>
            <p class="text-sm text-[#666666] mt-1">{{ $quiz->group->name ?? 'N/A' }} • {{ $quiz->duration }} min</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
            <div class="bg-white border border-[#E5E5E5] p-4 text-center">
                <p class="text-2xl font-bold text-[#000000]">{{ $submissions->count() }}</p>
                <p class="text-xs text-[#666666] uppercase tracking-wider">Submissions</p>
            </div>
            <div class="bg-white border border-[#E5E5E5] p-4 text-center">
                <p class="text-2xl font-bold text-[#000000]">{{ number_format($averageScore, 0) }}%</p>
                <p class="text-xs text-[#666666] uppercase tracking-wider">Average Score</p>
            </div>
            <div class="bg-white border border-[#E5E5E5] p-4 text-center">
                <p class="text-2xl font-bold text-[#000000]">{{ number_format($passRate, 0) }}%</p>
                <p class="text-xs text-[#666666] uppercase tracking-wider">Pass Rate</p>
            </div>
            <div class="bg-white border border-[#E5E5E5] p-4 text-center">
                <p class="text-2xl font-bold text-[#000000]">
                    {{ $submissions->where('score', '>=', 70)->count() }}
                </p>
                <p class="text-xs text-[#666666] uppercase tracking-wider">Top Scores</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="bg-white border border-[#E5E5E5]">
                <div class="border-b border-[#E5E5E5] px-4 py-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666]">Student Results</h3>
                </div>
                <div class="divide-y divide-[#E5E5E5]">
                    @forelse($submissions as $submission)
                        <div class="px-4 py-3 flex justify-between items-center">
                            <div>
                                <span class="text-sm font-bold text-[#000000]">{{ $submission->user->name ?? 'Unknown' }}</span>
                                <div class="flex items-center space-x-2 mt-0.5">
                                    <span class="text-[10px] text-[#666666]">{{ $submission->created_at->format('M d, Y h:i A') }}</span>
                                    @if($submission->is_auto_submitted)
                                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-1.5 py-0.5">Auto</span>
                                    @endif
                                </div>
                            </div>
                            <span class="text-lg font-bold {{ $submission->score >= 70 ? 'text-[#16A34A]' : ($submission->score >= 50 ? 'text-[#D97706]' : 'text-[#DC2626]') }}">
                                {{ $submission->score ?? 'N/A' }}%
                            </span>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center">
                            <p class="text-sm text-[#666666]">No submissions yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection