@extends('layouts.workspace')

@section('title', 'Blacklist Management')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('admin.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Blacklist</h2>
    </div>
    <div class="p-3 bg-[#FAFAFA] border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-2 text-center">
            <div>
                <p class="text-lg font-bold text-[#DC2626]">{{ $blacklisted->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Blacklisted</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#666666]">{{ $logs->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Total Logs</p>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">Blacklist Management</h1>
            <p class="text-sm text-[#666666] mt-1">Monitor and manage blacklisted users</p>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-6">
            {{-- Manual Blacklist Form --}}
            <div class="bg-white border border-[#E5E5E5] p-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#000000] mb-3">Manual Blacklist</h3>
                <form action="{{ route('admin.blacklist.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    @csrf
                    <div>
                        <select name="user_id" required class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000]">
                            <option value="">Select User</option>
                            @foreach(\App\Models\User::where('status', '!=', 'blacklisted')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="text" name="reason" placeholder="Reason..." required
                               class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000]">
                    </div>
                    <div>
                        <input type="number" name="duration" placeholder="Days" value="14" min="1" max="365"
                               class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000]">
                    </div>
                    <button type="submit"
                            class="bg-[#DC2626] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#B91C1C] transition-colors">
                        Blacklist User
                    </button>
                </form>
            </div>

            {{-- Current Blacklisted Users --}}
            <div class="bg-white border border-[#E5E5E5]">
                <div class="border-b border-[#E5E5E5] px-4 py-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#DC2626]">Current Blacklisted Users</h3>
                    <span class="text-[10px] text-[#666666]">{{ $blacklisted->count() }} users</span>
                </div>
                <div class="divide-y divide-[#E5E5E5]">
                    @forelse($blacklisted as $user)
                        <div class="px-4 py-3 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-bold text-[#000000]">{{ $user->name }}</span>
                                <p class="text-xs text-[#666666]">{{ $user->email }}</p>
                                @if($user->blacklist_expires_at)
                                    <span class="text-[10px] text-[#666666]">Expires: {{ $user->blacklist_expires_at->format('M d, Y') }}</span>
                                @endif
                            </div>
                            <div class="flex items-center space-x-2">
                                <form action="{{ route('admin.blacklist.remove', $user->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs font-bold uppercase tracking-wider bg-[#16A34A] text-white px-3 py-1 hover:bg-[#15803D] transition-colors">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center">
                            <p class="text-sm text-[#16A34A]">✅ No blacklisted users</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Blacklist Logs --}}
            <div class="bg-white border border-[#E5E5E5]">
                <div class="border-b border-[#E5E5E5] px-4 py-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666]">Blacklist Logs</h3>
                </div>
                <div class="divide-y divide-[#E5E5E5] max-h-64 overflow-y-auto">
                    @forelse($logs as $log)
                        <div class="px-4 py-2 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-bold text-[#000000]">{{ $log->user->name ?? 'Unknown' }}</span>
                                <span class="text-xs text-[#666666] ml-2">{{ $log->reason }}</span>
                            </div>
                            <span class="text-[10px] text-[#666666]">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="px-4 py-3 text-center">
                            <p class="text-sm text-[#666666]">No blacklist logs.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection