@extends('layouts.workspace')

@section('title', 'All Groups')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center justify-between bg-white sticky top-0">
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">All Groups</h2>
        <span class="text-[10px] text-[#666666]">{{ $groups->count() ?? 0 }}</span>
    </div>

    <div class="divide-y divide-[#E5E5E5]">
        @forelse($groups ?? [] as $group)
            <a href="{{ route('groups.topics', $group->id) }}" 
               class="block p-4 bg-white hover:bg-[#F5F5F5] cursor-pointer transition-colors space-y-1">
                <div class="flex justify-between items-baseline">
                    <h3 class="text-sm font-bold text-[#000000]">{{ $group->name }}</h3>
                    <span class="text-[10px] text-[#666666]">{{ $group->topics_count ?? 0 }} topics</span>
                </div>
                @if($group->description)
                    <p class="text-xs text-[#666666] line-clamp-1">{{ $group->description }}</p>
                @endif
                @if($group->users_count)
                    <p class="text-[10px] text-[#666666]">{{ $group->users_count }} members</p>
                @endif
            </a>
        @empty
            <div class="p-8 text-center">
                <p class="text-sm text-[#666666]">No groups available.</p>
            </div>
        @endforelse
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">Groups Directory</h1>
            <p class="text-sm text-[#666666] mt-1">Browse all available discussion groups</p>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="bg-white border border-[#E5E5E5]">
                <div class="border-b border-[#E5E5E5] px-4 py-3 flex justify-between items-center">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Available Groups</h2>
                    <span class="text-[10px] text-[#666666]">{{ $groups->count() ?? 0 }} groups</span>
                </div>
                <div class="divide-y divide-[#E5E5E5]">
                    @forelse($groups ?? [] as $group)
                        <div class="px-4 py-4 flex justify-between items-center hover:bg-[#F5F5F5] transition-colors">
                            <div>
                                <h3 class="text-sm font-bold text-[#000000]">{{ $group->name }}</h3>
                                @if($group->description)
                                    <p class="text-xs text-[#666666]">{{ $group->description }}</p>
                                @endif
                                <div class="flex items-center space-x-3 mt-1">
                                    <span class="text-[10px] text-[#666666]">{{ $group->topics_count ?? 0 }} topics</span>
                                    <span class="text-[10px] text-[#666666]">•</span>
                                    <span class="text-[10px] text-[#666666]">{{ $group->users_count ?? 0 }} members</span>
                                </div>
                            </div>
                            @auth
                                @if($group->isMember ?? false)
                                    <div class="flex items-center space-x-2">
                                        <span class="text-[10px] font-bold text-[#16A34A] border border-[#16A34A] px-3 py-1">Member</span>
                                        <form action="{{ route('groups.leave', $group->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to leave this group?')">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-[10px] font-bold uppercase tracking-wider text-[#DC2626] border border-[#DC2626] px-3 py-1 hover:bg-[#DC2626] hover:text-white transition-colors">
                                                Leave
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <form action="{{ route('groups.join', $group->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="text-[10px] font-bold uppercase tracking-wider border border-[#000000] px-3 py-1 hover:bg-[#000000] hover:text-white transition-colors">
                                            Join
                                        </button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center">
                            <p class="text-sm text-[#666666]">No groups available at the moment.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection