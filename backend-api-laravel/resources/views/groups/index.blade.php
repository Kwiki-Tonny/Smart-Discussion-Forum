@extends('layouts.workspace')

@section('title', 'Groups Workspace Directory')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center justify-between bg-white sticky top-0">
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">All Groups</h2>
        <button class="text-xl font-bold hover:opacity-60 leading-none">+</button>
    </div>

    <div class="divide-y divide-[#E5E5E5]">
        <div class="p-4 bg-white hover:bg-[#F5F5F5] cursor-pointer transition-colors space-y-2">
            <div class="flex justify-between items-baseline">
                <h3 class="text-sm font-bold text-[#000000]">Product Team</h3>
                <span class="text-[10px] text-[#666666]">10:42 AM</span>
            </div>
            <p class="text-xs text-[#666666] line-clamp-1">Sarah: The new feature specs are ready f...</p>
            <div class="flex space-x-1.5 pt-1">
                <span class="text-[9px] font-bold border border-[#000000] px-1.5 py-0.5 tracking-tight uppercase">Q3 Roadmap</span>
                <span class="text-[9px] font-bold border border-[#000000] px-1.5 py-0.5 tracking-tight uppercase">Design</span>
            </div>
        </div>

        <div class="p-4 bg-white hover:bg-[#F5F5F5] cursor-pointer transition-colors space-y-2">
            <div class="flex justify-between items-baseline">
                <h3 class="text-sm font-bold text-[#000000]">Marketing</h3>
                <span class="text-[10px] text-[#666666]">Yesterday</span>
            </div>
            <p class="text-xs text-[#666666] line-clamp-1">Campaigns finalized. Awaiting final ...</p>
        </div>

        <a href="#" class="block p-4 bg-white hover:bg-[#F5F5F5] cursor-pointer transition-colors space-y-2 border-l-2 border-black">
            <div class="flex justify-between items-baseline">
                <h3 class="text-sm font-bold text-[#000000]">Engineering</h3>
                <span class="text-[10px] text-[#666666]">Tuesday</span>
            </div>
            <p class="text-xs text-[#666666] line-clamp-1">Deploy scheduled for tonight at 23:00 UT...</p>
            <div class="flex space-x-1.5 pt-1">
                <span class="text-[9px] font-bold border border-[#000000] px-1.5 py-0.5 tracking-tight uppercase">DevOps</span>
            </div>
        </a>
    </div>
@endsection

@section('content')
    <div class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center">
        <div class="text-5xl mb-4">💬</div>
        <h2 class="text-lg font-bold uppercase tracking-wide text-[#000000] mb-2">Select a Group</h2>
        <p class="text-sm text-[#666666] max-w-sm leading-relaxed mb-6">
            Choose a discussion group from the sidebar directory to view active threads, participate in conversations, and manage workspace configurations.
        </p>
        <button class="bg-white border border-[#000000] px-5 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#F5F5F5] active:bg-[#E5E5E5] transition-colors shadow-sm">
            Create New Group
        </button>
    </div>
@endsection