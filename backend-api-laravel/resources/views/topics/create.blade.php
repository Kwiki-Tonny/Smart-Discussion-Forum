@extends('layouts.workspace')

@section('title', 'Create New Topic')


@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ url()->previous() }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Create Topic</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="flex items-center gap-2 text-xs text-[#666666]">
            <i data-lucide="info" style="width:14px;height:14px;color:#0A574F;"></i>
            <span>Select a group and provide topic details.</span>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-4">
        {{-- Tips --}}
        <div class="bg-[#F9F9F9] rounded-lg p-4 border border-[#E5E5E5]">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="lightbulb" style="width:16px;height:16px;color:#D97706;"></i>
                <h3 class="text-[10px] font-bold uppercase tracking-wider text-[#000000]">Tips</h3>
            </div>
            <ul class="space-y-1 text-[10px] text-[#666666]">
                <li class="flex items-center gap-2">✓ Use a clear title</li>
                <li class="flex items-center gap-2">✓ Add helpful description</li>
                <li class="flex items-center gap-2">✓ Pick the right group</li>
            </ul>
        </div>

        {{-- Stats --}}
        <div class="bg-[#F9F9F9] rounded-lg p-4 border border-[#E5E5E5]">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="bar-chart-2" style="width:16px;height:16px;color:#0A574F;"></i>
                <h3 class="text-[10px] font-bold uppercase tracking-wider text-[#000000]">Your Stats</h3>
            </div>
            <div class="grid grid-cols-2 gap-2 text-center">
                <div>
                    <p class="text-lg font-bold text-[#0A574F]">{{ Auth::user()->topics_count ?? 0 }}</p>
                    <p class="text-[8px] text-[#666666]">Topics</p>
                </div>
                <div>
                    <p class="text-lg font-bold text-[#2563EB]">{{ Auth::user()->groups_count ?? 0 }}</p>
                    <p class="text-[8px] text-[#666666]">Groups</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="flex items-center justify-center min-h-full p-6 bg-[#F9F9F9]">
        <div class="w-full max-w-2xl bg-white rounded-xl border-2 border-[#0A574F] shadow-sm p-8">

            {{-- Header with colored icons --}}
            <div class="border-b border-[#E5E5E5] pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-[#ECFDF5] rounded-lg flex items-center justify-center">
                        <i data-lucide="edit" style="width:24px;height:24px;color:#0A574F;"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold uppercase tracking-tight text-[#000000]">Create New Topic</h1>
                        <p class="text-sm text-[#666666] flex items-center gap-1">
                            <i data-lucide="message-circle" style="width:14px;height:14px;color:#0A574F;"></i>
                            Start a new discussion thread
                        </p>
                    </div>
                </div>
            </div>

            {{-- Success/Error Messages --}}
            @if(session('success'))
                <div class="mt-4 border border-[#16A34A] p-3 bg-[#F0FDF4] rounded-lg flex items-start gap-2">
                    <i data-lucide="check-circle" style="width:16px;height:16px;color:#16A34A;flex-shrink:0;margin-top:1px;"></i>
                    <p class="text-xs text-[#16A34A]">{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mt-4 border border-[#DC2626] p-3 bg-[#FEF2F2] rounded-lg flex items-start gap-2">
                    <i data-lucide="alert-circle" style="width:16px;height:16px;color:#DC2626;flex-shrink:0;margin-top:1px;"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <p class="text-xs text-[#DC2626]">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('topics.store') }}" class="mt-6 space-y-6">

                {{-- Step 1: Group --}}
                <div class="relative">
                    <div class="absolute -left-4 top-0 w-1 h-full bg-[#0A574F] rounded-full"></div>
                    <div class="pl-6 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-[#0A574F] text-white text-xs font-bold rounded-full">1</span>
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                                <i data-lucide="users" style="width:14px;height:14px;color:#0A574F;"></i>
                                Group
                                <span class="text-[10px] font-normal text-[#DC2626]">*</span>
                            </label>
                        </div>
                        <select name="group_id" required
                                class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition @error('group_id') border-[#DC2626] @enderror">
                            <option value="">Select a group</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('group_id')
                            <p class="text-[10px] text-[#DC2626] flex items-center gap-1">
                                <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- Step 2: Title --}}
                <div class="relative">
                    <div class="absolute -left-4 top-0 w-1 h-full bg-[#2563EB] rounded-full"></div>
                    <div class="pl-6 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-[#2563EB] text-white text-xs font-bold rounded-full">2</span>
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                                <i data-lucide="edit" style="width:14px;height:14px;color:#2563EB;"></i>
                                Topic Title
                                <span class="text-[10px] font-normal text-[#DC2626]">*</span>
                            </label>
                        </div>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 outline-none transition @error('title') border-[#DC2626] @enderror"
                               placeholder="Enter a descriptive title...">
                        @error('title')
                            <p class="text-[10px] text-[#DC2626] flex items-center gap-1">
                                <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- Step 3: Description --}}
                <div class="relative">
                    <div class="absolute -left-4 top-0 w-1 h-full bg-[#D97706] rounded-full"></div>
                    <div class="pl-6 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-[#D97706] text-white text-xs font-bold rounded-full">3</span>
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                                <i data-lucide="file-text" style="width:14px;height:14px;color:#D97706;"></i>
                                Description
                                <span class="text-[10px] font-normal text-[#666666]">(Optional)</span>
                            </label>
                        </div>
                        <textarea name="description" rows="4"
                                  class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#D97706] focus:ring-2 focus:ring-[#D97706]/20 outline-none transition @error('description') border-[#DC2626] @enderror"
                                  placeholder="Provide additional context for your topic...">{{ old('description') }}</textarea>
                        <p class="text-[9px] text-[#666666] flex items-center gap-1">
                            <i data-lucide="info" style="width:10px;height:10px;"></i>
                            Provide additional context for your topic (optional).
                        </p>
                        @error('description')
                            <p class="text-[10px] text-[#DC2626] flex items-center gap-1">
                                <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E5E5E5]">
                    <a href="{{ url()->previous() }}"
                       class="text-xs font-bold uppercase tracking-wider text-[#666666] hover:text-[#000000] px-4 py-2 rounded-lg hover:bg-[#F9F9F9] transition">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex items-center gap-2 bg-[#0A574F] text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition hover:shadow-sm">
                        <i data-lucide="plus-circle" style="width:16px;height:16px;"></i>
                        Create Topic
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        lucide.createIcons();
    </script>
@endpush