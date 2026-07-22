@extends('layouts.workspace')

@section('title', 'Registration Queue')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('admin.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Registrations</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#D97706]">{{ $pendingUsers->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Pending</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#16A34A]">{{ $approvedUsers->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Approved</p>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="p-3 bg-[#F9F9F9] border-b border-[#E5E5E5]">
        <form method="GET" action="{{ route('admin.registrations') }}" class="flex items-center gap-2">
            <div class="relative flex-1">
                <i data-lucide="search" style="width:14px;height:14px;color:#999;position:absolute;left:10px;top:50%;transform:translateY(-50%);"></i>
                <input type="text" name="search" placeholder="Search users..." value="{{ request('search') }}"
                       class="w-full bg-white border border-[#E5E5E5] rounded-lg pl-8 pr-3 py-1.5 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
            </div>
            <button type="submit" class="flex items-center gap-1 bg-[#0A574F] text-white px-3 py-1.5 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition">
                <i data-lucide="search" style="width:12px;height:12px;"></i>
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('admin.registrations') }}" class="text-xs text-[#666666] hover:text-[#0A574F] transition">Clear</a>
            @endif
        </form>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-3">
                        <i data-lucide="clipboard-list" style="width:28px;height:28px;color:#0A574F;"></i>
                        Registration Queue
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="user-plus" style="width:14px;height:14px;color:#0A574F;"></i>
                        Approve or reject pending registrations
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#D97706] flex items-center gap-1 border border-[#D97706] px-3 py-1 rounded-full bg-[#FEF3C7]">
                        <i data-lucide="clock" style="width:12px;height:12px;"></i>
                        {{ $pendingUsers->count() }} pending
                    </span>
                    <button class="bg-[#F9F9F9] border border-[#E5E5E5] px-3 py-1.5 text-xs rounded-lg hover:border-[#0A574F] hover:bg-white transition flex items-center gap-1">
                        <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-6">

            {{-- Pending Users --}}
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                <div class="border-b border-[#E5E5E5] px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="clock" style="width:18px;height:18px;color:#D97706;"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#D97706]">Pending Registrations</h3>
                    </div>
                    <span class="text-[10px] text-[#D97706] bg-[#FEF3C7] px-2 py-0.5 rounded-full">{{ $pendingUsers->count() }} pending</span>
                </div>
                <div class="divide-y divide-[#F5F5F5]">
                    @forelse($pendingUsers as $user)
                        <div class="px-5 py-4 flex items-center justify-between hover:bg-[#F9F9F9] transition">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-9 h-9 bg-[#FEF3C7] rounded-full flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="user" style="width:14px;height:14px;color:#D97706;"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center flex-wrap gap-2">
                                        <span class="text-sm font-bold text-[#000000]">{{ $user->name }}</span>
                                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-2 py-0.5 rounded-full">
                                            <i data-lucide="clock" style="width:8px;height:8px;"></i>
                                            Pending
                                        </span>
                                    </div>
                                    <p class="text-xs text-[#666666] flex items-center gap-1">
                                        <i data-lucide="mail" style="width:12px;height:12px;color:#999;"></i>
                                        {{ $user->email }}
                                    </p>
                                    <div class="flex items-center gap-3 mt-0.5 text-[10px] text-[#666666]">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="calendar" style="width:10px;height:10px;"></i>
                                            Registered: {{ $user->created_at->diffForHumans() }}
                                        </span>
                                        <span>•</span>
                                        <span>{{ $user->created_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                                <form action="{{ route('admin.registration.approve', $user->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="flex items-center gap-1 text-xs font-bold uppercase tracking-wider bg-[#16A34A] text-white px-3 py-1.5 rounded-lg hover:bg-[#15803D] transition">
                                        <i data-lucide="check" style="width:12px;height:12px;"></i>
                                        Approve
                                    </button>
                                </form>
                                <form action="{{ route('admin.registration.reject', $user->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Reject this registration? This will permanently delete the user.')"
                                            class="flex items-center gap-1 text-xs font-bold uppercase tracking-wider bg-[#DC2626] text-white px-3 py-1.5 rounded-lg hover:bg-[#B91C1C] transition">
                                        <i data-lucide="x" style="width:12px;height:12px;"></i>
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <i data-lucide="check-circle" style="width:48px;height:48px;color:#16A34A;margin:0 auto 0.75rem;display:block;"></i>
                            <p class="text-sm font-medium text-[#16A34A]">No pending registrations</p>
                            <p class="text-xs text-[#666666] mt-1">All users have been processed.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recently Approved --}}
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                <div class="border-b border-[#E5E5E5] px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="check-circle" style="width:18px;height:18px;color:#16A34A;"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#16A34A]">Recently Approved</h3>
                    </div>
                    <span class="text-[10px] text-[#16A34A] bg-[#F0FDF4] px-2 py-0.5 rounded-full">{{ $approvedUsers->count() }} approved</span>
                </div>
                <div class="divide-y divide-[#F5F5F5]">
                    @forelse($approvedUsers as $user)
                        <div class="px-5 py-4 flex items-center justify-between hover:bg-[#F9F9F9] transition">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-9 h-9 bg-[#F0FDF4] rounded-full flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="user-check" style="width:14px;height:14px;color:#16A34A;"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center flex-wrap gap-2">
                                        <span class="text-sm font-bold text-[#000000]">{{ $user->name }}</span>
                                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-2 py-0.5 rounded-full">
                                            <i data-lucide="check-circle" style="width:8px;height:8px;"></i>
                                            Active
                                        </span>
                                    </div>
                                    <p class="text-xs text-[#666666] flex items-center gap-1">
                                        <i data-lucide="mail" style="width:12px;height:12px;color:#999;"></i>
                                        {{ $user->email }}
                                    </p>
                                    <div class="flex items-center gap-3 mt-0.5 text-[10px] text-[#666666]">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="calendar" style="width:10px;height:10px;"></i>
                                            Joined: {{ $user->created_at->format('M d, Y') }}
                                        </span>
                                        <span>•</span>
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="folder" style="width:10px;height:10px;"></i>
                                            {{ $user->groups->count() }} groups
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] text-[#16A34A] font-bold flex items-center gap-1 flex-shrink-0 ml-4">
                                <i data-lucide="check-circle" style="width:12px;height:12px;"></i>
                                Approved
                            </span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <i data-lucide="users" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                            <p class="text-sm text-[#666666]">No approved users yet.</p>
                            <p class="text-xs text-[#94A3B8]">Approve pending users to see them here.</p>
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