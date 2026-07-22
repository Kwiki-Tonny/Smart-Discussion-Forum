@extends('layouts.workspace')

@section('title', 'Lecturer Profile')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Profile</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Profile Card --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-[#0A574F] text-white flex items-center justify-center text-2xl font-bold uppercase rounded-lg">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <h3 class="text-lg font-bold text-[#000000]">{{ $user->name }}</h3>
                <p class="text-sm text-[#666666] flex items-center gap-1">
                    <i data-lucide="mail" style="width:14px;height:14px;"></i>
                    {{ $user->email }}
                </p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-2 py-0.5 rounded-full">{{ $user->role }}</span>
                    <span class="text-[8px] font-bold uppercase tracking-wider text-[#16A34A] border border-[#16A34A] px-2 py-0.5 rounded-full flex items-center gap-1">
                        <i data-lucide="circle" style="width:6px;height:6px;fill:#16A34A;color:#16A34A;"></i>
                        Active
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#0A574F]">{{ $totalGroups }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Groups Managed</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#2563EB]">{{ $totalStudents }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Total Students</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#D97706]">{{ $totalTopics }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Topics</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#0A574F]">{{ $totalPosts }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Posts</p>
            </div>
        </div>
    </div>

    {{-- Your Groups --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-3">
        <div class="bg-white border border-[#E5E5E5] rounded-lg p-3">
            <div class="flex items-center gap-2 border-b border-[#E5E5E5] pb-2 mb-2">
                <i data-lucide="folder" style="width:14px;height:14px;color:#0A574F;"></i>
                <h4 class="text-xs font-bold uppercase tracking-wider text-[#000000]">Your Groups</h4>
                <span class="text-[10px] text-[#666666] bg-[#F9F9F9] px-1.5 py-0.5 rounded-full">{{ $groups->count() }}</span>
            </div>
            @forelse($groups as $group)
                <a href="{{ route('groups.topics', $group->id) }}"
                   class="block text-sm text-[#000000] hover:bg-[#F9F9F9] rounded-lg p-2 -mx-2 transition flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <i data-lucide="folder" style="width:12px;height:12px;color:#0A574F;flex-shrink:0;"></i>
                        <span class="truncate">{{ $group->name }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-[10px] text-[#666666] flex-shrink-0">
                        <span class="flex items-center gap-1">
                            <i data-lucide="message-circle" style="width:10px;height:10px;"></i>
                            {{ $group->topics_count }}
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="users" style="width:10px;height:10px;"></i>
                            {{ $group->users_count }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="text-center py-4">
                    <i data-lucide="folder-open" style="width:24px;height:24px;color:#94A3B8;margin:0 auto 0.25rem;display:block;"></i>
                    <p class="text-sm text-[#666666]">No groups yet</p>
                    <p class="text-xs text-[#94A3B8]">Create your first group to get started</p>
                </div>
            @endforelse
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
                        <i data-lucide="user-circle" style="width:28px;height:28px;color:#0A574F;"></i>
                        Lecturer Profile
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="bar-chart-2" style="width:14px;height:14px;color:#0A574F;"></i>
                        Overview of your teaching statistics
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4]">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        {{ $user->status }}
                    </span>
                    <button class="bg-[#F9F9F9] border border-[#E5E5E5] px-3 py-1.5 text-xs rounded-lg hover:border-[#0A574F] hover:bg-white transition flex items-center gap-1">
                        <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        {{-- Account Details --}}
        <div class="flex-1 p-6 overflow-y-auto">
            <div class="bg-white rounded-lg border-2 border-[#0A574F] shadow-sm p-6 max-w-2xl">
                <div class="flex items-center gap-2 border-b border-[#E5E5E5] pb-3 mb-4">
                    <i data-lucide="settings" style="width:20px;height:20px;color:#0A574F;"></i>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Account Details</h2>
                </div>
                <dl class="grid grid-cols-1 gap-2">
                    <div class="flex justify-between items-center border-b border-[#E5E5E5] py-2">
                        <dt class="text-xs text-[#666666] flex items-center gap-1">
                            <i data-lucide="user" style="width:12px;height:12px;color:#0A574F;"></i>
                            Name
                        </dt>
                        <dd class="text-sm text-[#000000] font-bold">{{ $user->name }}</dd>
                    </div>
                    <div class="flex justify-between items-center border-b border-[#E5E5E5] py-2">
                        <dt class="text-xs text-[#666666] flex items-center gap-1">
                            <i data-lucide="mail" style="width:12px;height:12px;color:#0A574F;"></i>
                            Email
                        </dt>
                        <dd class="text-sm text-[#000000] font-bold">{{ $user->email }}</dd>
                    </div>
                    <div class="flex justify-between items-center border-b border-[#E5E5E5] py-2">
                        <dt class="text-xs text-[#666666] flex items-center gap-1">
                            <i data-lucide="badge" style="width:12px;height:12px;color:#0A574F;"></i>
                            Role
                        </dt>
                        <dd class="text-sm text-[#000000] font-bold">{{ ucfirst($user->role) }}</dd>
                    </div>
                    <div class="flex justify-between items-center border-b border-[#E5E5E5] py-2">
                        <dt class="text-xs text-[#666666] flex items-center gap-1">
                            <i data-lucide="circle" style="width:12px;height:12px;color:#0A574F;"></i>
                            Status
                        </dt>
                        <dd class="text-sm text-[#16A34A] font-bold flex items-center gap-1">
                            <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                            {{ ucfirst($user->status) }}
                        </dd>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <dt class="text-xs text-[#666666] flex items-center gap-1">
                            <i data-lucide="calendar" style="width:12px;height:12px;color:#0A574F;"></i>
                            Member Since
                        </dt>
                        <dd class="text-sm text-[#000000] font-bold">{{ $user->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        lucide.createIcons();
    </script>
@endpush