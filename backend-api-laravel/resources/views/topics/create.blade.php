@extends('layouts.workspace')

@section('title', 'Create New Topic')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ url()->previous() }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Create Topic</h2>
    </div>
    <div class="p-4 space-y-1">
        <p class="text-xs text-[#666666]">Select a group and provide topic details.</p>
    </div>
@endsection

@section('content')
    <div class="flex items-center justify-center min-h-full p-6">
        <div class="w-full max-w-2xl bg-white border border-[#E5E5E5] p-8 shadow-sm">
            
            <div class="border-b border-[#000000] pb-4">
                <h1 class="text-xl font-bold uppercase tracking-tight">Create New Topic</h1>
                <p class="text-sm text-[#666666] mt-1">Start a new discussion thread</p>
            </div>

            @if(session('success'))
                <div class="mt-4 border border-[#16A34A] p-3 bg-[#F0FDF4]">
                    <p class="text-xs text-[#16A34A]">{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mt-4 border border-[#DC2626] p-3 bg-[#FEF2F2]">
                    @foreach($errors->all() as $error)
                        <p class="text-xs text-[#DC2626]">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('topics.store') }}" class="mt-6 space-y-5">
                @csrf

                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Group</label>
                    <select name="group_id" required 
                            class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('group_id') border-[#DC2626] @enderror">
                        <option value="">Select a group</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('group_id')
                        <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Topic Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" required 
                           class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('title') border-[#DC2626] @enderror">
                    @error('title')
                        <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Description (Optional)</label>
                    <textarea name="description" rows="4" 
                              class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('description') border-[#DC2626] @enderror">{{ old('description') }}</textarea>
                    <p class="text-[10px] text-[#666666]">Provide additional context for your topic (optional).</p>
                    @error('description')
                        <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E5E5E5]">
                    <a href="{{ url()->previous() }}" 
                       class="text-xs font-bold uppercase tracking-wider text-[#666666] hover:text-[#000000] transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-[#000000] text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                        Create Topic
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection