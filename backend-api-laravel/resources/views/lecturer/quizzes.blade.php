@extends('layouts.workspace')

@section('title', 'Quiz Management')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.dashboard') }}" class="mr-3 text-[#666666] hover:text-[#0A66C2] transition-colors">
            <i data-lucide="arrow-left" class="size-5"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Quizzes</h2>
    </div>

    <div class="p-3 bg-white border-b border-[#E5E5E5]">
        <a href="{{ route('lecturer.quiz.create') }}"
           class="flex items-center justify-center gap-2 w-full bg-[#0A66C2] text-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-[#094D8F] transition shadow-sm">
            <i data-lucide="plus-circle" class="size-4"></i> Create New Quiz
        </a>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-2">
        @forelse($quizzes as $quiz)
            <div class="bg-white rounded-xl border border-[#E5E5E5] p-3 shadow-sm hover:shadow-md transition">
                <h3 class="text-sm font-bold text-[#000000]">{{ $quiz->title }}</h3>
                <div class="flex items-center gap-2 mt-1 flex-wrap text-[10px] text-[#666666]">
                    <span class="flex items-center gap-1">
                        <i data-lucide="users" class="size-3"></i> {{ $quiz->group->name ?? 'N/A' }}
                    </span>
                    <span>•</span>
                    <span class="flex items-center gap-1">
                        <i data-lucide="clock" class="size-3"></i> {{ $quiz->duration }} min
                    </span>
                    <span>•</span>
                    <span class="flex items-center gap-1">
                        <i data-lucide="file-text" class="size-3"></i> {{ $quiz->submissions->count() }} submissions
                    </span>
                </div>
                @if($quiz->hasEnded())
                    <span class="inline-block mt-2 text-[10px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-2 py-0.5 rounded-full">Ended</span>
                @elseif(!$quiz->hasStarted())
                    <span class="inline-block mt-2 text-[10px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-2 py-0.5 rounded-full">Upcoming</span>
                @else
                    <span class="inline-block mt-2 text-[10px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-2 py-0.5 rounded-full">Active</span>
                @endif
                <div class="flex items-center gap-2 mt-3">
                    <a href="{{ route('lecturer.quiz.edit', $quiz->id) }}"
                       class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider border border-[#E5E5E5] rounded-lg px-3 py-1.5 hover:bg-[#F0F4FF] hover:border-[#0A66C2] transition flex items-center justify-center gap-1">
                        <i data-lucide="edit" class="size-3"></i> Edit
                    </a>
                    <a href="{{ route('lecturer.quiz.results', $quiz->id) }}"
                       class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider border border-[#E5E5E5] rounded-lg px-3 py-1.5 hover:bg-[#F0F4FF] hover:border-[#0A66C2] transition flex items-center justify-center gap-1">
                        <i data-lucide="bar-chart" class="size-3"></i> Results
                    </a>
                </div>
            </div>
        @empty
            <div class="p-8 text-center bg-white rounded-xl border border-[#E5E5E5]">
                <i data-lucide="file-question" class="size-10 text-[#999999] mx-auto mb-3"></i>
                <p class="text-sm text-[#666666]">No quizzes created yet.</p>
                <a href="{{ route('lecturer.quiz.create') }}" class="inline-block mt-3 text-sm font-semibold text-[#0A66C2] hover:text-[#094D8F] transition">
                    + Create Your First Quiz
                </a>
            </div>
        @endforelse
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9FAFB]">
        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-2">
                        <i data-lucide="file-question" class="size-6 text-[#0A66C2]"></i>
                        Quiz Management
                    </h1>
                    <p class="text-sm text-[#666666] mt-1">Create and manage quizzes for your groups</p>
                </div>
                <a href="{{ route('lecturer.quiz.create') }}"
                   class="flex items-center gap-2 bg-[#0A66C2] text-white px-4 py-2 text-sm font-bold uppercase tracking-wider rounded-xl hover:bg-[#094D8F] transition shadow-sm">
                    <i data-lucide="plus" class="size-4"></i> New Quiz
                </a>
            </div>
        </div>

        {{-- Quiz Grid --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($quizzes as $quiz)
                    <div class="bg-white rounded-2xl border border-[#E5E5E5] shadow-sm hover:shadow-lg transition p-5 flex flex-col">
                        <div class="flex items-start justify-between">
                            <h3 class="text-base font-bold text-[#000000] truncate">{{ $quiz->title }}</h3>
                            @if($quiz->hasEnded())
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-2 py-0.5 rounded-full flex-shrink-0 ml-2">Ended</span>
                            @elseif(!$quiz->hasStarted())
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-2 py-0.5 rounded-full flex-shrink-0 ml-2">Upcoming</span>
                            @else
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-2 py-0.5 rounded-full flex-shrink-0 ml-2">Active</span>
                            @endif
                        </div>

                        <div class="mt-2 space-y-1 text-[10px] text-[#666666]">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="users" class="size-3.5"></i>
                                <span>{{ $quiz->group->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="clock" class="size-3.5"></i>
                                <span>{{ $quiz->duration }} minutes</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="file-text" class="size-3.5"></i>
                                <span>{{ $quiz->submissions->count() }} submissions</span>
                            </div>
                            @if($quiz->starts_at)
                                <div class="flex items-center gap-1.5 text-[10px] text-[#666666]">
                                    <i data-lucide="calendar" class="size-3"></i>
                                    <span>Starts: {{ $quiz->starts_at->format('M d, Y h:i A') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 pt-4 border-t border-[#E5E5E5] flex items-center gap-2">
                            <a href="{{ route('lecturer.quiz.edit', $quiz->id) }}"
                               class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider border border-[#E5E5E5] rounded-xl px-3 py-2 hover:bg-[#F0F4FF] hover:border-[#0A66C2] transition flex items-center justify-center gap-1">
                                <i data-lucide="edit" class="size-3.5"></i> Edit
                            </a>
                            <a href="{{ route('lecturer.quiz.results', $quiz->id) }}"
                               class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider border border-[#E5E5E5] rounded-xl px-3 py-2 hover:bg-[#F0F4FF] hover:border-[#0A66C2] transition flex items-center justify-center gap-1">
                                <i data-lucide="bar-chart" class="size-3.5"></i> Results
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-2xl border border-[#E5E5E5] p-12 text-center">
                        <i data-lucide="file-question" class="size-12 text-[#999999] mx-auto mb-4"></i>
                        <h3 class="text-lg font-semibold text-[#000000]">No Quizzes Yet</h3>
                        <p class="text-sm text-[#666666] mt-1">Get started by creating your first quiz.</p>
                        <a href="{{ route('lecturer.quiz.create') }}" class="inline-block mt-4 bg-[#0A66C2] text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-[#094D8F] transition shadow-sm">
                            <i data-lucide="plus-circle" class="size-4 inline mr-2"></i> Create Quiz
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endpush