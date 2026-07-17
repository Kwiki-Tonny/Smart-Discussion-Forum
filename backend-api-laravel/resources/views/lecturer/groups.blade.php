@extends('layouts.workspace')

@section('title', 'My Groups')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">My Groups</h2>
    </div>
    <div class="p-3 bg-[#FAFAFA]">
        <a href="{{ route('lecturer.groups.create') }}"
           class="block w-full text-center bg-[#000000] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
            + Create New Group
        </a>
    </div>
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
        @forelse($groups as $group)
            <a href="{{ route('groups.topics', $group->id) }}"
               class="block px-3 py-2 bg-white hover:bg-[#F5F5F5] transition-colors border border-[#E5E5E5]">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-[#000000]">{{ $group->name }}</span>
                    <span class="text-[10px] text-[#666666]">{{ $group->topics_count ?? 0 }} topics</span>
                </div>
                <div class="flex items-center space-x-3 mt-1">
                    <span class="text-[9px] text-[#666666]">{{ $group->users_count ?? 0 }} students</span>
                    <span class="text-[9px] text-[#666666]">•</span>
                    <span class="text-[9px] text-[#666666]">Admin</span>
                </div>
            </a>
        @empty
            <div class="p-8 text-center">
                <p class="text-sm text-[#666666]">You haven't created any groups yet.</p>
                <a href="{{ route('lecturer.groups.create') }}" class="inline-block mt-4 text-xs font-bold text-[#000000] border border-[#000000] px-4 py-2 hover:bg-[#F5F5F5] transition-colors">
                    Create Your First Group
                </a>
            </div>
        @endforelse
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-[#000000]">My Groups</h1>
                    <p class="text-sm text-[#666666] mt-1">Groups you have created and manage</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('lecturer.students.export') }}"
                       class="bg-[#000000] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                        📊 Export Students
                    </a>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($groups as $group)
                    <div class="bg-white border border-[#E5E5E5] p-4">
                        <h3 class="text-sm font-bold text-[#000000]">{{ $group->name }}</h3>
                        <p class="text-xs text-[#666666] mt-1 line-clamp-2">{{ $group->description ?? 'No description' }}</p>
                        <div class="flex items-center space-x-3 mt-2 text-[10px] text-[#666666]">
                            <span>{{ $group->users_count ?? 0 }} students</span>
                            <span>•</span>
                            <span>{{ $group->topics_count ?? 0 }} topics</span>
                        </div>
                        <div class="flex items-center space-x-2 mt-3">
                            <a href="{{ route('groups.topics', $group->id) }}"
                               class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider border border-[#000000] px-2 py-1 hover:bg-[#000000] hover:text-white transition-colors">
                                Topics
                            </a>
                            <a href="{{ route('lecturer.group.analytics', $group->id) }}"
                               class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider border border-[#000000] px-2 py-1 hover:bg-[#000000] hover:text-white transition-colors">
                                Analytics
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white border border-[#E5E5E5] p-12 text-center">
                        <p class="text-sm text-[#666666]">You haven't created any groups yet.</p>
                        <a href="{{ route('lecturer.groups.create') }}" class="inline-block mt-4 text-sm font-bold text-[#000000] border border-[#000000] px-4 py-2 hover:bg-[#F5F5F5] transition-colors">
                            Create Your First Group
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection