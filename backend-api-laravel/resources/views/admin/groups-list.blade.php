@extends('layouts.workspace')

@section('title', 'All Groups – Analytics')

@section('context_panel')
    {{-- Left Panel: Back button, Ranking with Toggle --}}
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('admin.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">All Groups</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Ranking Toggle --}}
    <div class="p-3 bg-[#FAFAFA] border-b border-[#E5E5E5] flex items-center justify-between">
        <span class="text-[10px] font-bold uppercase tracking-wider text-[#666666] flex items-center gap-1">
            <i data-lucide="trophy" style="width:14px;height:14px;color:#0A574F;"></i>
            Group Rankings
        </span>
        <div class="flex items-center gap-1 bg-white rounded-lg border border-[#E5E5E5] p-0.5">
            <button id="sort-by-topics" class="sort-btn text-[10px] font-bold px-3 py-1 rounded-lg transition bg-[#0A574F] text-white">
                <i data-lucide="message-circle" style="width:12px;height:12px;display:inline;"></i> Topics
            </button>
            <button id="sort-by-replies" class="sort-btn text-[10px] font-bold px-3 py-1 rounded-lg transition hover:bg-[#F9F9F9]">
                <i data-lucide="message-square" style="width:12px;height:12px;display:inline;"></i> Replies
            </button>
        </div>
    </div>

    {{-- Ranked Group List --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1 flex items-center gap-1">
            <i data-lucide="list" style="width:12px;height:12px;"></i>
            All Groups
        </p>
        <div id="group-list" class="space-y-1">
            @foreach($groups as $index => $g)
                <a href="{{ route('admin.group.statistics', $g->id) }}"
                   class="group-item block px-3 py-2.5 bg-white border border-[#E5E5E5] rounded-lg hover:border-[#0A574F] transition"
                   data-topics="{{ $g->topics_count }}"
                   data-replies="{{ $g->posts_count }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-[10px] font-bold text-[#666666] w-5 flex-shrink-0">{{ $index + 1 }}</span>
                            <span class="text-sm font-bold text-[#000000] truncate">{{ $g->name }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-[10px] flex-shrink-0 ml-2">
                            <span class="flex items-center gap-1 text-[#0A574F]">
                                <i data-lucide="message-circle" style="width:12px;height:12px;"></i>
                                {{ $g->topics_count }}
                            </span>
                            <span class="flex items-center gap-1 text-[#2563EB]">
                                <i data-lucide="message-square" style="width:12px;height:12px;"></i>
                                {{ $g->posts_count }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Header with Search and Stats (Right Panel) --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-3">
                        <i data-lucide="grid" style="width:28px;height:28px;color:#0A574F;"></i>
                        Group Analytics
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="mouse-pointer" style="width:14px;height:14px;color:#0A574F;"></i>
                        Click a group to view detailed statistics
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    {{-- SEARCH INPUT --}}
                    <div class="relative">
                        <i data-lucide="search" style="width:16px;height:16px;position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94A3B8;"></i>
                        <input type="text" id="search-groups" placeholder="Search groups..."
                               class="pl-8 pr-4 py-1.5 border border-[#E5E5E5] rounded-lg text-sm bg-white focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition w-48 md:w-64">
                    </div>
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4] whitespace-nowrap">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        {{ $groups->count() }} groups
                    </span>
                    <button class="bg-[#F9F9F9] border border-[#E5E5E5] px-3 py-1.5 text-xs rounded-lg hover:border-[#0A574F] hover:bg-white transition flex items-center gap-1">
                        <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i>
                        Refresh
                    </button>
                </div>
            </div>

            {{-- Stats Cards (Right Panel) --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#0A574F]">{{ $groups->count() }}</p>
                        <p class="text-xs text-[#666666] font-medium">Total Groups</p>
                    </div>
                    <div class="w-10 h-10 bg-[#ECFDF5] rounded-lg flex items-center justify-center">
                        <i data-lucide="folder" style="width:20px;height:20px;color:#0A574F;"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#2563EB]">{{ $groups->sum('users_count') }}</p>
                        <p class="text-xs text-[#666666] font-medium">Total Members</p>
                    </div>
                    <div class="w-10 h-10 bg-[#E0F2FE] rounded-lg flex items-center justify-center">
                        <i data-lucide="users" style="width:20px;height:20px;color:#2563EB;"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#D97706]">{{ $groups->sum('topics_count') }}</p>
                        <p class="text-xs text-[#666666] font-medium">Total Topics</p>
                    </div>
                    <div class="w-10 h-10 bg-[#FEF3C7] rounded-lg flex items-center justify-center">
                        <i data-lucide="message-circle" style="width:20px;height:20px;color:#D97706;"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#16A34A]">{{ $groups->sum(fn($g) => $g->topics->sum('posts_count')) }}</p>
                        <p class="text-xs text-[#666666] font-medium">Total Posts</p>
                    </div>
                    <div class="w-10 h-10 bg-[#F0FDF4] rounded-lg flex items-center justify-center">
                        <i data-lucide="message-square" style="width:20px;height:20px;color:#16A34A;"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Groups Stacked List (Vertical rows instead of cards) --}}
        <div class="flex-1 overflow-y-auto px-6 pb-6 custom-scrollbar">
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                <div class="border-b border-[#E5E5E5] px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="list" style="width:18px;height:18px;color:#0A574F;"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#000000]">Group Details</h3>
                        <span id="group-count" class="text-[10px] text-[#666666] bg-[#F9F9F9] px-2 py-0.5 rounded-full">{{ $groups->count() }}</span>
                    </div>
                    <span class="text-[10px] text-[#666666]">Click a group to view analytics</span>
                </div>
                <div id="groups-list">
                    @forelse($groups as $group)
                        <div class="group-row px-5 py-4 flex items-center justify-between hover:bg-[#F9F9F9] transition border-b border-[#F5F5F5] last:border-0" data-name="{{ strtolower($group->name) }}">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-9 h-9 bg-[#ECFDF5] rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="folder" style="width:14px;height:14px;color:#0A574F;"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center flex-wrap gap-2">
                                        <span class="text-sm font-bold text-[#000000]">{{ $group->name }}</span>
                                        @if($group->created_by)
                                            <span class="text-[8px] font-bold uppercase tracking-wider text-[#0A574F] border border-[#0A574F] px-1.5 py-0.5 rounded-full">Created</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 mt-0.5 text-[10px] text-[#666666]">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="message-circle" style="width:10px;height:10px;"></i>
                                            {{ $group->topics_count }} topics
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="users" style="width:10px;height:10px;"></i>
                                            {{ $group->users_count }} members
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0 ml-4">
                                <a href="{{ route('admin.group.statistics', $group->id) }}"
                                   class="text-[10px] font-bold uppercase tracking-wider bg-[#0A574F] text-white px-3 py-1 rounded-lg hover:bg-[#08443e] transition">
                                    <i data-lucide="bar-chart-2" style="width:12px;height:12px;"></i>
                                    Analytics
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <i data-lucide="inbox" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                            <p class="text-sm text-[#666666]">No groups found.</p>
                            <p class="text-xs text-[#94A3B8]">Create a group to get started.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();

        // ─── LEFT PANEL SORT TOGGLE ──────────────────────────────
        const groupList = document.getElementById('group-list');
        const sortTopicsBtn = document.getElementById('sort-by-topics');
        const sortRepliesBtn = document.getElementById('sort-by-replies');

        function sortGroups(by) {
            const items = Array.from(groupList.querySelectorAll('.group-item'));
            items.sort((a, b) => {
                const aVal = parseInt(a.dataset[by]);
                const bVal = parseInt(b.dataset[by]);
                return bVal - aVal; // descending
            });
            items.forEach((item, index) => {
                const rankSpan = item.querySelector('.w-5');
                if (rankSpan) rankSpan.textContent = index + 1;
                groupList.appendChild(item);
            });
            // Update active button styles
            sortTopicsBtn.classList.toggle('bg-[#0A574F]', by === 'topics');
            sortTopicsBtn.classList.toggle('text-white', by === 'topics');
            sortTopicsBtn.classList.toggle('hover:bg-[#F9F9F9]', by !== 'topics');
            sortRepliesBtn.classList.toggle('bg-[#0A574F]', by === 'replies');
            sortRepliesBtn.classList.toggle('text-white', by === 'replies');
            sortRepliesBtn.classList.toggle('hover:bg-[#F9F9F9]', by !== 'replies');
        }

        sortTopicsBtn.addEventListener('click', () => sortGroups('topics'));
        sortRepliesBtn.addEventListener('click', () => sortGroups('replies'));

        // ─── RIGHT PANEL SEARCH FILTER ─────────────────────────────
        const searchInput = document.getElementById('search-groups');
        const rows = document.querySelectorAll('.group-row');
        const groupCount = document.getElementById('group-count');

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            let visibleCount = 0;
            rows.forEach(row => {
                const name = row.dataset.name;
                if (name.includes(query)) {
                    row.style.display = 'flex';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            if (groupCount) {
                groupCount.textContent = visibleCount;
            }
            // Also update the count badge in the header
            const countBadge = document.querySelector('.whitespace-nowrap');
            if (countBadge) {
                countBadge.textContent = visibleCount + ' groups';
            }
        });
    });
</script>
@endpush