@extends('layouts.workspace')

@section('title', 'All Groups')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center justify-between bg-white sticky top-0">
        <div class="flex items-center gap-2">
            <i data-lucide="users" style="width:18px;height:18px;color:#0A574F;"></i>
            <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">All Groups</h2>
        </div>
        <span class="text-[10px] text-[#666666] bg-[#ECFDF5] text-[#0A574F] px-2 py-0.5 rounded-full">{{ $groups->count() ?? 0 }}</span>
    </div>

    <div class="divide-y divide-[#E5E5E5]">
        @forelse($groups ?? [] as $group)
            <a href="{{ route('groups.topics', $group->id) }}" 
               class="block p-4 bg-white hover:bg-[#F9F9F9] cursor-pointer transition-colors space-y-1 border-l-2 border-transparent hover:border-[#0A574F]">
                <div class="flex justify-between items-baseline">
                    <div class="flex items-center gap-2">
                        <i data-lucide="folder" style="width:14px;height:14px;color:#0A574F;"></i>
                        <h3 class="text-sm font-bold text-[#000000]">{{ $group->name }}</h3>
                    </div>
                    <span class="text-[10px] text-[#2563EB] border border-[#2563EB] px-2 py-0.5 rounded-full">{{ $group->topics_count ?? 0 }} topics</span>
                </div>
                @if($group->description)
                    <p class="text-xs text-[#666666] line-clamp-1 pl-6">{{ $group->description }}</p>
                @endif
                @if($group->users_count)
                    <p class="text-[10px] text-[#666666] pl-6 flex items-center gap-1">
                        <i data-lucide="users" style="width:10px;height:10px;"></i>
                        {{ $group->users_count }} members
                    </p>
                @endif
            </a>
        @empty
            <div class="p-8 text-center border border-dashed border-[#E5E5E5] m-2 rounded-lg bg-white">
                <i data-lucide="users" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                <p class="text-sm text-[#666666]">No groups available.</p>
                <p class="text-xs text-[#94A3B8]">Check back later for new groups.</p>
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
                        <i data-lucide="groups" style="width:28px;height:28px;color:#0A574F;"></i>
                        Groups Directory
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="compass" style="width:14px;height:14px;color:#0A574F;"></i>
                        Browse all available discussion groups
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4]">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        {{ $groups->count() ?? 0 }} groups
                    </span>
                    <button class="bg-[#F9F9F9] border border-[#E5E5E5] px-3 py-1.5 text-xs rounded-lg hover:border-[#0A574F] hover:bg-white transition flex items-center gap-1">
                        <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6">
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#0A574F]">{{ $groups->count() ?? 0 }}</p>
                    <p class="text-xs text-[#666666] font-medium">Total Groups</p>
                </div>
                <div class="w-10 h-10 bg-[#ECFDF5] rounded-lg flex items-center justify-center">
                    <i data-lucide="groups" style="width:20px;height:20px;color:#0A574F;"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#2563EB]">{{ $groups->sum('users_count') ?? 0 }}</p>
                    <p class="text-xs text-[#666666] font-medium">Total Members</p>
                </div>
                <div class="w-10 h-10 bg-[#E0F2FE] rounded-lg flex items-center justify-center">
                    <i data-lucide="users" style="width:20px;height:20px;color:#2563EB;"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#D97706]">{{ $groups->sum('topics_count') ?? 0 }}</p>
                    <p class="text-xs text-[#666666] font-medium">Total Topics</p>
                </div>
                <div class="w-10 h-10 bg-[#FEF3C7] rounded-lg flex items-center justify-center">
                    <i data-lucide="message-circle" style="width:20px;height:20px;color:#D97706;"></i>
                </div>
            </div>
        </div>

        {{-- Groups Grid --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($groups ?? [] as $group)
                    <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm hover:shadow-md hover:border-[#0A574F] transition p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-bold text-[#000000] truncate flex items-center gap-2">
                                    <i data-lucide="folder" style="width:16px;height:16px;color:#0A574F;"></i>
                                    {{ $group->name }}
                                </h3>
                                <p class="text-xs text-[#666666] mt-1 line-clamp-2">
                                    {{ $group->description ?? 'No description provided' }}
                                </p>
                            </div>
                            @auth
                                @if($group->isMember ?? false)
                                    <span class="text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-1.5 py-0.5 rounded-full whitespace-nowrap ml-2 flex items-center gap-1">
                                        <i data-lucide="check" style="width:8px;height:8px;"></i>
                                        Member
                                    </span>
                                @endif
                            @endauth
                        </div>

                        <div class="flex items-center gap-3 mt-2 text-[10px] text-[#666666]">
                            <span class="flex items-center gap-1">
                                <i data-lucide="message-circle" style="width:12px;height:12px;"></i>
                                {{ $group->topics_count ?? 0 }} topics
                            </span>
                            <span>•</span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="users" style="width:12px;height:12px;"></i>
                                {{ $group->users_count ?? 0 }} members
                            </span>
                        </div>

                        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-[#E5E5E5]">
                            <a href="{{ route('groups.topics', $group->id) }}"
                               class="flex-1 text-center text-[10px] font-bold uppercase tracking-wider bg-[#0A574F] text-white px-3 py-1.5 rounded-lg hover:bg-[#08443e] transition">
                                <i data-lucide="eye" style="width:12px;height:12px;display:inline;"></i> View
                            </a>
                            @auth
                                @if($group->isMember ?? false)
                                    <form action="{{ route('groups.leave', $group->id) }}" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit" 
                                                onclick="return confirm('Are you sure you want to leave this group?')"
                                                class="w-full text-center text-[10px] font-bold uppercase tracking-wider text-[#DC2626] border border-[#DC2626] px-3 py-1.5 rounded-lg hover:bg-[#DC2626] hover:text-white transition">
                                            <i data-lucide="log-out" style="width:12px;height:12px;display:inline;"></i> Leave
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('groups.join', $group->id) }}" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit" 
                                                class="w-full text-center text-[10px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-3 py-1.5 rounded-lg hover:bg-[#16A34A] hover:text-white transition">
                                            <i data-lucide="log-in" style="width:12px;height:12px;display:inline;"></i> Join
                                        </button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-lg border border-dashed border-[#E5E5E5] p-12 text-center">
                        <i data-lucide="users" style="width:48px;height:48px;color:#94A3B8;margin:0 auto 0.75rem;display:block;"></i>
                        <p class="text-sm font-medium text-[#000000]">No groups available</p>
                        <p class="text-xs text-[#666666] mt-1">Check back later for new discussion groups.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Footer Status --}}
        <div class="border-t border-[#E5E5E5] bg-white px-8 py-3 flex items-center justify-between text-[11px] text-[#666666]">
            <div class="flex items-center gap-6">
                <span class="flex items-center gap-1">
                    <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                    System Status: <span class="text-[#000000] font-medium">Online</span>
                </span>
                <span class="flex items-center gap-1">
                    <i data-lucide="database" style="width:12px;height:12px;color:#2563EB;"></i>
                    Database: <span class="text-[#000000] font-medium">Connected</span>
                </span>
            </div>
            <div class="flex items-center gap-4">
                <a href="#" class="hover:text-[#0A574F] transition">Privacy Policy</a>
                <span class="text-[#D1D5DB]">·</span>
                <a href="#" class="hover:text-[#0A574F] transition">Terms of Service</a>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        lucide.createIcons();
    </script>
@endpush