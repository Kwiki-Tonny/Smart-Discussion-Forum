@extends('layouts.workspace')

@section('title', 'Create Quiz')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.quizzes') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Create Quiz</h2>
    </div>
    <div class="p-4 bg-[#FAFAFA] border-b border-[#E5E5E5]">
        <p class="text-xs text-[#666666]">Configure your quiz details below</p>
    </div>
    <div class="flex-1 overflow-y-auto custom-scrollbar p-3">
        <div class="bg-white border border-[#E5E5E5] p-4 space-y-2">
            <p class="text-xs font-bold uppercase tracking-wider text-[#000000]">Tips</p>
            <ul class="text-[10px] text-[#666666] space-y-1 list-disc pl-4">
                <li>Quizzes are timed and auto-submit</li>
                <li>Students must use the desktop app</li>
                <li>Results are locked until quiz ends</li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">Create New Quiz</h1>
            <p class="text-sm text-[#666666] mt-1">Set up a timed quiz for your students</p>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="max-w-2xl bg-white border border-[#E5E5E5] p-6">
                <form method="POST" action="{{ route('lecturer.quiz.store') }}" class="space-y-5">
                    @csrf

                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Quiz Title</label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('title') border-[#DC2626] @enderror"
                               placeholder="e.g., Midterm Exam 2026">
                        @error('title')
                            <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Target Group</label>
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

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Duration (minutes)</label>
                            <input type="number" name="duration" value="{{ old('duration', 30) }}" required min="1" max="180"
                                   class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('duration') border-[#DC2626] @enderror">
                            @error('duration')
                                <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Start Date & Time</label>
                            <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required
                                   class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('starts_at') border-[#DC2626] @enderror">
                            @error('starts_at')
                                <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Allowed Student Categories</label>
                        <div class="space-y-1 border border-[#E5E5E5] p-3">
                            @foreach(['active', 'warned_once', 'warned_twice'] as $category)
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="allowed_categories[]" value="{{ $category }}"
                                           {{ in_array($category, old('allowed_categories', ['active'])) ? 'checked' : '' }}
                                           class="accent-black">
                                    <span class="text-xs text-[#000000]">{{ str_replace('_', ' ', ucfirst($category)) }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-[#666666]">Only students with these statuses can take the quiz</p>
                        @error('allowed_categories')
                            <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E5E5E5]">
                        <a href="{{ route('lecturer.quizzes') }}"
                           class="text-xs font-bold uppercase tracking-wider text-[#666666] hover:text-[#000000] transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                                class="bg-[#000000] text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                            Create Quiz
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection