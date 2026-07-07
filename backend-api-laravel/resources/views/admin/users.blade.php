@extends('layouts.workspace')

@section('title', 'User Management')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('admin.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Users</h2>
    </div>

    <div class="p-3 bg-[#FAFAFA] border-b border-[#E5E5E5]">
        <div class="grid grid-cols-3 gap-1 text-center text-[10px]">
            <div>
                <p class="font-bold text-[#000000]">{{ $stats['total'] }}</p>
                <p class="text-[#666666]">Total</p>
            </div>
            <div>
                <p class="font-bold text-[#000000]">{{ $stats['active'] }}</p>
                <p class="text-[#666666]">Active</p>
            </div>
            <div>
                <p class="font-bold text-[#DC2626]">{{ $stats['blacklisted'] }}</p>
                <p class="text-[#666666]">Blacklisted</p>
            </div>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1">Filters</p>
        <div class="space-y-1">
            <a href="{{ route('admin.users') }}" class="block px-3 py-1 text-xs text-[#666666] hover:bg-[#F5F5F5]">All Users</a>
            <a href="{{ route('admin.users', ['role' => 'student']) }}" class="block px-3 py-1 text-xs text-[#666666] hover:bg-[#F5F5F5]">Students</a>
            <a href="{{ route('admin.users', ['role' => 'lecturer']) }}" class="block px-3 py-1 text-xs text-[#666666] hover:bg-[#F5F5F5]">Lecturers</a>
            <a href="{{ route('admin.users', ['status' => 'blacklisted']) }}" class="block px-3 py-1 text-xs text-[#DC2626] hover:bg-[#FEF2F2]">Blacklisted</a>
        </div>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-[#000000]">User Management</h1>
                    <p class="text-sm text-[#666666] mt-1">Manage all registered users</p>
                </div>
                <form method="GET" action="{{ route('admin.users') }}" class="flex items-center space-x-2">
                    <input type="text" name="search" placeholder="Search users..." value="{{ request('search') }}"
                           class="bg-white border border-[#E5E5E5] px-3 py-1.5 text-sm focus:outline-none focus:border-[#000000] transition-colors">
                    <button type="submit" class="bg-[#000000] text-white px-3 py-1.5 text-xs font-bold uppercase tracking-wider hover:bg-[#333333]">Search</button>
                </form>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="bg-white border border-[#E5E5E5]">
                <div class="border-b border-[#E5E5E5] px-4 py-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666]">All Users</h3>
                    <span class="text-[10px] text-[#666666]">{{ $users->total() }} users</span>
                </div>
                <div class="divide-y divide-[#E5E5E5]">
                    @forelse($users as $user)
                        <div class="px-4 py-3 flex items-center justify-between hover:bg-[#F5F5F5] transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm font-bold text-[#000000]">{{ $user->name }}</span>
                                    <span class="text-[8px] font-bold uppercase tracking-wider border border-[#E5E5E5] px-1.5 py-0.5">{{ $user->role }}</span>
                                    @if($user->status === 'blacklisted')
                                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#DC2626] border border-[#DC2626] px-1.5 py-0.5">Blacklisted</span>
                                    @elseif($user->status === 'active')
                                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-1.5 py-0.5">Active</span>
                                    @else
                                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-1.5 py-0.5">{{ str_replace('_', ' ', $user->status) }}</span>
                                    @endif
                                </div>
                                <p class="text-xs text-[#666666]">{{ $user->email }}</p>
                                <div class="flex items-center space-x-3 mt-0.5">
                                    <span class="text-[10px] text-[#666666]">Joined: {{ $user->created_at->format('M d, Y') }}</span>
                                    <span class="text-[10px] text-[#666666]">•</span>
                                    <span class="text-[10px] text-[#666666]">{{ $user->groups->count() }} groups</span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 flex-shrink-0 ml-4">
                                <a href="{{ route('admin.user.edit', $user->id) }}"
                                   class="text-xs text-[#666666] border border-[#E5E5E5] px-2 py-1 hover:bg-[#F5F5F5] transition-colors">
                                    Edit
                                </a>
                                <form action="{{ route('admin.user.delete', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this user?')"
                                            class="text-xs text-[#DC2626] border border-[#DC2626] px-2 py-1 hover:bg-[#FEF2F2] transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center">
                            <p class="text-sm text-[#666666]">No users found.</p>
                        </div>
                    @endforelse
                </div>
                <div class="px-4 py-3 border-t border-[#E5E5E5]">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection