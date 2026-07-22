@extends('layouts.workspace')

@section('title', 'Quiz Management')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Quizzes</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-3 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#0A574F]">{{ $quizzes->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Total Quizzes</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#2563EB]">{{ $quizzes->sum('submissions_count') ?? 0 }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Submissions</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#D97706]">{{ $quizzes->filter(fn($q) => $q->isActive())->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Active Quizzes</p>
            </div>
        </div>
    </div>

    {{-- Create Quiz Button --}}
    <div class="p-3 bg-[#F9F9F9] border-b border-[#E5E5E5]">
        <a href="{{ route('lecturer.quiz.create') }}"
           class="flex items-center justify-center gap-2 bg-[#0A574F] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition hover:shadow-sm">
            <i data-lucide="plus-circle" style="width:14px;height:14px;"></i>
            Create New Quiz
        </a>
    </div>

    {{-- Sidebar Quiz List --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-2">
        @forelse($quizzes as $quiz)
            <div class="bg-white border border-[#E5E5E5] rounded-lg p-3 hover:border-[#0A574F] transition">
                <h3 class="text-sm font-bold text-[#000000] flex items-center gap-2">
                    <i data-lucide="clipboard-list" style="width:14px;height:14px;color:#0A574F;"></i>
                    {{ $quiz->title }}
                </h3>
                <div class="flex items-center gap-2 mt-1 flex-wrap text-[10px] text-[#666666]">
                    <span class="flex items-center gap-1">
                        <i data-lucide="users" style="width:10px;height:10px;"></i>
                        {{ $quiz->group->name ?? 'N/A' }}
                    </span>
                    <span>•</span>
                    <span class="flex items-center gap-1">
                        <i data-lucide="clock" style="width:10px;height:10px;"></i>
                        {{ $quiz->duration }} min
                    </span>
                    <span>•</span>
                    <span class="flex items-center gap-1">
                        <i data-lucide="file-check" style="width:10px;height:10px;"></i>
                        {{ $quiz->submissions->count() }} submissions
                    </span>
                </div>
                @if($quiz->hasEnded())
                    <span class="inline-block mt-1 text-[8px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-1.5 py-0.5 rounded-full">Ended</span>
                @elseif(!$quiz->hasStarted())
                    <span class="inline-block mt-1 text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-1.5 py-0.5 rounded-full">Upcoming</span>
                @else
                    <span class="inline-block mt-1 text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-1.5 py-0.5 rounded-full">Active</span>
                @endif
                <div class="flex items-center gap-2 mt-2">
                    <a href="{{ route('lecturer.quiz.edit', $quiz->id) }}"
                       class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider bg-[#0A574F] text-white px-2 py-1 rounded-lg hover:bg-[#08443e] transition">
                        Edit Questions
                    </a>
                    <a href="{{ route('lecturer.quiz.results', $quiz->id) }}"
                       class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider border border-[#E5E5E5] text-[#000000] px-2 py-1 rounded-lg hover:border-[#0A574F] hover:bg-[#F9F9F9] transition">
                        Results
                    </a>
                </div>
            </div>
        @empty
            <div class="p-8 text-center border border-dashed border-[#E5E5E5] rounded-lg bg-white">
                <i data-lucide="clipboard-list" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                <p class="text-sm text-[#666666]">No quizzes created yet.</p>
                <a href="{{ route('lecturer.quiz.create') }}" class="inline-block mt-3 text-xs font-bold text-[#0A574F] border border-[#0A574F] px-4 py-1.5 rounded-lg hover:bg-[#0A574F] hover:text-white transition">
                    Create Your First Quiz
                </a>
            </div>
        @endforelse
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-3">
                        <i data-lucide="clipboard-list" style="width:28px;height:28px;color:#0A574F;"></i>
                        Quiz Management
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="bar-chart-2" style="width:14px;height:14px;color:#0A574F;"></i>
                        Create and manage quizzes for your groups
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4]">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        {{ $quizzes->filter(fn($q) => $q->isActive())->count() }} active
                    </span>
                    <button class="bg-[#F9F9F9] border border-[#E5E5E5] px-3 py-1.5 text-xs rounded-lg hover:border-[#0A574F] hover:bg-white transition flex items-center gap-1">
                        <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        {{-- Quiz Cards --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($quizzes as $quiz)
                    <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm hover:shadow-md hover:border-[#0A574F] transition p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-bold text-[#000000] truncate flex items-center gap-2">
                                    <i data-lucide="clipboard-list" style="width:16px;height:16px;color:#0A574F;"></i>
                                    {{ $quiz->title }}
                                </h3>
                                <div class="flex items-center gap-2 mt-1 text-[10px] text-[#666666] flex-wrap">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="users" style="width:12px;height:12px;"></i>
                                        {{ $quiz->group->name ?? 'N/A' }}
                                    </span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="clock" style="width:12px;height:12px;"></i>
                                        {{ $quiz->duration }} min
                                    </span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="file-check" style="width:12px;height:12px;"></i>
                                        {{ $quiz->submissions->count() }} submissions
                                    </span>
                                </div>
                                @if($quiz->starts_at)
                                    <p class="text-[9px] text-[#666666] mt-1 flex items-center gap-1">
                                        <i data-lucide="calendar" style="width:10px;height:10px;"></i>
                                        Starts: {{ $quiz->starts_at->format('M d, Y h:i A') }}
                                    </p>
                                @endif
                            </div>
                            @if($quiz->hasEnded())
                                <span class="text-[8px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-2 py-0.5 rounded-full whitespace-nowrap ml-2">Ended</span>
                            @elseif(!$quiz->hasStarted())
                                <span class="text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-2 py-0.5 rounded-full whitespace-nowrap ml-2">Upcoming</span>
                            @else
                                <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-2 py-0.5 rounded-full whitespace-nowrap ml-2">Active</span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-[#E5E5E5]">
                            <a href="{{ route('lecturer.quiz.edit', $quiz->id) }}"
                               class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider bg-[#0A574F] text-white px-3 py-1.5 rounded-lg hover:bg-[#08443e] transition">
                                <i data-lucide="edit" style="width:12px;height:12px;display:inline;"></i> Edit
                            </a>
                            <a href="{{ route('lecturer.quiz.results', $quiz->id) }}"
                               class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider border border-[#E5E5E5] text-[#000000] px-3 py-1.5 rounded-lg hover:border-[#0A574F] hover:bg-[#F9F9F9] transition">
                                <i data-lucide="bar-chart-2" style="width:12px;height:12px;display:inline;"></i> Results
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-lg border border-dashed border-[#E5E5E5] p-12 text-center">
                        <i data-lucide="clipboard-list" style="width:48px;height:48px;color:#94A3B8;margin:0 auto 0.75rem;display:block;"></i>
                        <p class="text-sm font-medium text-[#000000]">No quizzes created yet</p>
                        <p class="text-xs text-[#666666] mt-1">Start by creating your first quiz to test your students.</p>
                        <a href="{{ route('lecturer.quiz.create') }}" class="inline-block mt-4 text-sm font-bold text-[#0A574F] border border-[#0A574F] px-6 py-2 rounded-lg hover:bg-[#0A574F] hover:text-white transition">
                            Create Your First Quiz
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Footer Status --}}
        <div class="border-t border-[#E5E5E5] bg-white px-8 py-3 flex items-center justify-between text-[11px] text-[#666666]">
            <div class="flex items-center gap-6">
                <span class="flex items-center gap-1">
                    <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                    System Status: <span class="text-[#000000] font-medium">Online</span>
                </span>
                <span class="flex items-center gap-1">
                    <i data-lucide="database" style="width:12px;height:12px;color:#2563EB;"></i>
                    Database: <span class="text-[#000000] font-medium">Connected</span>
                </span>
            </div>
            <div class="flex items-center gap-4">
                <a href="#" class="hover:text-[#0A574F] transition">Privacy Policy</a>
                <span class="text-[#D1D5DB]">·</span>
                <a href="#" class="hover:text-[#0A574F] transition">Terms of Service</a>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        lucide.createIcons();
    </script>
@endpush