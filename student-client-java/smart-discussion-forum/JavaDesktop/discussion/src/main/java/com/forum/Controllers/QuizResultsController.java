package com.forum.controllers;

import com.forum.models.QuizAttempt;
import com.forum.models.QuizAttemptDetail;
import com.forum.services.ApiService;
import javafx.application.Platform;
import javafx.concurrent.Task;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.Label;
import javafx.scene.control.ProgressBar;
import javafx.scene.control.Separator;
import javafx.scene.layout.GridPane;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.Region;
import javafx.scene.layout.VBox;
import javafx.scene.text.Text;

import java.util.List;

public class QuizResultsController {

    @FXML private VBox resultsList;

    private VBox rightPanel;
    private List<QuizAttempt> attempts;
    private final ApiService api = ApiService.getInstance();

    public void setRightPanel(VBox rightPanel) {
        this.rightPanel = rightPanel;
    }

    public void setAttempts(List<QuizAttempt> attempts) {
        this.attempts = attempts;
        System.out.println("QuizResultsController.setAttempts: " + (attempts != null ? attempts.size() : 0) + " attempts");
        if (attempts != null) {
            for (QuizAttempt a : attempts) {
                System.out.println("  Attempt ID: " + a.id + ", quizTitle: " + a.quizTitle);
            }
        }
        populateList();
    }

    public void showError(String message) {
        resultsList.getChildren().clear();
        Label error = new Label("❌ " + message);
        error.setStyle("-fx-padding: 20px; -fx-text-fill: #dc2626; -fx-wrap-text: true;");
        resultsList.getChildren().add(error);
    }

    private void populateList() {
        resultsList.getChildren().clear();

        if (attempts == null || attempts.isEmpty()) {
            Label empty = new Label("📭 No quiz attempts yet.");
            empty.setStyle("-fx-padding: 40px; -fx-text-fill: #999999; -fx-font-size: 14px; -fx-alignment: center;");
            resultsList.getChildren().add(empty);
            return;
        }

        for (QuizAttempt attempt : attempts) {
            VBox item = new VBox(4);
            item.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-width: 0 0 1px 0; " +
                    "-fx-padding: 12px 14px; -fx-cursor: hand;");
            item.setMaxWidth(Double.MAX_VALUE);
            item.setOnMouseClicked(e -> showQuizDetail(attempt));

            HBox row = new HBox(8);
            row.setAlignment(Pos.CENTER_LEFT);
            row.setMaxWidth(Double.MAX_VALUE);

            VBox infoBox = new VBox(2);
            // Build the title with a reliable fallback
            String title = (attempt.quizTitle != null && !attempt.quizTitle.trim().isEmpty())
                    ? attempt.quizTitle
                    : "Quiz #" + attempt.id;
            Label nameLabel = new Label(title);
            nameLabel.setStyle("-fx-font-size: 13px; -fx-font-weight: 600; -fx-text-fill: #000000;");
            nameLabel.setMaxWidth(Double.MAX_VALUE);

            String scoreText = (attempt.score != 0) ? "Score: " + attempt.score + "/" + attempt.totalQuestions : "Incomplete";
            Label scoreLabel = new Label(scoreText);
            scoreLabel.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");
            infoBox.getChildren().addAll(nameLabel, scoreLabel);

            Region spacer = new Region();
            HBox.setHgrow(spacer, Priority.ALWAYS);

            boolean completed = (attempt.score != 0);
            String status = completed ? "✅ Completed" : "⚠️ Incomplete";
            String statusColor = completed ? "#065f46" : "#b45309";
            String statusBg = completed ? "#d1fae5" : "#fef3c7";
            Label statusLabel = new Label(status);
            statusLabel.setStyle("-fx-background-color: " + statusBg + "; -fx-text-fill: " + statusColor + "; " +
                    "-fx-font-size: 9px; -fx-font-weight: 600; -fx-padding: 2px 10px; -fx-background-radius: 12px;");

            row.getChildren().addAll(infoBox, spacer, statusLabel);
            item.getChildren().add(row);

            resultsList.getChildren().add(item);
            System.out.println("Added item for attempt ID: " + attempt.id + ", title: " + title);
        }
        System.out.println("Total items added to resultsList: " + resultsList.getChildren().size());
    }

    private void showQuizDetail(QuizAttempt attempt) {
        if (rightPanel == null) return;

        rightPanel.getChildren().clear();
        VBox loadingBox = new VBox(12);
        loadingBox.setAlignment(Pos.CENTER);
        loadingBox.setPadding(new Insets(40));
        Label loadingLabel = new Label("📊 Loading details...");
        loadingLabel.setStyle("-fx-font-size: 14px; -fx-text-fill: #666666;");
        loadingBox.getChildren().add(loadingLabel);
        rightPanel.getChildren().add(loadingBox);

        Task<QuizAttemptDetail> task = new Task<>() {
            @Override
            protected QuizAttemptDetail call() throws Exception {
                return api.getQuizAttemptDetail(attempt.id);
            }
        };
        task.setOnSucceeded(e -> {
            QuizAttemptDetail detail = task.getValue();
            Platform.runLater(() -> renderDetail(detail));
        });
        task.setOnFailed(e -> {
            Platform.runLater(() -> {
                rightPanel.getChildren().clear();
                Label error = new Label("❌ Failed to load details: " + task.getException().getMessage());
                error.setStyle("-fx-padding: 40px; -fx-text-fill: #dc2626; -fx-wrap-text: true; -fx-alignment: center;");
                rightPanel.getChildren().add(error);
                task.getException().printStackTrace();
            });
        });
        new Thread(task).start();
    }

    private void renderDetail(QuizAttemptDetail detail) {
        VBox detailBox = new VBox(20);
        detailBox.setPadding(new Insets(20));
        detailBox.setAlignment(Pos.TOP_CENTER);
        detailBox.setStyle("-fx-background-color: #ffffff;");
        detailBox.setMaxWidth(Double.MAX_VALUE);

        String title = (detail.quizTitle != null && !detail.quizTitle.trim().isEmpty())
                ? detail.quizTitle
                : "Quiz Results";
        Text titleText = new Text("📊 " + title);
        titleText.setStyle("-fx-font-size: 20px; -fx-font-weight: 700; -fx-fill: #000000;");
        Separator separator1 = new Separator();

        VBox scoreBox = new VBox(8);
        scoreBox.setAlignment(Pos.CENTER);
        Text scorePercentage = new Text(String.format("%.1f%%", detail.percentage));
        scorePercentage.setStyle("-fx-font-size: 36px; -fx-font-weight: 700; -fx-fill: #16a34a;");
        ProgressBar progressBar = new ProgressBar(detail.percentage / 100.0);
        progressBar.setPrefWidth(250);
        progressBar.setStyle("-fx-accent: #16a34a; -fx-background-color: #e5e5e5; -fx-background-radius: 10px; -fx-pref-height: 12px;");
        scoreBox.getChildren().addAll(scorePercentage, progressBar);

        GridPane statsGrid = new GridPane();
        statsGrid.setHgap(30);
        statsGrid.setVgap(8);
        statsGrid.setAlignment(Pos.CENTER);
        statsGrid.setPadding(new Insets(10, 0, 10, 0));

        String[] headers = {"Correct", "Incorrect", "Total"};
        String[] colors = {"#16a34a", "#dc2626", "#000000"};
        int[] values = {detail.correct, detail.incorrect, detail.totalQuestions};

        for (int i = 0; i < 3; i++) {
            VBox cell = new VBox(4);
            cell.setAlignment(Pos.CENTER);
            Label headerLabel = new Label(headers[i]);
            headerLabel.setStyle("-fx-font-size: 13px; -fx-text-fill: #666666; -fx-font-weight: 600;");
            Text valueText = new Text(String.valueOf(values[i]));
            valueText.setStyle("-fx-font-size: 28px; -fx-font-weight: 700; -fx-fill: " + colors[i] + ";");
            cell.getChildren().addAll(headerLabel, valueText);
            statsGrid.add(cell, i, 0);
        }

        GridPane extraGrid = new GridPane();
        extraGrid.setHgap(20);
        extraGrid.setAlignment(Pos.CENTER);
        extraGrid.setPadding(new Insets(10, 0, 10, 0));

        VBox unansweredBox = new VBox(2);
        unansweredBox.setAlignment(Pos.CENTER);
        Label unansweredLabel = new Label("Unanswered");
        unansweredLabel.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");
        Text unansweredValue = new Text(String.valueOf(detail.unanswered));
        unansweredValue.setStyle("-fx-font-size: 20px; -fx-font-weight: 700; -fx-fill: #f59e0b;");
        unansweredBox.getChildren().addAll(unansweredLabel, unansweredValue);

        VBox scoreBox2 = new VBox(2);
        scoreBox2.setAlignment(Pos.CENTER);
        Label scoreLabel2 = new Label("Score");
        scoreLabel2.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");
        int total = detail.correct + detail.incorrect + detail.unanswered;
        Text scoreValue = new Text(detail.correct + "/" + total);
        scoreValue.setStyle("-fx-font-size: 20px; -fx-font-weight: 700; -fx-fill: #000000;");
        scoreBox2.getChildren().addAll(scoreLabel2, scoreValue);

        extraGrid.add(unansweredBox, 0, 0);
        extraGrid.add(scoreBox2, 1, 0);

        Separator separator2 = new Separator();

        Label footer = new Label("For detailed analytics, visit the web client.");
        footer.setStyle("-fx-font-size: 13px; -fx-text-fill: #999999; -fx-font-style: italic; -fx-padding: 10 0 0 0;");

        detailBox.getChildren().addAll(
                titleText,
                separator1,
                scoreBox,
                statsGrid,
                extraGrid,
                separator2,
                footer
        );

        rightPanel.getChildren().clear();
        rightPanel.getChildren().add(detailBox);
        VBox.setVgrow(detailBox, Priority.ALWAYS);
    }
}