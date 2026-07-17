@extends('layouts.workspace')

@section('title', 'All Groups – Analytics')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('admin.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">All Groups</h2>
    </div>
    <div class="p-3 bg-[#FAFAFA] border-b border-[#E5E5E5]">
        <p class="text-xs text-[#666666]">{{ $groups->count() }} groups total</p>
    </div>
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
        @forelse($groups as $group)
            <a href="{{ route('admin.group.statistics', $group->id) }}"
               class="block px-3 py-2 bg-white hover:bg-[#F5F5F5] transition-colors border border-[#E5E5E5]">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-[#000000]">{{ $group->name }}</span>
                    <span class="text-[10px] text-[#666666]">{{ $group->topics_count }} topics</span>
                </div>
                <span class="text-[9px] text-[#666666]">{{ $group->users_count }} members</span>
            </a>
        @empty
            <p class="text-sm text-[#666666]">No groups available.</p>
        @endforelse
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">Group Analytics</h1>
            <p class="text-sm text-[#666666] mt-1">Click a group to view detailed statistics</p>
        </div>
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($groups as $group)
                    <a href="{{ route('admin.group.statistics', $group->id) }}"
                       class="bg-white border border-[#E5E5E5] p-4 hover:bg-[#F5F5F5] transition-colors">
                        <h3 class="text-sm font-bold text-[#000000]">{{ $group->name }}</h3>
                        <div class="flex items-center space-x-3 mt-1 text-[10px] text-[#666666]">
                            <span>{{ $group->topics_count }} topics</span>
                            <span>•</span>
                            <span>{{ $group->users_count }} members</span>
                        </div>
                        <span class="inline-block mt-2 text-[8px] font-bold uppercase tracking-wider border border-[#000000] px-2 py-0.5">
                            View Stats
                        </span>
                    </a>
                @empty
                    <div class="col-span-full bg-white border border-[#E5E5E5] p-12 text-center">
                        <p class="text-sm text-[#666666]">No groups yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection