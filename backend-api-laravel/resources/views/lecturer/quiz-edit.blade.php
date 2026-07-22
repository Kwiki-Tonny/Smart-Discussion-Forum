@extends('layouts.workspace')

@section('title', 'Edit Quiz - ' . $quiz->title)

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.quizzes') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] truncate">{{ $quiz->title }}</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-3 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#0A574F]">{{ $quiz->questions->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Questions</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#2563EB]">{{ $quiz->duration }} min</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Duration</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#D97706]">{{ $quiz->submissions->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Submissions</p>
            </div>
        </div>
    </div>

    {{-- Question List --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-2">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1 flex items-center gap-1">
            <i data-lucide="list" style="width:12px;height:12px;"></i>
            Questions
        </p>
        @forelse($quiz->questions as $q)
            <div class="bg-white border border-[#E5E5E5] rounded-lg p-3 hover:border-[#0A574F] transition">
                <div class="flex justify-between items-start">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-[#0A574F] bg-[#ECFDF5] px-2 py-0.5 rounded-full">#{{ $loop->iteration }}</span>
                            <p class="text-xs font-bold text-[#000000]">{{ $q->question }}</p>
                        </div>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span class="text-[8px] font-bold uppercase tracking-wider text-[#2563EB] border border-[#2563EB] px-2 py-0.5 rounded-full">
                                {{ ucfirst($q->type) }}
                            </span>
                            <span class="text-[8px] text-[#666666] flex items-center gap-1">
                                <i data-lucide="star" style="width:8px;height:8px;"></i>
                                {{ $q->points }} pts
                            </span>
                            @if($q->type !== 'text')
                                <span class="text-[8px] text-[#666666] flex items-center gap-1">
                                    <i data-lucide="list" style="width:8px;height:8px;"></i>
                                    {{ count($q->options ?? []) }} options
                                </span>
                            @endif
                        </div>
                        @if($q->correct_answers)
                            <p class="text-[8px] text-[#16A34A] mt-0.5 flex items-center gap-1">
                                <i data-lucide="check-circle" style="width:10px;height:10px;"></i>
                                Correct: {{ is_array($q->correct_answers) ? implode(', ', $q->correct_answers) : $q->correct_answers }}
                            </p>
                        @endif
                    </div>
                    <form action="{{ route('lecturer.quiz.question.delete', [$quiz->id, $q->id]) }}" method="POST" class="flex-shrink-0 ml-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[#DC2626] hover:text-[#B91C1C] text-sm font-bold p-1 rounded-lg hover:bg-[#FEF2F2] transition" onclick="return confirm('Remove this question?')">
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-6 text-center bg-white border border-dashed border-[#E5E5E5] rounded-lg">
                <i data-lucide="help-circle" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                <p class="text-sm text-[#666666]">No questions added yet.</p>
                <p class="text-xs text-[#94A3B8]">Use the form below to add questions to this quiz.</p>
            </div>
        @endforelse
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
                        {{ $quiz->title }}
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-2 flex-wrap">
                        <span class="flex items-center gap-1">
                            <i data-lucide="users" style="width:14px;height:14px;color:#0A574F;"></i>
                            {{ $quiz->group->name ?? 'N/A' }}
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="clock" style="width:14px;height:14px;color:#0A574F;"></i>
                            {{ $quiz->duration }} min
                        </span>
                        @if($quiz->hasEnded())
                            <span class="text-[#DC2626] font-bold flex items-center gap-1">
                                <i data-lucide="circle" style="width:8px;height:8px;fill:#DC2626;color:#DC2626;"></i>
                                Ended
                            </span>
                        @elseif(!$quiz->hasStarted())
                            <span class="text-[#16A34A] font-bold flex items-center gap-1">
                                <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                                Upcoming
                            </span>
                        @else
                            <span class="text-[#D97706] font-bold flex items-center gap-1">
                                <i data-lucide="circle" style="width:8px;height:8px;fill:#D97706;color:#D97706;"></i>
                                Active
                            </span>
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <form action="{{ route('lecturer.quiz.toggle', $quiz->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider border border-[#E5E5E5] text-[#000000] px-3 py-1.5 rounded-lg hover:border-[#0A574F] hover:bg-[#F9F9F9] transition">
                            <i data-lucide="{{ $quiz->is_active ? 'pause-circle' : 'play-circle' }}" style="width:14px;height:14px;"></i>
                            {{ $quiz->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Bulk Question Addition --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm p-6">

                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="plus-circle" style="width:20px;height:20px;color:#0A574F;"></i>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Add Questions</h2>
                    </div>
                    <button type="button" id="add-question-btn"
                            class="flex items-center gap-1 bg-[#0A574F] text-white px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition">
                        <i data-lucide="plus" style="width:12px;height:12px;"></i>
                        Add Another Question
                    </button>
                </div>

                <form method="POST" action="{{ route('lecturer.quiz.question.store.bulk', $quiz->id) }}" id="bulk-question-form">
                    @csrf
                    <div id="questions-container" class="space-y-4">

                        {{-- Initial question block --}}
                        <div class="question-block bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg p-4 relative hover:border-[#0A574F] transition">
                            <div class="absolute top-3 right-3">
                                <button type="button" class="remove-question-btn text-[#DC2626] hover:text-[#B91C1C] text-sm font-bold p-1 rounded-lg hover:bg-[#FEF2F2] transition" title="Remove this question">
                                    <i data-lucide="x" style="width:14px;height:14px;"></i>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 gap-3">
                                <div class="space-y-1">
                                    <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                                        <i data-lucide="help-circle" style="width:12px;height:12px;color:#0A574F;"></i>
                                        Question
                                    </label>
                                    <textarea name="questions[0][question]" rows="2" required
                                              class="w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition"
                                              placeholder="Enter the question..."></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                                            <i data-lucide="list" style="width:12px;height:12px;color:#2563EB;"></i>
                                            Type
                                        </label>
                                        <select name="questions[0][type]" class="question-type w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                                            <option value="single">Single Choice</option>
                                            <option value="multiple">Multiple Choice</option>
                                            <option value="text">Free Text</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                                            <i data-lucide="star" style="width:12px;height:12px;color:#D97706;"></i>
                                            Points
                                        </label>
                                        <input type="number" name="questions[0][points]" value="1" min="1" max="100"
                                               class="w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                                    </div>
                                </div>
                                <div class="space-y-1 options-container">
                                    <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                                        <i data-lucide="list" style="width:12px;height:12px;color:#2563EB;"></i>
                                        Options (Enter one per line)
                                    </label>
                                    <textarea name="questions[0][options]" rows="3"
                                              class="w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition"
                                              placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                                </div>
                                <div class="space-y-1 correct-container">
                                    <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                                        <i data-lucide="check-circle" style="width:12px;height:12px;color:#16A34A;"></i>
                                        Correct Answer(s)
                                    </label>
                                    <input type="text" name="questions[0][correct_answers]"
                                           class="correct-input w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition"
                                           placeholder="Enter correct option number(s) separated by commas (e.g., 1,3)">
                                    <p class="text-[9px] text-[#666666] flex items-center gap-1">
                                        <i data-lucide="info" style="width:10px;height:10px;"></i>
                                        For text questions, enter the exact expected answer
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-[#E5E5E5] mt-4">
                        <a href="{{ route('lecturer.quizzes') }}"
                           class="flex items-center gap-1 text-xs font-bold uppercase tracking-wider text-[#666666] hover:text-[#0A574F] transition">
                            <i data-lucide="arrow-left" style="width:12px;height:12px;"></i>
                            Back to Quizzes
                        </a>
                        <div class="flex items-center gap-3">
                            <span id="question-counter" class="text-xs text-[#666666] flex items-center gap-1">
                                <i data-lucide="list" style="width:12px;height:12px;"></i>
                                1 question
                            </span>
                            <button type="submit"
                                    class="flex items-center gap-2 bg-[#0A574F] text-white px-6 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition hover:shadow-sm">
                                <i data-lucide="save" style="width:14px;height:14px;"></i>
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
    lucide.createIcons();

    let questionCount = 1;
    const container = document.getElementById('questions-container');
    const addBtn = document.getElementById('add-question-btn');
    const counter = document.getElementById('question-counter');

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

    addBtn.addEventListener('click', function() {
        const newIndex = container.children.length;
        const lastBlock = container.lastElementChild;
        const newBlock = lastBlock.cloneNode(true);

        newBlock.querySelectorAll('textarea, input').forEach(input => {
            input.value = '';
        });

        const typeSelect = newBlock.querySelector('.question-type');
        if (typeSelect) typeSelect.value = 'single';

        newBlock.querySelectorAll('textarea, input, select').forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace(/\[\d+\]/, '[' + newIndex + ']'));
            }
        });

        let removeBtn = newBlock.querySelector('.remove-question-btn');
        if (!removeBtn) {
            const headerDiv = newBlock.querySelector('.absolute.top-3.right-3');
            if (headerDiv) {
                removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'remove-question-btn text-[#DC2626] hover:text-[#B91C1C] text-sm font-bold p-1 rounded-lg hover:bg-[#FEF2F2] transition';
                removeBtn.innerHTML = '<i data-lucide="x" style="width:14px;height:14px;"></i>';
                removeBtn.title = 'Remove this question';
                headerDiv.appendChild(removeBtn);
            }
        }

        container.appendChild(newBlock);
        updateIndices();
        lucide.createIcons();
    });

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

    updateIndices();

    document.querySelectorAll('.question-type').forEach(select => {
        const block = select.closest('.question-block');
        const optionsContainer = block.querySelector('.options-container');
        const correctInput = block.querySelector('.correct-input');
        if (select.value === 'text') {
            optionsContainer.style.display = 'none';
            correctInput.placeholder = 'Enter the expected answer...';
        }
    });
});
</script>
@endpush

