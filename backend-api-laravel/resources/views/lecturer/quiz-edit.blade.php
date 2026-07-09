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
        @forelse($quiz->questions as $q)
            <div class="bg-white border border-[#E5E5E5] p-3">
                <div class="flex justify-between items-start">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-[#000000]">{{ $q->question }}</p>
                        <div class="flex items-center space-x-2 mt-1 flex-wrap">
                            <span class="text-[8px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-1.5 py-0.5">
                                {{ ucfirst($q->type) }}
                            </span>
                            <span class="text-[8px] text-[#666666]">{{ $q->points }} pts</span>
                            @if($q->type !== 'text')
                                <span class="text-[8px] text-[#666666]">{{ count($q->options ?? []) }} options</span>
                            @endif
                        </div>
                        @if($q->correct_answers)
                            <p class="text-[8px] text-[#16A34A] mt-0.5">
                                ✅ Correct: {{ is_array($q->correct_answers) ? implode(', ', $q->correct_answers) : $q->correct_answers }}
                            </p>
                        @endif
                    </div>
                    <form action="{{ route('lecturer.quiz.question.delete', [$quiz->id, $q->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[#DC2626] hover:text-[#B91C1C] text-sm font-bold" onclick="return confirm('Remove this question?')">✕</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-4 text-center bg-white border border-[#E5E5E5]">
                <p class="text-sm text-[#666666]">No questions added yet.</p>
            </div>
        @endforelse
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
                        @if($quiz->hasEnded())
                            <span class="text-[#DC2626] font-bold">● Ended</span>
                        @elseif(!$quiz->hasStarted())
                            <span class="text-[#16A34A] font-bold">● Upcoming</span>
                        @else
                            <span class="text-[#16A34A] font-bold">● Active</span>
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

        {{-- Bulk Question Addition --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="bg-white border border-[#E5E5E5] p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Add Questions</h2>
                    <button type="button" id="add-question-btn"
                            class="bg-[#000000] text-white px-4 py-1.5 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                        + Add Another Question
                    </button>
                </div>

                <form method="POST" action="{{ route('lecturer.quiz.question.store.bulk', $quiz->id) }}" id="bulk-question-form">
                    @csrf
                    <div id="questions-container">
                        {{-- Initial question block --}}
                        <div class="question-block border border-[#E5E5E5] p-4 mb-4 relative">
                            <div class="absolute top-2 right-2">
                                <button type="button" class="remove-question-btn text-[#DC2626] hover:text-[#B91C1C] text-sm font-bold" title="Remove this question">✕</button>
                            </div>
                            <div class="grid grid-cols-1 gap-3">
                                <div class="space-y-1">
                                    <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Question</label>
                                    <textarea name="questions[0][question]" rows="2" required
                                              class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors"
                                              placeholder="Enter the question..."></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Type</label>
                                        <select name="questions[0][type]" class="question-type w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors">
                                            <option value="single">Single Choice</option>
                                            <option value="multiple">Multiple Choice</option>
                                            <option value="text">Free Text</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Points</label>
                                        <input type="number" name="questions[0][points]" value="1" min="1" max="100"
                                               class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors">
                                    </div>
                                </div>
                                <div class="space-y-1 options-container">
                                    <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Options (Enter one per line)</label>
                                    <textarea name="questions[0][options]" rows="3"
                                              class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors"
                                              placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                                </div>
                                <div class="space-y-1 correct-container">
                                    <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Correct Answer(s)</label>
                                    <input type="text" name="questions[0][correct_answers]"
                                           class="correct-input w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors"
                                           placeholder="Enter correct option number(s) separated by commas (e.g., 1,3)">
                                    <p class="text-[9px] text-[#666666]">For text questions, enter the exact expected answer</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-[#E5E5E5]">
                        <a href="{{ route('lecturer.quizzes') }}"
                           class="text-xs font-bold uppercase tracking-wider text-[#666666] hover:text-[#000000] transition-colors">
                            Back to Quizzes
                        </a>
                        <div class="flex items-center space-x-3">
                            <span id="question-counter" class="text-xs text-[#666666]">1 question</span>
                            <button type="submit"
                                    class="bg-[#000000] text-white px-6 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                                Save All Questions
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let questionCount = 1;
    const container = document.getElementById('questions-container');
    const addBtn = document.getElementById('add-question-btn');
    const counter = document.getElementById('question-counter');

    // Function to update question indices
    function updateIndices() {
        const blocks = container.querySelectorAll('.question-block');
        blocks.forEach((block, index) => {
            block.querySelectorAll('textarea, input, select').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                }
            });
        });
        counter.textContent = blocks.length + ' question' + (blocks.length > 1 ? 's' : '');
    }

    // Add new question block
    addBtn.addEventListener('click', function() {
        const newIndex = container.children.length;
        const lastBlock = container.lastElementChild;
        const newBlock = lastBlock.cloneNode(true);

        // Clear all input values in the new block (keep structure)
        newBlock.querySelectorAll('textarea, input').forEach(input => {
            input.value = '';
        });

        // Ensure the type select is reset to 'single'
        const typeSelect = newBlock.querySelector('.question-type');
        if (typeSelect) typeSelect.value = 'single';

        // Update names with new index
        newBlock.querySelectorAll('textarea, input, select').forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace(/\[\d+\]/, '[' + newIndex + ']'));
            }
        });

        // Ensure remove button exists and works
        let removeBtn = newBlock.querySelector('.remove-question-btn');
        if (!removeBtn) {
            const headerDiv = newBlock.querySelector('.absolute.top-2.right-2');
            if (headerDiv) {
                removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'remove-question-btn text-[#DC2626] hover:text-[#B91C1C] text-sm font-bold';
                removeBtn.innerHTML = '✕';
                removeBtn.title = 'Remove this question';
                headerDiv.appendChild(removeBtn);
            }
        }

        // Append the new block
        container.appendChild(newBlock);
        updateIndices();
    });

    // Remove question (delegated)
    container.addEventListener('click', function(e) {
        const removeBtn = e.target.closest('.remove-question-btn');
        if (removeBtn) {
            const block = removeBtn.closest('.question-block');
            if (block && container.children.length > 1) {
                block.remove();
                updateIndices();
            } else {
                alert('You must have at least one question.');
            }
        }
    });

    // Handle type change to show/hide options
    container.addEventListener('change', function(e) {
        const select = e.target.closest('.question-type');
        if (select) {
            const block = select.closest('.question-block');
            const optionsContainer = block.querySelector('.options-container');
            const correctInput = block.querySelector('.correct-input');

            if (select.value === 'text') {
                optionsContainer.style.display = 'none';
                correctInput.placeholder = 'Enter the expected answer...';
            } else {
                optionsContainer.style.display = 'block';
                correctInput.placeholder = 'Enter correct option number(s) separated by commas (e.g., 1,3)';
            }
        }
    });

    // Initial update for any existing fields
    updateIndices();
});
</script>
@endpush