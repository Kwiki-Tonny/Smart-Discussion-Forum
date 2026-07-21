package com.forum.Controllers;

import javafx.animation.Animation;
import javafx.animation.KeyFrame;
import javafx.animation.Timeline;
import javafx.fxml.FXML;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.layout.Region;
import javafx.scene.layout.VBox;
import javafx.scene.text.Text;
import javafx.stage.Stage;
import javafx.util.Duration;

import java.util.*;

public class QuizController {
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

    private int currentQuestion = 0;
    private Map<Integer, Object> answers = new HashMap<>();
    private int focusLossCount = 0;
    private Timeline timer;
    private int secondsRemaining = 300;
    private String quizTitle;
    private int quizIndex;
    private Runnable onClose;

    // Questions list (unchanged – 10 questions)
    private List<Question> questions = Arrays.asList(
            // ... your 10 questions ...
    );

    public void setQuizData(String title, int index, Runnable onClose) {
        this.quizTitle = title;
        this.quizIndex = index;
        this.onClose = onClose;
        quizTitleText.setText(title);
        initializeQuiz();
    }

    @FXML
    public void initialize() {
        // Delayed initialization
    }

    private void initializeQuiz() {
        showQuestion(0);
        startTimer();
        setupLockdown();
    }

    private void showQuestion(int index) {
        // ... (unchanged – same as your existing code) ...
    }

    @FXML
    public void handlePrevious() {
        if (currentQuestion > 0) showQuestion(currentQuestion - 1);
    }

    @FXML
    public void handleNext() {
        if (currentQuestion < questions.size() - 1) showQuestion(currentQuestion + 1);
    }

    @FXML
    public void handleSubmit() {
        int score = calculateScore();
        int total = questions.size();
        double percentage = (score * 100.0) / total;
        saveQuizResult(score, total, false);

        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Quiz Complete");
        alert.setHeaderText("✅ Quiz Submitted Successfully!");
        alert.setContentText(String.format("Your Score: %d / %d (%.1f%%)", score, total, percentage));
        alert.showAndWait();

        closeQuiz();
    }

    private int calculateScore() {
        return quizIndex;
        
        // ... (unchanged – same as your existing code) ...
    }

    private void saveQuizResult(int score, int total, boolean autoSubmitted) {
        String status = autoSubmitted ? "Auto-Submitted" : "Completed";
        String date = java.time.LocalDateTime.now().format(
                java.time.format.DateTimeFormatter.ofPattern("MMM dd, yyyy HH:mm"));
        System.out.println("Quiz result saved: " + quizTitle + " " + score + "/" + total + " " + status);
        // Optionally store in database
    }

    private void startTimer() {
        if (quizIndex == 0) secondsRemaining = 180;   // Physics
        else if (quizIndex == 1) secondsRemaining = 120; // Chemistry
        else secondsRemaining = 240;                  // Math

        timer = new Timeline(new KeyFrame(Duration.seconds(1), e -> {
            secondsRemaining--;
            int mins = secondsRemaining / 60;
            int secs = secondsRemaining % 60;
            timerText.setText(String.format("⏱️ %02d:%02d", mins, secs));

            if (secondsRemaining <= 60) {
                timerText.setStyle("-fx-fill: #ff6b6b;");
            }

            if (secondsRemaining <= 0) {
                timer.stop();
                autoSubmit("⏰ Time is up! Quiz auto-submitted.");
            }
        }));
        timer.setCycleCount(Animation.INDEFINITE);
        timer.play();
    }

    private void setupLockdown() {
        // Disable copy/paste
        quizTitleText.getScene().getRoot().setOnKeyPressed(e -> {
            if (e.isControlDown() && (e.getCode() == javafx.scene.input.KeyCode.C ||
                    e.getCode() == javafx.scene.input.KeyCode.V)) {
                e.consume();
            }
        });

        // Focus loss detection
        Stage stage = (Stage) quizTitleText.getScene().getWindow();
        stage.focusedProperty().addListener((obs, oldVal, newVal) -> {
            if (!newVal && timer != null && timer.getStatus() == Animation.Status.RUNNING) {
                focusLossCount++;
                if (focusLossCount >= 3) {
                    autoSubmit("⚠️ Focus lost 3 times. Quiz auto-submitted.");
                } else {
                    Alert alert = new Alert(Alert.AlertType.WARNING);
                    alert.setTitle("Warning");
                    alert.setHeaderText(null);
                    alert.setContentText("⚠️ Please stay focused on the quiz! (" + (3 - focusLossCount) + " warnings remaining)");
                    alert.showAndWait();
                }
            }
        });
    }

    private void autoSubmit(String reason) {
        if (timer != null) timer.stop();
        int score = calculateScore();
        int total = questions.size();
        double percentage = (score * 100.0) / total;
        saveQuizResult(score, total, true);

        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Quiz Auto-Submitted");
        alert.setHeaderText(reason);
        alert.setContentText(String.format("Your Score: %d / %d (%.1f%%)", score, total, percentage));
        alert.showAndWait();

        closeQuiz();
    }

    private void closeQuiz() {
        if (timer != null) timer.stop();
        if (onClose != null) onClose.run(); // This releases the lockdown via the callback
    }

    // Question classes (unchanged)
    private static class Question { /* ... */ }
    private enum QuestionType { SINGLE, MULTIPLE }
}