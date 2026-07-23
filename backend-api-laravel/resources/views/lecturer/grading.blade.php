@extends('layouts.workspace')

@section('title', 'Grading Matrix')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Grading</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#0A574F]">{{ $students->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Students</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#2563EB]">{{ number_format($students->avg('participation_score') ?? 0, 0) }}%</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Average Score</p>
            </div>
        </div>
    </div>

    {{-- Formula Reminder --}}
    <div class="p-3 bg-[#F9F9F9] border-b border-[#E5E5E5]">
        <div class="flex items-center justify-center gap-2 text-xs text-[#666666]">
            <i data-lucide="info" style="width:14px;height:14px;color:#0A574F;"></i>
            <span class="font-medium">Scoring:</span>
            <span>Topics × 5 + Posts × 2</span>
        </div>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-3">
                        <i data-lucide="bar-chart-3" style="width:28px;height:28px;color:#0A574F;"></i>
                        Grading Matrix
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="users" style="width:14px;height:14px;color:#0A574F;"></i>
                        Student participation scores based on activity
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4]">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        {{ $students->count() }} active
                    </span>
                    <button class="bg-[#F9F9F9] border border-[#E5E5E5] px-3 py-1.5 text-xs rounded-lg hover:border-[#0A574F] hover:bg-white transition flex items-center gap-1">
                        <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-6">

            {{-- Stats Row --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#0A574F]">{{ $students->count() }}</p>
                        <p class="text-xs text-[#666666] font-medium">Total Students</p>
                    </div>
                    <div class="w-10 h-10 bg-[#ECFDF5] rounded-lg flex items-center justify-center">
                        <i data-lucide="users" style="width:20px;height:20px;color:#0A574F;"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#2563EB]">{{ number_format($students->avg('participation_score') ?? 0, 0) }}%</p>
                        <p class="text-xs text-[#666666] font-medium">Average Score</p>
                    </div>
                    <div class="w-10 h-10 bg-[#E0F2FE] rounded-lg flex items-center justify-center">
                        <i data-lucide="trending-up" style="width:20px;height:20px;color:#2563EB;"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#D97706]">{{ $students->sum('topics_count') }}</p>
                        <p class="text-xs text-[#666666] font-medium">Total Topics</p>
                    </div>
                    <div class="w-10 h-10 bg-[#FEF3C7] rounded-lg flex items-center justify-center">
                        <i data-lucide="message-circle" style="width:20px;height:20px;color:#D97706;"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#0A574F]">{{ $students->sum('posts_count') }}</p>
                        <p class="text-xs text-[#666666] font-medium">Total Posts</p>
                    </div>
                    <div class="w-10 h-10 bg-[#ECFDF5] rounded-lg flex items-center justify-center">
                        <i data-lucide="message-square" style="width:20px;height:20px;color:#0A574F;"></i>
                    </div>
                </div>
            </div>

            {{-- Formula Card --}}
            <div class="bg-white rounded-lg border-2 border-[#0A574F] shadow-sm p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-[#ECFDF5] rounded-lg flex items-center justify-center">
                        <i data-lucide="calculator" style="width:20px;height:20px;color:#0A574F;"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Participation Formula</h3>
                        <p class="text-xs text-[#666666]">Topics × 5 + Posts × 2 = Participation Score</p>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3 text-center">
                    <div class="bg-[#F9F9F9] rounded-lg p-2">
                        <span class="text-sm font-bold text-[#D97706]">Topics</span>
                        <span class="text-xs text-[#666666] block">× 5 points each</span>
                    </div>
                    <div class="bg-[#F9F9F9] rounded-lg p-2 flex items-center justify-center">
                        <i data-lucide="plus" style="width:20px;height:20px;color:#0A574F;"></i>
                    </div>
                    <div class="bg-[#F9F9F9] rounded-lg p-2">
                        <span class="text-sm font-bold text-[#2563EB]">Posts</span>
                        <span class="text-xs text-[#666666] block">× 2 points each</span>
                    </div>
                </div>
                <div class="mt-3 text-center text-xs text-[#666666]">
                    <i data-lucide="info" style="width:12px;height:12px;display:inline;"></i>
                    Scores are calculated automatically based on student activity.
                </div>
            </div>

            {{-- Student List --}}
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                <div class="border-b border-[#E5E5E5] px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="list" style="width:18px;height:18px;color:#0A574F;"></i>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Student Participation</h3>
                    </div>
                    <span class="text-xs bg-[#ECFDF5] text-[#0A574F] px-2 py-1 rounded-full">{{ $students->count() }} students</span>
                </div>

                <div class="divide-y divide-[#F5F5F5] max-h-[400px] overflow-y-auto">
                    @forelse($students as $student)
                        <div class="px-5 py-4 flex items-center justify-between hover:bg-[#F9F9F9] transition">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-9 h-9 bg-[#ECFDF5] rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="user" style="width:16px;height:16px;color:#0A574F;"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-[#000000] truncate">{{ $student->name }}</p>
                                    <div class="flex items-center gap-3 text-[10px] text-[#666666]">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="message-circle" style="width:10px;height:10px;"></i>
                                            {{ $student->topics_count }} topics
                                        </span>
                                        <span>•</span>
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="message-square" style="width:10px;height:10px;"></i>
                                            {{ $student->posts_count }} posts
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 flex-shrink-0 ml-4">
                                <div class="w-28 h-2 bg-[#E5E5E5] rounded-full overflow-hidden hidden sm:block">
                                    <div class="h-full rounded-full" style="width: {{ min($student->participation_score, 100) }}%; background: {{ $student->participation_score >= 70 ? '#16A34A' : ($student->participation_score >= 40 ? '#D97706' : '#DC2626') }};"></div>
                                </div>
                                <span class="text-sm font-bold text-[#000000] w-12 text-right">
                                    {{ $student->participation_score }}%
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <i data-lucide="users" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                            <p class="text-sm text-[#666666]">No students found.</p>
                            <p class="text-xs text-[#94A3B8]">Students will appear here once they join your groups.</p>
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