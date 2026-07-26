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

/**
 * QuizResultsController manages the "Quiz Results" view within the Smart Discussion Forum application.
 * 
 * <p><b>Architectural Role:</b>
 * This class implements a Master-Detail UI pattern:
 * <ul>
 *   <li><b>Master (Left Panel):</b> Displays a scrollable list of the user's historical {@link QuizAttempt} records.</li>
 *   <li><b>Detail (Right Panel):</b> Displays a comprehensive, visually rich breakdown of a specific attempt's performance 
 *       (via {@link QuizAttemptDetail}) when a user clicks on a list item.</li>
 * </ul>
 * 
 * <p><b>Concurrency Model:</b>
 * Fetching detailed quiz results from the backend is a blocking network operation. This controller utilizes 
 * JavaFX {@link Task} to execute the API call on a background thread, preventing UI freezes. 
 * Upon completion, {@link Platform#runLater(Runnable)} is strictly used to marshal the UI updates back 
 * onto the JavaFX Application Thread, ensuring thread safety.
 * 
 * <p><b>State Management:</b>
 * Relies on the singleton {@link ApiService} for data retrieval. The list of attempts is injected 
 * externally (typically by {@link MainController}) via the {@link #setAttempts(List)} method.
 * 
 * @author Forum Development Team
 * @version 2.0
 * @see QuizAttempt
 * @see QuizAttemptDetail
 * @see ApiService
 */
public class QuizResultsController {

    // =========================================================================
    // ─── FXML INJECTIONS & STATE ─────────────────────────────────────────────
    // =========================================================================

    /** 
     * The left-hand sidebar container. Automatically populated by the FXMLLoader.
     * It holds the vertically stacked list of quiz attempt summary cards.
     */
    @FXML 
    private VBox resultsList;

    /** 
     * Reference to the main content area (right panel) where detailed quiz analytics are rendered.
     * This is injected externally by the parent controller (e.g., MainController) during navigation.
     */
    private VBox rightPanel;

    /** 
     * Cached list of quiz attempts for the current user. 
     * Populated via {@link #setAttempts(List)}.
     */
    private List<QuizAttempt> attempts;

    /** 
     * Singleton instance responsible for all HTTP network communications with the backend server.
     */
    private final ApiService api = ApiService.getInstance();

    // =========================================================================
    // ─── INITIALIZATION & STATE SETTERS ──────────────────────────────────────
    // =========================================================================

    /**
     * Injects the reference to the right-hand detail panel.
     * 
     * <p><b>Design Pattern:</b> This facilitates the Master-Detail architecture, allowing this 
     * controller to manipulate the main content area without needing direct access to the 
     * parent controller's FXML fields.
     * 
     * @param rightPanel The {@link VBox} container where quiz details will be rendered.
     */
    public void setRightPanel(VBox rightPanel) {
        this.rightPanel = rightPanel;
    }

    /**
     * Sets the list of quiz attempts and triggers the UI rendering process.
     * 
     * <p><b>Behavior:</b>
     * <ol>
     *   <li>Caches the provided list locally.</li>
     *   <li>Outputs debug information to the console for traceability.</li>
     *   <li>Calls {@link #populateList()} to generate the visual summary cards.</li>
     * </ol>
     * 
     * @param attempts The list of {@link QuizAttempt} objects to display. Can be null or empty.
     */
    public void setAttempts(List<QuizAttempt> attempts) {
        this.attempts = attempts;
        
        // Debug logging for troubleshooting data binding issues
        System.out.println("QuizResultsController.setAttempts: " + (attempts != null ? attempts.size() : 0) + " attempts");
        if (attempts != null) {
            for (QuizAttempt a : attempts) {
                System.out.println("  Attempt ID: " + a.id + ", quizTitle: " + a.quizTitle);
            }
        }
        
        populateList();
    }

    // =========================================================================
    // ─── UI HELPERS ──────────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Clears the results list and displays a prominent, user-friendly error message.
     * Used when the initial fetch of quiz attempts fails.
     * 
     * @param message The specific error message to display to the user.
     */
    public void showError(String message) {
        resultsList.getChildren().clear();
        Label error = new Label("❌ " + message);
        error.setStyle("-fx-padding: 20px; -fx-text-fill: #dc2626; -fx-wrap-text: true;");
        resultsList.getChildren().add(error);
    }

    // =========================================================================
    // ─── MASTER VIEW: POPULATE LIST ──────────────────────────────────────────
    // =========================================================================

    /**
     * Dynamically generates the UI nodes for the quiz attempts list.
     * 
     * <p><b>Rendering Logic:</b>
     * <ul>
     *   <li>Handles the empty state gracefully with a muted placeholder message.</li>
     *   <li>Iterates through the {@link #attempts} list, creating a styled {@link VBox} card for each.</li>
     *   <li>Implements a fallback for missing quiz titles (e.g., "Quiz #123").</li>
     *   <li>Dynamically styles the status badge (Completed vs. Incomplete) based on whether a score exists.</li>
     *   <li>Attaches a mouse click listener to each card to trigger {@link #showQuizDetail(QuizAttempt)}.</li>
     * </ul>
     */
    private void populateList() {
        resultsList.getChildren().clear();

        // Handle empty state
        if (attempts == null || attempts.isEmpty()) {
            Label empty = new Label("📭 No quiz attempts yet.");
            empty.setStyle("-fx-padding: 40px; -fx-text-fill: #999999; -fx-font-size: 14px; -fx-alignment: center;");
            resultsList.getChildren().add(empty);
            return;
        }

        // Build UI cards for each attempt
        for (QuizAttempt attempt : attempts) {
            VBox item = new VBox(4);
            item.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-width: 0 0 1px 0; " +
                    "-fx-padding: 12px 14px; -fx-cursor: hand;");
            item.setMaxWidth(Double.MAX_VALUE);
            
            // Attach click handler to load details in the right panel
            item.setOnMouseClicked(e -> showQuizDetail(attempt));

            HBox row = new HBox(8);
            row.setAlignment(Pos.CENTER_LEFT);
            row.setMaxWidth(Double.MAX_VALUE);

            VBox infoBox = new VBox(2);
            
            // Build the title with a reliable fallback in case the backend returns a null/empty title
            String title = (attempt.quizTitle != null && !attempt.quizTitle.trim().isEmpty())
                    ? attempt.quizTitle
                    : "Quiz #" + attempt.id;
            
            Label nameLabel = new Label(title);
            nameLabel.setStyle("-fx-font-size: 13px; -fx-font-weight: 600; -fx-text-fill: #000000;");
            nameLabel.setMaxWidth(Double.MAX_VALUE);

            // Format score display, handling the "Incomplete" state
            String scoreText = (attempt.score != 0) ? "Score: " + attempt.score + "/" + attempt.totalQuestions : "Incomplete";
            Label scoreLabel = new Label(scoreText);
            scoreLabel.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");
            
            infoBox.getChildren().addAll(nameLabel, scoreLabel);

            // Spacer to push the status badge to the far right
            Region spacer = new Region();
            HBox.setHgrow(spacer, Priority.ALWAYS);

            // Determine completion status and apply corresponding color scheme
            boolean completed = (attempt.score != 0);
            String status = completed ? "✅ Completed" : "⚠️ Incomplete";
            String statusColor = completed ? "#065f46" : "#b45309"; // Dark green vs. Dark amber
            String statusBg = completed ? "#d1fae5" : "#fef3c7";    // Light green vs. Light amber
            
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

    // =========================================================================
    // ─── DETAIL VIEW: LOAD & RENDER ──────────────────────────────────────────
    // =========================================================================

    /**
     * Initiates the asynchronous loading of detailed analytics for a specific quiz attempt.
     * 
     * <p><b>Concurrency & UX Flow:</b>
     * <ol>
     *   <li>Immediately clears the right panel and shows a "Loading..." placeholder to provide instant feedback.</li>
     *   <li>Spawns a background {@link Task} to call {@link ApiService#getQuizAttemptDetail(int)}.</li>
     *   <li>On success: Marshals the result to the JavaFX thread via {@link Platform#runLater} and calls {@link #renderDetail}.</li>
     *   <li>On failure: Marshals the error to the JavaFX thread and displays a user-friendly error message in the right panel.</li>
     * </ol>
     * 
     * @param attempt The {@link QuizAttempt} selected by the user.
     */
    private void showQuizDetail(QuizAttempt attempt) {
        // Safety check: ensure the right panel has been injected
        if (rightPanel == null) return;

        // Show immediate loading state
        rightPanel.getChildren().clear();
        VBox loadingBox = new VBox(12);
        loadingBox.setAlignment(Pos.CENTER);
        loadingBox.setPadding(new Insets(40));
        Label loadingLabel = new Label("📊 Loading details...");
        loadingLabel.setStyle("-fx-font-size: 14px; -fx-text-fill: #666666;");
        loadingBox.getChildren().add(loadingLabel);
        rightPanel.getChildren().add(loadingBox);

        // Background task for network request
        Task<QuizAttemptDetail> task = new Task<>() {
            @Override
            protected QuizAttemptDetail call() throws Exception {
                return api.getQuizAttemptDetail(attempt.id);
            }
        };
        
        task.setOnSucceeded(e -> {
            QuizAttemptDetail detail = task.getValue();
            // CRITICAL: UI updates must occur on the JavaFX Application Thread
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

    /**
     * Constructs and renders the detailed analytics view for a completed quiz attempt.
     * 
     * <p><b>UI Composition:</b>
     * <ul>
     *   <li><b>Header:</b> Quiz title with a fallback.</li>
     *   <li><b>Score Visualization:</b> Large percentage text and a styled {@link ProgressBar}.</li>
     *   <li><b>Primary Stats Grid:</b> 3-column layout showing Correct, Incorrect, and Total questions.</li>
     *   <li><b>Secondary Stats Grid:</b> 2-column layout showing Unanswered count and raw score fraction.</li>
     *   <li><b>Footer:</b> A note directing users to the web client for deeper analytics.</li>
     * </ul>
     * 
     * @param detail The {@link QuizAttemptDetail} object containing the granular performance data.
     */
    private void renderDetail(QuizAttemptDetail detail) {
        VBox detailBox = new VBox(20);
        detailBox.setPadding(new Insets(20));
        detailBox.setAlignment(Pos.TOP_CENTER);
        detailBox.setStyle("-fx-background-color: #ffffff;");
        detailBox.setMaxWidth(Double.MAX_VALUE);

        // Header with fallback title
        String title = (detail.quizTitle != null && !detail.quizTitle.trim().isEmpty())
                ? detail.quizTitle
                : "Quiz Results";
        Text titleText = new Text("📊 " + title);
        titleText.setStyle("-fx-font-size: 20px; -fx-font-weight: 700; -fx-fill: #000000;");
        Separator separator1 = new Separator();

        // Primary Score Visualization
        VBox scoreBox = new VBox(8);
        scoreBox.setAlignment(Pos.CENTER);
        Text scorePercentage = new Text(String.format("%.1f%%", detail.percentage));
        scorePercentage.setStyle("-fx-font-size: 36px; -fx-font-weight: 700; -fx-fill: #16a34a;"); // Vibrant green
        
        ProgressBar progressBar = new ProgressBar(detail.percentage / 100.0);
        progressBar.setPrefWidth(250);
        // Custom styling to match the application's green accent theme
        progressBar.setStyle("-fx-accent: #16a34a; -fx-background-color: #e5e5e5; -fx-background-radius: 10px; -fx-pref-height: 12px;");
        scoreBox.getChildren().addAll(scorePercentage, progressBar);

        // Primary Statistics Grid (Correct, Incorrect, Total)
        GridPane statsGrid = new GridPane();
        statsGrid.setHgap(30);
        statsGrid.setVgap(8);
        statsGrid.setAlignment(Pos.CENTER);
        statsGrid.setPadding(new Insets(10, 0, 10, 0));

        String[] headers = {"Correct", "Incorrect", "Total"};
        String[] colors = {"#16a34a", "#dc2626", "#000000"}; // Green, Red, Black
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

        // Secondary Statistics Grid (Unanswered, Raw Score)
        GridPane extraGrid = new GridPane();
        extraGrid.setHgap(20);
        extraGrid.setAlignment(Pos.CENTER);
        extraGrid.setPadding(new Insets(10, 0, 10, 0));

        VBox unansweredBox = new VBox(2);
        unansweredBox.setAlignment(Pos.CENTER);
        Label unansweredLabel = new Label("Unanswered");
        unansweredLabel.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");
        Text unansweredValue = new Text(String.valueOf(detail.unanswered));
        unansweredValue.setStyle("-fx-font-size: 20px; -fx-font-weight: 700; -fx-fill: #f59e0b;"); // Amber for warning/neutral
        unansweredBox.getChildren().addAll(unansweredLabel, unansweredValue);

        VBox scoreBox2 = new VBox(2);
        scoreBox2.setAlignment(Pos.CENTER);
        Label scoreLabel2 = new Label("Score");
        scoreLabel2.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");
        
        // Calculate total attempted + unanswered for the denominator
        int total = detail.correct + detail.incorrect + detail.unanswered;
        Text scoreValue = new Text(detail.correct + "/" + total);
        scoreValue.setStyle("-fx-font-size: 20px; -fx-font-weight: 700; -fx-fill: #000000;");
        scoreBox2.getChildren().addAll(scoreLabel2, scoreValue);

        extraGrid.add(unansweredBox, 0, 0);
        extraGrid.add(scoreBox2, 1, 0);

        Separator separator2 = new Separator();

        // Footer note
        Label footer = new Label("For detailed analytics, visit the web client.");
        footer.setStyle("-fx-font-size: 13px; -fx-text-fill: #999999; -fx-font-style: italic; -fx-padding: 10 0 0 0;");

        // Assemble the complete detail view
        detailBox.getChildren().addAll(
                titleText,
                separator1,
                scoreBox,
                statsGrid,
                extraGrid,
                separator2,
                footer
        );

        // Replace the loading state with the fully rendered detail view
        rightPanel.getChildren().clear();
        rightPanel.getChildren().add(detailBox);
        
        // Allow the detail box to expand and fill available vertical space
        VBox.setVgrow(detailBox, Priority.ALWAYS);
    }
}