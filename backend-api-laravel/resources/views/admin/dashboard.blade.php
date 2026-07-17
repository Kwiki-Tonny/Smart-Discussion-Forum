@extends('layouts.workspace')

@section('title', 'Admin Dashboard')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Admin Portal</h2>
    </div>

    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-2 text-center">
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $totalUsers }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Users</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $totalGroups }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Groups</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $pendingRegistrations }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Pending</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $blacklistedUsers }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Blacklisted</p>
            </div>
        </div>
    </div>

    <div class="p-3 bg-[#FAFAFA] space-y-2">
        <a href="{{ route('admin.users') }}"
           class="block w-full text-center bg-[#000000] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
            👥 Manage Users
        </a>
        <a href="{{ route('admin.registrations') }}"
           class="block w-full text-center bg-white border border-[#000000] px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#F5F5F5] transition-colors">
            📋 Registration Queue
        </a>
        <a href="{{ route('admin.blacklist') }}"
           class="block w-full text-center bg-white border border-[#000000] px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#F5F5F5] transition-colors">
            🚫 Blacklist Management
        </a>
        <a href="{{ route('admin.configuration') }}"
           class="block w-full text-center bg-white border border-[#000000] px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#F5F5F5] transition-colors">
            ⚙️ System Configuration
        </a>
    </div>

    {{-- Groups List for Analytics --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1">All Groups</p>
        @forelse($groups as $group)
            <a href="{{ route('admin.group.statistics', $group->id) }}"
               class="block px-3 py-2 bg-white hover:bg-[#F5F5F5] transition-colors border border-[#E5E5E5]">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-[#000000]">{{ $group->name }}</span>
                    <span class="text-[10px] text-[#666666]">{{ $group->topics_count ?? 0 }} topics</span>
                </div>
                <div class="flex items-center space-x-3 mt-1">
                    <span class="text-[9px] text-[#666666]">{{ $group->users_count ?? 0 }} members</span>
                    <span class="text-[9px] text-[#666666]">•</span>
                    <span class="text-[9px] text-[#666666]">View Stats</span>
                </div>
            </a>
        @empty
            <div class="p-4 text-center">
                <p class="text-sm text-[#666666]">No groups available.</p>
            </div>
        @endforelse
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-[#000000]">Admin Dashboard</h1>
                    <p class="text-sm text-[#666666] mt-1">System-wide overview and management</p>
                </div>
                <span class="text-xs text-[#666666] border border-[#E5E5E5] px-2 py-1">
                    {{ $groups->count() }} groups
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
            <div class="bg-white border border-[#E5E5E5] p-4">
                <p class="text-2xl font-bold text-[#000000]">{{ $totalUsers }}</p>
                <p class="text-xs text-[#666666] uppercase tracking-wider">Total Users</p>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="text-[10px] text-[#666666]">Students: {{ $totalStudents }}</span>
                    <span class="text-[10px] text-[#666666]">•</span>
                    <span class="text-[10px] text-[#666666]">Lecturers: {{ $totalLecturers }}</span>
                </div>
            </div>
            <div class="bg-white border border-[#E5E5E5] p-4">
                <p class="text-2xl font-bold text-[#000000]">{{ $totalGroups }}</p>
                <p class="text-xs text-[#666666] uppercase tracking-wider">Groups</p>
                <p class="text-[10px] text-[#666666] mt-1">{{ $totalTopics }} topics, {{ $totalPosts }} posts</p>
            </div>
            <div class="bg-white border border-[#E5E5E5] p-4">
                <p class="text-2xl font-bold text-[#000000]">{{ $totalQuizzes }}</p>
                <p class="text-xs text-[#666666] uppercase tracking-wider">Quizzes</p>
                <p class="text-[10px] text-[#666666] mt-1">{{ $totalSubmissions }} submissions</p>
            </div>
            <div class="bg-white border border-[#E5E5E5] p-4">
                <p class="text-2xl font-bold text-[#000000]">{{ $pendingRegistrations }}</p>
                <p class="text-xs text-[#666666] uppercase tracking-wider">Pending</p>
                <p class="text-[10px] text-[#DC2626] mt-1">{{ $blacklistedUsers }} blacklisted</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 px-6 pb-6 flex-1 overflow-y-auto">
            <div class="bg-white border border-[#E5E5E5] p-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Recent Registrations</h3>
                @forelse($recentUsers as $user)
                    <div class="flex justify-between items-center border-b border-[#E5E5E5] py-2">
                        <div>
                            <span class="text-sm font-bold text-[#000000]">{{ $user->name }}</span>
                            <span class="text-[10px] text-[#666666] block">{{ $user->email }}</span>
                        </div>
                        <span class="text-[10px] text-[#666666]">{{ $user->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-sm text-[#666666]">No recent registrations.</p>
                @endforelse
            </div>

            <div class="bg-white border border-[#E5E5E5] p-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Recent Blacklist Logs</h3>
                @forelse($recentBlacklistLogs as $log)
                    <div class="flex justify-between items-center border-b border-[#E5E5E5] py-2">
                        <div>
                            <span class="text-sm font-bold text-[#DC2626]">{{ $log->user->name ?? 'Unknown' }}</span>
                            <span class="text-[10px] text-[#666666] block">{{ $log->reason }}</span>
                        </div>
                        <span class="text-[10px] text-[#666666]">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-sm text-[#666666]">No blacklist logs.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection