@extends('layouts.workspace')

@section('title', 'Available Quizzes')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Quizzes</h2>
    </div>
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <p class="text-xs text-[#666666]">Available quizzes for your groups</p>
    </div>
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-2">
        @forelse($quizzes as $quiz)
            <div class="bg-white border border-[#E5E5E5] p-3">
                <h3 class="text-sm font-bold text-[#000000]">{{ $quiz->title }}</h3>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="text-[10px] text-[#666666]">{{ $quiz->group->name ?? 'N/A' }}</span>
                    <span class="text-[10px] text-[#666666]">•</span>
                    <span class="text-[10px] text-[#666666]">{{ $quiz->duration }} min</span>
                </div>
                @if($quiz->has_taken)
                    <span class="inline-block mt-1 text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-1.5 py-0.5">Completed ✅</span>
                @else
                    <a href="{{ route('student.quiz.take', $quiz->id) }}"
                       class="block text-center mt-2 text-[10px] font-bold uppercase tracking-wider bg-[#000000] text-white px-3 py-1 hover:bg-[#333333] transition-colors">
                        Start Quiz
                    </a>
                @endif
            </div>
        @empty
            <div class="p-8 text-center">
                <p class="text-sm text-[#666666]">No quizzes available at the moment.</p>
            </div>
        @endforelse
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">Available Quizzes</h1>
            <p class="text-sm text-[#666666] mt-1">Complete quizzes before they expire</p>
        </div>
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($quizzes as $quiz)
                    <div class="bg-white border border-[#E5E5E5] p-4">
                        <div class="flex items-start justify-between">
                            <h3 class="text-sm font-bold text-[#000000]">{{ $quiz->title }}</h3>
                            @if($quiz->has_taken)
                                <span class="text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-1.5 py-0.5 flex-shrink-0 ml-2">Done</span>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2 mt-1 flex-wrap">
                            <span class="text-[10px] text-[#666666]">{{ $quiz->group->name ?? 'N/A' }}</span>
                            <span class="text-[10px] text-[#666666]">•</span>
                            <span class="text-[10px] text-[#666666]">{{ $quiz->duration }} minutes</span>
                        </div>
                        @if($quiz->ends_at)
                            <p class="text-[9px] text-[#D97706] mt-1">
                                ⏱️ Ends: {{ $quiz->ends_at->format('M d, Y h:i A') }}
                            </p>
                        @endif
                        @if(!$quiz->has_taken)
                            <a href="{{ route('student.quiz.take', $quiz->id) }}"
                               class="block text-center mt-3 text-xs font-bold uppercase tracking-wider bg-[#000000] text-white px-3 py-2 hover:bg-[#333333] transition-colors">
                                Start Quiz
                            </a>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full bg-white border border-[#E5E5E5] p-12 text-center">
                        <p class="text-sm text-[#666666]">No quizzes available right now.</p>
                        <p class="text-xs text-[#666666] mt-1">Check back later or ask your lecturer.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection