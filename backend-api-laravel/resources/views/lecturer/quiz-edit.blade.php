@extends('layouts.workspace')

@section('title', 'Create Quiz')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.quizzes') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Create Quiz</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-3">
                        <i data-lucide="clipboard-list" style="width:28px;height:28px;color:#0A574F;"></i>
                        Create New Quiz
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="settings" style="width:14px;height:14px;color:#0A574F;"></i>
                        Set up a timed quiz for your students
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4]">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        Draft
                    </span>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="max-w-3xl space-y-6">

                {{-- Tips Card --}}
                <div class="bg-white rounded-lg border-2 border-[#0A574F] shadow-sm p-6">
                    <div class="flex items-center gap-2 border-b border-[#E5E5E5] pb-3 mb-4">
                        <i data-lucide="lightbulb" style="width:20px;height:20px;color:#D97706;"></i>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Tips</h3>
                    </div>
                    <div class="space-y-2 text-sm text-[#666666]">
                        <div class="flex items-center gap-2">
                            <i data-lucide="clock" style="width:16px;height:16px;color:#0A574F;"></i>
                            <span>Quizzes are timed and auto-submit when time expires</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="monitor" style="width:16px;height:16px;color:#0A574F;"></i>
                            <span>Students must use the desktop app to take quizzes</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="lock" style="width:16px;height:16px;color:#0A574F;"></i>
                            <span>Results are locked and hidden until the quiz ends</span>
                        </div>
                    </div>
                </div>

                {{-- Form Card --}}
                <form method="POST" action="{{ route('lecturer.quiz.store') }}" class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm p-6">
                    @csrf

                    {{-- Group Selection --}}
                    <div class="space-y-1 mb-4">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                            <i data-lucide="users" style="width:14px;height:14px;color:#0A574F;"></i>
                            Target Group
                        </label>
                        <select name="group_id" required
                                class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
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

                    {{-- Quiz Title --}}
                    <div class="space-y-1 mb-4">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                            <i data-lucide="edit" style="width:14px;height:14px;color:#0A574F;"></i>
                            Quiz Title
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition"
                               placeholder="e.g., Mid-Term Assessment">
                        @error('title')
                            <p class="text-[10px] text-[#DC2626] flex items-center gap-1">
                                <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="space-y-1 mb-4">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                            <i data-lucide="file-text" style="width:14px;height:14px;color:#0A574F;"></i>
                            Description (Optional)
                        </label>
                        <textarea name="description" rows="2"
                                  class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition"
                                  placeholder="Brief description of the quiz...">{{ old('description') }}</textarea>
                    </div>

                    {{-- Duration --}}
                    <div class="space-y-1 mb-4">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                            <i data-lucide="clock" style="width:14px;height:14px;color:#0A574F;"></i>
                            Duration (Minutes)
                        </label>
                        <input type="number" name="duration" value="{{ old('duration', 30) }}" required min="1" max="180"
                               class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                        @error('duration')
                            <p class="text-[10px] text-[#DC2626] flex items-center gap-1">
                                <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Starts At --}}
                    <div class="space-y-1 mb-4">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                            <i data-lucide="calendar" style="width:14px;height:14px;color:#0A574F;"></i>
                            Start Date & Time (Optional)
                        </label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"
                               class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                        <p class="text-[9px] text-[#666666] flex items-center gap-1">
                            <i data-lucide="info" style="width:10px;height:10px;"></i>
                            Leave blank to activate immediately upon saving
                        </p>
                    </div>

                    {{-- Ends At --}}
                    <div class="space-y-1 mb-4">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                            <i data-lucide="calendar" style="width:14px;height:14px;color:#0A574F;"></i>
                            End Date & Time (Optional)
                        </label>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"
                               class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                        <p class="text-[9px] text-[#666666] flex items-center gap-1">
                            <i data-lucide="info" style="width:10px;height:10px;"></i>
                            Leave blank to never expire
                        </p>
                    </div>

                    {{-- Student Categories --}}
                    <div class="space-y-2 mb-6">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                            <i data-lucide="users" style="width:14px;height:14px;color:#0A574F;"></i>
                            Allowed Student Categories
                        </label>
                        <div class="bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg p-3 space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-[#000000]">
                                <input type="checkbox" name="allow_active" value="1" checked
                                       class="accent-[#0A574F] w-4 h-4 rounded">
                                Active
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-[#666666]">
                                <input type="checkbox" name="allow_warned_one" value="1"
                                       class="accent-[#0A574F] w-4 h-4 rounded">
                                Warned once
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-[#666666]">
                                <input type="checkbox" name="allow_warned_two" value="1"
                                       class="accent-[#0A574F] w-4 h-4 rounded">
                                Warned twice
                            </label>
                        </div>
                        <p class="text-[9px] text-[#666666] flex items-center gap-1">
                            <i data-lucide="info" style="width:10px;height:10px;"></i>
                            Only students with these statuses can take the quiz
                        </p>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E5E5E5]">
                        <a href="{{ route('lecturer.quizzes') }}"
                           class="text-xs font-bold uppercase tracking-wider text-[#666666] hover:text-[#000000] px-4 py-2 rounded-lg hover:bg-[#F9F9F9] transition">
                            Cancel
                        </a>
                        <button type="submit"
                                class="flex items-center gap-2 bg-[#0A574F] text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition hover:shadow-sm">
                            <i data-lucide="plus-circle" style="width:16px;height:16px;"></i>
                            Create Quiz
                        </button>
                    </div>
                </form>
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