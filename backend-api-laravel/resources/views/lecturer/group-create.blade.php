@extends('layouts.workspace')

@section('title', 'My Groups')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">My Groups</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-3 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#0A574F]">{{ $groups->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Total Groups</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#2563EB]">{{ $groups->sum('topics_count') }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Total Topics</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#D97706]">{{ $groups->sum('users_count') }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Total Students</p>
            </div>
        </div>
    </div>

    {{-- Create Group Button --}}
    <div class="p-3 bg-[#F9F9F9] border-b border-[#E5E5E5]">
        <a href="{{ route('lecturer.groups.create') }}"
           class="flex items-center justify-center gap-2 bg-[#0A574F] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition hover:shadow-sm">
            <i data-lucide="plus-circle" style="width:14px;height:14px;"></i>
            Create New Group
        </a>
    </div>

    {{-- Sidebar Group List --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
        @forelse($groups as $group)
            <a href="{{ route('groups.topics', $group->id) }}"
               class="block px-3 py-2.5 bg-white hover:bg-[#F9F9F9] transition-colors border border-[#E5E5E5] rounded-lg hover:border-[#0A574F]">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-[#000000]">{{ $group->name }}</span>
                    <span class="text-[10px] text-[#2563EB] border border-[#2563EB] px-2 py-0.5 rounded-full">{{ $group->topics_count ?? 0 }} topics</span>
                </div>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-[9px] text-[#666666] flex items-center gap-1">
                        <i data-lucide="users" style="width:10px;height:10px;"></i>
                        {{ $group->users_count ?? 0 }} students
                    </span>
                    <span class="text-[9px] text-[#666666]">•</span>
                    <span class="text-[9px] text-[#0A574F] border border-[#0A574F] px-1.5 py-0.5 rounded-full">Admin</span>
                </div>
            </a>
        @empty
            <div class="p-8 text-center border border-dashed border-[#E5E5E5] rounded-lg bg-white">
                <i data-lucide="folder-open" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                <p class="text-sm text-[#666666]">You haven't created any groups yet.</p>
                <a href="{{ route('lecturer.groups.create') }}" class="inline-block mt-3 text-xs font-bold text-[#0A574F] border border-[#0A574F] px-4 py-1.5 rounded-lg hover:bg-[#0A574F] hover:text-white transition">
                    Create Your First Group
                </a>
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
                        <i data-lucide="folder" style="width:28px;height:28px;color:#0A574F;"></i>
                        My Groups
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="users" style="width:14px;height:14px;color:#0A574F;"></i>
                        Groups you have created and manage
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('lecturer.students.export') }}"
                       class="flex items-center gap-2 bg-[#16A34A] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#15803D] transition">
                        <i data-lucide="download" style="width:14px;height:14px;"></i>
                        Export Students
                    </a>
                </div>
            </div>
        </div>

        {{-- Groups Grid --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($groups as $group)
                    <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm hover:shadow-md hover:border-[#0A574F] transition p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-bold text-[#000000] truncate flex items-center gap-2">
                                    <i data-lucide="folder" style="width:16px;height:16px;color:#0A574F;"></i>
                                    {{ $group->name }}
                                </h3>
                                <p class="text-xs text-[#666666] mt-1 line-clamp-2">
                                    {{ $group->description ?? 'No description provided' }}
                                </p>
                            </div>
                            <span class="text-[9px] text-[#0A574F] border border-[#0A574F] px-1.5 py-0.5 rounded-full whitespace-nowrap ml-2">Admin</span>
                        </div>

                        <div class="flex items-center gap-4 mt-3 text-[10px] text-[#666666]">
                            <span class="flex items-center gap-1">
                                <i data-lucide="users" style="width:12px;height:12px;"></i>
                                {{ $group->users_count ?? 0 }} students
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="message-circle" style="width:12px;height:12px;"></i>
                                {{ $group->topics_count ?? 0 }} topics
                            </span>
                        </div>

                        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-[#E5E5E5]">
                            <a href="{{ route('groups.topics', $group->id) }}"
                               class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider bg-[#0A574F] text-white px-3 py-1.5 rounded-lg hover:bg-[#08443e] transition">
                                Topics
                            </a>
                            <a href="{{ route('lecturer.group.analytics', $group->id) }}"
                               class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider border border-[#E5E5E5] text-[#000000] px-3 py-1.5 rounded-lg hover:border-[#0A574F] hover:bg-[#F9F9F9] transition">
                                Analytics
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-lg border border-dashed border-[#E5E5E5] p-12 text-center">
                        <i data-lucide="folder-open" style="width:48px;height:48px;color:#94A3B8;margin:0 auto 0.75rem;display:block;"></i>
                        <p class="text-sm font-medium text-[#000000]">No groups created yet</p>
                        <p class="text-xs text-[#666666] mt-1">Start by creating your first group to manage students and topics.</p>
                        <a href="{{ route('lecturer.groups.create') }}" class="inline-block mt-4 text-sm font-bold text-[#0A574F] border border-[#0A574F] px-6 py-2 rounded-lg hover:bg-[#0A574F] hover:text-white transition">
                            Create Your First Group
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        lucide.createIcons();
    </script>
@endpush