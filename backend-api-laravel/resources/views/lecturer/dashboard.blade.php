@extends('layouts.workspace')

@section('title', 'Lecturer Dashboard')

@section('context_panel')
    {{-- Sidebar header with back arrow --}}
    <div class="p-4 border-b border-slate-200 flex items-center bg-white sticky top-0">
        <a href="{{ route('dashboard') }}" class="mr-3 text-slate-600 hover:text-blue-600 transition-colors">
            <i data-lucide="arrow-left" class="size-5"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Lecturer Portal</h2>
    </div>

    {{-- Quick stats --}}
    <div class="p-4 bg-white border-b border-slate-200">
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-slate-50 rounded-lg p-2 text-center">
                <p class="text-xl font-bold text-slate-800">{{ $totalGroups }}</p>
                <p class="text-[10px] text-slate-500 uppercase tracking-wider flex items-center justify-center gap-1">
                    <i data-lucide="users" class="size-3"></i> Groups
                </p>
            </div>
            <div class="bg-slate-50 rounded-lg p-2 text-center">
                <p class="text-xl font-bold text-slate-800">{{ $totalStudents }}</p>
                <p class="text-[10px] text-slate-500 uppercase tracking-wider flex items-center justify-center gap-1">
                    <i data-lucide="graduation-cap" class="size-3"></i> Students
                </p>
            </div>
            <div class="bg-slate-50 rounded-lg p-2 text-center">
                <p class="text-xl font-bold text-slate-800">{{ $totalTopics }}</p>
                <p class="text-[10px] text-slate-500 uppercase tracking-wider flex items-center justify-center gap-1">
                    <i data-lucide="message-square" class="size-3"></i> Topics
                </p>
            </div>
        </div>
    </div>

    {{-- Action buttons --}}
    <div class="p-3 bg-slate-50 space-y-2">
        <a href="{{ route('lecturer.quiz.create') }}"
           class="flex items-center justify-center gap-2 w-full bg-blue-600 text-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-blue-700 transition shadow-sm">
            <i data-lucide="plus-circle" class="size-4"></i> Create Quiz
        </a>
        <a href="{{ route('lecturer.quizzes') }}"
           class="flex items-center justify-center gap-2 w-full bg-white border border-slate-300 px-4 py-2.5 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-50 transition">
            <i data-lucide="list-checks" class="size-4"></i> View All Quizzes
        </a>
        <a href="{{ route('lecturer.grading') }}"
           class="flex items-center justify-center gap-2 w-full bg-white border border-slate-300 px-4 py-2.5 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-50 transition">
            <i data-lucide="clipboard-check" class="size-4"></i> Grading Matrix
        </a>
    </div>

    {{-- Your Groups --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-2 py-1 flex items-center gap-1">
            <i data-lucide="folder-open" class="size-3"></i> Your Groups
        </p>
        @foreach($groups as $group)
            <a href="{{ route('lecturer.group.analytics', $group->id) }}"
               class="block px-4 py-3 bg-white hover:bg-slate-50 transition-colors rounded-lg border border-slate-200 shadow-sm">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-slate-800">{{ $group->name }}</span>
                    <span class="text-[10px] text-slate-500 flex items-center gap-1">
                        <i data-lucide="message-circle" class="size-3"></i> {{ $group->topics_count ?? 0 }}
                    </span>
                </div>
                <span class="text-[10px] text-slate-500 flex items-center gap-1">
                    <i data-lucide="user" class="size-3"></i> {{ $group->users_count ?? 0 }} students
                </span>
            </a>
        @endforeach
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-slate-50">
        {{-- Header --}}
        <div class="bg-white border-b border-slate-200 px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="layout-dashboard" class="size-6 text-blue-600"></i>
                        Lecturer Dashboard
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">Overview of all groups and student activity</p>
                </div>
                <span class="flex items-center gap-2 text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-full">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    {{ $activeStudents }} active students
                </span>
            </div>
        </div>

        {{-- Stats cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-start gap-4">
                <div class="p-3 bg-blue-50 rounded-lg">
                    <i data-lucide="graduation-cap" class="size-6 text-blue-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalStudents }}</p>
                    <p class="text-xs text-slate-500 uppercase tracking-wider">Total Students</p>
                    <p class="text-[10px] text-emerald-600 mt-1 flex items-center gap-1">
                        <i data-lucide="trending-up" class="size-3"></i> {{ $activeStudents }} active this week
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-start gap-4">
                <div class="p-3 bg-purple-50 rounded-lg">
                    <i data-lucide="message-square" class="size-6 text-purple-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalTopics }}</p>
                    <p class="text-xs text-slate-500 uppercase tracking-wider">Topics</p>
                    <p class="text-[10px] text-slate-500 mt-1 flex items-center gap-1">
                        <i data-lucide="file-text" class="size-3"></i> {{ $totalPosts }} posts total
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-start gap-4">
                <div class="p-3 bg-orange-50 rounded-lg">
                    <i data-lucide="file-question" class="size-6 text-orange-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalQuizzes }}</p>
                    <p class="text-xs text-slate-500 uppercase tracking-wider">Quizzes</p>
                    <p class="text-[10px] text-slate-500 mt-1 flex items-center gap-1">
                        <i data-lucide="send" class="size-3"></i> {{ $totalSubmissions }} submissions
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-start gap-4">
                <div class="p-3 bg-emerald-50 rounded-lg">
                    <i data-lucide="percent" class="size-6 text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ number_format($avgScore, 0) }}%</p>
                    <p class="text-xs text-slate-500 uppercase tracking-wider">Avg Score</p>
                    <p class="text-[10px] text-slate-500 mt-1 flex items-center gap-1">
                        <i data-lucide="bar-chart" class="size-3"></i> Across all quizzes
                    </p>
                </div>
            </div>
        </div>

        {{-- Charts & lists --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 px-6 pb-6 flex-1 overflow-y-auto">
            {{-- Topics per Group --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4 flex items-center gap-2">
                    <i data-lucide="bar-chart-2" class="size-4"></i> Topics Per Group
                </h3>
                @if(count($topicsPerGroup) > 0)
                    <div class="space-y-3">
                        @foreach($topicsPerGroup as $name => $count)
                            <div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-slate-700 truncate">{{ $name }}</span>
                                    <span class="text-slate-500 flex-shrink-0 ml-2 font-mono">{{ $count }}</span>
                                </div>
                                <div class="w-full h-2.5 bg-slate-100 rounded-full mt-1 overflow-hidden">
                                    <div class="h-full bg-blue-600 rounded-full" style="width: {{ ($count / max(1, max($topicsPerGroup))) * 100 }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500">No topics yet.</p>
                @endif
            </div>

            {{-- Top Students --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4 flex items-center gap-2">
                    <i data-lucide="award" class="size-4"></i> Top Students
                </h3>
                @if($topStudents->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($topStudents->take(5) as $student)
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center text-xs font-bold text-slate-700">
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    </div>
                                    <span class="text-sm font-medium text-slate-800 truncate">{{ $student->name }}</span>
                                </div>
                                <span class="text-xs text-slate-500 flex items-center gap-1">
                                    <i data-lucide="message-circle" class="size-3"></i> {{ $student->posts_count }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500">No student activity yet.</p>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Initialize Lucide icons --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
@endpush