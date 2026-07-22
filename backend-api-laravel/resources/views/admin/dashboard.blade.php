@extends('layouts.workspace')

@section('title', 'Admin Dashboard')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Admin Portal</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-4 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#0A574F]">{{ $totalUsers }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Users</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#2563EB]">{{ $totalGroups }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Groups</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#D97706]">{{ $pendingRegistrations }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Pending</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#DC2626]">{{ $blacklistedUsers }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Blacklisted</p>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="p-3 bg-[#F9F9F9] border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-2">
            <a href="{{ route('admin.users') }}"
               class="flex items-center justify-center gap-2 bg-[#0A574F] text-white px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition">
                <i data-lucide="users" style="width:14px;height:14px;"></i>
                Users
            </a>
            <a href="{{ route('admin.registrations') }}"
               class="flex items-center justify-center gap-2 bg-white border border-[#D97706] text-[#D97706] px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#FEF3C7] transition">
                <i data-lucide="clipboard-list" style="width:14px;height:14px;"></i>
                Registrations
            </a>
            <a href="{{ route('admin.blacklist') }}"
               class="flex items-center justify-center gap-2 bg-white border border-[#DC2626] text-[#DC2626] px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#FEF2F2] transition">
                <i data-lucide="ban" style="width:14px;height:14px;"></i>
                Blacklist
            </a>
            <a href="{{ route('admin.configuration') }}"
               class="flex items-center justify-center gap-2 bg-white border border-[#2563EB] text-[#2563EB] px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#E0F2FE] transition">
                <i data-lucide="settings" style="width:14px;height:14px;"></i>
                Settings
            </a>
        </div>
    </div>

    {{-- Groups List --}}
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
                    <span class="text-[10px] text-[#2563EB] border border-[#2563EB] px-2 py-0.5 rounded-full">{{ $group->topics_count ?? 0 }} topics</span>
                </div>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-[9px] text-[#666666] flex items-center gap-1">
                        <i data-lucide="users" style="width:10px;height:10px;"></i>
                        {{ $group->users_count ?? 0 }} members
                    </span>
                    <span class="text-[9px] text-[#666666]">•</span>
                    <span class="text-[9px] text-[#0A574F] flex items-center gap-1">
                        <i data-lucide="bar-chart-2" style="width:10px;height:10px;"></i>
                        View Stats
                    </span>
                </div>
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
                        <i data-lucide="shield-check" style="width:28px;height:28px;color:#0A574F;"></i>
                        Admin Dashboard
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="activity" style="width:14px;height:14px;color:#0A574F;"></i>
                        System-wide overview and management
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#666666] flex items-center gap-1 border border-[#E5E5E5] px-3 py-1 rounded-full bg-[#F9F9F9]">
                        <i data-lucide="clock" style="width:12px;height:12px;"></i>
                        {{ now()->format('M d, Y h:i A') }}
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
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#0A574F]">{{ $totalUsers }}</p>
                        <p class="text-xs text-[#666666] font-medium mt-1">Total Users</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-[10px] bg-[#ECFDF5] text-[#0A574F] px-2 py-0.5 rounded-full">{{ $totalStudents }} Students</span>
                            <span class="text-[10px] bg-[#F9F9F9] text-[#666666] px-2 py-0.5 rounded-full">{{ $totalLecturers }} Lecturers</span>
                        </div>
                    </div>
                    <div class="w-11 h-11 bg-[#ECFDF5] rounded-lg flex items-center justify-center">
                        <i data-lucide="users" style="width:22px;height:22px;color:#0A574F;"></i>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-[#E5E5E5]">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1">
                        <i data-lucide="trending-up" style="width:12px;height:12px;"></i>
                        +12% this month
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-[#E5E5E5] p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#2563EB]">{{ $totalGroups }}</p>
                        <p class="text-xs text-[#666666] font-medium mt-1">Total Groups</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-[10px] text-[#666666] flex items-center gap-1">
                                <i data-lucide="message-circle" style="width:10px;height:10px;"></i>
                                {{ $totalTopics }} Topics
                            </span>
                            <span class="text-[10px] text-[#666666] flex items-center gap-1">
                                <i data-lucide="message-square" style="width:10px;height:10px;"></i>
                                {{ $totalPosts }} Posts
                            </span>
                        </div>
                    </div>
                    <div class="w-11 h-11 bg-[#E0F2FE] rounded-lg flex items-center justify-center">
                        <i data-lucide="grid" style="width:22px;height:22px;color:#2563EB;"></i>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-[#E5E5E5]">
                    <span class="text-xs text-[#2563EB] flex items-center gap-1">
                        <i data-lucide="trending-up" style="width:12px;height:12px;"></i>
                        +5 new this week
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-[#E5E5E5] p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#D97706]">{{ $totalQuizzes }}</p>
                        <p class="text-xs text-[#666666] font-medium mt-1">Quizzes</p>
                        <p class="text-[10px] text-[#666666] mt-2">
                            <i data-lucide="file-check" style="width:10px;height:10px;display:inline;"></i>
                            {{ $totalSubmissions }} Submissions
                        </p>
                    </div>
                    <div class="w-11 h-11 bg-[#FEF3C7] rounded-lg flex items-center justify-center">
                        <i data-lucide="file-question" style="width:22px;height:22px;color:#D97706;"></i>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-[#E5E5E5]">
                    <span class="text-xs text-[#D97706] flex items-center gap-1">
                        <i data-lucide="clock" style="width:12px;height:12px;"></i>
                        {{ $totalQuizzes }} active quizzes
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-[#E5E5E5] p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-[#DC2626]">{{ $pendingRegistrations }}</p>
                        <p class="text-xs text-[#666666] font-medium mt-1">Pending</p>
                        <p class="text-[10px] text-[#DC2626] mt-2 flex items-center gap-1">
                            <i data-lucide="alert-circle" style="width:10px;height:10px;"></i>
                            {{ $blacklistedUsers }} Blacklisted
                        </p>
                    </div>
                    <div class="w-11 h-11 bg-[#FEF2F2] rounded-lg flex items-center justify-center">
                        <i data-lucide="clock" style="width:22px;height:22px;color:#DC2626;"></i>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-[#E5E5E5]">
                    <span class="text-xs text-[#DC2626] flex items-center gap-1">
                        <i data-lucide="alert-triangle" style="width:12px;height:12px;"></i>
                        Needs attention
                    </span>
                </div>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 px-6 pb-6 flex-1 overflow-y-auto">

            {{-- Recent Registrations --}}
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                <div class="border-b border-[#E5E5E5] px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="user-plus" style="width:18px;height:18px;color:#16A34A;"></i>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Recent Registrations</h3>
                    </div>
                    <a href="{{ route('admin.registrations') }}" class="text-xs text-[#0A574F] hover:text-[#08443e] font-medium flex items-center gap-1">
                        View all <i data-lucide="arrow-right" style="width:12px;height:12px;"></i>
                    </a>
                </div>
                <div class="divide-y divide-[#F5F5F5] max-h-[280px] overflow-y-auto">
                    @forelse($recentUsers as $user)
                        <div class="px-5 py-3 flex items-center justify-between hover:bg-[#F9F9F9] transition">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-[#ECFDF5] rounded-full flex items-center justify-center">
                                    <i data-lucide="user" style="width:14px;height:14px;color:#0A574F;"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-[#000000]">{{ $user->name }}</p>
                                    <p class="text-xs text-[#666666]">{{ $user->email }}</p>
                                </div>
                            </div>
                            <span class="text-[10px] text-[#666666]">{{ $user->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <i data-lucide="check-circle" style="width:32px;height:32px;color:#16A34A;margin:0 auto 0.5rem;display:block;"></i>
                            <p class="text-sm text-[#666666]">No recent registrations</p>
                            <p class="text-xs text-[#94A3B8]">All caught up! ✅</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Blacklist Activity --}}
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                <div class="border-b border-[#E5E5E5] px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="ban" style="width:18px;height:18px;color:#DC2626;"></i>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Recent Blacklist Activity</h3>
                    </div>
                    <a href="{{ route('admin.blacklist') }}" class="text-xs text-[#0A574F] hover:text-[#08443e] font-medium flex items-center gap-1">
                        View all <i data-lucide="arrow-right" style="width:12px;height:12px;"></i>
                    </a>
                </div>
                <div class="divide-y divide-[#F5F5F5] max-h-[280px] overflow-y-auto">
                    @forelse($recentBlacklistLogs as $log)
                        <div class="px-5 py-3 flex items-center justify-between hover:bg-[#F9F9F9] transition">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-[#FEF2F2] rounded-full flex items-center justify-center">
                                    <i data-lucide="user-x" style="width:14px;height:14px;color:#DC2626;"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-[#DC2626]">{{ $log->user->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-[#666666]">{{ $log->reason }}</p>
                                </div>
                            </div>
                            <span class="text-[10px] text-[#666666]">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <i data-lucide="shield-check" style="width:32px;height:32px;color:#16A34A;margin:0 auto 0.5rem;display:block;"></i>
                            <p class="text-sm text-[#666666]">No blacklist activity</p>
                            <p class="text-xs text-[#94A3B8]">All clear! ✅</p>
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