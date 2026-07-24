@extends('layouts.workspace')

@section('title', 'Lecturer Dashboard')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Lecturer Portal</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-4 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#000000]">{{ $totalGroups }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Groups</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#000000]">{{ $totalStudents }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Students</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#000000]">{{ $totalTopics }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Topics</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#000000]">{{ $totalQuizzes }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Quizzes</p>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="p-3 bg-[#F9F9F9] border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-2">
            <a href="{{ route('lecturer.quiz.create') }}"
               class="flex items-center justify-center gap-2 bg-[#0A574F] text-white px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition">
                <i data-lucide="plus-circle" style="width:14px;height:14px;"></i>
                Create Quiz
            </a>
            <a href="{{ route('lecturer.quizzes') }}"
               class="flex items-center justify-center gap-2 bg-white border border-[#2563EB] text-[#2563EB] px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#E0F2FE] transition">
                <i data-lucide="clipboard-list" style="width:14px;height:14px;"></i>
                View All Quizzes
            </a>
            <a href="{{ route('lecturer.grading') }}"
               class="flex items-center justify-center gap-2 bg-white border border-[#D97706] text-[#D97706] px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#FEF3C7] transition">
                <i data-lucide="bar-chart-3" style="width:14px;height:14px;"></i>
                Grading Matrix
            </a>
            <a href="{{ route('lecturer.students.export') }}"
               class="flex items-center justify-center gap-2 bg-white border border-[#16A34A] text-[#16A34A] px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#F0FDF4] transition">
                <i data-lucide="download" style="width:14px;height:14px;"></i>
                Export Students
            </a>
        </div>
    </div>

    {{-- Your Groups --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1 flex items-center gap-1">
            <i data-lucide="folder" style="width:12px;height:12px;"></i>
            Your Groups
        </p>
        @foreach($groups as $group)
            <a href="{{ route('groups.topics', $group->id) }}"
               class="block px-3 py-2.5 bg-white hover:bg-[#F9F9F9] transition-colors border border-[#E5E5E5] rounded-lg hover:border-[#0A574F]">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-[#000000]">{{ $group->name }}</span>
                    <span class="text-[10px] text-[#2563EB] border border-[#2563EB] px-2 py-0.5 rounded-full">{{ $group->topics_count ?? 0 }} topics</span>
                </div>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-[9px] text-[#666666] flex items-center gap-1">
                        <i data-lucide="users" style="width:10px;height:10px;"></i>
                        {{ $group->users_count ?? 0 }} students
                    </span>
                    <span class="text-[9px] text-[#666666]">•</span>
                    <span class="text-[9px] text-[#0A574F] border border-[#0A574F] px-1.5 py-0.5 rounded-full">Admin</span>
                </div>
            </a>
        @endforeach
        <a href="{{ route('lecturer.groups') }}"
           class="block px-3 py-2 text-center text-xs font-bold uppercase tracking-wider text-[#666666] hover:text-[#0A574F] transition-colors border border-[#E5E5E5] border-dashed rounded-lg hover:border-[#0A574F]">
            View All My Groups →
        </a>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-3">
                        <i data-lucide="graduation-cap" style="width:28px;height:28px;color:#0A574F;"></i>
                        Lecturer Dashboard
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="bar-chart-2" style="width:14px;height:14px;color:#0A574F;"></i>
                        Overview of your groups and student activity
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4]">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        {{ $activeStudents }} active students
                    </span>
                    <button class="bg-[#F9F9F9] border border-[#E5E5E5] px-3 py-1.5 text-xs rounded-lg hover:border-[#0A574F] hover:bg-white transition flex items-center gap-1">
                        <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#000000]">{{ $totalStudents }}</p>
                        <p class="text-xs text-[#666666] font-medium mt-1">Total Students</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-[10px] bg-[#F0FDF4] text-[#16A34A] px-2 py-0.5 rounded-full">{{ $activeStudents }} active</span>
                        </div>
                    </div>
                    <div class="w-11 h-11 bg-[#F0FDF4] rounded-lg flex items-center justify-center">
                        <i data-lucide="users" style="width:22px;height:22px;color:#16A34A;"></i>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-[#E5E5E5]">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1">
                        <i data-lucide="trending-up" style="width:12px;height:12px;"></i>
                        {{ $activeStudents }} active this week
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-[#E5E5E5] p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#000000]">{{ $totalTopics }}</p>
                        <p class="text-xs text-[#666666] font-medium mt-1">Topics</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-[10px] text-[#666666] flex items-center gap-1">
                                <i data-lucide="message-square" style="width:10px;height:10px;"></i>
                                {{ $totalPosts }} posts
                            </span>
                        </div>
                    </div>
                    <div class="w-11 h-11 bg-[#E0F2FE] rounded-lg flex items-center justify-center">
                        <i data-lucide="message-circle" style="width:22px;height:22px;color:#2563EB;"></i>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-[#E5E5E5]">
                    <span class="text-xs text-[#2563EB] flex items-center gap-1">
                        <i data-lucide="file-text" style="width:12px;height:12px;"></i>
                        {{ $totalPosts }} posts total
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-[#E5E5E5] p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#000000]">{{ $totalQuizzes }}</p>
                        <p class="text-xs text-[#666666] font-medium mt-1">Quizzes</p>
                        <p class="text-[10px] text-[#666666] mt-2">
                            <i data-lucide="file-check" style="width:10px;height:10px;display:inline;"></i>
                            {{ $totalSubmissions }} submissions
                        </p>
                    </div>
                    <div class="w-11 h-11 bg-[#FEF3C7] rounded-lg flex items-center justify-center">
                        <i data-lucide="clipboard-list" style="width:22px;height:22px;color:#D97706;"></i>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-[#E5E5E5]">
                    <span class="text-xs text-[#D97706] flex items-center gap-1">
                        <i data-lucide="clock" style="width:12px;height:12px;"></i>
                        {{ $totalSubmissions }} submissions
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-[#E5E5E5] p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#0A574F]">{{ number_format($avgScore, 0) }}%</p>
                        <p class="text-xs text-[#666666] font-medium mt-1">Avg Score</p>
                        <p class="text-[10px] text-[#666666] mt-2">
                            <i data-lucide="bar-chart-2" style="width:10px;height:10px;display:inline;"></i>
                            Across your quizzes
                        </p>
                    </div>
                    <div class="w-11 h-11 bg-[#ECFDF5] rounded-lg flex items-center justify-center">
                        <i data-lucide="trending-up" style="width:22px;height:22px;color:#0A574F;"></i>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-[#E5E5E5]">
                    <span class="text-xs text-[#0A574F] flex items-center gap-1">
                        <i data-lucide="arrow-up" style="width:12px;height:12px;"></i>
                        Overall performance
                    </span>
                </div>
            </div>
        </div>

        {{-- Charts & Top Students --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 px-6 pb-6 flex-1 overflow-y-auto">

            {{-- Topics Per Group --}}
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                <div class="border-b border-[#E5E5E5] px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="pie-chart" style="width:18px;height:18px;color:#0A574F;"></i>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Topics Per Group</h3>
                    </div>
                    <span class="text-xs bg-[#ECFDF5] text-[#0A574F] px-2 py-1 rounded-full">{{ count($topicsPerGroup) }} groups</span>
                </div>
                <div class="p-5">
                    @if(count($topicsPerGroup) > 0)
                        <div class="space-y-3">
                            @foreach($topicsPerGroup as $name => $count)
                                <div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-[#000000] font-medium truncate">{{ $name }}</span>
                                        <span class="text-[#2563EB] font-bold flex-shrink-0 ml-2">{{ $count }}</span>
                                    </div>
                                    <div class="w-full h-2 bg-[#E5E5E5] rounded-full mt-0.5 overflow-hidden">
                                        <div class="h-full bg-[#0A574F] rounded-full" style="width: {{ ($count / max(1, max($topicsPerGroup))) * 100 }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <i data-lucide="inbox" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                            <p class="text-sm text-[#666666]">No topics created yet</p>
                            <p class="text-xs text-[#94A3B8]">Start by adding topics to your groups</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Top Students --}}
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                <div class="border-b border-[#E5E5E5] px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="award" style="width:18px;height:18px;color:#D97706;"></i>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Top Active Students</h3>
                    </div>
                    <span class="text-xs bg-[#FEF3C7] text-[#D97706] px-2 py-1 rounded-full">{{ $topStudents->count() }} active</span>
                </div>
                <div class="divide-y divide-[#F5F5F5] max-h-[280px] overflow-y-auto">
                    @forelse($topStudents->take(5) as $student)
                        <div class="px-5 py-3 flex items-center justify-between hover:bg-[#F9F9F9] transition">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-[#ECFDF5] rounded-lg flex items-center justify-center">
                                    <i data-lucide="user" style="width:14px;height:14px;color:#0A574F;"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-[#000000]">{{ $student->name }}</p>
                                    <p class="text-xs text-[#666666]">{{ $student->email ?? '' }}</p>
                                </div>
                            </div>
                            <span class="text-[10px] text-[#16A34A] font-bold">{{ $student->posts_count }} posts</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <i data-lucide="users" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                            <p class="text-sm text-[#666666]">No student activity yet</p>
                            <p class="text-xs text-[#94A3B8]">Students will appear here as they participate</p>
                        </div>
                    @endforelse
                </div>
                <div class="border-t border-[#E5E5E5] px-5 py-3">
                    <a href="{{ route('lecturer.groups') }}" class="text-xs text-[#0A574F] hover:text-[#08443e] font-medium flex items-center gap-1">
                        View all groups <i data-lucide="arrow-right" style="width:12px;height:12px;"></i>
                    </a>
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