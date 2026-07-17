@extends('layouts.workspace')

@section('title', 'Lecturer Dashboard')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Lecturer Portal</h2>
    </div>

    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-3 gap-2 text-center">
            <div>
                <p class="text-xl font-bold text-[#000000]">{{ $totalGroups }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Groups</p>
            </div>
            <div>
                <p class="text-xl font-bold text-[#000000]">{{ $totalStudents }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Students</p>
            </div>
            <div>
                <p class="text-xl font-bold text-[#000000]">{{ $totalTopics }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Topics</p>
            </div>
        </div>
    </div>

    <div class="p-3 bg-[#FAFAFA] space-y-2">
        <a href="{{ route('lecturer.quiz.create') }}"
           class="block w-full text-center bg-[#000000] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
            + Create Quiz
        </a>
        <a href="{{ route('lecturer.quizzes') }}"
           class="block w-full text-center bg-white border border-[#000000] px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#F5F5F5] transition-colors">
            View All Quizzes
        </a>
        <a href="{{ route('lecturer.grading') }}"
           class="block w-full text-center bg-white border border-[#000000] px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#F5F5F5] transition-colors">
            Grading Matrix
        </a>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1">Your Groups</p>
        @foreach($groups as $group)
            <a href="{{ route('lecturer.group.analytics', $group->id) }}"
               class="block px-3 py-2 bg-white hover:bg-[#F5F5F5] transition-colors border border-[#E5E5E5]">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-[#000000]">{{ $group->name }}</span>
                    <span class="text-[10px] text-[#666666]">{{ $group->topics_count ?? 0 }} topics</span>
                </div>
                <span class="text-[9px] text-[#666666]">{{ $group->users_count ?? 0 }} students</span>
            </a>
        @endforeach
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-[#000000]">Lecturer Dashboard</h1>
                    <p class="text-sm text-[#666666] mt-1">Overview of all groups and student activity</p>
                </div>
                <span class="text-xs text-[#16A34A] border border-[#16A34A] px-2 py-1">● {{ $activeStudents }} active students</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
            <div class="bg-white border border-[#E5E5E5] p-4">
                <p class="text-2xl font-bold text-[#000000]">{{ $totalStudents }}</p>
                <p class="text-xs text-[#666666] uppercase tracking-wider">Total Students</p>
                <p class="text-[10px] text-[#16A34A] mt-1">{{ $activeStudents }} active this week</p>
            </div>
            <div class="bg-white border border-[#E5E5E5] p-4">
                <p class="text-2xl font-bold text-[#000000]">{{ $totalTopics }}</p>
                <p class="text-xs text-[#666666] uppercase tracking-wider">Topics</p>
                <p class="text-[10px] text-[#666666] mt-1">{{ $totalPosts }} posts total</p>
            </div>
            <div class="bg-white border border-[#E5E5E5] p-4">
                <p class="text-2xl font-bold text-[#000000]">{{ $totalQuizzes }}</p>
                <p class="text-xs text-[#666666] uppercase tracking-wider">Quizzes</p>
                <p class="text-[10px] text-[#666666] mt-1">{{ $totalSubmissions }} submissions</p>
            </div>
            <div class="bg-white border border-[#E5E5E5] p-4">
                <p class="text-2xl font-bold text-[#000000]">{{ number_format($avgScore, 0) }}%</p>
                <p class="text-xs text-[#666666] uppercase tracking-wider">Avg Score</p>
                <p class="text-[10px] text-[#666666] mt-1">Across all quizzes</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 px-6 pb-6 flex-1 overflow-y-auto">
            <div class="bg-white border border-[#E5E5E5] p-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Topics Per Group</h3>
                @if(count($topicsPerGroup) > 0)
                    <div class="space-y-2">
                        @foreach($topicsPerGroup as $name => $count)
                            <div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-[#000000] truncate">{{ $name }}</span>
                                    <span class="text-[#666666] flex-shrink-0 ml-2">{{ $count }}</span>
                                </div>
                                <div class="w-full h-2 bg-[#E5E5E5] mt-0.5">
                                    <div class="h-full bg-[#000000]" style="width: {{ ($count / max(1, max($topicsPerGroup))) * 100 }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-[#666666]">No topics yet.</p>
                @endif
            </div>

            <div class="bg-white border border-[#E5E5E5] p-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Top Students</h3>
                @if($topStudents->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($topStudents->take(5) as $student)
                            <div class="flex justify-between items-center border-b border-[#E5E5E5] pb-1">
                                <span class="text-sm text-[#000000] truncate">{{ $student->name }}</span>
                                <span class="text-[10px] text-[#666666] flex-shrink-0 ml-2">{{ $student->posts_count }} posts</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-[#666666]">No student activity yet.</p>
                @endif
            </div>
        </div>
    </div>
@endsection