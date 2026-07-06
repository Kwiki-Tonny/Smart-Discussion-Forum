@extends('layouts.workspace')

@section('title', $quiz->title)

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] truncate">{{ $quiz->title }}</h2>
    </div>
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div id="timer-display" class="text-center">
            <p class="text-3xl font-bold text-[#000000]" id="timer">--:--</p>
            <p class="text-[10px] text-[#666666] uppercase tracking-wider">Time Remaining</p>
        </div>
    </div>
    <div class="p-3 bg-[#FAFAFA] border-b border-[#E5E5E5]">
        <div class="flex items-center justify-between text-xs">
            <span class="text-[#666666]">{{ $quiz->group->name ?? 'N/A' }}</span>
            <span class="text-[#666666]">{{ $quiz->duration }} min</span>
        </div>
    </div>
    <div class="flex-1 overflow-y-auto custom-scrollbar p-3">
        <div class="bg-[#FFF8F0] border border-[#D97706] p-3">
            <p class="text-[10px] text-[#D97706] font-bold uppercase tracking-wider">⚠️ Quiz Rules</p>
            <ul class="text-[9px] text-[#666666] mt-1 space-y-1 list-disc pl-4">
                <li>Do not switch tabs or leave this page</li>
                <li>Copy-paste is disabled</li>
                <li>Quiz auto-submits when time expires</li>
                <li>You will be warned if you try to leave</li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full" id="quiz-container">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-4">
            <h1 class="text-lg font-bold text-[#000000]">{{ $quiz->title }}</h1>
            <p class="text-xs text-[#666666] mt-0.5">Answer all questions before time runs out</p>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-6" id="quiz-questions">
            {{-- Questions will be loaded here --}}
            <div class="bg-white border border-[#E5E5E5] p-6 text-center">
                <p class="text-sm text-[#666666]">Loading questions...</p>
            </div>
        </div>

        <div class="bg-white border-t border-[#E5E5E5] px-6 py-4 flex items-center justify-between">
            <span class="text-xs text-[#666666]" id="question-counter">Question 0 of 0</span>
            <button id="submit-quiz-btn"
                    class="bg-[#000000] text-white px-6 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                Submit Quiz
            </button>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================================
    // 1. INITIALIZE
    // ============================================================
    const quizId = {{ $quiz->id }};
    let remainingSeconds = {{ $remainingSeconds }};
    let timerInterval = null;
    let isSubmitted = false;
    let warningCount = 0;
    const maxWarnings = 3;

    // Mock questions (in real app, these come from the server)
    const questions = [
        {
            id: 1,
            question: 'What is the chemical symbol for water?',
            options: ['H2O', 'CO2', 'NaCl', 'HCl'],
            correct: 0
        },
        {
            id: 2,
            question: 'What is the pH value of pure water?',
            options: ['5', '6', '7', '8'],
            correct: 2
        },
        {
            id: 3,
            question: 'Which of the following is an example of a covalent bond?',
            options: ['NaCl', 'H2O', 'KCl', 'MgO'],
            correct: 1
        }
    ];

    // ============================================================
    // 2. RENDER QUESTIONS
    // ============================================================
    function renderQuestions() {
        const container = document.getElementById('quiz-questions');
        let html = '';
        questions.forEach((q, index) => {
            html += `
                <div class="bg-white border border-[#E5E5E5] p-4">
                    <p class="text-sm font-bold text-[#000000]">${index + 1}. ${q.question}</p>
                    <div class="mt-2 space-y-1">
                        ${q.options.map((opt, optIndex) => `
                            <label class="flex items-center space-x-3 cursor-pointer p-1 hover:bg-[#F5F5F5] transition-colors">
                                <input type="radio" name="q${q.id}" value="${optIndex}" class="accent-black">
                                <span class="text-sm text-[#000000]">${opt}</span>
                            </label>
                        `).join('')}
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
        updateQuestionCounter();
    }

    function updateQuestionCounter() {
        const answered = document.querySelectorAll('input[type="radio"]:checked').length;
        const total = questions.length;
        document.getElementById('question-counter').textContent = `Question ${answered} of ${total}`;
    }

    // ============================================================
    // 3. TIMER
    // ============================================================
    function startTimer() {
        updateTimerDisplay();
        timerInterval = setInterval(function() {
            remainingSeconds--;
            updateTimerDisplay();

            // Warning when less than 60 seconds
            if (remainingSeconds <= 60 && remainingSeconds > 0) {
                document.getElementById('timer-display').style.backgroundColor = '#FEF2F2';
                document.getElementById('timer').style.color = '#DC2626';
            }

            if (remainingSeconds <= 0) {
                clearInterval(timerInterval);
                autoSubmit('Time expired!');
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        const minutes = Math.floor(Math.max(0, remainingSeconds) / 60);
        const seconds = Math.floor(Math.max(0, remainingSeconds) % 60);
        document.getElementById('timer').textContent =
            String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
    }

    // ============================================================
    // 4. SOFT LOCKDOWN - Copy/Paste Prevention
    // ============================================================
    document.addEventListener('copy', function(e) {
        e.preventDefault();
        showWarning('Copying is disabled during the quiz.');
    });

    document.addEventListener('paste', function(e) {
        e.preventDefault();
        showWarning('Pasting is disabled during the quiz.');
    });

    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        showWarning('Right-click is disabled during the quiz.');
    });

    // ============================================================
    // 5. SOFT LOCKDOWN - Tab Switch Detection
    // ============================================================
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            warningCount++;
            const remaining = maxWarnings - warningCount;
            if (remaining <= 0) {
                autoSubmit('Multiple tab switches detected!');
            } else {
                showWarning(`⚠️ Warning ${warningCount}/${maxWarnings}: Do not switch tabs! Remaining: ${remaining}`);
            }
        }
    });

    // ============================================================
    // 6. NOTIFICATIONS
    // ============================================================
    function showWarning(message) {
        // Remove existing warning
        const existing = document.getElementById('quiz-warning');
        if (existing) existing.remove();

        const warning = document.createElement('div');
        warning.id = 'quiz-warning';
        warning.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-[#DC2626] text-white px-6 py-3 z-50 border border-[#B91C1C] shadow-lg';
        warning.innerHTML = `
            <div class="flex items-center space-x-3">
                <span class="text-sm font-bold">⚠️</span>
                <span class="text-sm">${message}</span>
            </div>
        `;
        document.body.appendChild(warning);

        setTimeout(() => {
            if (warning) warning.remove();
        }, 4000);
    }

    // ============================================================
    // 7. SUBMIT QUIZ
    // ============================================================
    function submitQuiz(autoSubmitted = false) {
        if (isSubmitted) return;
        isSubmitted = true;

        // Collect answers
        const answers = {};
        document.querySelectorAll('input[type="radio"]:checked').forEach(input => {
            const name = input.name;
            answers[name] = parseInt(input.value);
        });

        // Show loading
        const submitBtn = document.getElementById('submit-quiz-btn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Submitting...';
        submitBtn.disabled = true;

        fetch('{{ route("student.quiz.submit", $quiz->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                answers: answers,
                time_spent: {{ $quiz->duration * 60 }} - remainingSeconds,
                auto_submitted: autoSubmitted
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const container = document.getElementById('quiz-container');
                container.innerHTML = `
                    <div class="flex-1 flex items-center justify-center p-8">
                        <div class="bg-white border border-[#16A34A] p-8 max-w-md w-full text-center">
                            <div class="text-4xl mb-4">🎉</div>
                            <h2 class="text-xl font-bold text-[#000000]">Quiz Submitted!</h2>
                            <p class="text-sm text-[#666666] mt-2">${autoSubmitted ? '⚠️ Auto-submitted due to time expiry.' : '✅ Successfully submitted.'}</p>
                            <p class="text-2xl font-bold text-[#16A34A] mt-4">Score: ${data.score}%</p>
                            <a href="{{ route('student.quizzes') }}" class="inline-block mt-6 bg-[#000000] text-white px-6 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                                Return to Quizzes
                            </a>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Submit error:', error);
            alert('Failed to submit quiz. Please try again.');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }

    function autoSubmit(reason) {
        if (isSubmitted) return;
        showWarning(`⏰ ${reason} Auto-submitting...`);
        setTimeout(() => {
            submitQuiz(true);
        }, 2000);
    }

    // ============================================================
    // 8. EVENT LISTENERS
    // ============================================================
    document.getElementById('submit-quiz-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to submit your answers?')) {
            submitQuiz(false);
        }
    });

    // Update question counter on radio change
    document.addEventListener('change', function(e) {
        if (e.target.type === 'radio') {
            updateQuestionCounter();
        }
    });

    // ============================================================
    // 9. INITIALIZATION
    // ============================================================
    renderQuestions();
    startTimer();
});
</script>
@endpush