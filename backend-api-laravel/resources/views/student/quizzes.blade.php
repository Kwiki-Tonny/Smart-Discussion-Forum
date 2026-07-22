@extends('layouts.workspace')

@section('title', 'Available Quizzes')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Quizzes</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-4 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#0A574F]">{{ $quizzes->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Total</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#16A34A]">{{ $quizzes->filter(fn($q) => !$q->has_taken && $q->isActive())->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Available</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#2563EB]">{{ $quizzes->filter(fn($q) => $q->has_taken)->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Completed</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border-2 border-[#DC2626] hover:shadow-md transition-all">
                <p class="text-xl font-bold text-[#DC2626]">{{ $quizzes->filter(fn($q) => $q->hasEnded() && !$q->has_taken)->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Ended</p>
            </div>
        </div>
    </div>

    {{-- Sidebar Quiz List --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-2">
        @forelse($quizzes as $quiz)
            <div class="bg-white border border-[#E5E5E5] rounded-lg p-3 hover:border-[#0A574F] transition {{ $quiz->hasEnded() ? 'border-l-4 border-l-[#DC2626]' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
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
                        </div>
                    </div>
                    @if($quiz->has_taken)
                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-2 py-0.5 rounded-full flex-shrink-0 ml-2 flex items-center gap-1">
                            <i data-lucide="check-circle" style="width:8px;height:8px;"></i>
                            Done
                        </span>
                    @elseif($quiz->isActive())
                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-2 py-0.5 rounded-full flex-shrink-0 ml-2 flex items-center gap-1">
                            <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                            Active
                        </span>
                    @elseif($quiz->hasEnded())
                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#DC2626] border-2 border-[#DC2626] px-2 py-0.5 rounded-full flex-shrink-0 ml-2 flex items-center gap-1">
                            <i data-lucide="x-circle" style="width:8px;height:8px;"></i>
                            Ended
                        </span>
                    @else
                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-2 py-0.5 rounded-full flex-shrink-0 ml-2">
                            <i data-lucide="clock" style="width:8px;height:8px;display:inline;"></i>
                            Upcoming
                        </span>
                    @endif
                </div>
                @if($quiz->has_taken)
                    <a href="{{ route('quiz.report', $quiz->id) }}"
                       class="block text-center mt-2 text-[10px] font-bold uppercase tracking-wider text-[#0A574F] border border-[#0A574F] px-3 py-1 rounded-lg hover:bg-[#0A574F] hover:text-white transition">
                        View Results
                    </a>
                @else
                    <a href="{{ route('student.quiz.take', $quiz->id) }}"
                       class="block text-center mt-2 text-[10px] font-bold uppercase tracking-wider bg-[#0A574F] text-white px-3 py-1 rounded-lg hover:bg-[#08443e] transition">
                        Start Quiz
                    </a>
                @endif
            </div>
        @empty
            <div class="p-8 text-center border border-dashed border-[#E5E5E5] rounded-lg bg-white">
                <i data-lucide="file-question" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                <p class="text-sm text-[#666666]">No quizzes available at the moment.</p>
                <p class="text-xs text-[#94A3B8]">Check back later or ask your lecturer.</p>
            </div>
        @endforelse
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Header with Red Accent --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-3">
                        <i data-lucide="file-question" style="width:28px;height:28px;color:#0A574F;"></i>
                        Available Quizzes
                        @if($quizzes->filter(fn($q) => $q->hasEnded() && !$q->has_taken)->count() > 0)
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#DC2626] bg-[#FEF2F2] border border-[#DC2626] px-2 py-0.5 rounded-full flex items-center gap-1">
                                <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                                {{ $quizzes->filter(fn($q) => $q->hasEnded() && !$q->has_taken)->count() }} expired
                            </span>
                        @endif
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="clock" style="width:14px;height:14px;color:#0A574F;"></i>
                        Complete quizzes before they expire
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4]">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        {{ $quizzes->filter(fn($q) => !$q->has_taken && $q->isActive())->count() }} available
                    </span>
                    <button class="bg-[#F9F9F9] border border-[#E5E5E5] px-3 py-1.5 text-xs rounded-lg hover:border-[#0A574F] hover:bg-white transition flex items-center gap-1">
                        <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        {{-- Quiz Grid --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($quizzes as $quiz)
                    <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm hover:shadow-md hover:border-[#0A574F] transition p-5 {{ $quiz->hasEnded() ? 'border-l-4 border-l-[#DC2626]' : '' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="clipboard-list" style="width:16px;height:16px;color:#0A574F;"></i>
                                    <h3 class="text-sm font-bold text-[#000000] truncate">{{ $quiz->title }}</h3>
                                </div>
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
                                </div>
                                @if($quiz->questions_count)
                                    <p class="text-[10px] text-[#666666] mt-1 flex items-center gap-1">
                                        <i data-lucide="list" style="width:10px;height:10px;"></i>
                                        {{ $quiz->questions_count }} questions
                                    </p>
                                @endif
                            </div>
                            @if($quiz->has_taken)
                                <span class="text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-2 py-0.5 rounded-full flex-shrink-0 ml-2 flex items-center gap-1">
                                    <i data-lucide="check-circle" style="width:8px;height:8px;"></i>
                                    Done
                                </span>
                            @elseif($quiz->isActive())
                                <span class="text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-2 py-0.5 rounded-full flex-shrink-0 ml-2 flex items-center gap-1">
                                    <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                                    Active
                                </span>
                            @elseif($quiz->hasEnded())
                                <span class="text-[8px] font-bold uppercase tracking-wider text-[#DC2626] border-2 border-[#DC2626] px-2 py-0.5 rounded-full flex-shrink-0 ml-2 flex items-center gap-1">
                                    <i data-lucide="x-circle" style="width:8px;height:8px;"></i>
                                    Ended
                                </span>
                            @else
                                <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-2 py-0.5 rounded-full flex-shrink-0 ml-2">
                                    <i data-lucide="clock" style="width:8px;height:8px;display:inline;"></i>
                                    Upcoming
                                </span>
                            @endif
                        </div>

                        @if($quiz->ends_at && !$quiz->has_taken)
                            <p class="text-[9px] text-[#D97706] mt-2 flex items-center gap-1">
                                <i data-lucide="clock" style="width:10px;height:10px;"></i>
                                Ends: {{ $quiz->ends_at->format('M d, Y h:i A') }}
                            </p>
                        @endif

                        @if($quiz->starts_at && !$quiz->isActive() && !$quiz->has_taken)
                            <p class="text-[9px] text-[#2563EB] mt-2 flex items-center gap-1">
                                <i data-lucide="calendar" style="width:10px;height:10px;"></i>
                                Starts: {{ $quiz->starts_at->format('M d, Y h:i A') }}
                            </p>
                        @endif

                      <div class="mt-4 pt-4 border-t border-[#E5E5E5]">
    @if($quiz->has_taken)
        <a href="{{ route('quiz.report', $quiz->id) }}"
           class="flex items-center justify-center gap-2 text-center text-xs font-bold uppercase tracking-wider bg-white text-[#0A574F] border-2 border-[#0A574F] px-4 py-2 rounded-lg hover:bg-[#0A574F] hover:text-white transition-all">
            <i data-lucide="bar-chart-2" style="width:14px;height:14px;"></i>
            View Results
        </a>
    @elseif($quiz->isActive())
        <a href="{{ route('student.quiz.take', $quiz->id) }}"
           class="flex items-center justify-center gap-2 text-center text-xs font-bold uppercase tracking-wider bg-white text-[#0A574F] border-2 border-[#0A574F] px-4 py-2 rounded-lg hover:bg-[#0A574F] hover:text-white transition-all">
            <i data-lucide="play" style="width:14px;height:14px;"></i>
            Start Quiz
        </a>
    @elseif($quiz->hasEnded())
        <a href="#"
           onclick="return false;"
           class="flex items-center justify-center gap-2 text-center text-xs font-bold uppercase tracking-wider bg-white text-[#DC2626] border-2 border-[#DC2626] px-4 py-2 rounded-lg hover:bg-[#DC2626] hover:text-white transition-all cursor-pointer">
            <i data-lucide="x-circle" style="width:14px;height:14px;"></i>
            Quiz Ended
        </a>
    @else
        <button disabled
                class="w-full text-center text-xs font-bold uppercase tracking-wider bg-[#E5E5E5] text-[#999999] px-4 py-2 rounded-lg cursor-not-allowed">
            <i data-lucide="clock" style="width:14px;height:14px;display:inline;"></i>
            Not Started Yet
        </button>
    @endif
</div> 
                @empty
                    <div class="col-span-full bg-white rounded-lg border border-dashed border-[#E5E5E5] p-12 text-center">
                        <i data-lucide="file-question" style="width:48px;height:48px;color:#94A3B8;margin:0 auto 0.75rem;display:block;"></i>
                        <p class="text-sm font-medium text-[#000000]">No quizzes available right now</p>
                        <p class="text-xs text-[#666666] mt-1">Check back later or ask your lecturer for available quizzes.</p>
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