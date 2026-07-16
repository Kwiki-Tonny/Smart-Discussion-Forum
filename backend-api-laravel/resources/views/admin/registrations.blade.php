@extends('layouts.workspace')

@section('title', 'Registration Queue')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('admin.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Registrations</h2>
    </div>
    <div class="p-3 bg-[#FAFAFA] border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-2 text-center">
            <div>
                <p class="text-lg font-bold text-[#D97706]">{{ $pendingUsers->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Pending</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#16A34A]">{{ $approvedUsers->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Approved</p>
            </div>
        </div>
    </div>
    {{-- Quick Filter/Search --}}
    <div class="p-2 bg-white border-b border-[#E5E5E5]">
        <form method="GET" action="{{ route('admin.registrations') }}" class="flex items-center space-x-2">
            <input type="text" name="search" placeholder="Search users..." value="{{ request('search') }}"
                   class="flex-1 bg-white border border-[#E5E5E5] px-3 py-1.5 text-sm focus:outline-none focus:border-[#000000] transition-colors">
            <button type="submit" class="bg-[#000000] text-white px-3 py-1.5 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('admin.registrations') }}" class="text-xs text-[#666666] hover:text-[#000000]">Clear</a>
            @endif
        </form>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-[#000000]">Registration Queue</h1>
                    <p class="text-sm text-[#666666] mt-1">Approve or reject pending registrations</p>
                </div>
                <span class="text-xs text-[#D97706] border border-[#D97706] px-2 py-1">
                    {{ $pendingUsers->count() }} pending
                </span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-6">
            {{-- Pending Users --}}
            <div class="bg-white border border-[#E5E5E5]">
                <div class="border-b border-[#E5E5E5] px-4 py-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#D97706]">Pending Registrations</h3>
                    <span class="text-[10px] text-[#666666]">{{ $pendingUsers->count() }} pending</span>
                </div>
                <div class="divide-y divide-[#E5E5E5]">
                    @forelse($pendingUsers as $user)
                        <div class="px-4 py-3 flex items-center justify-between hover:bg-[#F5F5F5] transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm font-bold text-[#000000]">{{ $user->name }}</span>
                                    <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-1.5 py-0.5">
                                        Pending
                                    </span>
                                </div>
                                <p class="text-xs text-[#666666]">{{ $user->email }}</p>
                                <div class="flex items-center space-x-3 mt-0.5">
                                    <span class="text-[10px] text-[#666666]">Registered: {{ $user->created_at->diffForHumans() }}</span>
                                    <span class="text-[10px] text-[#666666]">•</span>
                                    <span class="text-[10px] text-[#666666]">{{ $user->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 flex-shrink-0 ml-4">
                                <form action="{{ route('admin.registration.approve', $user->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs font-bold uppercase tracking-wider bg-[#16A34A] text-white px-3 py-1.5 hover:bg-[#15803D] transition-colors">
                                        Approve
                                    </button>
                                </form>
                                <form action="{{ route('admin.registration.reject', $user->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Reject this registration? This will permanently delete the user.')"
                                            class="text-xs font-bold uppercase tracking-wider bg-[#DC2626] text-white px-3 py-1.5 hover:bg-[#B91C1C] transition-colors">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center">
                            <p class="text-sm text-[#16A34A]">✅ No pending registrations</p>
                            <p class="text-xs text-[#666666] mt-1">All users have been processed.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recently Approved --}}
            <div class="bg-white border border-[#E5E5E5]">
                <div class="border-b border-[#E5E5E5] px-4 py-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#16A34A]">Recently Approved</h3>
                    <span class="text-[10px] text-[#666666]">{{ $approvedUsers->count() }} approved</span>
                </div>
                <div class="divide-y divide-[#E5E5E5]">
                    @forelse($approvedUsers as $user)
                        <div class="px-4 py-3 flex items-center justify-between hover:bg-[#F5F5F5] transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm font-bold text-[#000000]">{{ $user->name }}</span>
                                    <span class="text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-1.5 py-0.5">
                                        Active
                                    </span>
                                </div>
                                <p class="text-xs text-[#666666]">{{ $user->email }}</p>
                                <div class="flex items-center space-x-3 mt-0.5">
                                    <span class="text-[10px] text-[#666666]">Joined: {{ $user->created_at->format('M d, Y') }}</span>
                                    <span class="text-[10px] text-[#666666]">•</span>
                                    <span class="text-[10px] text-[#666666]">{{ $user->groups->count() }} groups</span>
                                </div>
                            </div>
                            <span class="text-[10px] text-[#16A34A] flex-shrink-0 ml-4">✅ Approved</span>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center">
                            <p class="text-sm text-[#666666]">No approved users yet.</p>
                            <p class="text-xs text-[#666666] mt-1">Approve pending users to see them here.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection