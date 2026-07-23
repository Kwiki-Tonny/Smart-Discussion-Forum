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

public class QuizController {

    // ─── FXML INJECTIONS ──────────────────────────────────────────
    @FXML private Text quizTitleText;
    @FXML private Text timerText;
    @FXML private Text qNumText;
    @FXML private Text qText;
    @FXML private VBox optionsContainer;
    @FXML private Region progressFill;
    @FXML private Text progressText;
    @FXML private Button prevBtn;
    @FXML private Button nextBtn;
    @FXML private Button submitBtn;

    // ─── STATE ─────────────────────────────────────────────────────
    private int currentQuestion = 0;
    // Stores answers keyed by question index (not ID) to preserve order
    // Values can be: Integer (single), List<Integer> (multiple), String (text)
    private Map<Integer, Object> answers = new HashMap<>();
    private int focusLossCount = 0;
    private Timeline timer;
    private int remainingSeconds = 0;
    private int attemptId = -1;
    private List<Question> questions = new ArrayList<>();
    private Runnable onClose;
    private boolean submitted = false;

    // ─── SERVICES ──────────────────────────────────────────────────
    private final ApiService api = ApiService.getInstance();

    // ─── INITIALIZATION ───────────────────────────────────────────

    @FXML
    public void initialize() {
        // Nothing to do here; setQuizData will be called
    }

    /**
     * Called by MainController to set up the quiz.
     * @param attempt The QuizAttempt object returned by the API
     * @param onClose Callback to run when the quiz is closed (releases lockdown)
     */
    public void setQuizData(QuizAttempt attempt, Runnable onClose) {
        this.attemptId = attempt.id;
        this.questions = attempt.quiz.questions;
        this.onClose = onClose;

        quizTitleText.setText(attempt.quiz.title);

        // Calculate remaining time based on startedAt + duration
        LocalDateTime startedAt = LocalDateTime.parse(attempt.startedAt, DateTimeFormatter.ISO_DATE_TIME);
        LocalDateTime expiresAt = startedAt.plusSeconds(attempt.durationSeconds);
        long remaining = ChronoUnit.SECONDS.between(LocalDateTime.now(), expiresAt);

        if (remaining <= 0) {
            Platform.runLater(() -> {
                autoSubmit("⏰ Time has already expired. Auto-submitting...");
            });
            return;
        }

        this.remainingSeconds = (int) remaining;

        // ─── WINDOW CLOSE HANDLER ────────────────────────────────
        Stage stage = (Stage) quizTitleText.getScene().getWindow();
        stage.setOnCloseRequest(e -> {
            if (!submitted) {
                e.consume(); // Prevent immediate close
                autoSubmit("⚠️ Window closed during quiz. Auto-submitted.");
            }
        });

        showQuestion(0);
        startTimer();
        setupLockdown();
    }

    // ─── QUESTION NAVIGATION ─────────────────────────────────────

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

        // ─── RENDER BASED ON QUESTION TYPE ──────────────────────
        if ("text".equalsIgnoreCase(q.type)) {
            // FREE TEXT: TextArea
            TextArea textArea = new TextArea();
            textArea.setPromptText("Type your answer here...");
            textArea.setPrefRowCount(3);
            textArea.setStyle("-fx-padding: 8px 12px; -fx-border-color: #d0d5dd; -fx-border-radius: 6px; -fx-background-radius: 6px; -fx-font-size: 14px;");
            textArea.setWrapText(true);
            if (selected != null) {
                textArea.setText((String) selected);
            }
            // Store the text in answers whenever it changes
            textArea.textProperty().addListener((obs, oldVal, newVal) -> answers.put(index, newVal));
            optionsContainer.getChildren().add(textArea);

        } else if ("multiple".equalsIgnoreCase(q.type)) {
            // MULTIPLE CHOICE: CheckBoxes
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
                    if (selectedList.contains(optionIndex)) {
                        cb.setSelected(true);
                    }
                    cb.setStyle("-fx-padding: 6px 12px; -fx-border-color: #e5e5e5; -fx-border-radius: 6px; " +
                            "-fx-background-radius: 6px; -fx-cursor: hand; -fx-background-color: #ffffff;");
                    cb.selectedProperty().addListener((obs, oldVal, newVal) -> {
                        List<Integer> currentList = (List<Integer>) answers.getOrDefault(index, new ArrayList<>());
                        if (newVal && !currentList.contains(optionIndex)) {
                            currentList.add(optionIndex);
                        } else if (!newVal) {
                            currentList.remove(Integer.valueOf(optionIndex));
                        }
                        // Sort to keep consistent order
                        Collections.sort(currentList);
                        answers.put(index, currentList);
                    });
                    optionsBox.getChildren().add(cb);
                }
                optionsContainer.getChildren().add(optionsBox);
            }

        } else {
            // SINGLE CHOICE (default): RadioButtons
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
                    if (selected != null && selected.equals(optionIndex)) {
                        rb.setSelected(true);
                    }
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

        // Navigation buttons
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

    @FXML
    public void handlePrevious() {
        if (currentQuestion > 0) showQuestion(currentQuestion - 1);
    }

    @FXML
    public void handleNext() {
        if (currentQuestion < questions.size() - 1) showQuestion(currentQuestion + 1);
    }

    // ─── TIMER ─────────────────────────────────────────────────────

    private void startTimer() {
        timer = new Timeline(new KeyFrame(Duration.seconds(1), e -> {
            remainingSeconds--;
            updateTimerUI(remainingSeconds);

            if (remainingSeconds <= 0) {
                timer.stop();
                autoSubmit("⏰ Time is up! Auto-submitting...");
            }
        }));
        timer.setCycleCount(Animation.INDEFINITE);
        timer.play();
    }

    private void updateTimerUI(int seconds) {
        int mins = seconds / 60;
        int secs = seconds % 60;
        timerText.setText(String.format("⏱️ %02d:%02d", mins, secs));

        if (seconds <= 60) {
            timerText.setStyle("-fx-fill: #ff6b6b;");
        } else {
            timerText.setStyle("-fx-fill: #000000;");
        }
    }

    // ─── SUBMIT ────────────────────────────────────────────────────

    @FXML
    public void handleSubmit() {
        if (submitted) return;

        // Check local time
        if (remainingSeconds <= 0) {
            autoSubmit("Time expired. Auto-submitting...");
            return;
        }

        // Build answers map: questionId -> answer (Integer, List<Integer>, or String)
        Map<Integer, Object> answerMap = new HashMap<>();
        int answeredCount = 0;
        for (int i = 0; i < questions.size(); i++) {
            Question q = questions.get(i);
            Object ans = answers.get(i);
            if (ans != null) {
                // For text, we store String; for single, Integer; for multiple, List<Integer>
                if (ans instanceof String && ((String) ans).trim().isEmpty()) {
                    // Skip empty text answers
                    continue;
                }
                answerMap.put(q.id, ans);
                answeredCount++;
            }
        }

        // Check if any questions were answered
        if (answeredCount == 0) {
            Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
            confirm.setTitle("Empty Submission");
            confirm.setHeaderText("You haven't answered any questions.");
            confirm.setContentText("Are you sure you want to submit a blank quiz?");
            Optional<ButtonType> result = confirm.showAndWait();
            if (result.isEmpty() || result.get() != ButtonType.OK) {
                return;
            }
        } else {
            Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
            confirm.setTitle("Submit Quiz");
            confirm.setHeaderText("Submit your answers?");
            confirm.setContentText("You have answered " + answeredCount + " out of " + questions.size() + " questions. Are you sure?");
            Optional<ButtonType> result = confirm.showAndWait();
            if (result.isEmpty() || result.get() != ButtonType.OK) {
                return;
            }
        }

        // Disable UI during submission
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
                    // 410 Gone means time expired on server – auto-submit anyway
                    autoSubmit("⏰ Time expired on server. Auto-submitting...");
                } else {
                    showToast("❌ Submission failed: " + error);
                    task.getException().printStackTrace();
                }
            });
        });
        new Thread(task).start();
    }

    private void autoSubmit(String reason) {
        if (submitted) return;
        submitted = true;
        if (timer != null) timer.stop();

        // Build answers map (whatever the student filled)
        Map<Integer, Object> answerMap = new HashMap<>();
        for (int i = 0; i < questions.size(); i++) {
            Question q = questions.get(i);
            Object ans = answers.get(i);
            if (ans != null) {
                if (ans instanceof String && ((String) ans).trim().isEmpty()) {
                    continue;
                }
                answerMap.put(q.id, ans);
            }
        }

        // Try to submit – backend will handle expired validation
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
                // Even if submission fails, we need to close the quiz.
                // Show a generic message.
                showToast("❌ Auto-submission failed. Please contact support.");
                closeQuiz();
            });
        });
        new Thread(task).start();
    }

    // ─── RESULT DISPLAY ───────────────────────────────────────────

    private void showResultAlert(String header, QuizAttemptDetail detail) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Quiz Complete");
        alert.setHeaderText(header);
        String content = String.format("Your Score: %d / %d (%.1f%%)",
                detail.correct, detail.totalQuestions, detail.percentage);
        alert.setContentText(content);
        alert.showAndWait();
    }

    private void showToast(String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Notification");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    // ─── LOCKDOWN & FOCUS LOSS ───────────────────────────────────

    private void setupLockdown() {
        // Disable copy/paste
        quizTitleText.getScene().getRoot().setOnKeyPressed(e -> {
            if (e.isControlDown() && (e.getCode() == javafx.scene.input.KeyCode.C ||
                    e.getCode() == javafx.scene.input.KeyCode.V)) {
                e.consume();
            }
        });

        // Focus loss detection – only count when the window itself loses focus
        Stage stage = (Stage) quizTitleText.getScene().getWindow();
        stage.focusedProperty().addListener((obs, oldVal, newVal) -> {
            if (!newVal && timer != null && timer.getStatus() == Animation.Status.RUNNING && !submitted) {
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

    // ─── CLOSE ─────────────────────────────────────────────────────

    private void closeQuiz() {
        // Remove window close handler to prevent re-triggering
        Stage stage = (Stage) quizTitleText.getScene().getWindow();
        stage.setOnCloseRequest(null);

        if (timer != null) timer.stop();
        if (onClose != null) {
            Platform.runLater(onClose);
        }
    }

    // ─── CLEANUP (optional, called when the view is destroyed) ──

    public void cleanup() {
        if (timer != null) timer.stop();
        // Remove window close handler
        Stage stage = (Stage) quizTitleText.getScene().getWindow();
        stage.setOnCloseRequest(null);
    }
}