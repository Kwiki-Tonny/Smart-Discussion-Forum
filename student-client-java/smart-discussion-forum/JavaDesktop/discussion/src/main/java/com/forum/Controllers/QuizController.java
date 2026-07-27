package com.forum.controllers;

import com.forum.models.Question;
import com.forum.models.QuizAttempt;
import com.forum.models.QuizAttemptDetail;
import com.forum.services.ApiService;
import javafx.animation.Animation;
import javafx.animation.KeyFrame;
import javafx.animation.Timeline;
import javafx.application.Platform;
import javafx.concurrent.Task;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.layout.Region;
import javafx.scene.layout.VBox;
import javafx.scene.text.Text;
import javafx.stage.Stage;
import javafx.util.Duration;

import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.time.temporal.ChronoUnit;
import java.util.*;

/**
 * Controller managing the dynamic quiz evaluation interface in JavaFX.
 *
 * Handles dynamic question rendering (text, single choice, and multiple choice),
 * real-time timer countdowns, asynchronous API submissions, and focus-loss anti-cheat enforcement.
 */
public class QuizController {

    /* =========================================================================
     * FXML UI COMPONENTS
     * ========================================================================= */

    /** Header text display for the active quiz title. */
    @FXML private Text quizTitleText;

    /** Display label showing remaining quiz time in MM:SS format. */
    @FXML private Text timerText;

    /** Text display tracking current question position (e.g., "Question 1 of 5"). */
    @FXML private Text qNumText;

    /** Main prompt text container for the currently displayed question. */
    @FXML private Text qText;

    /** Dynamic container holding input controls (radio buttons, checkboxes, text areas) for options. */
    @FXML private VBox optionsContainer;

    /** Graphical indicator region for visual completion progress. */
    @FXML private Region progressFill;

    /** Numerical progress label showing answered count. */
    @FXML private Text progressText;

    /** Navigation button to step back to the previous question. */
    @FXML private Button prevBtn;

    /** Navigation button to advance to the next question. */
    @FXML private Button nextBtn;

    /** Submission action button to complete and finalize the quiz attempt. */
    @FXML private Button submitBtn;

    /* =========================================================================
     * STATE MANAGEMENT & DATA FIELDS
     * ========================================================================= */

    /** Index pointer of the currently rendered question (0-indexed). */
    private int currentQuestion = 0;

    /** In-memory store linking question index to selected/typed candidate responses. */
    private Map<Integer, Object> answers = new HashMap<>();

    /** Counter tracking the number of times the window loses desktop focus during an active quiz. */
    private int focusLossCount = 0;

    /** Animation timer executing per-second countdown updates. */
    private Timeline timer;

    /** Seconds remaining before automated quiz expiry and submission. */
    private int remainingSeconds = 0;

    /** Unique server-assigned identifier for the current quiz attempt session. */
    private int attemptId = -1;

    /** List of question instances associated with this quiz attempt. */
    private List<Question> questions = new ArrayList<>();

    /** Cleanup callback action executed when the quiz window is closed. */
    private Runnable onClose;

    /** Flag indicating if the current attempt has been submitted to prevent duplicate processing. */
    private boolean submitted = false;

    /** Flag guarding against premature auto-submitting during component instantiation. */
    private boolean isQuizStarted = false; // Prevents auto-submit on load

    /** Singleton REST client instance for transmitting quiz answers to backend. */
    private final ApiService api = ApiService.getInstance();

    /**
     * Called automatically by JavaFX framework after FXML loading is complete.
     */
    @FXML
    public void initialize() { }

    /**
     * Populates controller fields with attempt data, calculates remaining duration,
     * configures lockdown anti-cheat listeners, and starts the countdown timer.
     *
     * @param attempt The quiz attempt payload containing question details and timestamps
     * @param onClose Callback runnable triggered upon quiz completion or dismissal
     */
    public void setQuizData(QuizAttempt attempt, Runnable onClose) {
        this.attemptId = attempt.id;
        this.questions = attempt.quiz.questions;
        this.onClose = onClose;

        quizTitleText.setText(attempt.quiz.title);

        // ─── CALCULATE REMAINING TIME ──────────────────────────────
        LocalDateTime startedAt = LocalDateTime.parse(attempt.startedAt, DateTimeFormatter.ISO_DATE_TIME);
        LocalDateTime expiresAt = startedAt.plusSeconds(attempt.durationSeconds);
        long remaining = ChronoUnit.SECONDS.between(LocalDateTime.now(), expiresAt);

        // ─── DEBUG LOG ──────────────────────────────────────────────
        System.out.println("QuizController.setQuizData:");
        System.out.println("  startedAt: " + attempt.startedAt);
        System.out.println("  durationSeconds: " + attempt.durationSeconds);
        System.out.println("  expiresAt (server): " + expiresAt);
        System.out.println("  current time (client): " + LocalDateTime.now());
        System.out.println("  remaining seconds: " + remaining);

        // ─── ADD A 2-SECOND BUFFER (prevent immediate expiry) ──────
        int bufferSeconds = 2;

        if (remaining <= 0) {
            // If it's just slightly expired (within buffer), show the quiz anyway
            if (remaining > -bufferSeconds) {
                System.out.println("  Quiz just expired (within buffer). Starting anyway.");
                this.remainingSeconds = 1; // give at least 1 second to see the quiz
                showQuestion(0);
                startTimer();
                setupLockdown();
                return;
            } else {
                // Truly expired – show a message and close
                Platform.runLater(() -> {
                    showToast("⏰ This quiz has already expired. You cannot start it.");
                    if (onClose != null) {
                        onClose.run();
                    }
                });
                return;
            }
        }

        this.remainingSeconds = (int) remaining;

        // ─── SHOW THE QUIZ ──────────────────────────────────────────
        showQuestion(0);
        startTimer();
        setupLockdown();
    }
    
    /**
     * Renders question elements, dynamic selection controls (Text, Single Choice, Multiple Choice),
     * and updates progress bar and control button states based on question index.
     *
     * @param index Zero-based question array index to display
     */
    private void showQuestion(int index) {
        if (index < 0 || index >= questions.size()) return;

        currentQuestion = index;
        Question q = questions.get(index);

        qNumText.setText("Question " + (index + 1) + " of " + questions.size());
        qText.setText(q.text);

        double progress = ((double) (index + 1) / questions.size()) * 100;
        progressFill.setPrefWidth(progress);
        progressText.setText((index + 1) + "/" + questions.size());

        optionsContainer.getChildren().clear();
        Object selected = answers.get(index);

        if ("text".equalsIgnoreCase(q.type)) {
            TextArea textArea = new TextArea();
            textArea.setPromptText("Type your answer here...");
            textArea.setPrefRowCount(3);
            textArea.setStyle("-fx-padding: 8px 12px; -fx-border-color: #d0d5dd; -fx-border-radius: 6px; -fx-background-radius: 6px; -fx-font-size: 14px;");
            textArea.setWrapText(true);
            if (selected != null) textArea.setText((String) selected);
            textArea.textProperty().addListener((obs, oldVal, newVal) -> answers.put(index, newVal));
            optionsContainer.getChildren().add(textArea);
        } else if ("multiple".equalsIgnoreCase(q.type)) {
            if (q.options == null || q.options.isEmpty()) {
                Label noOptions = new Label("No options available.");
                noOptions.setStyle("-fx-text-fill: #999999; -fx-font-size: 13px;");
                optionsContainer.getChildren().add(noOptions);
            } else {
                List<Integer> selectedList = (selected != null) ? (List<Integer>) selected : new ArrayList<>();
                VBox optionsBox = new VBox(6);
                optionsBox.setPadding(new Insets(4, 0, 0, 0));
                for (int i = 0; i < q.options.size(); i++) {
                    final int optionIndex = i;
                    CheckBox cb = new CheckBox(q.options.get(i));
                    cb.setUserData(optionIndex);
                    if (selectedList.contains(optionIndex)) cb.setSelected(true);
                    cb.setStyle("-fx-padding: 6px 12px; -fx-border-color: #e5e5e5; -fx-border-radius: 6px; " +
                            "-fx-background-radius: 6px; -fx-cursor: hand; -fx-background-color: #ffffff;");
                    cb.selectedProperty().addListener((obs, oldVal, newVal) -> {
                        List<Integer> currentList = (List<Integer>) answers.getOrDefault(index, new ArrayList<>());
                        if (newVal && !currentList.contains(optionIndex)) currentList.add(optionIndex);
                        else if (!newVal) currentList.remove(Integer.valueOf(optionIndex));
                        Collections.sort(currentList);
                        answers.put(index, currentList);
                    });
                    optionsBox.getChildren().add(cb);
                }
                optionsContainer.getChildren().add(optionsBox);
            }
        } else { // single choice
            if (q.options == null || q.options.isEmpty()) {
                Label noOptions = new Label("No options available.");
                noOptions.setStyle("-fx-text-fill: #999999; -fx-font-size: 13px;");
                optionsContainer.getChildren().add(noOptions);
            } else {
                ToggleGroup group = new ToggleGroup();
                VBox optionsBox = new VBox(6);
                optionsBox.setPadding(new Insets(4, 0, 0, 0));
                for (int i = 0; i < q.options.size(); i++) {
                    final int optionIndex = i;
                    RadioButton rb = new RadioButton(q.options.get(i));
                    rb.setToggleGroup(group);
                    rb.setUserData(optionIndex);
                    if (selected != null && selected.equals(optionIndex)) rb.setSelected(true);
                    rb.setStyle("-fx-padding: 6px 12px; -fx-border-color: #1A7A64; -fx-border-radius: 6px; " +
                            "-fx-background-radius: 6px; -fx-cursor: hand; -fx-background-color: #ffffff;");
                    rb.selectedProperty().addListener((obs, oldVal, newVal) -> {
                        if (newVal) answers.put(index, optionIndex);
                    });
                    optionsBox.getChildren().add(rb);
                }
                optionsContainer.getChildren().add(optionsBox);
            }
        }

        prevBtn.setDisable(index == 0);
        if (index == questions.size() - 1) {
            nextBtn.setVisible(false);
            nextBtn.setManaged(false);
            submitBtn.setVisible(true);
            submitBtn.setManaged(true);
        } else {
            nextBtn.setVisible(true);
            nextBtn.setManaged(true);
            submitBtn.setVisible(false);
            submitBtn.setManaged(false);
        }
    }

    /** Navigates back to the preceding question if not on the first question. */
    @FXML public void handlePrevious() { if (currentQuestion > 0) showQuestion(currentQuestion - 1); }

    /** Navigates forward to the next question if not on the last question. */
    @FXML public void handleNext() { if (currentQuestion < questions.size() - 1) showQuestion(currentQuestion + 1); }

    /**
     * Initializes and starts the 1-second interval JavaFX Timeline timer.
     * Automatically triggers autoSubmit when remaining time reaches zero.
     */
    private void startTimer() {
        timer = new Timeline(new KeyFrame(Duration.seconds(1), e -> {
            remainingSeconds--;
            updateTimerUI(remainingSeconds);
            if (remainingSeconds <= 0 && isQuizStarted) {
                timer.stop();
                autoSubmit("⏰ Time is up! Auto-submitting...");
            }
        }));
        timer.setCycleCount(Animation.INDEFINITE);
        timer.play();
    }

    /**
     * Formats remaining seconds into MM:SS display format and applies warning color when time is short.
     *
     * @param seconds Integer count of remaining time
     */
    private void updateTimerUI(int seconds) {
        int mins = seconds / 60;
        int secs = seconds % 60;
        timerText.setText(String.format("⏱️ %02d:%02d", mins, secs));
        timerText.setStyle(seconds <= 60 ? "-fx-fill: #ff6b6b;" : "-fx-fill: #000000;");
    }

    /**
     * Handles manual user submission request. Validates answered counts, asks for confirmation
     * via modal alert dialogs, and dispatches API submission asynchronously on a background thread.
     */
    @FXML
    public void handleSubmit() {
        if (submitted) return;
        if (remainingSeconds <= 0) {
            autoSubmit("Time expired. Auto-submitting...");
            return;
        }
        if (!isQuizStarted) {
            showToast("⏳ Quiz not ready yet.");
            return;
        }

        Map<Integer, Object> answerMap = new HashMap<>();
        int answeredCount = 0;
        for (int i = 0; i < questions.size(); i++) {
            Question q = questions.get(i);
            Object ans = answers.get(i);
            if (ans != null) {
                if (ans instanceof String && ((String) ans).trim().isEmpty()) continue;
                answerMap.put(q.id, ans);
                answeredCount++;
            }
        }

        if (answeredCount == 0) {
            Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
            confirm.setTitle("Empty Submission");
            confirm.setHeaderText("You haven't answered any questions.");
            confirm.setContentText("Are you sure you want to submit a blank quiz?");
            Optional<ButtonType> result = confirm.showAndWait();
            if (result.isEmpty() || result.get() != ButtonType.OK) return;
        } else {
            Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
            confirm.setTitle("Submit Quiz");
            confirm.setHeaderText("Submit your answers?");
            confirm.setContentText("You have answered " + answeredCount + " out of " + questions.size() + " questions. Are you sure?");
            Optional<ButtonType> result = confirm.showAndWait();
            if (result.isEmpty() || result.get() != ButtonType.OK) return;
        }

        submitBtn.setDisable(true);
        Task<QuizAttemptDetail> task = new Task<>() {
            @Override
            protected QuizAttemptDetail call() throws Exception {
                return api.submitQuiz(attemptId, answerMap);
            }
        };
        task.setOnSucceeded(e -> {
            QuizAttemptDetail detail = task.getValue();
            Platform.runLater(() -> {
                submitted = true;
                if (timer != null) timer.stop();
                showResultAlert("✅ Quiz Submitted!", detail);
                closeQuiz();
            });
        });
        task.setOnFailed(e -> {
            Platform.runLater(() -> {
                submitBtn.setDisable(false);
                String error = task.getException().getMessage();
                if (error != null && error.contains("410")) {
                    autoSubmit("⏰ Time expired on server. Auto-submitting...");
                } else {
                    showToast("❌ Submission failed: " + error);
                    task.getException().printStackTrace();
                }
            });
        });
        new Thread(task).start();
    }

    /**
     * Handles forced automated quiz submission caused by timeout, lost window focus,
     * or explicit server expiry responses.
     *
     * @param reason Description text displayed in the completion alert header
     */
    private void autoSubmit(String reason) {
        if (submitted) return;
        submitted = true;
        if (timer != null) timer.stop();
        // Do not auto-submit if quiz hasn't started properly
        if (!isQuizStarted) {
            System.out.println("Auto-submit prevented: quiz not started.");
            closeQuiz();
            return;
        }
        
        Map<Integer, Object> answerMap = new HashMap<>();
        for (int i = 0; i < questions.size(); i++) {
            Question q = questions.get(i);
            Object ans = answers.get(i);
            if (ans != null) {
                if (ans instanceof String && ((String) ans).trim().isEmpty()) continue;
                answerMap.put(q.id, ans);
            }
        }

        Task<QuizAttemptDetail> task = new Task<>() {
            @Override
            protected QuizAttemptDetail call() throws Exception {
                return api.submitQuiz(attemptId, answerMap);
            }
        };
        task.setOnSucceeded(e -> {
            QuizAttemptDetail detail = task.getValue();
            Platform.runLater(() -> {
                showResultAlert(reason, detail);
                closeQuiz();
            });
        });
        task.setOnFailed(e -> {
            Platform.runLater(() -> {
                showToast("❌ Auto-submission failed. Please contact support.");
                closeQuiz();
            });
        });
        new Thread(task).start();
    }

    /**
     * Renders a modal confirmation dialog displaying final score metrics upon submission completion.
     *
     * @param header Banner text header for the result window
     * @param detail Submission response details containing correct response counts and percentages
     */
    private void showResultAlert(String header, QuizAttemptDetail detail) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Quiz Complete");
        alert.setHeaderText(header);
        alert.setContentText(String.format("Your Score: %d / %d (%.1f%%)",
                detail.correct, detail.totalQuestions, detail.percentage));
        alert.showAndWait();
    }

    /**
     * Helper method to render simple informational message dialogs.
     *
     * @param message Text payload to present in the dialog content area
     */
    private void showToast(String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Notification");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    /**
     * Configures window event listeners to prevent copy/paste keyboard shortcuts
     * and track window defocus events (focus loss warning & auto-submit enforcement).
     */
    private void setupLockdown() {
        quizTitleText.getScene().getRoot().setOnKeyPressed(e -> {
            if (e.isControlDown() && (e.getCode() == javafx.scene.input.KeyCode.C ||
                    e.getCode() == javafx.scene.input.KeyCode.V)) {
                e.consume();
            }
        });
        Stage stage = (Stage) quizTitleText.getScene().getWindow();
        stage.focusedProperty().addListener((obs, oldVal, newVal) -> {
            if (!newVal && timer != null && timer.getStatus() == Animation.Status.RUNNING && !submitted && isQuizStarted) {
                focusLossCount++;
                if (focusLossCount >= 3) {
                    autoSubmit("⚠️ Focus lost 3 times. Quiz auto-submitted.");
                } else {
                    Platform.runLater(() -> {
                        Alert alert = new Alert(Alert.AlertType.WARNING);
                        alert.setTitle("Warning");
                        alert.setHeaderText(null);
                        alert.setContentText("⚠️ Please stay focused on the quiz! (" + (3 - focusLossCount) + " warnings remaining)");
                        alert.showAndWait();
                    });
                }
            }
        });
    }

    /**
     * Stops active countdown timers, unbinds window event handlers, and triggers the onClose callback.
     */
    @FXML
    public void closeQuiz() {
        // Remove window close handler to prevent re-triggering
        Stage stage = (Stage) quizTitleText.getScene().getWindow();
        stage.setOnCloseRequest(null);

        if (timer != null) timer.stop();
        if (onClose != null) {
            Platform.runLater(onClose);
        }
    }

    /**
     * Performs stage resource teardown and halts active background timers.
     */
    public void cleanup() {
        if (timer != null) timer.stop();
        Stage stage = (Stage) quizTitleText.getScene().getWindow();
        stage.setOnCloseRequest(null);
    }
}