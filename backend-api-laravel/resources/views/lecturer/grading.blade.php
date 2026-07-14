@extends('layouts.workspace')

@section('title', 'Grading Matrix')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Grading</h2>
    </div>
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <p class="text-xs text-[#666666]">Participation marks based on activity</p>
        <div class="grid grid-cols-2 gap-2 mt-3 text-center">
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $students->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Students</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ number_format($students->avg('participation_score') ?? 0, 0) }}%</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Average Score</p>
            </div>
        </div>
    </div>
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1">Scoring: Topics ×5 + Posts ×2</p>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">Grading Matrix</h1>
            <p class="text-sm text-[#666666] mt-1">Student participation scores</p>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="bg-white border border-[#E5E5E5]">
                <div class="border-b border-[#E5E5E5] px-4 py-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666]">Student Participation</h3>
                    <span class="text-[10px] text-[#666666]">Topics ×5 + Posts ×2</span>
                </div>
                <div class="divide-y divide-[#E5E5E5]">
                    @forelse($students as $student)
                        <div class="px-4 py-3 flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <span class="text-sm font-bold text-[#000000]">{{ $student->name }}</span>
                                <div class="flex items-center space-x-3 mt-0.5">
                                    <span class="text-[10px] text-[#666666]">{{ $student->topics_count }} topics</span>
                                    <span class="text-[10px] text-[#666666]">•</span>
                                    <span class="text-[10px] text-[#666666]">{{ $student->posts_count }} posts</span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4 flex-shrink-0 ml-4">
                                <div class="w-24 h-2 bg-[#E5E5E5]">
                                    <div class="h-full bg-[#000000]" style="width: {{ $student->participation_score }}%;"></div>
                                </div>
                                <span class="text-sm font-bold text-[#000000] w-8 text-right">{{ $student->participation_score }}%</span>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center">
                            <p class="text-sm text-[#666666]">No students found.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection