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
    // ──────────────────────────────────────────────────────────────
    // 1. INITIALIZE
    // ──────────────────────────────────────────────────────────────
    const quizId = {{ $quiz->id }};
    let remainingSeconds = {{ $remainingSeconds }};
    const initialRemainingSeconds = remainingSeconds; // store for time_spent calculation
    let timerInterval = null;
    let isSubmitted = false;
    let warningCount = 0;
    const maxWarnings = 3;

    const questions = @json($quiz->questions);

    if (!questions || questions.length === 0) {
        document.getElementById('quiz-questions').innerHTML = `
            <div class="bg-white border border-[#E5E5E5] p-6 text-center">
                <p class="text-sm text-[#DC2626]">⚠️ This quiz has no questions yet.</p>
                <p class="text-xs text-[#666666]">Please contact your lecturer.</p>
            </div>
        `;
        document.getElementById('submit-quiz-btn').disabled = true;
        return;
    }

    // ──────────────────────────────────────────────────────────────
    // 2. RENDER QUESTIONS
    // ──────────────────────────────────────────────────────────────
    function renderQuestions() {
        const container = document.getElementById('quiz-questions');
        let html = '';
        questions.forEach((q, index) => {
            html += `
                <div class="bg-white border border-[#E5E5E5] p-4">
                    <p class="text-sm font-bold text-[#000000]">${index + 1}. ${q.question}</p>
                    <div class="mt-2 space-y-1">
            `;

            if (q.type === 'text') {
                html += `
                    <input type="text" name="q${q.id}" class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition" placeholder="Type your answer...">
                `;
            } else {
                const options = q.options || [];
                const inputType = q.type === 'multiple' ? 'checkbox' : 'radio';
                options.forEach((opt, optIndex) => {
                    html += `
                        <label class="flex items-center space-x-3 cursor-pointer p-1 hover:bg-[#F5F5F5] transition-colors">
                            <input type="${inputType}" name="q${q.id}" value="${optIndex}" class="accent-black">
                            <span class="text-sm text-[#000000]">${opt}</span>
                        </label>
                    `;
                });
            }

            html += `
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
        updateQuestionCounter();
    }

    function updateQuestionCounter() {
        const total = questions.length;
        const answered = document.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked, input[type="text"]').length;
        document.getElementById('question-counter').textContent = `Question ${Math.min(answered + 1, total)} of ${total}`;
    }

    // ──────────────────────────────────────────────────────────────
    // 3. TIMER
    // ──────────────────────────────────────────────────────────────
    function startTimer() {
        updateTimerDisplay();
        timerInterval = setInterval(function() {
            remainingSeconds--;
            updateTimerDisplay();
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

    // ──────────────────────────────────────────────────────────────
    // 4. LOCKDOWN
    // ──────────────────────────────────────────────────────────────
    document.addEventListener('copy', function(e) { e.preventDefault(); showWarning('Copying is disabled.'); });
    document.addEventListener('paste', function(e) { e.preventDefault(); showWarning('Pasting is disabled.'); });
    document.addEventListener('contextmenu', function(e) { e.preventDefault(); showWarning('Right-click is disabled.'); });

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

    function showWarning(message) {
        const existing = document.getElementById('quiz-warning');
        if (existing) existing.remove();
        const warning = document.createElement('div');
        warning.id = 'quiz-warning';
        warning.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-[#DC2626] text-white px-6 py-3 z-50 border border-[#B91C1C] shadow-lg';
        warning.innerHTML = `<div class="flex items-center space-x-3"><span class="text-sm font-bold">⚠️</span><span class="text-sm">${message}</span></div>`;
        document.body.appendChild(warning);
        setTimeout(() => { if (warning) warning.remove(); }, 4000);
    }

    // ──────────────────────────────────────────────────────────────
    // 5. SUBMIT QUIZ (FIXED: time_spent as integer)
    // ──────────────────────────────────────────────────────────────
    function submitQuiz(autoSubmitted = false) {
        if (isSubmitted) return;
        isSubmitted = true;

        const answers = {};
        document.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked, input[type="text"]').forEach(input => {
            const name = input.name;
            if (input.type === 'radio' || input.type === 'checkbox') {
                if (!answers[name]) answers[name] = [];
                answers[name].push(parseInt(input.value));
            } else if (input.type === 'text') {
                answers[name] = input.value.trim();
            }
        });

        // For single choice, flatten to single value
        Object.keys(answers).forEach(key => {
            if (Array.isArray(answers[key]) && answers[key].length === 1) {
                answers[key] = answers[key][0];
            }
        });

        // Compute time spent in seconds (integer)
        const timeSpent = initialRemainingSeconds - remainingSeconds;

        const submitBtn = document.getElementById('submit-quiz-btn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Submitting...';
        submitBtn.disabled = true;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!token) {
            alert('CSRF token missing. Please refresh the page and try again.');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            isSubmitted = false;
            return;
        }

        const url = '{{ route("student.quiz.submit", $quiz->id) }}';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                answers: answers,
                time_spent: timeSpent,
                auto_submitted: autoSubmitted
            }),
            credentials: 'same-origin'
        })
        .then(async response => {
            if (!response.ok) {
                let errorMsg = response.statusText;
                try {
                    const errorData = await response.json();
                    if (errorData.message) errorMsg = errorData.message;
                } catch (_) {}
                throw new Error(`HTTP ${response.status}: ${errorMsg}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
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
            } else {
                alert(data.message || 'Submission failed. Please try again.');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                isSubmitted = false;
            }
        })
        .catch(error => {
            console.error('Submission error:', error);
            alert('Failed to submit quiz: ' + error.message);
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            isSubmitted = false;
        });
    }

    function autoSubmit(reason) {
        if (isSubmitted) return;
        showWarning(`⏰ ${reason} Auto-submitting...`);
        setTimeout(() => { submitQuiz(true); }, 2000);
    }

    // ──────────────────────────────────────────────────────────────
    // 6. EVENT LISTENERS
    // ──────────────────────────────────────────────────────────────
    document.getElementById('submit-quiz-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to submit your answers?')) {
            submitQuiz(false);
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target.type === 'radio' || e.target.type === 'checkbox' || e.target.type === 'text') {
            updateQuestionCounter();
        }
    });

    // ──────────────────────────────────────────────────────────────
    // 7. INITIALIZATION
    // ──────────────────────────────────────────────────────────────
    renderQuestions();
    startTimer();
});
</script>
@endpush