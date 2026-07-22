@extends('layouts.workspace')

@section('title', 'All Groups – Analytics')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('admin.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">All Groups</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-3 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#0A574F]">{{ $groups->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Groups</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#2563EB]">{{ $groups->sum('users_count') }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Members</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#D97706]">{{ $groups->sum('topics_count') }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Topics</p>
            </div>
        </div>
    </div>

    {{-- Groups Sidebar List --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1 flex items-center gap-1">
            <i data-lucide="folder" style="width:12px;height:12px;"></i>
            All Groups
        </p>
        @forelse($groups as $group)
            <a href="{{ route('admin.group.statistics', $group->id) }}"
               class="block px-3 py-2.5 bg-white hover:bg-[#F9F9F9] transition-colors border border-[#E5E5E5] rounded-lg hover:border-[#0A574F]">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-[#000000]">{{ $group->name }}</span>
                    <span class="text-[10px] text-[#2563EB] border border-[#2563EB] px-2 py-0.5 rounded-full">{{ $group->topics_count }} topics</span>
                </div>
                <span class="text-[9px] text-[#666666] flex items-center gap-1 mt-0.5">
                    <i data-lucide="users" style="width:10px;height:10px;"></i>
                    {{ $group->users_count }} members
                </span>
            </a>
        @empty
            <div class="p-8 text-center border border-dashed border-[#E5E5E5] rounded-lg bg-white">
                <i data-lucide="folder-open" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                <p class="text-sm text-[#666666]">No groups available.</p>
            </div>
        @endforelse
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
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
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4]">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        {{ $groups->count() }} groups
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

        {{-- Groups Grid --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($groups as $group)
                    <a href="{{ route('admin.group.statistics', $group->id) }}"
                       class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm hover:shadow-md hover:border-[#0A574F] transition p-5 group">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-bold text-[#000000] truncate flex items-center gap-2">
                                    <i data-lucide="folder" style="width:16px;height:16px;color:#0A574F;"></i>
                                    {{ $group->name }}
                                </h3>
                                <div class="flex items-center gap-3 mt-1 text-[10px] text-[#666666]">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="message-circle" style="width:10px;height:10px;"></i>
                                        {{ $group->topics_count }} topics
                                    </span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="users" style="width:10px;height:10px;"></i>
                                        {{ $group->users_count }} members
                                    </span>
                                </div>
                            </div>
                            <span class="flex items-center gap-1 text-[8px] font-bold uppercase tracking-wider text-[#0A574F] border border-[#0A574F] px-2 py-0.5 rounded-full group-hover:bg-[#0A574F] group-hover:text-white transition whitespace-nowrap ml-2">
                                <i data-lucide="bar-chart-2" style="width:10px;height:10px;"></i>
                                View Stats
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full bg-white rounded-lg border border-dashed border-[#E5E5E5] p-12 text-center">
                        <i data-lucide="folder-open" style="width:48px;height:48px;color:#94A3B8;margin:0 auto 0.75rem;display:block;"></i>
                        <p class="text-sm font-medium text-[#000000]">No groups available</p>
                        <p class="text-xs text-[#666666] mt-1">Groups will appear here once they are created.</p>
                    </div>
                @endforelse
            </div>
        </div>

        
@endsection

@push('scripts')
    <script>
        lucide.createIcons();
    </script>
@endpush