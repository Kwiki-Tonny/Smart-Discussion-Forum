@extends('layouts.workspace')

@section('title', 'Quiz Management')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Quizzes</h2>
    </div>
    <div class="p-3 bg-[#FAFAFA]">
        <a href="{{ route('lecturer.quiz.create') }}"
           class="block w-full text-center bg-[#000000] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
            + Create New Quiz
        </a>
    </div>
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-2">
        @forelse($quizzes as $quiz)
            <div class="bg-white border border-[#E5E5E5] p-3">
                <h3 class="text-sm font-bold text-[#000000]">{{ $quiz->title }}</h3>
                <div class="flex items-center space-x-2 mt-1 flex-wrap">
                    <span class="text-[10px] text-[#666666]">{{ $quiz->group->name ?? 'N/A' }}</span>
                    <span class="text-[10px] text-[#666666]">•</span>
                    <span class="text-[10px] text-[#666666]">{{ $quiz->duration }} min</span>
                    <span class="text-[10px] text-[#666666]">•</span>
                    <span class="text-[10px] text-[#666666]">{{ $quiz->submissions->count() }} submissions</span>
                </div>
                @if($quiz->hasEnded())
                    <span class="inline-block text-[8px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-1.5 py-0.5 mt-1">Ended</span>
                @elseif(!$quiz->hasStarted())
                    <span class="inline-block text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-1.5 py-0.5 mt-1">Upcoming</span>
                @else
                    <span class="inline-block text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-1.5 py-0.5 mt-1">Active</span>
                @endif
                <div class="flex items-center space-x-2 mt-2">
                    <a href="{{ route('lecturer.quiz.edit', $quiz->id) }}"
                       class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider border border-[#000000] px-2 py-1 hover:bg-[#000000] hover:text-white transition-colors">
                        Edit Questions
                    </a>
                    <a href="{{ route('lecturer.quiz.results', $quiz->id) }}"
                       class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider border border-[#000000] px-2 py-1 hover:bg-[#000000] hover:text-white transition-colors">
                        Results
                    </a>
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <p class="text-sm text-[#666666]">No quizzes created yet.</p>
            </div>
        @endforelse
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">Quiz Management</h1>
            <p class="text-sm text-[#666666] mt-1">Create and manage quizzes for your groups</p>
        </div>
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($quizzes as $quiz)
                    <div class="bg-white border border-[#E5E5E5] p-4">
                        <h3 class="text-sm font-bold text-[#000000]">{{ $quiz->title }}</h3>
                        <div class="flex items-center space-x-2 mt-1 flex-wrap">
                            <span class="text-[10px] text-[#666666]">{{ $quiz->group->name ?? 'N/A' }}</span>
                            <span class="text-[10px] text-[#666666]">•</span>
                            <span class="text-[10px] text-[#666666]">{{ $quiz->duration }} min</span>
                            <span class="text-[10px] text-[#666666]">•</span>
                            <span class="text-[10px] text-[#666666]">{{ $quiz->submissions->count() }} submissions</span>
                        </div>
                        @if($quiz->starts_at)
                            <p class="text-[9px] text-[#666666] mt-1">
                                Starts: {{ $quiz->starts_at->format('M d, Y h:i A') }}
                            </p>
                        @endif
                        @if($quiz->hasEnded())
                            <span class="inline-block mt-1 text-[8px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-1.5 py-0.5">Ended</span>
                        @elseif(!$quiz->hasStarted())
                            <span class="inline-block mt-1 text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-1.5 py-0.5">Upcoming</span>
                        @else
                            <span class="inline-block mt-1 text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-1.5 py-0.5">Active</span>
                        @endif
                        <div class="flex items-center space-x-2 mt-3">
                            <a href="{{ route('lecturer.quiz.edit', $quiz->id) }}"
                               class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider border border-[#000000] px-2 py-1 hover:bg-[#000000] hover:text-white transition-colors">
                                Edit
                            </a>
                            <a href="{{ route('lecturer.quiz.results', $quiz->id) }}"
                               class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider border border-[#000000] px-2 py-1 hover:bg-[#000000] hover:text-white transition-colors">
                                Results
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white border border-[#E5E5E5] p-12 text-center">
                        <p class="text-sm text-[#666666]">No quizzes created yet.</p>
                        <a href="{{ route('lecturer.quiz.create') }}" class="inline-block mt-4 text-sm font-bold text-[#000000] border border-[#000000] px-4 py-2 hover:bg-[#F5F5F5] transition-colors">
                            Create Your First Quiz
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection