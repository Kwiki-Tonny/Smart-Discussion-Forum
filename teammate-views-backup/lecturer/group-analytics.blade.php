@extends('layouts.workspace')

@section('title', $group->name . ' - Analytics')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] truncate">{{ $group->name }}</h2>
    </div>
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-3 gap-2 text-center">
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $group->topics_count ?? 0 }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Topics</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $group->users_count ?? 0 }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Students</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ count($categories) }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Categories</p>
            </div>
        </div>
    </div>
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1">Top Topics</p>
        @forelse($topTopics as $topic)
            <div class="px-3 py-2 bg-white border border-[#E5E5E5]">
                <p class="text-sm font-bold text-[#000000] truncate">{{ $topic->title }}</p>
                <span class="text-[10px] text-[#666666]">{{ $topic->posts_count }} replies</span>
            </div>
        @empty
            <p class="text-sm text-[#666666] px-3 py-2">No topics yet.</p>
        @endforelse
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">{{ $group->name }} – Analytics</h1>
            <p class="text-sm text-[#666666] mt-1">Detailed statistics for this group</p>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white border border-[#E5E5E5] p-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Category Distribution</h3>
                    @if(count($categories) > 0)
                        <div class="space-y-2">
                            @foreach($categories as $category => $count)
                                <div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-[#000000]">{{ $category }}</span>
                                        <span class="text-[#666666]">{{ $count }}</span>
                                    </div>
                                    <div class="w-full h-2 bg-[#E5E5E5] mt-0.5">
                                        <div class="h-full bg-[#000000]" style="width: {{ ($count / max(1, max($categories))) * 100 }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-[#666666]">No categorized topics yet.</p>
                    @endif
                </div>

                <div class="bg-white border border-[#E5E5E5] p-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Top Students</h3>
                    @if($studentParticipation->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($studentParticipation->take(5) as $student)
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

            <div class="bg-white border border-[#E5E5E5] p-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Daily Activity (Last 30 Days)</h3>
                @if(count($dailyActivity) > 0)
                    <div class="flex items-end h-24 space-x-1">
                        @php $max = max($dailyActivity) ?: 1; @endphp
                        @foreach($dailyActivity as $date => $count)
                            <div class="flex-1 flex flex-col items-center">
                                <div class="w-full bg-[#000000]" style="height: {{ ($count / $max) * 80 }}px;"></div>
                                <span class="text-[8px] text-[#666666] mt-1">{{ \Carbon\Carbon::parse($date)->format('d M') }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-[#666666]">No activity in the last 30 days.</p>
                @endif
            </div>
        </div>
    </div>
@endsection