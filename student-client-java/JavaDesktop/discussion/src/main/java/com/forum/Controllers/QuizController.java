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
    private Runnable onClose; // callback to close the quiz view and return to quizzes

    // Sample questions (10 questions)
    private List<Question> questions = Arrays.asList(
            new Question("What is the pH of pure water at 25°C?",
                    Arrays.asList("5", "6", "7", "8"), 2, QuestionType.SINGLE),
            new Question("Which of the following are noble gases? (Select all that apply)",
                    Arrays.asList("Helium", "Neon", "Argon", "Oxygen"), Arrays.asList(0, 1, 2), QuestionType.MULTIPLE),
            new Question("What is the chemical symbol for Gold?",
                    Arrays.asList("Au", "Ag", "Fe", "Cu"), 0, QuestionType.SINGLE),
            new Question("Which of these are units of force? (Select all that apply)",
                    Arrays.asList("Newton", "Joule", "Pascal", "Dyne"), Arrays.asList(0, 3), QuestionType.MULTIPLE),
            new Question("What is the speed of light in vacuum (approx)?",
                    Arrays.asList("3×10⁸ m/s", "3×10⁶ m/s", "3×10¹⁰ m/s", "3×10⁴ m/s"), 0, QuestionType.SINGLE),
            new Question("Which planets are gas giants? (Select all that apply)",
                    Arrays.asList("Jupiter", "Saturn", "Mars", "Venus"), Arrays.asList(0, 1), QuestionType.MULTIPLE),
            new Question("What is the atomic number of Carbon?",
                    Arrays.asList("4", "6", "8", "12"), 1, QuestionType.SINGLE),
            new Question("Which of these are renewable energy sources? (Select all that apply)",
                    Arrays.asList("Solar", "Wind", "Coal", "Geothermal"), Arrays.asList(0, 1, 3), QuestionType.MULTIPLE),
            new Question("What is the SI unit of electric current?",
                    Arrays.asList("Volt", "Ampere", "Ohm", "Watt"), 1, QuestionType.SINGLE),
            new Question("Which elements are halogens? (Select all that apply)",
                    Arrays.asList("Fluorine", "Chlorine", "Bromine", "Sodium"), Arrays.asList(0, 1, 2), QuestionType.MULTIPLE)
    );

    /**
     * Called by MainController to set quiz data and close callback.
     * @param title quiz title
     * @param index quiz index (0,1,2) for timer duration
     * @param onClose callback to run when quiz is closed
     */
    public void setQuizData(String title, int index, Runnable onClose) {
        this.quizTitle = title;
        this.quizIndex = index;
        this.onClose = onClose;
        quizTitleText.setText(title);
        initializeQuiz();
    }

    @FXML
    public void initialize() {
        // Delayed initialization via setQuizData
    }

    private void initializeQuiz() {
        showQuestion(0);
        startTimer();
        setupLockdown();
    }

    private void showQuestion(int index) {
        if (index < 0 || index >= questions.size()) return;

        currentQuestion = index;
        Question q = questions.get(index);

        qNumText.setText("Question " + (index + 1) + " of " + questions.size());
        qText.setText(q.text);

        double progress = ((double) (index + 1) / questions.size()) * 100;
        
        progressText.setText((index + 1) + "/" + questions.size());

        optionsContainer.getChildren().clear();
        Object selected = answers.get(index);

        if (q.type == QuestionType.SINGLE) {
            ToggleGroup group = new ToggleGroup();
            for (int i = 0; i < q.options.size(); i++) {
                final int optionIndex = i;
                RadioButton rb = new RadioButton(q.options.get(optionIndex));
                rb.setToggleGroup(group);
                rb.setUserData(optionIndex);
                if (selected != null && selected.equals(optionIndex)) {
                    rb.setSelected(true);
                }
                rb.setStyle("-fx-padding: 8px 12px; -fx-border-color: #e5e5e5; -fx-border-radius: 6px; " +
                        "-fx-background-radius: 6px; -fx-cursor: hand; -fx-background-color: #ffffff;");
                rb.selectedProperty().addListener((obs, oldVal, newVal) -> {
                    if (newVal) {
                        answers.put(index, optionIndex);
                    }
                });
                optionsContainer.getChildren().add(rb);
            }
        } else if (q.type == QuestionType.MULTIPLE) {
            List<Integer> selectedList = selected != null ? (List<Integer>) selected : new ArrayList<>();
            for (int i = 0; i < q.options.size(); i++) {
                CheckBox cb = new CheckBox(q.options.get(i));
                cb.setUserData(i);
                if (selectedList.contains(i)) {
                    cb.setSelected(true);
                }
                cb.setStyle("-fx-padding: 8px 12px; -fx-border-color: #e5e5e5; -fx-border-radius: 6px; " +
                        "-fx-background-radius: 6px; -fx-cursor: hand; -fx-background-color: #ffffff;");
                int finalI = i;
                cb.selectedProperty().addListener((obs, oldVal, newVal) -> {
                    List<Integer> currentList = (List<Integer>) answers.getOrDefault(index, new ArrayList<>());
                    if (newVal && !currentList.contains(finalI)) {
                        currentList.add(finalI);
                    } else if (!newVal) {
                        currentList.remove(Integer.valueOf(finalI));
                    }
                    answers.put(index, currentList);
                });
                optionsContainer.getChildren().add(cb);
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

    @FXML
    public void handlePrevious() {
        if (currentQuestion > 0) {
            showQuestion(currentQuestion - 1);
        }
    }

    @FXML
    public void handleNext() {
        if (currentQuestion < questions.size() - 1) {
            showQuestion(currentQuestion + 1);
        }
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

    @FXML
    public void handleCloseQuiz() {
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Close Quiz");
        confirm.setHeaderText("Are you sure you want to close the quiz?");
        confirm.setContentText("Your progress will be lost.");
        if (confirm.showAndWait().orElse(ButtonType.CANCEL) == ButtonType.OK) {
            if (timer != null) timer.stop();
            closeQuiz();
        }
    }

    private int calculateScore() {
        int score = 0;
        for (int i = 0; i < questions.size(); i++) {
            Question q = questions.get(i);
            Object ans = answers.get(i);
            if (q.type == QuestionType.SINGLE) {
                if (ans != null && ans.equals(q.correctSingle)) {
                    score++;
                }
            } else if (q.type == QuestionType.MULTIPLE) {
                if (ans != null && ans instanceof List) {
                    List<Integer> userAnswers = (List<Integer>) ans;
                    List<Integer> correctAnswers = (List<Integer>) q.correctMultiple;
                    Collections.sort(userAnswers);
                    Collections.sort(correctAnswers);
                    if (userAnswers.equals(correctAnswers)) {
                        score++;
                    }
                }
            }
        }
        return score;
    }

    private void saveQuizResult(int score, int total, boolean autoSubmitted) {
        String status = autoSubmitted ? "Auto-Submitted" : "Completed";
        String date = java.time.LocalDateTime.now().format(
                java.time.format.DateTimeFormatter.ofPattern("MMM dd, yyyy HH:mm"));
        
    }

    private void startTimer() {
        // Set timer based on quiz index
        if (quizIndex == 0) secondsRemaining = 180;   // Physics: 3 min
        else if (quizIndex == 1) secondsRemaining = 120; // Chemistry: 2 min
        else secondsRemaining = 240;                  // Math: 4 min

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
        // Disable copy/paste on the entire quiz root
        quizTitleText.getScene().getRoot().setOnKeyPressed(e -> {
            if (e.isControlDown() && (e.getCode() == javafx.scene.input.KeyCode.C ||
                    e.getCode() == javafx.scene.input.KeyCode.V)) {
                e.consume();
            }
        });

        // Focus loss detection on the main window
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
        if (onClose != null) onClose.run();
    }

    // ---------- Inner classes for Question ----------
    private static class Question {
        String text;
        List<String> options;
        QuestionType type;
        Integer correctSingle;
        List<Integer> correctMultiple;

        Question(String text, List<String> options, int correct, QuestionType type) {
            this.text = text;
            this.options = options;
            this.type = type;
            this.correctSingle = correct;
        }

        Question(String text, List<String> options, List<Integer> correct, QuestionType type) {
            this.text = text;
            this.options = options;
            this.type = type;
            this.correctMultiple = correct;
        }
    }

    private enum QuestionType {
        SINGLE, MULTIPLE
    }
}