package com.forum;

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

import java.util.Arrays;
import java.util.List;

public class QuizResultsController {

    @FXML private VBox resultsList;

    private VBox rightPanel; // Reference to the right panel (threadArea)
    private List<ResultData> sampleResults;

    // Data class for quiz results
    private static class ResultData {
        String title;
        int total;
        int passed;
        int failed;
        int unanswered;
        double percentage;

        ResultData(String title, int total, int passed, int failed, int unanswered, double percentage) {
            this.title = title;
            this.total = total;
            this.passed = passed;
            this.failed = failed;
            this.unanswered = unanswered;
            this.percentage = percentage;
        }
    }

    @FXML
    public void initialize() {
        // Sample data (replace with database later)
        sampleResults = Arrays.asList(
                new ResultData("Physics 101 Midterm", 10, 10, 0, 0, 100.0),
                new ResultData("Chemistry Lab Quiz", 8, 6, 1, 1, 75.0),
                new ResultData("Mathematics Week 5", 12, 10, 1, 1, 83.3),
                new ResultData("Biology Cell Division", 10, 7, 2, 1, 70.0),
                new ResultData("Quantum Mechanics Basics", 5, 4, 0, 1, 80.0)
        );

        populateList();
    }

    /**
     * Called by MainController to pass the right panel reference.
     */
    public void setRightPanel(VBox rightPanel) {
        this.rightPanel = rightPanel;
    }

    private void populateList() {
        resultsList.getChildren().clear();

        for (ResultData res : sampleResults) {
            VBox item = new VBox(4);
            item.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-width: 0 0 1px 0; " +
                    "-fx-padding: 12px 14px; -fx-cursor: hand;");
            item.setOnMouseClicked(e -> showQuizDetail(res));

            HBox row = new HBox(8);
            row.setAlignment(Pos.CENTER_LEFT);

            VBox infoBox = new VBox(2);
            Label nameLabel = new Label(res.title);
            nameLabel.setStyle("-fx-font-size: 13px; -fx-font-weight: 600;");
            Label scoreLabel = new Label("Score: " + res.passed + "/" + res.total);
            scoreLabel.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");
            infoBox.getChildren().addAll(nameLabel, scoreLabel);

            Region spacer = new Region();
            HBox.setHgrow(spacer, Priority.ALWAYS);

            String status = res.passed == res.total ? "✅ Completed" : "⚠️ Incomplete";
            Label statusLabel = new Label(status);
            String statusColor = res.passed == res.total ? "#065f46" : "#b45309";
            String statusBg = res.passed == res.total ? "#d1fae5" : "#fef3c7";
            statusLabel.setStyle("-fx-background-color: " + statusBg + "; -fx-text-fill: " + statusColor + "; " +
                    "-fx-font-size: 9px; -fx-font-weight: 600; -fx-padding: 2px 10px; -fx-background-radius: 12px;");

            row.getChildren().addAll(infoBox, spacer, statusLabel);
            item.getChildren().add(row);
            resultsList.getChildren().add(item);
        }
    }

    private void showQuizDetail(ResultData result) {
        if (rightPanel == null) return;

        // Build the detail view programmatically
        VBox detailBox = new VBox(20);
        detailBox.setPadding(new Insets(20));
        detailBox.setAlignment(Pos.TOP_CENTER);
        detailBox.setStyle("-fx-background-color: #ffffff;");
        detailBox.setMaxWidth(Double.MAX_VALUE);

        // Title
        Text title = new Text("📊 " + result.title);
        title.setStyle("-fx-font-size: 20px; -fx-font-weight: 700; -fx-fill: #000000;");

        Separator separator1 = new Separator();

        // Score Percentage and Progress Bar
        VBox scoreBox = new VBox(8);
        scoreBox.setAlignment(Pos.CENTER);
        Text scorePercentage = new Text(String.format("%.1f%%", result.percentage));
        scorePercentage.setStyle("-fx-font-size: 36px; -fx-font-weight: 700; -fx-fill: #16a34a;");
        ProgressBar progressBar = new ProgressBar(result.percentage / 100.0);
        progressBar.setPrefWidth(250);
        progressBar.setStyle("-fx-accent: #16a34a; -fx-background-color: #e5e5e5; -fx-background-radius: 10px; -fx-pref-height: 12px;");
        scoreBox.getChildren().addAll(scorePercentage, progressBar);

        // Stats Grid (Correct, Incorrect, Total)
        GridPane statsGrid = new GridPane();
        statsGrid.setHgap(30);
        statsGrid.setVgap(8);
        statsGrid.setAlignment(Pos.CENTER);
        statsGrid.setPadding(new Insets(10, 0, 10, 0));

        // Headers
        String[] headers = {"Correct", "Incorrect", "Total"};
        String[] colors = {"#16a34a", "#dc2626", "#000000"};
        int[] values = {result.passed, result.failed, result.total};

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

        // Additional Stats: Unanswered and Score
        GridPane extraGrid = new GridPane();
        extraGrid.setHgap(20);
        extraGrid.setAlignment(Pos.CENTER);
        extraGrid.setPadding(new Insets(10, 0, 10, 0));

        VBox unansweredBox = new VBox(2);
        unansweredBox.setAlignment(Pos.CENTER);
        Label unansweredLabel = new Label("Unanswered");
        unansweredLabel.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");
        Text unansweredValue = new Text(String.valueOf(result.unanswered));
        unansweredValue.setStyle("-fx-font-size: 20px; -fx-font-weight: 700; -fx-fill: #f59e0b;");
        unansweredBox.getChildren().addAll(unansweredLabel, unansweredValue);

        VBox scoreBox2 = new VBox(2);
        scoreBox2.setAlignment(Pos.CENTER);
        Label scoreLabel2 = new Label("Score");
        scoreLabel2.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");
        Text scoreValue = new Text(result.passed + "/" + result.total);
        scoreValue.setStyle("-fx-font-size: 20px; -fx-font-weight: 700; -fx-fill: #000000;");
        scoreBox2.getChildren().addAll(scoreLabel2, scoreValue);

        extraGrid.add(unansweredBox, 0, 0);
        extraGrid.add(scoreBox2, 1, 0);

        Separator separator2 = new Separator();

        // Footer Message
        Label footer = new Label("For Detailed Analytics visit the web client");
        footer.setStyle("-fx-font-size: 13px; -fx-text-fill: #999999; -fx-font-style: italic; -fx-padding: 10 0 0 0;");

        detailBox.getChildren().addAll(
                title,
                separator1,
                scoreBox,
                statsGrid,
                extraGrid,
                separator2,
                footer
        );

        // Replace right panel content
        rightPanel.getChildren().clear();
        rightPanel.getChildren().add(detailBox);
        VBox.setVgrow(detailBox, Priority.ALWAYS);
    }

    // Refresh method to reload data from database
    public void refresh() {
        // In the future, load from DatabaseHandler here
        populateList();
    }
}