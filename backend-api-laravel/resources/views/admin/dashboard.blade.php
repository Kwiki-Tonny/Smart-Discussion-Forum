@extends('layouts.workspace')

@section('title', 'Admin Dashboard')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#1E293B]">Admin Portal</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-4 gap-3">
            <div class="text-center p-2 rounded-lg bg-[#F8FAFC] hover:bg-[#F1F5F9] transition-colors">
                <p class="text-xl font-bold text-[#4F46E5]">{{ $totalUsers }}</p>
                <p class="text-[9px] text-[#64748B] uppercase tracking-wider font-medium">Users</p>
            </div>
            <div class="text-center p-2 rounded-lg bg-[#F8FAFC] hover:bg-[#F1F5F9] transition-colors">
                <p class="text-xl font-bold text-[#0EA5E9]">{{ $totalGroups }}</p>
                <p class="text-[9px] text-[#64748B] uppercase tracking-wider font-medium">Groups</p>
            </div>
            <div class="text-center p-2 rounded-lg bg-[#F8FAFC] hover:bg-[#F1F5F9] transition-colors">
                <p class="text-xl font-bold text-[#F59E0B]">{{ $pendingRegistrations }}</p>
                <p class="text-[9px] text-[#64748B] uppercase tracking-wider font-medium">Pending</p>
            </div>
            <div class="text-center p-2 rounded-lg bg-[#F8FAFC] hover:bg-[#F1F5F9] transition-colors">
                <p class="text-xl font-bold text-[#EF4444]">{{ $blacklistedUsers }}</p>
                <p class="text-[9px] text-[#64748B] uppercase tracking-wider font-medium">Blacklisted</p>
            </div>
        </div>
    </div>

    {{-- Navigation Buttons --}}
    <div class="p-3 bg-[#F8FAFC] border-b border-[#E5E5E5]">
        <div class="grid grid-cols-4 gap-2">
            <a href="{{ route('admin.users') }}" 
               class="flex items-center justify-center gap-2 bg-[#4F46E5] text-white px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#4338CA] transition-all hover:shadow-md">
                <i class="bi bi-people text-sm"></i> Users
            </a>
            <a href="{{ route('admin.registrations') }}" 
               class="flex items-center justify-center gap-2 bg-white border border-[#E2E8F0] text-[#1E293B] px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#F1F5F9] transition-all hover:border-[#4F46E5]">
                <i class="bi bi-person-plus text-sm"></i> Registrations
            </a>
            <a href="{{ route('admin.blacklist') }}" 
               class="flex items-center justify-center gap-2 bg-white border border-[#E2E8F0] text-[#1E293B] px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#F1F5F9] transition-all hover:border-[#EF4444]">
                <i class="bi bi-ban text-sm"></i> Blacklist
            </a>
            <a href="{{ route('admin.configuration') }}" 
               class="flex items-center justify-center gap-2 bg-white border border-[#E2E8F0] text-[#1E293B] px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#F1F5F9] transition-all hover:border-[#0EA5E9]">
                <i class="bi bi-gear text-sm"></i> Settings
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F8FAFC]">
        {{-- Header --}}
        <div class="bg-white border-b border-[#E2E8F0] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#0F172A] flex items-center gap-3">
                        <i class="bi bi-grid-fill text-[#4F46E5]"></i>
                        Admin Dashboard
                    </h1>
                    <p class="text-sm text-[#64748B] mt-1">
                        <i class="bi bi-clock-history me-1"></i>
                        Last updated: {{ now()->format('M d, Y h:i A') }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#64748B]">
                        <i class="bi bi-circle-fill text-[#22C55E] me-1" style="font-size: 8px;"></i>
                        System Online
                    </span>
                    <button class="bg-[#F1F5F9] px-3 py-1.5 text-xs rounded-lg hover:bg-[#E2E8F0] transition-colors">
                        <i class="bi bi-arrow-repeat me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
            <div class="bg-white rounded-xl shadow-sm border border-[#E2E8F0] p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-[#0F172A]">{{ $totalUsers }}</p>
                        <p class="text-sm text-[#64748B] font-medium mt-1">Total Users</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-xs bg-[#EEF2FF] text-[#4F46E5] px-2 py-0.5 rounded-full">
                                {{ $totalStudents }} Students
                            </span>
                            <span class="text-xs bg-[#F1F5F9] text-[#64748B] px-2 py-0.5 rounded-full">
                                {{ $totalLecturers }} Lecturers
                            </span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-[#EEF2FF] rounded-xl flex items-center justify-center">
                        <i class="bi bi-people text-2xl text-[#4F46E5]"></i>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-[#E2E8F0]">
                    <span class="text-xs text-[#22C55E]">
                        <i class="bi bi-arrow-up me-1"></i> +12% this month
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-[#E2E8F0] p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-[#0F172A]">{{ $totalGroups }}</p>
                        <p class="text-sm text-[#64748B] font-medium mt-1">Total Groups</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-xs text-[#64748B]">
                                <i class="bi bi-chat-dots me-1"></i>{{ $totalTopics }} Topics
                            </span>
                            <span class="text-xs text-[#64748B]">
                                <i class="bi bi-chat me-1"></i>{{ $totalPosts }} Posts
                            </span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-[#E0F2FE] rounded-xl flex items-center justify-center">
                        <i class="bi bi-grid-3x3-gap-fill text-2xl text-[#0EA5E9]"></i>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-[#E2E8F0]">
                    <span class="text-xs text-[#0EA5E9]">
                        <i class="bi bi-arrow-up me-1"></i> +5 new this week
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-[#E2E8F0] p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-[#0F172A]">{{ $totalQuizzes }}</p>
                        <p class="text-sm text-[#64748B] font-medium mt-1">Quizzes</p>
                        <p class="text-xs text-[#64748B] mt-2">
                            <i class="bi bi-file-earmark-check me-1"></i>
                            {{ $totalSubmissions }} Submissions
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-[#FEF3C7] rounded-xl flex items-center justify-center">
                        <i class="bi bi-question-octagon text-2xl text-[#F59E0B]"></i>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-[#E2E8F0]">
                    <span class="text-xs text-[#F59E0B]">
                        <i class="bi bi-clock me-1"></i> {{ $totalQuizzes }} active quizzes
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-[#E2E8F0] p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-[#0F172A]">{{ $pendingRegistrations }}</p>
                        <p class="text-sm text-[#64748B] font-medium mt-1">Pending</p>
                        <p class="text-xs text-[#EF4444] mt-2">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            {{ $blacklistedUsers }} Blacklisted
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-[#FEF2F2] rounded-xl flex items-center justify-center">
                        <i class="bi bi-clock-history text-2xl text-[#EF4444]"></i>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-[#E2E8F0]">
                    <span class="text-xs text-[#EF4444]">
                        <i class="bi bi-exclamation-circle me-1"></i> Needs attention
                    </span>
                </div>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 px-6 pb-6 flex-1 overflow-y-auto">
            {{-- Recent Registrations --}}
            <div class="bg-white rounded-xl shadow-sm border border-[#E2E8F0]">
                <div class="border-b border-[#E2E8F0] px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-person-plus text-[#4F46E5]"></i>
                        <h3 class="text-sm font-bold text-[#0F172A]">Recent Registrations</h3>
                    </div>
                    <span class="text-xs bg-[#F1F5F9] px-2 py-1 rounded-full text-[#64748B]">
                        {{ $recentUsers->count() }} new
                    </span>
                </div>
                <div class="divide-y divide-[#F1F5F9] max-h-[300px] overflow-y-auto">
                    @forelse($recentUsers as $user)
                        <div class="px-5 py-3 flex items-center justify-between hover:bg-[#F8FAFC] transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-[#EEF2FF] rounded-full flex items-center justify-center">
                                    <i class="bi bi-person text-sm text-[#4F46E5]"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-[#0F172A]">{{ $user->name }}</p>
                                    <p class="text-xs text-[#64748B]">{{ $user->email }}</p>
                                </div>
                            </div>
                            <span class="text-xs text-[#64748B]">{{ $user->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <i class="bi bi-check-circle text-2xl text-[#22C55E] block mb-2"></i>
                            <p class="text-sm text-[#64748B]">No recent registrations</p>
                        </div>
                    @endforelse
                </div>
                <div class="border-t border-[#E2E8F0] px-5 py-3">
                    <a href="{{ route('admin.registrations') }}" class="text-xs text-[#4F46E5] hover:text-[#4338CA] font-medium">
                        View all registrations <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            {{-- Recent Blacklist Logs --}}
            <div class="bg-white rounded-xl shadow-sm border border-[#E2E8F0]">
                <div class="border-b border-[#E2E8F0] px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-ban text-[#EF4444]"></i>
                        <h3 class="text-sm font-bold text-[#0F172A]">Recent Blacklist Activity</h3>
                    </div>
                    <span class="text-xs bg-[#FEF2F2] px-2 py-1 rounded-full text-[#EF4444]">
                        {{ $recentBlacklistLogs->count() }} logs
                    </span>
                </div>
                <div class="divide-y divide-[#F1F5F9] max-h-[300px] overflow-y-auto">
                    @forelse($recentBlacklistLogs as $log)
                        <div class="px-5 py-3 flex items-center justify-between hover:bg-[#F8FAFC] transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-[#FEF2F2] rounded-full flex items-center justify-center">
                                    <i class="bi bi-person-x text-sm text-[#EF4444]"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-[#0F172A]">{{ $log->user->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-[#64748B]">{{ $log->reason }}</p>
                                </div>
                            </div>
                            <span class="text-xs text-[#64748B]">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <i class="bi bi-check-circle text-2xl text-[#22C55E] block mb-2"></i>
                            <p class="text-sm text-[#64748B]">No blacklist activity</p>
                        </div>
                    @endforelse
                </div>
                <div class="border-t border-[#E2E8F0] px-5 py-3">
                    <a href="{{ route('admin.blacklist') }}" class="text-xs text-[#4F46E5] hover:text-[#4338CA] font-medium">
                        View blacklist <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection