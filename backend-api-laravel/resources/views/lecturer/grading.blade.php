@extends('layouts.workspace')

@section('title', 'Grading Matrix')

@section('context_panel')
    <div class="p-4 border-b border-slate-200 flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.dashboard') }}" class="mr-3 text-slate-600 hover:text-blue-600 transition-colors">
            <i data-lucide="arrow-left" class="size-5"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Grading</h2>
    </div>

    <div class="p-4 bg-white border-b border-slate-200">
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-slate-50 rounded-lg p-3 text-center">
                <p class="text-xl font-bold text-slate-800 flex items-center justify-center gap-1">
                    <i data-lucide="users" class="size-4 text-blue-600"></i> {{ $students->count() }}
                </p>
                <p class="text-[10px] text-slate-500 uppercase tracking-wider">Students</p>
            </div>
            <div class="bg-slate-50 rounded-lg p-3 text-center">
                <p class="text-xl font-bold text-slate-800 flex items-center justify-center gap-1">
                    <i data-lucide="percent" class="size-4 text-emerald-600"></i> {{ number_format($students->avg('participation_score') ?? 0, 0) }}%
                </p>
                <p class="text-[10px] text-slate-500 uppercase tracking-wider">Average Score</p>
            </div>
        </div>
        <div class="mt-3 flex items-center justify-center gap-2 text-xs text-slate-500 bg-slate-50 rounded-md px-3 py-1.5">
            <i data-lucide="info" class="size-3"></i>
            Scoring: Topics ×5 + Posts ×2
        </div>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-3">
        {{-- Empty state or summary? We'll keep it simple --}}
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-slate-50">
        {{-- Header --}}
        <div class="bg-white border-b border-slate-200 px-8 py-6">
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="clipboard-check" class="size-6 text-blue-600"></i>
                Grading Matrix
            </h1>
            <p class="text-sm text-slate-500 mt-1">Student participation scores</p>
        </div>

        {{-- Student List --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="max-w-4xl mx-auto space-y-3">
                @forelse($students as $student)
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex flex-col sm:flex-row sm:items-center gap-4 hover:shadow-md transition">
                        {{-- Avatar + Name --}}
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="w-10 h-10 bg-slate-200 rounded-full flex items-center justify-center text-sm font-bold text-slate-700 flex-shrink-0">
                                {{ strtoupper(substr($student->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate">{{ $student->name }}</p>
                                <div class="flex items-center gap-3 text-xs text-slate-500">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="message-square" class="size-3"></i> {{ $student->topics_count }} topics
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="message-circle" class="size-3"></i> {{ $student->posts_count }} posts
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Score bar --}}
                        <div class="flex items-center gap-4 flex-shrink-0 w-full sm:w-auto">
                            <div class="w-full sm:w-40 h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                @php
                                    $score = $student->participation_score;
                                    $color = $score >= 70 ? 'bg-emerald-500' : ($score >= 40 ? 'bg-amber-500' : 'bg-red-500');
                                @endphp
                                <div class="h-full {{ $color }} rounded-full" style="width: {{ $score }}%;"></div>
                            </div>
                            <span class="text-sm font-bold text-slate-800 w-12 text-right tabular-nums">{{ $score }}%</span>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                        <i data-lucide="inbox" class="size-10 text-slate-300 mx-auto mb-3"></i>
                        <p class="text-sm text-slate-500">No students found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
@endpush