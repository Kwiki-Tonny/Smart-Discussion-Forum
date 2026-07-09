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
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">Registration Queue</h1>
            <p class="text-sm text-[#666666] mt-1">Approve or reject pending registrations</p>
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
                        <div class="px-4 py-3 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-bold text-[#000000]">{{ $user->name }}</span>
                                <p class="text-xs text-[#666666]">{{ $user->email }}</p>
                                <span class="text-[10px] text-[#666666]">Registered: {{ $user->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <form action="{{ route('admin.registration.approve', $user->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs font-bold uppercase tracking-wider bg-[#16A34A] text-white px-3 py-1 hover:bg-[#15803D] transition-colors">
                                        Approve
                                    </button>
                                </form>
                                <form action="{{ route('admin.registration.reject', $user->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Reject this registration?')"
                                            class="text-xs font-bold uppercase tracking-wider bg-[#DC2626] text-white px-3 py-1 hover:bg-[#B91C1C] transition-colors">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center">
                            <p class="text-sm text-[#16A34A]">✅ No pending registrations</p>
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
                        <div class="px-4 py-3 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-bold text-[#000000]">{{ $user->name }}</span>
                                <p class="text-xs text-[#666666]">{{ $user->email }}</p>
                                <span class="text-[10px] text-[#666666]">Joined: {{ $user->created_at->format('M d, Y') }}</span>
                            </div>
                            <span class="text-[10px] text-[#16A34A]">✅ Approved</span>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center">
                            <p class="text-sm text-[#666666]">No approved users yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection