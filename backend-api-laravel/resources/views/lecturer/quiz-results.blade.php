@extends('layouts.workspace')

@section('title', $quiz->title . ' - Results')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.quizzes') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] truncate">Results</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#2563EB]">{{ number_format($averageScore, 0) }}%</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Average</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#16A34A]">{{ number_format($passRate, 0) }}%</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Pass Rate</p>
            </div>
        </div>
    </div>

    {{-- Submissions List --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1 flex items-center gap-1">
            <i data-lucide="users" style="width:12px;height:12px;"></i>
            {{ $submissions->count() }} Submissions
        </p>
        @foreach($submissions->take(10) as $submission)
            <div class="px-3 py-2.5 bg-white border border-[#E5E5E5] rounded-lg hover:border-[#0A574F] transition flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-7 h-7 bg-[#ECFDF5] rounded-full flex items-center justify-center flex-shrink-0">
                        <i data-lucide="user" style="width:12px;height:12px;color:#0A574F;"></i>
                    </div>
                    <span class="text-sm font-bold text-[#000000] truncate">{{ $submission->user->name ?? 'Unknown' }}</span>
                    @if($submission->is_auto_submitted)
                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-1.5 py-0.5 rounded-full flex items-center gap-1">
                            <i data-lucide="clock" style="width:8px;height:8px;"></i>
                            Auto
                        </span>
                    @endif
                </div>
                <span class="text-sm font-bold {{ $submission->score >= 70 ? 'text-[#16A34A]' : ($submission->score >= 50 ? 'text-[#D97706]' : 'text-[#DC2626]') }}">
                    {{ $submission->score ?? 'N/A' }}%
                </span>
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
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-3">
                        <i data-lucide="bar-chart-2" style="width:28px;height:28px;color:#0A574F;"></i>
                        {{ $quiz->title }} – Results
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
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('lecturer.quiz.export', $quiz->id) }}"
                       class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider bg-[#0A574F] text-white px-4 py-2 rounded-lg hover:bg-[#08443e] transition hover:shadow-sm">
                        <i data-lucide="download" style="width:14px;height:14px;"></i>
                        Export Results
                    </a>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#0A574F]">{{ $submissions->count() }}</p>
                    <p class="text-xs text-[#666666] font-medium">Submissions</p>
                </div>
                <div class="w-10 h-10 bg-[#ECFDF5] rounded-lg flex items-center justify-center">
                    <i data-lucide="users" style="width:20px;height:20px;color:#0A574F;"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#2563EB]">{{ number_format($averageScore, 0) }}%</p>
                    <p class="text-xs text-[#666666] font-medium">Average Score</p>
                    <div class="w-full h-1.5 bg-[#E5E5E5] rounded-full mt-2 overflow-hidden">
                        <div class="h-full bg-[#2563EB] rounded-full" style="width: {{ number_format($averageScore, 0) }}%;"></div>
                    </div>
                </div>
                <div class="w-10 h-10 bg-[#E0F2FE] rounded-lg flex items-center justify-center">
                    <i data-lucide="trending-up" style="width:20px;height:20px;color:#2563EB;"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#16A34A]">{{ number_format($passRate, 0) }}%</p>
                    <p class="text-xs text-[#666666] font-medium">Pass Rate</p>
                </div>
                <div class="w-10 h-10 bg-[#F0FDF4] rounded-lg flex items-center justify-center">
                    <i data-lucide="check-circle" style="width:20px;height:20px;color:#16A34A;"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#D97706]">{{ $submissions->where('score', '>=', 70)->count() }}</p>
                    <p class="text-xs text-[#666666] font-medium">Top Scores</p>
                </div>
                <div class="w-10 h-10 bg-[#FEF3C7] rounded-lg flex items-center justify-center">
                    <i data-lucide="award" style="width:20px;height:20px;color:#D97706;"></i>
                </div>
            </div>
        </div>

        {{-- Student Results --}}
        <div class="flex-1 overflow-y-auto px-6 pb-6 custom-scrollbar">
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                <div class="border-b border-[#E5E5E5] px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="list" style="width:18px;height:18px;color:#0A574F;"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#000000]">Student Results</h3>
                    </div>
                    <a href="{{ route('lecturer.quiz.export', $quiz->id) }}"
                       class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-[#0A574F] border border-[#0A574F] px-2 py-1 rounded-lg hover:bg-[#0A574F] hover:text-white transition">
                        <i data-lucide="download" style="width:10px;height:10px;"></i>
                        Export
                    </a>
                </div>
                <div class="divide-y divide-[#F5F5F5] max-h-[400px] overflow-y-auto">
                    @forelse($submissions as $submission)
                        <div class="px-5 py-4 flex items-center justify-between hover:bg-[#F9F9F9] transition">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-9 h-9 bg-[#ECFDF5] rounded-full flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="user" style="width:14px;height:14px;color:#0A574F;"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center flex-wrap gap-2">
                                        <span class="text-sm font-bold text-[#000000]">{{ $submission->user->name ?? 'Unknown' }}</span>
                                        @if($submission->is_auto_submitted)
                                            <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-1.5 py-0.5 rounded-full flex items-center gap-1">
                                                <i data-lucide="clock" style="width:8px;height:8px;"></i>
                                                Auto
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 mt-0.5 text-[10px] text-[#666666]">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="calendar" style="width:10px;height:10px;"></i>
                                            {{ $submission->created_at->format('M d, Y h:i A') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0 ml-4">
                                <div class="w-20 h-1.5 bg-[#E5E5E5] rounded-full overflow-hidden hidden sm:block">
                                    <div class="h-full rounded-full" style="width: {{ $submission->score ?? 0 }}%; background: {{ ($submission->score ?? 0) >= 70 ? '#16A34A' : (($submission->score ?? 0) >= 50 ? '#D97706' : '#DC2626') }};"></div>
                                </div>
                                <span class="text-sm font-bold {{ $submission->score >= 70 ? 'text-[#16A34A]' : ($submission->score >= 50 ? 'text-[#D97706]' : 'text-[#DC2626]') }}">
                                    {{ $submission->score ?? 'N/A' }}%
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <i data-lucide="inbox" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                            <p class="text-sm text-[#666666]">No submissions yet.</p>
                            <p class="text-xs text-[#94A3B8]">Students will appear here once they submit the quiz.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        lucide.createIcons();
    </script>
@endpush