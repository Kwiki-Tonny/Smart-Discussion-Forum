@extends('layouts.workspace')

@section('title', 'Create Group')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.groups') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Create Group</h2>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">Create New Group</h1>
            <p class="text-sm text-[#666666] mt-1">You will be the admin of this group</p>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="max-w-2xl bg-white border border-[#E5E5E5] p-6">
                <form method="POST" action="{{ route('lecturer.groups.store') }}" class="space-y-5">
                    @csrf

                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Group Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('name') border-[#DC2626] @enderror"
                               placeholder="e.g., Software Engineering 2026">
                        @error('name')
                            <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('description') border-[#DC2626] @enderror"
                                  placeholder="Describe the purpose of this group...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E5E5E5]">
                        <a href="{{ route('lecturer.groups') }}"
                           class="text-xs font-bold uppercase tracking-wider text-[#666666] hover:text-[#000000] transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                                class="bg-[#000000] text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                            Create Group
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection