@extends('layouts.workspace')

@section('title', 'Recommended Topics')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Recommendations</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Affinity Scores (Your Top Categories) --}}
    @if(isset($affinityScores) && count($affinityScores) > 0)
        <div class="p-4 bg-white border-b border-[#E5E5E5]">
            <div class="flex items-center gap-2 mb-3">
                <i data-lucide="brain" style="width:16px;height:16px;color:#0A574F;"></i>
                <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666]">Your Top Categories</p>
                <span class="text-[8px] text-[#0A574F] bg-[#ECFDF5] px-2 py-0.5 rounded-full ml-auto">ML</span>
            </div>
            <div class="space-y-2">
                @php $displayCategories = array_slice($affinityScores, 0, 5); @endphp
                @foreach($displayCategories as $category => $score)
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-[#666666] flex items-center gap-1">
                                <i data-lucide="tag" style="width:10px;height:10px;color:#0A574F;"></i>
                                {{ $category }}
                            </span>
                            <span class="text-[10px] font-bold text-[#0A574F]">{{ $score }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-[#E5E5E5] rounded-full mt-0.5 overflow-hidden">
                            <div class="h-full bg-[#0A574F] rounded-full transition-all" style="width: {{ $score }}%;"></div>
                        </div>
                    </div>
                @endforeach
                @if(count($affinityScores) > 5)
                    <p class="text-[9px] text-[#666666] text-center mt-1">
                        + {{ count($affinityScores) - 5 }} more categories
                    </p>
                @endif
            </div>
        </div>
    @endif
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-3">
                        <i data-lucide="thumbs-up" style="width:28px;height:28px;color:#0A574F;"></i>
                        Recommended Topics
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="sparkles" style="width:14px;height:14px;color:#0A574F;"></i>
                        Topics we think you'll find interesting based on your activity
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4]">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        {{ $recommendations->count() }} recommendations
                    </span>
                    <button class="bg-[#F9F9F9] border border-[#E5E5E5] px-3 py-1.5 text-xs rounded-lg hover:border-[#0A574F] hover:bg-white transition flex items-center gap-1">
                        <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6">
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#0A574F]">{{ $recommendations->count() }}</p>
                    <p class="text-xs text-[#666666] font-medium">Total Recommendations</p>
                </div>
                <div class="w-10 h-10 bg-[#ECFDF5] rounded-lg flex items-center justify-center">
                    <i data-lucide="thumbs-up" style="width:20px;height:20px;color:#0A574F;"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#2563EB]">{{ count($affinityScores ?? []) }}</p>
                    <p class="text-xs text-[#666666] font-medium">Interest Categories</p>
                </div>
                <div class="w-10 h-10 bg-[#E0F2FE] rounded-lg flex items-center justify-center">
                    <i data-lucide="brain" style="width:20px;height:20px;color:#2563EB;"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#D97706]">{{ $recommendations->filter(fn($t) => $t->ml_category)->count() }}</p>
                    <p class="text-xs text-[#666666] font-medium">Categorized Topics</p>
                </div>
                <div class="w-10 h-10 bg-[#FEF3C7] rounded-lg flex items-center justify-center">
                    <i data-lucide="tag" style="width:20px;height:20px;color:#D97706;"></i>
                </div>
            </div>
        </div>

        {{-- Recommendations Grid --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            @if($recommendations->isEmpty())
                <div class="bg-white rounded-lg border border-dashed border-[#E5E5E5] p-12 text-center">
                    <i data-lucide="thumbs-up" style="width:48px;height:48px;color:#94A3B8;margin:0 auto 0.75rem;display:block;"></i>
                    <p class="text-sm font-medium text-[#000000]">No recommendations yet</p>
                    <p class="text-xs text-[#666666] mt-1">Interact with more topics to get personalized suggestions.</p>
                    <a href="{{ route('groups.index') }}" class="inline-block mt-4 text-sm font-bold text-[#0A574F] border border-[#0A574F] px-6 py-2 rounded-lg hover:bg-[#0A574F] hover:text-white transition">
                        <i data-lucide="compass" style="width:14px;height:14px;display:inline;"></i>
                        Browse Groups
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($recommendations as $topic)
                        <a href="{{ route('topics.show', [$topic->group_id, $topic->id]) }}"
                           class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm hover:shadow-md hover:border-[#0A574F] transition p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="message-circle" style="width:16px;height:16px;color:#0A574F;"></i>
                                        <h3 class="text-sm font-bold text-[#000000] truncate">{{ $topic->title }}</h3>
                                    </div>
                                    <div class="flex items-center flex-wrap gap-2 mt-1">
                                        <span class="text-xs text-[#666666] flex items-center gap-1">
                                            <i data-lucide="folder" style="width:10px;height:10px;"></i>
                                            {{ $topic->group->name }}
                                        </span>
                                        @if($topic->ml_category)
                                            <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-2 py-0.5 rounded-full">
                                                <i data-lucide="tag" style="width:8px;height:8px;display:inline;"></i>
                                                {{ $topic->ml_category }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-[10px] text-[#2563EB] flex items-center gap-1 flex-shrink-0 ml-2">
                                    <i data-lucide="message-square" style="width:12px;height:12px;"></i>
                                    {{ $topic->posts_count ?? 0 }} replies
                                </span>
                            </div>
                            <div class="flex items-center flex-wrap gap-3 mt-2 text-[10px] text-[#666666]">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="user" style="width:10px;height:10px;"></i>
                                    {{ $topic->creator->name ?? 'Unknown' }}
                                </span>
                                <span>•</span>
                                <span class="flex items-center gap-1">
                                    <i data-lucide="clock" style="width:10px;height:10px;"></i>
                                    {{ $topic->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        lucide.createIcons();
    </script>
@endpush