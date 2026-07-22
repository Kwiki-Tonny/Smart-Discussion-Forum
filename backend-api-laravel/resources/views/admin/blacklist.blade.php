@extends('layouts.workspace')

@section('title', 'Blacklist Management')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('admin.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            ←
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Blacklist</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Stats Boxes --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#DC2626] transition-all">
                <p class="text-xl font-bold text-[#DC2626]">{{ $blacklisted->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Blacklisted</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#666666]">{{ $logs->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Total Logs</p>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-3">
                        {{-- ONLY ICON LEFT --}}
                        <i data-lucide="ban" style="width:28px;height:28px;color:#DC2626;"></i>
                        Blacklist Management
                    </h1>
                    <p class="text-sm text-[#666666] mt-1">Monitor and manage blacklisted users</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#DC2626] flex items-center gap-1 border border-[#DC2626] px-3 py-1 rounded-full bg-[#FEF2F2]">
                        <span class="inline-block w-1.5 h-1.5 bg-[#DC2626] rounded-full mr-1"></span>
                        {{ $blacklisted->count() }} blacklisted
                    </span>
                    <button class="bg-[#F9F9F9] border border-[#E5E5E5] px-3 py-1.5 text-xs rounded-lg hover:border-[#0A574F] hover:bg-white transition">
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-6">

            {{-- Manual Blacklist Form --}}
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#000000] mb-4">Manual Blacklist</h3>
                <form action="{{ route('admin.blacklist.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    @csrf
                    <div>
                        <select name="user_id" required class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                            <option value="">Select User</option>
                            @foreach(\App\Models\User::where('status', '!=', 'blacklisted')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="text" name="reason" placeholder="Reason..." required
                               class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                    </div>
                    <div>
                        <input type="number" name="duration" placeholder="Days" value="14" min="1" max="365"
                               class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                    </div>
                    <button type="submit"
                            class="bg-[#DC2626] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#B91C1C] transition-colors hover:shadow-sm">
                        Blacklist User
                    </button>
                </form>
            </div>

            {{-- Current Blacklisted Users --}}
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                <div class="border-b border-[#E5E5E5] px-5 py-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#DC2626]">Current Blacklisted Users</h3>
                    <span class="text-[10px] text-[#DC2626] bg-[#FEF2F2] px-2 py-0.5 rounded-full">{{ $blacklisted->count() }} users</span>
                </div>
                <div class="divide-y divide-[#F5F5F5]">
                    @forelse($blacklisted as $user)
                        <div class="px-5 py-4 flex items-center justify-between hover:bg-[#F9F9F9] transition">
                            <div>
                                <span class="text-sm font-bold text-[#000000]">{{ $user->name }}</span>
                                <p class="text-xs text-[#666666]">{{ $user->email }}</p>
                                @if($user->blacklist_expires_at)
                                    <span class="text-[10px] text-[#666666] mt-0.5 block">Expires: {{ $user->blacklist_expires_at->format('M d, Y') }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <form action="{{ route('admin.blacklist.remove', $user->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs font-bold uppercase tracking-wider bg-[#16A34A] text-white px-3 py-1 rounded-lg hover:bg-[#15803D] transition">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <p class="text-sm font-medium text-[#16A34A]">✅ No blacklisted users</p>
                            <p class="text-xs text-[#666666] mt-1">All users are in good standing.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Blacklist Logs --}}
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm">
                <div class="border-b border-[#E5E5E5] px-5 py-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666]">Blacklist Logs</h3>
                    <span class="text-[10px] text-[#666666] bg-[#F9F9F9] px-2 py-0.5 rounded-full">{{ $logs->count() }} logs</span>
                </div>
                <div class="divide-y divide-[#F5F5F5] max-h-64 overflow-y-auto">
                    @forelse($logs as $log)
                        <div class="px-5 py-3 flex items-center justify-between hover:bg-[#F9F9F9] transition">
                            <div>
                                <span class="text-sm font-bold text-[#000000]">{{ $log->user->name ?? 'Unknown' }}</span>
                                <span class="text-xs text-[#666666] ml-2">{{ $log->reason }}</span>
                            </div>
                            <span class="text-[10px] text-[#666666] flex-shrink-0 ml-4">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center">
                            <p class="text-sm text-[#666666]">No blacklist logs.</p>
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