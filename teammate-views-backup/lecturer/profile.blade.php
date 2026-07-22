@extends('layouts.workspace')

@section('title', 'Lecturer Profile')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Profile</h2>
    </div>
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-[#000000] text-white flex items-center justify-center text-xl font-bold uppercase">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <h3 class="text-sm font-bold text-[#000000]">{{ $user->name }}</h3>
                <p class="text-xs text-[#666666]">{{ $user->email }}</p>
                <span class="text-[8px] font-bold uppercase tracking-wider border border-[#E5E5E5] px-1.5 py-0.5 mt-1 inline-block">{{ $user->role }}</span>
            </div>
        </div>
    </div>
    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-3">
        <div class="grid grid-cols-2 gap-2">
            <div class="bg-white border border-[#E5E5E5] p-3 text-center">
                <p class="text-lg font-bold text-[#000000]">{{ $totalGroups }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Groups Managed</p>
            </div>
            <div class="bg-white border border-[#E5E5E5] p-3 text-center">
                <p class="text-lg font-bold text-[#000000]">{{ $totalStudents }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Total Students</p>
            </div>
            <div class="bg-white border border-[#E5E5E5] p-3 text-center">
                <p class="text-lg font-bold text-[#000000]">{{ $totalTopics }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Topics</p>
            </div>
            <div class="bg-white border border-[#E5E5E5] p-3 text-center">
                <p class="text-lg font-bold text-[#000000]">{{ $totalPosts }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Posts</p>
            </div>
        </div>
        <div class="bg-white border border-[#E5E5E5] p-3">
            <h4 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-2">Your Groups</h4>
            @foreach($groups as $group)
                <a href="{{ route('groups.topics', $group->id) }}" class="block text-sm text-[#000000] hover:bg-[#F5F5F5] p-1 -mx-1">
                    {{ $group->name }} ({{ $group->topics_count }} topics, {{ $group->users_count }} members)
                </a>
            @endforeach
        </div>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">Lecturer Profile</h1>
            <p class="text-sm text-[#666666] mt-1">Overview of your teaching statistics</p>
        </div>
        <div class="flex-1 p-6 overflow-y-auto">
            <div class="bg-white border border-[#E5E5E5] p-6">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] mb-4">Account Details</h2>
                <dl class="grid grid-cols-1 gap-2">
                    <div class="flex justify-between border-b border-[#E5E5E5] py-2">
                        <dt class="text-xs text-[#666666]">Name</dt>
                        <dd class="text-sm text-[#000000] font-bold">{{ $user->name }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-[#E5E5E5] py-2">
                        <dt class="text-xs text-[#666666]">Email</dt>
                        <dd class="text-sm text-[#000000] font-bold">{{ $user->email }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-[#E5E5E5] py-2">
                        <dt class="text-xs text-[#666666]">Role</dt>
                        <dd class="text-sm text-[#000000] font-bold">{{ ucfirst($user->role) }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-[#E5E5E5] py-2">
                        <dt class="text-xs text-[#666666]">Status</dt>
                        <dd class="text-sm text-[#000000] font-bold">{{ ucfirst($user->status) }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-xs text-[#666666]">Member Since</dt>
                        <dd class="text-sm text-[#000000] font-bold">{{ $user->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endsection