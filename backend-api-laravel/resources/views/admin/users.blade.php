@extends('layouts.workspace')

@section('title', 'User Management')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('admin.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Users</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-4 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#0A574F]">{{ $stats['total'] }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Total</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#2563EB]">{{ $stats['active'] }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Active</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#D97706]">{{ $stats['students'] ?? 0 }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Students</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#DC2626]">{{ $stats['blacklisted'] }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Blacklisted</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1 bg-[#F9F9F9]">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1 flex items-center gap-1">
            <i data-lucide="filter" style="width:12px;height:12px;"></i>
            Filters
        </p>
        <a href="{{ route('admin.users') }}" 
           class="block px-3 py-2 text-xs font-medium rounded-lg {{ request()->routeIs('admin.users') && !request()->has('role') && !request()->has('status') ? 'bg-[#0A574F] text-white' : 'text-[#666666] hover:bg-[#F0F0F0] hover:text-[#000000]' }} transition flex items-center gap-2">
            <i data-lucide="users" style="width:14px;height:14px;"></i>
            All Users
        </a>
        <a href="{{ route('admin.users', ['role' => 'student']) }}" 
           class="block px-3 py-2 text-xs font-medium rounded-lg {{ request()->get('role') === 'student' ? 'bg-[#0A574F] text-white' : 'text-[#666666] hover:bg-[#F0F0F0] hover:text-[#000000]' }} transition flex items-center gap-2">
            <i data-lucide="graduation-cap" style="width:14px;height:14px;"></i>
            Students
        </a>
        <a href="{{ route('admin.users', ['role' => 'lecturer']) }}" 
           class="block px-3 py-2 text-xs font-medium rounded-lg {{ request()->get('role') === 'lecturer' ? 'bg-[#0A574F] text-white' : 'text-[#666666] hover:bg-[#F0F0F0] hover:text-[#000000]' }} transition flex items-center gap-2">
            <i data-lucide="user" style="width:14px;height:14px;"></i>
            Lecturers
        </a>
        <a href="{{ route('admin.users', ['status' => 'blacklisted']) }}" 
           class="block px-3 py-2 text-xs font-medium rounded-lg {{ request()->get('status') === 'blacklisted' ? 'bg-[#DC2626] text-white' : 'text-[#DC2626] hover:bg-[#FEF2F2]' }} transition flex items-center gap-2">
            <i data-lucide="ban" style="width:14px;height:14px;"></i>
            Blacklisted
        </a>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-3">
                        <i data-lucide="users" style="width:28px;height:28px;color:#0A574F;"></i>
                        User Management
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="shield" style="width:14px;height:14px;color:#0A574F;"></i>
                        Manage all registered users
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4]">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        {{ $users->total() }} total
                    </span>
                    <button class="bg-[#F9F9F9] border border-[#E5E5E5] px-3 py-1.5 text-xs rounded-lg hover:border-[#0A574F] hover:bg-white transition flex items-center gap-1">
                        <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i>
                        Refresh
                    </button>
                </div>
            </div>
            {{-- Search Bar --}}
            <div class="mt-4">
                <form method="GET" action="{{ route('admin.users') }}" class="flex items-center gap-2 max-w-md">
                    <div class="relative flex-1">
                        <i data-lucide="search" style="width:16px;height:16px;color:#999;position:absolute;left:12px;top:50%;transform:translateY(-50%);"></i>
                        <input type="text" name="search" placeholder="Search users..." value="{{ request('search') }}"
                               class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg pl-9 pr-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                    </div>
                    <button type="submit" class="flex items-center gap-1 bg-[#0A574F] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition">
                        <i data-lucide="search" style="width:14px;height:14px;"></i>
                        Search
                    </button>
                </form>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#0A574F]">{{ $stats['total'] }}</p>
                    <p class="text-xs text-[#666666] font-medium">Total Users</p>
                </div>
                <div class="w-10 h-10 bg-[#ECFDF5] rounded-lg flex items-center justify-center">
                    <i data-lucide="users" style="width:20px;height:20px;color:#0A574F;"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#2563EB]">{{ $stats['active'] }}</p>
                    <p class="text-xs text-[#666666] font-medium">Active</p>
                </div>
                <div class="w-10 h-10 bg-[#E0F2FE] rounded-lg flex items-center justify-center">
                    <i data-lucide="user-check" style="width:20px;height:20px;color:#2563EB;"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#D97706]">{{ $stats['students'] ?? 0 }}</p>
                    <p class="text-xs text-[#666666] font-medium">Students</p>
                </div>
                <div class="w-10 h-10 bg-[#FEF3C7] rounded-lg flex items-center justify-center">
                    <i data-lucide="graduation-cap" style="width:20px;height:20px;color:#D97706;"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#DC2626]">{{ $stats['blacklisted'] }}</p>
                    <p class="text-xs text-[#666666] font-medium">Blacklisted</p>
                </div>
                <div class="w-10 h-10 bg-[#FEF2F2] rounded-lg flex items-center justify-center">
                    <i data-lucide="ban" style="width:20px;height:20px;color:#DC2626;"></i>
                </div>
            </div>
        </div>
        {{-- Users Table --}}
        <div class="flex-1 overflow-y-auto px-6 pb-6 custom-scrollbar">
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                <div class="border-b border-[#E5E5E5] px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="list" style="width:18px;height:18px;color:#0A574F;"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#000000]">All Users</h3>
                    </div>
                    <span class="text-xs text-[#0A574F] bg-[#ECFDF5] px-2 py-0.5 rounded-full">{{ $users->total() }} users</span>
                </div>
                <div class="divide-y divide-[#F5F5F5] max-h-[500px] overflow-y-auto custom-scrollbar">
                    @forelse($users as $user)
                        <div class="px-5 py-4 hover:bg-[#F9F9F9] transition flex items-center justify-between">
                            {{-- Left Section --}}
                            <div class="flex items-center gap-4 min-w-0 flex-1">
                                {{-- Avatar --}}
                                <div class="w-10 h-10 bg-[#ECFDF5] rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-bold text-[#0A574F]">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                </div>

                                {{-- User Info --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center flex-wrap gap-x-3 gap-y-1">
                                        <span class="text-sm font-bold text-[#000000]">{{ $user->name }}</span>

                                        {{-- Role Badge --}}
                                        <span class="text-[8px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full 
                                            {{ $user->role === 'admin' ? 'bg-[#ECFDF5] text-[#0A574F] border border-[#0A574F]' : '' }}
                                            {{ $user->role === 'student' ? 'bg-[#E0F2FE] text-[#2563EB] border border-[#2563EB]' : '' }}
                                            {{ $user->role === 'lecturer' ? 'bg-[#FEF3C7] text-[#D97706] border border-[#D97706]' : '' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>

                                        {{-- Status Badge --}}
                                        @if($user->status === 'blacklisted')
                                            <span class="text-[8px] font-bold uppercase tracking-wider text-[#DC2626] border border-[#DC2626] px-2 py-0.5 rounded-full flex items-center gap-1">
                                                <i data-lucide="ban" style="width:8px;height:8px;"></i>
                                                Blacklisted
                                            </span>
                                        @elseif($user->status === 'active')
                                            <span class="text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-2 py-0.5 rounded-full flex items-center gap-1">
                                                <i data-lucide="check-circle" style="width:8px;height:8px;"></i>
                                                Active
                                            </span>
                                        @else
                                            <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-2 py-0.5 rounded-full flex items-center gap-1">
                                                <i data-lucide="alert-circle" style="width:8px;height:8px;"></i>
                                                {{ str_replace('_', ' ', $user->status) }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Email + Metadata --}}
                                    <div class="flex items-center flex-wrap gap-x-4 gap-y-0.5 mt-1 text-xs text-[#666666]">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="mail" style="width:12px;height:12px;color:#999;"></i>
                                            {{ $user->email }}
                                        </span>
                                        <span class="text-[#E5E5E5] hidden sm:inline">|</span>
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="calendar" style="width:12px;height:12px;color:#999;"></i>
                                            Joined: {{ $user->created_at->format('M d, Y') }}
                                        </span>
                                        <span class="text-[#E5E5E5] hidden sm:inline">|</span>
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="folder" style="width:12px;height:12px;color:#999;"></i>
                                            {{ $user->groups->count() }} groups
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                                <a href="{{ route('admin.user.edit', $user->id) }}" 
                                   class="flex items-center gap-1 text-xs font-medium text-[#0A574F] border border-[#0A574F] px-3 py-1.5 rounded-lg hover:bg-[#0A574F] hover:text-white transition">
                                    <i data-lucide="edit" style="width:12px;height:12px;"></i>
                                    Edit
                                </a>
                                <form action="{{ route('admin.user.delete', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this user?')"
                                            class="flex items-center gap-1 text-xs font-medium text-[#DC2626] border border-[#DC2626] px-3 py-1.5 rounded-lg hover:bg-[#DC2626] hover:text-white transition">
                                        <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <i data-lucide="inbox" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                            <p class="text-sm text-[#666666]">No users found.</p>
                            <p class="text-xs text-[#94A3B8]">Try adjusting your search or filter.</p>
                        </div>
                    @endforelse
                </div>
                @if($users->hasPages())
                    <div class="border-t border-[#E5E5E5] px-5 py-3">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>

        
@endsection

@push('scripts')
    <script>
        lucide.createIcons();
    </script>
@endpush