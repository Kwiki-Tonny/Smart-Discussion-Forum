@extends('layouts.workspace')

@section('title', 'Edit Quiz - ' . $quiz->title)

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.quizzes') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] truncate">{{ $quiz->title }}</h2>
    </div>
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-2 text-center">
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $quiz->questions->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Questions</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $quiz->duration }} min</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Duration</p>
            </div>
        </div>
    </div>
    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-2">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1">Questions</p>
        @foreach($quiz->questions as $q)
            <div class="bg-white border border-[#E5E5E5] p-3">
                <div class="flex justify-between items-start">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-[#000000]">{{ $q->question }}</p>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="text-[8px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-1.5 py-0.5">
                                {{ $q->type }}
                            </span>
                            <span class="text-[8px] text-[#666666]">{{ $q->points }} pts</span>
                        </div>
                    </div>
                    <form action="{{ route('lecturer.quiz.question.delete', [$quiz->id, $q->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[#DC2626] hover:text-[#B91C1C] text-xs" onclick="return confirm('Remove this question?')">✕</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-[#000000]">{{ $quiz->title }}</h1>
                    <p class="text-sm text-[#666666] mt-1">
                        {{ $quiz->group->name ?? 'N/A' }} • {{ $quiz->duration }} min
                        @if($quiz->isActive())
                            <span class="text-[#16A34A]">● Active</span>
                        @else
                            <span class="text-[#DC2626]">● Inactive</span>
                        @endif
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <form action="{{ route('lecturer.quiz.toggle', $quiz->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="text-xs font-bold uppercase tracking-wider border border-[#000000] px-3 py-1 hover:bg-[#000000] hover:text-white transition-colors">
                            {{ $quiz->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="bg-white border border-[#E5E5E5] p-6">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] mb-4">Add Question</h2>

                <form method="POST" action="{{ route('lecturer.quiz.question.store', $quiz->id) }}" class="space-y-4">
                    @csrf

                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Question</label>
                        <textarea name="question" rows="2" required
                                  class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors"
                                  placeholder="Enter the question..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Type</label>
                            <select name="type" id="question-type" required
                                    class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors">
                                <option value="single">Single Choice</option>
                                <option value="multiple">Multiple Choice</option>
                                <option value="text">Free Text</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Points</label>
                            <input type="number" name="points" value="1" min="1" max="100"
                                   class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors">
                        </div>
                    </div>

                    <div class="space-y-1" id="options-container">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Options (Enter one per line)</label>
                        <textarea name="options" rows="4"
                                  class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors"
                                  placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                    </div>

                    <div class="space-y-1" id="correct-container">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Correct Answer(s)</label>
                        <input type="text" name="correct_answers" id="correct-input"
                               class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors"
                               placeholder="Enter correct option number(s) separated by commas (e.g., 1,3)">
                        <p class="text-[9px] text-[#666666]">For text questions, enter the exact expected answer</p>
                    </div>

                    <button type="submit"
                            class="bg-[#000000] text-white px-6 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                        Add Question
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('question-type');
    const optionsContainer = document.getElementById('options-container');
    const correctContainer = document.getElementById('correct-container');
    const correctInput = document.getElementById('correct-input');

    typeSelect.addEventListener('change', function() {
        const type = this.value;
        if (type === 'text') {
            optionsContainer.style.display = 'none';
            correctInput.placeholder = 'Enter the expected answer...';
        } else {
            optionsContainer.style.display = 'block';
            correctInput.placeholder = 'Enter correct option number(s) separated by commas (e.g., 1,3)';
        }
    });
});
</script>
@endpush