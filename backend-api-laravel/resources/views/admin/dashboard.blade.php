@extends('layouts.workspace')

@section('title', 'Admin Dashboard')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('dashboard') }}" class="mr-3 text-[#666666] hover:text-[#0A66C2] transition-colors">
            <i data-lucide="arrow-left" class="size-5"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Admin Portal v2.0</h2>
    </div>

    {{-- Quick Stats (refined) --}}
    <div class="p-4 bg-[#F9FAFB] border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-xl p-3 text-center border border-[#E5E5E5] shadow-sm hover:shadow-md transition">
                <p class="text-lg font-bold text-[#000000] flex items-center justify-center gap-1">
                    <i data-lucide="users" class="size-4 text-[#0A66C2]"></i> {{ $totalUsers }}
                </p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Users</p>
            </div>
            <div class="bg-white rounded-xl p-3 text-center border border-[#E5E5E5] shadow-sm hover:shadow-md transition">
                <p class="text-lg font-bold text-[#000000] flex items-center justify-center gap-1">
                    <i data-lucide="folder-git" class="size-4 text-[#0A66C2]"></i> {{ $totalGroups }}
                </p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Groups</p>
            </div>
            <div class="bg-white rounded-xl p-3 text-center border border-[#E5E5E5] shadow-sm hover:shadow-md transition">
                <p class="text-lg font-bold text-[#000000] flex items-center justify-center gap-1">
                    <i data-lucide="clock" class="size-4 text-[#D97706]"></i> {{ $pendingRegistrations }}
                </p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Pending</p>
            </div>
            <div class="bg-white rounded-xl p-3 text-center border border-[#E5E5E5] shadow-sm hover:shadow-md transition">
                <p class="text-lg font-bold text-[#000000] flex items-center justify-center gap-1">
                    <i data-lucide="ban" class="size-4 text-[#DC2626]"></i> {{ $blacklistedUsers }}
                </p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Blacklisted</p>
            </div>
        </div>
    </div>

    {{-- Navigation (professional layout) --}}
    <div class="p-3 bg-[#F9FAFB] space-y-2 border-b border-[#E5E5E5]">
        <a href="{{ route('admin.users') }}"
           class="flex items-center justify-center gap-2 w-full bg-[#0A66C2] text-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-[#094D8F] transition shadow-sm">
            <i data-lucide="users" class="size-4"></i> Manage Users
        </a>
        <a href="{{ route('admin.registrations') }}"
           class="flex items-center justify-center gap-2 w-full bg-white border border-[#E5E5E5] px-4 py-2.5 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-[#F0F4FF] hover:border-[#0A66C2] transition">
            <i data-lucide="clipboard-list" class="size-4"></i> Registrations
        </a>
        <a href="{{ route('admin.blacklist') }}"
           class="flex items-center justify-center gap-2 w-full bg-white border border-[#E5E5E5] px-4 py-2.5 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-[#F0F4FF] hover:border-[#0A66C2] transition">
            <i data-lucide="ban" class="size-4"></i> Blacklist
        </a>
        <a href="{{ route('admin.configuration') }}"
           class="flex items-center justify-center gap-2 w-full bg-white border border-[#E5E5E5] px-4 py-2.5 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-[#F0F4FF] hover:border-[#0A66C2] transition">
            <i data-lucide="settings" class="size-4"></i> Settings
        </a>
    </div>

    {{-- Groups List --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1 flex items-center gap-1">
            <i data-lucide="folder-open" class="size-3"></i> All Groups
        </p>
        @forelse($groups as $group)
            <a href="{{ route('admin.group.statistics', $group->id) }}"
               class="block px-4 py-2.5 bg-white hover:bg-[#F0F4FF] transition-colors rounded-xl border border-[#E5E5E5] shadow-sm">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-[#000000]">{{ $group->name }}</span>
                    <span class="text-[10px] text-[#666666] flex items-center gap-1">
                        <i data-lucide="message-circle" class="size-3"></i> {{ $group->topics_count ?? 0 }}
                    </span>
                </div>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-[9px] text-[#666666] flex items-center gap-1">
                        <i data-lucide="users" class="size-3"></i> {{ $group->users_count ?? 0 }} members
                    </span>
                    <span class="text-[9px] text-[#666666]">•</span>
                    <span class="text-[9px] text-[#666666] flex items-center gap-1">
                        <i data-lucide="bar-chart" class="size-3"></i> View Stats
                    </span>
                </div>
            </a>
        @empty
            <div class="p-4 text-center bg-white rounded-xl border border-[#E5E5E5]">
                <i data-lucide="inbox" class="size-8 text-[#999999] mx-auto mb-2"></i>
                <p class="text-sm text-[#666666]">No groups available.</p>
            </div>
        @endforelse
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9FAFB]">
        {{-- Header with Last Updated --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-2">
                        <i data-lucide="shield-check" class="size-6 text-[#0A66C2]"></i>
                        Admin Dashboard
                    </h1>
                    <p class="text-sm text-[#666666] mt-0.5">System-wide overview and management</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1 text-[10px] text-[#666666] bg-[#F9FAFB] px-3 py-1.5 rounded-full border border-[#E5E5E5]">
                        <i data-lucide="clock" class="size-3"></i>
                        Last updated: {{ now()->format('M d, Y h:i A') }}
                    </span>
                    <span class="flex items-center gap-1 text-xs text-[#666666] border border-[#E5E5E5] px-3 py-1.5 rounded-full">
                        <i data-lucide="folder-git" class="size-3"></i> {{ $groups->count() }} groups
                    </span>
                </div>
            </div>
        </div>

        {{-- Stats Grid with refined cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
            {{-- Total Users --}}
            <div class="bg-white rounded-2xl border border-[#E5E5E5] shadow-sm p-5 hover:shadow-md transition">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-[#F0F4FF] rounded-xl">
                                <i data-lucide="users" class="size-5 text-[#0A66C2]"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-[#000000]">{{ $totalUsers }}</p>
                                <p class="text-xs text-[#666666] uppercase tracking-wider">Total Users</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 mt-2 text-[10px] text-[#666666]">
                            <span class="flex items-center gap-1"><i data-lucide="graduation-cap" class="size-3"></i> {{ $totalStudents }} Students</span>
                            <span class="flex items-center gap-1"><i data-lucide="user" class="size-3"></i> {{ $totalLecturers }} Lecturers</span>
                        </div>
                    </div>
                    <span class="flex items-center gap-1 text-[10px] font-semibold text-[#16A34A] bg-[#F0FDF4] px-2 py-0.5 rounded-full">
                        <i data-lucide="trending-up" class="size-3"></i> +12%
                    </span>
                </div>
            </div>

            {{-- Total Groups --}}
            <div class="bg-white rounded-2xl border border-[#E5E5E5] shadow-sm p-5 hover:shadow-md transition">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-[#F0F4FF] rounded-xl">
                                <i data-lucide="folder-git" class="size-5 text-[#0A66C2]"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-[#000000]">{{ $totalGroups }}</p>
                                <p class="text-xs text-[#666666] uppercase tracking-wider">Total Groups</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 mt-2 text-[10px] text-[#666666]">
                            <span class="flex items-center gap-1"><i data-lucide="message-square" class="size-3"></i> {{ $totalTopics }} topics</span>
                            <span class="flex items-center gap-1"><i data-lucide="message-circle" class="size-3"></i> {{ $totalPosts }} posts</span>
                        </div>
                    </div>
                    <span class="flex items-center gap-1 text-[10px] font-semibold text-[#16A34A] bg-[#F0FDF4] px-2 py-0.5 rounded-full">
                        <i data-lucide="trending-up" class="size-3"></i> +5 new
                    </span>
                </div>
            </div>

            {{-- Quizzes --}}
            <div class="bg-white rounded-2xl border border-[#E5E5E5] shadow-sm p-5 hover:shadow-md transition">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-[#F0F4FF] rounded-xl">
                        <i data-lucide="file-question" class="size-5 text-[#0A66C2]"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-[#000000]">{{ $totalQuizzes }}</p>
                        <p class="text-xs text-[#666666] uppercase tracking-wider">Quizzes</p>
                        <div class="flex items-center gap-3 mt-1 text-[10px] text-[#666666]">
                            <span class="flex items-center gap-1"><i data-lucide="send" class="size-3"></i> {{ $totalSubmissions }} submissions</span>
                            <span class="flex items-center gap-1 text-[#16A34A]"><i data-lucide="check-circle" class="size-3"></i> {{ $activeQuizzes ?? 0 }} active</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pending --}}
            <div class="bg-white rounded-2xl border border-[#E5E5E5] shadow-sm p-5 hover:shadow-md transition">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-[#F0FDF4] rounded-xl">
                                <i data-lucide="clock" class="size-5 text-[#D97706]"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-[#000000]">{{ $pendingRegistrations }}</p>
                                <p class="text-xs text-[#666666] uppercase tracking-wider">Pending</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-2 text-[10px] text-[#666666]">
                            <span class="flex items-center gap-1"><i data-lucide="ban" class="size-3 text-[#DC2626]"></i> {{ $blacklistedUsers }} blacklisted</span>
                        </div>
                    </div>
                    @if($pendingRegistrations > 0)
                        <span class="flex items-center gap-1 text-[10px] font-semibold text-[#D97706] bg-[#FEF3C7] px-2 py-0.5 rounded-full">
                            <i data-lucide="alert-circle" class="size-3"></i> Needs attention
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Two Columns --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 px-6 pb-6 flex-1 overflow-y-auto">
            {{-- Recent Registrations --}}
            <div class="bg-white rounded-2xl border border-[#E5E5E5] shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] flex items-center gap-2">
                        <i data-lucide="user-plus" class="size-4 text-[#0A66C2]"></i> Recent Registrations
                    </h3>
                    <a href="{{ route('admin.registrations') }}" class="text-[10px] font-semibold text-[#0A66C2] hover:text-[#094D8F] transition flex items-center gap-1">
                        View all <i data-lucide="arrow-right" class="size-3"></i>
                    </a>
                </div>
                <div class="space-y-2">
                    @forelse($recentUsers as $user)
                        <div class="flex items-center justify-between border-b border-[#E5E5E5] py-2.5 last:border-0">
                            <div class="flex items-center gap-3">
                                {{-- Avatar with initials only (no numbers) --}}
                                <div class="w-8 h-8 bg-[#F0F4FF] rounded-full flex items-center justify-center text-xs font-bold text-[#0A66C2]">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="text-sm font-bold text-[#000000]">{{ $user->name }}</span>
                                    <span class="text-[10px] text-[#666666] block">{{ $user->email }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] text-[#666666]">{{ $user->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="text-center py-6">
                            <i data-lucide="inbox" class="size-8 text-[#999999] mx-auto mb-2"></i>
                            <p class="text-sm text-[#666666]">No recent registrations.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Blacklist Activity --}}
            <div class="bg-white rounded-2xl border border-[#E5E5E5] shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] flex items-center gap-2">
                        <i data-lucide="ban" class="size-4 text-[#DC2626]"></i> Recent Blacklist Activity
                    </h3>
                    <a href="{{ route('admin.blacklist') }}" class="text-[10px] font-semibold text-[#0A66C2] hover:text-[#094D8F] transition flex items-center gap-1">
                        View all <i data-lucide="arrow-right" class="size-3"></i>
                    </a>
                </div>
                <div class="space-y-2">
                    @forelse($recentBlacklistLogs as $log)
                        <div class="flex items-center justify-between border-b border-[#E5E5E5] py-2.5 last:border-0">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-[#FEF2F2] rounded-full flex items-center justify-center text-xs font-bold text-[#DC2626]">
                                    {{ strtoupper(substr($log->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <span class="text-sm font-bold text-[#DC2626]">{{ $log->user->name ?? 'Unknown' }}</span>
                                    <span class="text-[10px] text-[#666666] block">{{ $log->reason }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] text-[#666666]">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="text-center py-6">
                            <i data-lucide="check-circle" class="size-8 text-[#16A34A] mx-auto mb-2"></i>
                            <p class="text-sm text-[#666666]">No blacklist activity</p>
                            <p class="text-[10px] text-[#16A34A] mt-1">All clear ✓</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Footer legal (privacy, terms) – optional, but matches mockup --}}
        <div class="px-6 pb-4 text-center text-[9px] text-[#999999] border-t border-[#E5E5E5] pt-4 bg-white">
            <span>Privacy Policy</span>
            <span class="mx-2">•</span>
            <span>Terms of Service</span>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endpush