package com.forum.Controllers;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.HPos;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.input.Clipboard;
import javafx.scene.input.ClipboardContent;
import javafx.scene.layout.*;
import javafx.scene.shape.Circle;
import javafx.scene.text.Text;
import javafx.stage.Modality;
import javafx.stage.Stage;
import javafx.stage.StageStyle;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.*;

public class MainController {
    @FXML private Text userNameText;
    @FXML private Text contextTitle;
    @FXML private Button contextActionBtn;
    @FXML private VBox contextList;
    @FXML private VBox threadArea;
    @FXML private VBox replyForm;
    @FXML private TextArea replyText;
    @FXML private CheckBox privateCheck;
    @FXML private Circle statusDot;
    @FXML private Label statusLabel;
    @FXML private Text syncStatus;

    @FXML private Button navGroups;
    @FXML private Button navProfile;
    @FXML private Button navQuizzes;
    @FXML private Button navResults;

    // State
    private String currentView = "groups";
    private GroupData currentGroup;
    private TopicData currentTopic;
    private boolean isGroupJoined = false;
    private List<GroupData> groups = new ArrayList<>();
    private boolean quizActive = false;  // NEW: track if a quiz is currently displayed

    // Data classes
    private static class GroupData {
        int id;
        String name;
        String description;
        boolean joined;
        List<TopicData> topics = new ArrayList<>();
        List<String> members = new ArrayList<>();

        GroupData(int id, String name, String description) {
            this.id = id;
            this.name = name;
            this.description = description;
            this.joined = false;
            this.members = Arrays.asList("Dr. Smith", "Student A", "Student B", "Student C", "Student D");
        }
    }

    private static class TopicData {
        int id;
        String title;
        String author;
        String date;
        boolean isPrivate;
        boolean isPublic;
        List<String> visibleToMembers = new ArrayList<>();
        List<PostData> posts = new ArrayList<>();

        TopicData(int id, String title, String author, String date) {
            this.id = id;
            this.title = title;
            this.author = author;
            this.date = date;
            this.isPrivate = false;
            this.isPublic = true;
        }
    }

    private static class PostData {
        int id;
        String author;
        String initials;
        String date;
        String text;
        int likes;
        boolean liked;
        boolean isPrivate;
        List<PostData> replies = new ArrayList<>();

        PostData(int id, String author, String initials, String date, String text) {
            this.id = id;
            this.author = author;
            this.initials = initials;
            this.date = date;
            this.text = text;
            this.likes = 0;
            this.liked = false;
            this.isPrivate = false;
        }
    }

    @FXML
    public void initialize() {
        try {
            System.out.println("MainController.initialize: start");
            initializeSampleData();
            showGroups();
            System.out.println("MainController.initialize: done");
        } catch (Exception e) {
            System.err.println("Exception in MainController.initialize:");
            e.printStackTrace();
            throw e;
        }
    }

    private void initializeSampleData() {
        // Physics group
        GroupData physics = new GroupData(1, "Physics 101", "Discussion forum for Physics 101");
        physics.joined = true;
        TopicData topic1 = new TopicData(101, "Newton's Laws of Motion", "Dr. Smith", "2 days ago");
        topic1.posts.add(new PostData(1, "Dr. Smith", "DS", "2 days ago",
                "Newton's First Law states that an object will remain at rest or in uniform motion unless acted upon by an external force."));
        PostData reply1 = new PostData(2, "Student A", "SA", "1 day ago", "Can you give an example?");
        reply1.replies.add(new PostData(3, "Dr. Smith", "DS", "12 hours ago", "A book on a table is a good example. It stays at rest until you push it."));
        topic1.posts.add(reply1);
        topic1.posts.add(new PostData(6, "Student C", "SC", "1 day ago", "This is great! Can you explain the second law too?"));
        physics.topics.add(topic1);
        physics.topics.add(new TopicData(102, "Thermodynamics: First Law", "Student A", "5 days ago"));
        physics.topics.add(new TopicData(103, "Quantum Mechanics Basics", "Student B", "1 week ago"));

        // Chemistry group
        GroupData chemistry = new GroupData(2, "Chemistry Lab", "Discussion forum for Chemistry Lab");
        chemistry.topics.add(new TopicData(201, "Acid-Base Titration Techniques", "Dr. Lee", "3 days ago"));
        chemistry.topics.add(new TopicData(202, "Organic Chemistry Nomenclature", "Student D", "1 week ago"));

        // Math group
        GroupData math = new GroupData(3, "Mathematics", "Discussion forum for Mathematics");
        math.topics.add(new TopicData(301, "Calculus: Derivatives Explained", "Dr. Chen", "4 days ago"));

        // Biology group
        GroupData biology = new GroupData(4, "Biology", "Discussion forum for Biology");
        biology.topics.add(new TopicData(401, "Cell Division: Mitosis vs Meiosis", "Student E", "2 days ago"));

        groups.add(physics);
        groups.add(chemistry);
        groups.add(math);
        groups.add(biology);
    }

    // ==================== NAVIGATION ====================

    @FXML
    public void showGroups() {
        currentView = "groups";
        setActiveNav(navGroups);
        contextTitle.setText("Groups");
        contextActionBtn.setVisible(false);
        contextActionBtn.setManaged(false);
        replyForm.setVisible(false);
        replyForm.setManaged(false);
        renderGroups();
        // Clear thread area
        threadArea.getChildren().clear();
        VBox placeholder = new VBox(12);
        placeholder.setAlignment(Pos.CENTER);
        placeholder.setPadding(new Insets(60, 20, 60, 20));
        Label icon = new Label("📚");
        icon.setStyle("-fx-font-size: 32px; -fx-text-fill: #999999;");
        Label msg = new Label("Select a group to view topics");
        msg.setStyle("-fx-text-fill: #999999; -fx-font-size: 14px;");
        placeholder.getChildren().addAll(icon, msg);
        threadArea.getChildren().add(placeholder);
    }

   @FXML
public void showProfile() {
    currentView = "profile";
    setActiveNav(navProfile);
    contextTitle.setText("Profile");
    contextActionBtn.setVisible(false);
    contextActionBtn.setManaged(false);
    replyForm.setVisible(false);
    replyForm.setManaged(false);

    contextList.getChildren().clear();
    
    // Show performance stats in left panel
    VBox statsBox = new VBox(16);
    statsBox.setPadding(new Insets(16));
    statsBox.setStyle("-fx-background-color: #ffffff;");
    Label statsTitle = new Label("📊 Performance");
    statsTitle.setStyle("-fx-font-size: 14px; -fx-font-weight: 700; -fx-text-fill: #000000;");
    String[][] stats = {
        {"📝", "Total Posts", "42"},
        {"💬", "Total Replies", "78"},
        {"📈", "Insights", "+12% this week"},
        {"📊", "Analytics", "Active in 4 groups"}
    };
    for (String[] stat : stats) {
        HBox row = new HBox(12);
        row.setAlignment(Pos.CENTER_LEFT);
        row.setStyle("-fx-padding: 8px 0; -fx-border-color: #f0f0f0; -fx-border-width: 0 0 1px 0;");
        Label iconLabel = new Label(stat[0]);
        iconLabel.setStyle("-fx-font-size: 16px;");
        Label nameLabel = new Label(stat[1]);
        nameLabel.setStyle("-fx-font-size: 13px; -fx-text-fill: #333333;");
        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);
        Label valueLabel = new Label(stat[2]);
        valueLabel.setStyle("-fx-font-size: 13px; -fx-font-weight: 600; -fx-text-fill: #000000;");
        row.getChildren().addAll(iconLabel, nameLabel, spacer, valueLabel);
        statsBox.getChildren().add(row);
    }
    contextList.getChildren().add(statsBox);

    // --- PROFILE CARD (Main right panel) ---
    threadArea.getChildren().clear();

    // Outer container to center the card
    VBox outerWrapper = new VBox();
    outerWrapper.setAlignment(Pos.CENTER);
    outerWrapper.setPadding(new Insets(20));
    outerWrapper.setFillWidth(true);

    // Main card (bigger and centered)
    VBox profileBox = new VBox(20);                 // increased spacing
    profileBox.setPadding(new Insets(30));          // more padding inside
    profileBox.setStyle("-fx-background-color: #ffffff; " +
                        "-fx-border-color: #e5e5e5; " +
                        "-fx-border-radius: 12px; " +
                        "-fx-background-radius: 12px;");
    profileBox.setMaxWidth(700);                    // larger max width
    profileBox.setAlignment(Pos.CENTER);

    // --- Avatar row (unchanged content) ---
    HBox avatarRow = new HBox(16);
    avatarRow.setAlignment(Pos.CENTER_LEFT);
    Label avatar = new Label("JS");
    avatar.setStyle("-fx-min-width: 64px; -fx-min-height: 64px; -fx-background-radius: 50%; " +
                    "-fx-background-color: #000000; -fx-text-fill: #ffffff; " +
                    "-fx-font-size: 28px; -fx-font-weight: 600; -fx-alignment: center;");
    VBox infoBox = new VBox(4);
    Label nameLabel = new Label("Dr. Jane Smith");
    nameLabel.setStyle("-fx-font-size: 20px; -fx-font-weight: 700;");
    Label emailLabel = new Label("jane.smith@university.edu");
    emailLabel.setStyle("-fx-text-fill: #666666;");
    Label roleLabel = new Label("Lecturer");
    roleLabel.setStyle("-fx-background-color: #dbeafe; -fx-text-fill: #1d4ed8; " +
                       "-fx-font-size: 11px; -fx-font-weight: 600; -fx-padding: 2px 12px; " +
                       "-fx-background-radius: 12px;");
    infoBox.getChildren().addAll(nameLabel, emailLabel, roleLabel);
    avatarRow.getChildren().addAll(avatar, infoBox);

    // --- Stats Grid (evenly spread) ---
    GridPane statsGrid = new GridPane();
    statsGrid.setHgap(20);
    statsGrid.setVgap(12);
    statsGrid.setPadding(new Insets(20, 0, 0, 0));
    statsGrid.setAlignment(Pos.CENTER);

    // Make each column take 25% of the available width for even spread
    ColumnConstraints col1 = new ColumnConstraints();
    col1.setPercentWidth(25);
    col1.setHalignment(HPos.CENTER);
    ColumnConstraints col2 = new ColumnConstraints();
    col2.setPercentWidth(25);
    col2.setHalignment(HPos.CENTER);
    ColumnConstraints col3 = new ColumnConstraints();
    col3.setPercentWidth(25);
    col3.setHalignment(HPos.CENTER);
    ColumnConstraints col4 = new ColumnConstraints();
    col4.setPercentWidth(25);
    col4.setHalignment(HPos.CENTER);
    statsGrid.getColumnConstraints().addAll(col1, col2, col3, col4);

    String[][] profileStats = {
        {"📚", "Topics", "15"},
        {"📝", "Posts", "42"},
        {"💬", "Replies", "78"},
        {"📊", "Quizzes", "5"}
    };
    for (int i = 0; i < profileStats.length; i++) {
        VBox statBox = new VBox(4);
        statBox.setAlignment(Pos.CENTER);
        Label iconLabel = new Label(profileStats[i][0]);
        iconLabel.setStyle("-fx-font-size: 20px;");
        Label numLabel = new Label(profileStats[i][2]);
        numLabel.setStyle("-fx-font-size: 26px; -fx-font-weight: 700; -fx-text-fill: #000000;");
        Label descLabel = new Label(profileStats[i][1]);
        descLabel.setStyle("-fx-font-size: 13px; -fx-text-fill: #666666;");
        statBox.getChildren().addAll(iconLabel, numLabel, descLabel);
        statsGrid.add(statBox, i, 0);
    }

    // Assemble card
    profileBox.getChildren().addAll(avatarRow, statsGrid);
    outerWrapper.getChildren().add(profileBox);
    threadArea.getChildren().add(outerWrapper);
}
    @FXML
    public void showQuizzes() {
        currentView = "quizzes";
        setActiveNav(navQuizzes);
        contextTitle.setText("Quizzes");
        contextActionBtn.setVisible(false);
        contextActionBtn.setManaged(false);
        replyForm.setVisible(false);
        replyForm.setManaged(false);

        // Show quizzes in left panel
        contextList.getChildren().clear();
        
        VBox quizzesBox = new VBox(8);
        quizzesBox.setPadding(new Insets(12));
        
        String[][] quizzes = {
            {"Physics 101 Midterm", "10 questions · 3 min", "Due soon", "#fef2f2", "#dc2626"},
            {"Chemistry Lab Quiz", "8 questions · 2 min", "Open", "#dbeafe", "#1d4ed8"},
            {"Mathematics Week 5", "12 questions · 4 min", "Upcoming", "#d1fae5", "#065f46"}
        };

        for (int i = 0; i < quizzes.length; i++) {
            VBox card = new VBox(6);
            card.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-radius: 8px; -fx-background-radius: 8px; -fx-padding: 12px 14px;");
            card.setPrefWidth(240);
            
            HBox headerRow = new HBox();
            headerRow.setAlignment(Pos.CENTER_LEFT);
            Label titleLabel = new Label(quizzes[i][0]);
            titleLabel.setStyle("-fx-font-size: 14px; -fx-font-weight: 600;");
            Region spacer = new Region();
            HBox.setHgrow(spacer, Priority.ALWAYS);
            Label statusLabel = new Label(quizzes[i][2]);
            statusLabel.setStyle("-fx-background-color: " + quizzes[i][3] + "; -fx-text-fill: " + quizzes[i][4] + "; " +
                    "-fx-font-size: 9px; -fx-font-weight: 600; -fx-padding: 2px 10px; -fx-background-radius: 12px;");
            headerRow.getChildren().addAll(titleLabel, spacer, statusLabel);

            Label infoLabel = new Label(quizzes[i][1]);
            infoLabel.setStyle("-fx-text-fill: #666666; -fx-font-size: 12px;");

            Button startBtn = new Button("▶ Start Quiz");
            startBtn.setStyle("-fx-background-color: #000000; -fx-text-fill: #ffffff; -fx-padding: 4px; " +
                    "-fx-border-radius: 6px; -fx-background-radius: 6px; -fx-font-size: 12px;");
            startBtn.setMaxWidth(Double.MAX_VALUE);
            final int quizIndex = i;
            startBtn.setOnAction(e -> startQuiz(quizIndex, quizzes[quizIndex][0]));

            card.getChildren().addAll(headerRow, infoLabel, startBtn);
            quizzesBox.getChildren().add(card);
        }
        
        contextList.getChildren().add(quizzesBox);

        // Show placeholder in right panel if no quiz active
        if (!quizActive) {
            threadArea.getChildren().clear();
            VBox placeholder = new VBox(12);
            placeholder.setAlignment(Pos.CENTER);
            placeholder.setPadding(new Insets(60, 20, 60, 20));
            Label icon = new Label("📝");
            icon.setStyle("-fx-font-size: 32px; -fx-text-fill: #999999;");
            Label msg = new Label("Select a quiz to start");
            msg.setStyle("-fx-text-fill: #999999; -fx-font-size: 14px;");
            placeholder.getChildren().addAll(icon, msg);
            threadArea.getChildren().add(placeholder);
        }
    }
    
@FXML
public void showResults() {
    currentView = "results";
    setActiveNav(navResults);
    contextTitle.setText("Quiz Results");
    contextActionBtn.setVisible(false);
    contextActionBtn.setManaged(false);
    replyForm.setVisible(false);
    replyForm.setManaged(false);

    // Load the quiz result list into the left panel
    try {
        FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/forum/quiz_result.fxml"));
        Parent listView = loader.load();

        QuizResultsController controller = loader.getController();
        controller.setRightPanel(threadArea); // Pass the right panel reference

        contextList.getChildren().clear();
        contextList.getChildren().add(listView);
        VBox.setVgrow(listView, Priority.ALWAYS);

    } catch (Exception e) {
        e.printStackTrace();
        Label error = new Label("Could not load quiz results.");
        error.setStyle("-fx-padding: 20px; -fx-text-fill: #dc2626;");
        contextList.getChildren().add(error);
    }

    // Clear right panel and show placeholder
    threadArea.getChildren().clear();
    VBox placeholder = new VBox(12);
    placeholder.setAlignment(Pos.CENTER);
    placeholder.setPadding(new Insets(60, 20, 60, 20));
    Label icon = new Label("📊");
    icon.setStyle("-fx-font-size: 32px; -fx-text-fill: #999999;");
    Label msg = new Label("Select a quiz result to view detailed analytics");
    msg.setStyle("-fx-text-fill: #999999; -fx-font-size: 14px;");
    placeholder.getChildren().addAll(icon, msg);
    threadArea.getChildren().add(placeholder);
}

    @FXML
    public void handleCreateTopic() {
        if (currentGroup == null) {
            showToast("Please select a group first.");
            return;
        }
        showCreateTopicDialog(currentGroup);
    }

    private void setActiveNav(Button active) {
        navGroups.getStyleClass().remove("active");
        navProfile.getStyleClass().remove("active");
        navQuizzes.getStyleClass().remove("active");
        navResults.getStyleClass().remove("active");
        active.getStyleClass().add("active");
    }

    // ==================== GROUPS ====================

    private void renderGroups() {
        contextList.getChildren().clear();

        if (groups.isEmpty()) {
            Label empty = new Label("No groups available.");
            empty.setStyle("-fx-padding: 40px 20px; -fx-text-fill: #999999; -fx-font-size: 14px;");
            empty.setAlignment(Pos.CENTER);
            contextList.getChildren().add(empty);
            return;
        }

        for (GroupData group : groups) {
            VBox item = new VBox(4);
            item.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-width: 0 0 1px 0; " +
                    "-fx-padding: 12px 16px; -fx-cursor: hand;");
            item.setOnMouseClicked(e -> handleGroupClick(group));

            Label title = new Label(group.name);
            title.setStyle("-fx-font-size: 14px; -fx-font-weight: 600; -fx-text-fill: #000000;");

            Label desc = new Label(group.description);
            desc.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");

            HBox metaRow = new HBox(12);
            metaRow.setAlignment(Pos.CENTER_RIGHT);
            Label topicsLabel = new Label("📄 " + group.topics.size() + " topics");
            topicsLabel.setStyle("-fx-font-size: 11px; -fx-text-fill: #999999;");
            Label membersLabel = new Label("👤 " + group.members.size() + " members");
            membersLabel.setStyle("-fx-font-size: 11px; -fx-text-fill: #999999;");

            Region spacer = new Region();
            HBox.setHgrow(spacer, Priority.ALWAYS);

            Button joinBtn = new Button(group.joined ? "Leave" : "Join");
            joinBtn.setStyle("-fx-background-color: " + (group.joined ? "#dc2626" : "#000000") + "; " +
                    "-fx-text-fill: #ffffff; -fx-font-size: 11px; -fx-font-weight: 600; -fx-padding: 2px 14px; " +
                    "-fx-border-radius: 12px; -fx-background-radius: 12px;");
            joinBtn.setOnAction(e -> {
                e.consume();
                if (!group.joined) {
                    showCommunityRules(group);
                } else {
                    group.joined = false;
                    renderGroups();
                }
            });

            metaRow.getChildren().addAll(topicsLabel, membersLabel, spacer, joinBtn);
            item.getChildren().addAll(title, desc, metaRow);
            contextList.getChildren().add(item);
        }
    }

    private void handleGroupClick(GroupData group) {
        if (!group.joined) {
            showCommunityRules(group);
            return;
        }
        openGroupTopics(group);
    }

    private void showCommunityRules(GroupData group) {
        try {
            Stage rulesStage = new Stage();
            rulesStage.initModality(Modality.APPLICATION_MODAL);
            rulesStage.initStyle(StageStyle.UNDECORATED);
            rulesStage.setTitle("Community Rules");

            VBox root = new VBox(16);
            root.setStyle("-fx-background-color: #ffffff; -fx-border-radius: 16px; -fx-background-radius: 16px; " +
                    "-fx-padding: 0; -fx-effect: dropshadow(gaussian, rgba(0,0,0,0.3), 24, 0, 0, 8);");
            root.setPrefWidth(500);

            HBox header = new HBox(10);
            header.setAlignment(Pos.CENTER_RIGHT);
            header.setStyle("-fx-background-color: #000000; -fx-padding: 16px 20px; " +
                    "-fx-border-radius: 16px 16px 0 0; -fx-background-radius: 16px 16px 0 0;");
            Label title = new Label("Community Rules");
            title.setStyle("-fx-font-size: 16px; -fx-font-weight: 700; -fx-text-fill: #ffffff;");
            header.getChildren().add(title);

            VBox body = new VBox(12);
            body.setPadding(new Insets(20));

            String[][] rules = {
                    {"📜", "Be respectful — Maintain professional discourse at all times. Personal attacks, harassment, or exclusionary behavior will not be tolerated."},
                    {"🚫", "No spam — Keep the environment clean. Avoid repetitive posts, unauthorized self-promotion, or irrelevant content."},
                    {"🎯", "Stay on topic — Ensure your contributions align with the specific group's purpose."},
                    {"🔒", "Protect Privacy — Do not share sensitive internal data or personal information belonging to others."}
            };

            for (String[] rule : rules) {
                HBox ruleBox = new HBox(10);
                ruleBox.setAlignment(Pos.TOP_LEFT);
                Label icon = new Label(rule[0]);
                icon.setStyle("-fx-font-size: 14px;");
                Label text = new Label(rule[1]);
                text.setStyle("-fx-font-size: 13px; -fx-text-fill: #1e293b; -fx-wrap-text: true;");
                text.setMaxWidth(420);
                ruleBox.getChildren().addAll(icon, text);
                body.getChildren().add(ruleBox);
            }

            // Footer - Decline on left, Accept on right
            HBox footer = new HBox(8);
            footer.setAlignment(Pos.CENTER);
            footer.setStyle("-fx-padding: 12px 20px 20px 20px; -fx-border-color: #e5e5e5; -fx-border-width: 1px 0 0 0;");

            Button declineBtn = new Button("Decline");
            declineBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #666666; -fx-font-size: 13px; " +
                    "-fx-font-weight: 600; -fx-padding: 10px 30px; -fx-border-radius: 6px; -fx-background-radius: 6px; -fx-cursor: hand;");
            declineBtn.setOnAction(e -> rulesStage.close());

            Region footerSpacer = new Region();
            HBox.setHgrow(footerSpacer, Priority.ALWAYS);

            Button acceptBtn = new Button("Accept to Continue");
            acceptBtn.setStyle("-fx-background-color: #000000; -fx-text-fill: #ffffff; -fx-font-size: 13px; " +
                    "-fx-font-weight: 600; -fx-padding: 10px 30px; -fx-border-radius: 6px; -fx-background-radius: 6px; -fx-cursor: hand;");
            acceptBtn.setOnAction(e -> {
                group.joined = true;
                rulesStage.close();
                renderGroups();
                openGroupTopics(group);
            });

            footer.getChildren().addAll(declineBtn, footerSpacer, acceptBtn);
            root.getChildren().addAll(header, body, footer);

            Scene scene = new Scene(root);
            scene.getStylesheets().add(getClass().getResource("/com/demo/style.css").toExternalForm());
            rulesStage.setScene(scene);
            rulesStage.showAndWait();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    // ==================== TOPICS ====================

    private void openGroupTopics(GroupData group) {
        currentGroup = group;
        isGroupJoined = group.joined;
        contextTitle.setText(group.name);
        contextActionBtn.setVisible(true);
        contextActionBtn.setManaged(true);
        contextActionBtn.setText("+");
        contextActionBtn.setOnAction(e -> showCreateTopicDialog(group));

        replyForm.setVisible(false);
        replyForm.setManaged(false);
        
        renderTopics(group);
        
        // Show placeholder in thread area
        threadArea.getChildren().clear();
        VBox placeholder = new VBox(12);
        placeholder.setAlignment(Pos.CENTER);
        placeholder.setPadding(new Insets(60, 20, 60, 20));
        Label icon = new Label("💬");
        icon.setStyle("-fx-font-size: 32px; -fx-text-fill: #999999;");
        Label msg = new Label("Select a topic to view discussion");
        msg.setStyle("-fx-text-fill: #999999; -fx-font-size: 14px;");
        placeholder.getChildren().addAll(icon, msg);
        threadArea.getChildren().add(placeholder);
    }

    private void renderTopics(GroupData group) {
        contextList.getChildren().clear();

        if (group.topics.isEmpty()) {
            Label empty = new Label("No topics yet. Start a new discussion!");
            empty.setStyle("-fx-padding: 40px 20px; -fx-text-fill: #999999; -fx-font-size: 14px;");
            empty.setAlignment(Pos.CENTER);
            contextList.getChildren().add(empty);
            return;
        }

        for (TopicData topic : group.topics) {
            VBox item = new VBox(4);
            item.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-width: 0 0 1px 0; " +
                    "-fx-padding: 12px 16px; -fx-cursor: hand;");
            item.setOnMouseClicked(e -> openTopic(group, topic));

            Label title = new Label(topic.title);
            title.setStyle("-fx-font-size: 14px; -fx-font-weight: 600; -fx-text-fill: #000000;");

            Label sub = new Label("by " + topic.author + " • " + topic.date);
            sub.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");

            HBox metaRow = new HBox(12);
            metaRow.setAlignment(Pos.CENTER_LEFT);
            Label repliesLabel = new Label("💬 " + topic.posts.size() + " replies");
            repliesLabel.setStyle("-fx-font-size: 11px; -fx-text-fill: #999999;");

            Label tagLabel = new Label("General");
            tagLabel.setStyle("-fx-font-size: 9px; -fx-font-weight: 700; -fx-padding: 1px 10px; " +
                    "-fx-background-radius: 12px; -fx-background-color: #e5e5e5; -fx-text-fill: #333333;");

            if (topic.isPrivate) {
                Label privateLabel = new Label("🔒 Private");
                privateLabel.setStyle("-fx-font-size: 9px; -fx-font-weight: 700; -fx-padding: 1px 10px; " +
                        "-fx-background-radius: 12px; -fx-background-color: #fef3c7; -fx-text-fill: #b45309;");
                metaRow.getChildren().addAll(repliesLabel, tagLabel, privateLabel);
            } else {
                metaRow.getChildren().addAll(repliesLabel, tagLabel);
            }

            item.getChildren().addAll(title, sub, metaRow);
            contextList.getChildren().add(item);
        }
    }

    // ==================== THREAD / CHAT ====================

    private void openTopic(GroupData group, TopicData topic) {
        currentTopic = topic;
        contextTitle.setText(group.name);
        contextActionBtn.setVisible(true);
        contextActionBtn.setManaged(true);
        contextActionBtn.setText("+");
        contextActionBtn.setOnAction(e -> showCreateTopicDialog(group));

        replyForm.setVisible(true);
        replyForm.setManaged(true);

        renderThread(group, topic);
        renderTopics(group);
    }

    private void renderThread(GroupData group, TopicData topic) {
        threadArea.getChildren().clear();

        // Top bar with Back arrow and Share button
        HBox topBar = new HBox(12);
        topBar.setAlignment(Pos.CENTER_LEFT);
        topBar.setStyle("-fx-padding: 0 0 12 0;");

        Button backBtn = new Button("← Back");
        backBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #666666; -fx-font-size: 13px; -fx-cursor: hand;");
        backBtn.setOnAction(e -> openGroupTopics(group));

        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        Button shareBtn = new Button("📤 Share");
        shareBtn.setStyle("-fx-background-color: transparent; -fx-border-color: #e5e5e5; -fx-border-radius: 6px; " +
                "-fx-padding: 4px 14px; -fx-font-size: 12px; -fx-text-fill: #333333; -fx-cursor: hand;");
        shareBtn.setOnAction(e -> shareTopic(topic));

        Button exportBtn = new Button("📄 Export PDF");
        exportBtn.setStyle("-fx-background-color: transparent; -fx-border-color: #e5e5e5; -fx-border-radius: 6px; " +
                "-fx-padding: 4px 14px; -fx-font-size: 12px; -fx-text-fill: #333333; -fx-cursor: hand;");
        exportBtn.setOnAction(e -> exportToPDF(topic));

        topBar.getChildren().addAll(backBtn, spacer, shareBtn, exportBtn);

        // Thread header
        Label title = new Label(topic.title);
        title.setStyle("-fx-font-size: 20px; -fx-font-weight: 700; -fx-text-fill: #000000;");

        HBox metaRow = new HBox(16);
        metaRow.setPadding(new Insets(0, 0, 12, 0));
        Label authorLabel = new Label("by " + topic.author + " • " + topic.date);
        authorLabel.setStyle("-fx-font-size: 13px; -fx-text-fill: #666666;");

        // Posts container with ScrollPane for scrolling
        VBox postsContainer = new VBox(10);
        postsContainer.setPadding(new Insets(0, 0, 16, 0));

        if (topic.posts.isEmpty()) {
            VBox emptyBox = new VBox(8);
            emptyBox.setAlignment(Pos.CENTER);
            emptyBox.setPadding(new Insets(30, 20, 30, 20));
            Label emptyIcon = new Label("💬");
            emptyIcon.setStyle("-fx-font-size: 24px; -fx-text-fill: #999999;");
            Label emptyMsg = new Label("No posts yet. Be the first to reply!");
            emptyMsg.setStyle("-fx-text-fill: #999999; -fx-font-size: 14px;");
            emptyBox.getChildren().addAll(emptyIcon, emptyMsg);
            postsContainer.getChildren().add(emptyBox);
        } else {
            for (PostData post : topic.posts) {
                VBox postBox = createPostView(post, topic);
                postsContainer.getChildren().add(postBox);
            }
        }

        // Wrap posts in ScrollPane
        ScrollPane scrollPane = new ScrollPane(postsContainer);
        scrollPane.setFitToWidth(true);
        scrollPane.setStyle("-fx-background-color: transparent; -fx-background: transparent;");
        scrollPane.getStyleClass().add("thread-scroll");

        // Add all to thread area (header stays static, scrollable content below)
        threadArea.getChildren().addAll(topBar, title, metaRow, scrollPane);
        VBox.setVgrow(scrollPane, Priority.ALWAYS);
    }

    private VBox createPostView(PostData post, TopicData topic) {
        VBox postBox = new VBox(6);
        postBox.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-radius: 8px; " +
                "-fx-background-radius: 8px; -fx-padding: 14px 18px;");

        // Header
        HBox header = new HBox(10);
        header.setAlignment(Pos.CENTER_LEFT);

        Label avatar = new Label(post.initials);
        avatar.setStyle("-fx-min-width: 28px; -fx-min-height: 28px; -fx-background-radius: 50%; " +
                "-fx-background-color: #e5e5e5; -fx-alignment: center; -fx-font-weight: 600; -fx-font-size: 12px; " +
                "-fx-text-fill: #000000;");

        Label name = new Label(post.author);
        name.setStyle("-fx-font-weight: 600; -fx-font-size: 14px; -fx-text-fill: #000000;");

        Label time = new Label(post.date);
        time.setStyle("-fx-font-size: 12px; -fx-text-fill: #999999;");

        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        Label likeBtn = new Label((post.liked ? "❤️" : "🤍") + " " + post.likes);
        likeBtn.setStyle("-fx-font-size: 13px; -fx-text-fill: " + (post.liked ? "#dc2626" : "#666666") + "; " +
                "-fx-padding: 4px 10px; -fx-background-radius: 20px; -fx-background-color: #f5f5f5; -fx-cursor: hand;");
        likeBtn.setOnMouseClicked(e -> toggleLike(post, topic));

        if (post.isPrivate) {
            Label privateTag = new Label("🔒 Private");
            privateTag.setStyle("-fx-font-size: 9px; -fx-font-weight: 700; -fx-padding: 2px 10px; " +
                    "-fx-background-radius: 12px; -fx-background-color: #fef3c7; -fx-text-fill: #b45309;");
            header.getChildren().addAll(avatar, name, time, spacer, privateTag, likeBtn);
        } else {
            header.getChildren().addAll(avatar, name, time, spacer, likeBtn);
        }

        // Body
        Label body = new Label(post.text);
        body.setStyle("-fx-font-size: 14px; -fx-text-fill: #1e293b; -fx-wrap-text: true;");
        body.setMaxWidth(Double.MAX_VALUE);

        // Actions
        HBox actions = new HBox(8);
        actions.setPadding(new Insets(8, 0, 0, 0));

        Button replyBtn = new Button("💬 Reply");
        replyBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #666666; -fx-font-size: 12px; " +
                "-fx-padding: 4px 8px; -fx-border-radius: 4px; -fx-cursor: hand;");
        replyBtn.setOnAction(e -> showInlineReply(post, topic));

        Button sharePostBtn = new Button("📤 Share");
        sharePostBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #666666; -fx-font-size: 12px; " +
                "-fx-padding: 4px 8px; -fx-border-radius: 4px; -fx-cursor: hand;");
        sharePostBtn.setOnAction(e -> sharePost(post));

        actions.getChildren().addAll(replyBtn, sharePostBtn);

        postBox.getChildren().addAll(header, body, actions);

        // Replies (nested)
        if (!post.replies.isEmpty()) {
            VBox repliesBox = new VBox(6);
            repliesBox.setPadding(new Insets(8, 0, 0, 16));
            repliesBox.setStyle("-fx-border-color: #e5e5e5; -fx-border-width: 0 0 0 2px;");

            for (PostData reply : post.replies) {
                VBox replyView = createPostView(reply, topic);
                replyView.setStyle("-fx-border-color: #e5e5e5; -fx-border-width: 0 0 0 2px; " +
                        "-fx-border-radius: 0 8px 8px 0; -fx-background-radius: 0 8px 8px 0; " +
                        "-fx-padding: 10px 14px; -fx-background-color: #ffffff;");
                repliesBox.getChildren().add(replyView);
            }

            postBox.getChildren().add(repliesBox);
        }

        return postBox;
    }

    // ==================== INLINE REPLY ====================

    private void showInlineReply(PostData parentPost, TopicData topic) {
        // Find the post box
        VBox postBox = findParentPostBox(parentPost);
        if (postBox == null) return;

        // Check if reply input already exists
        for (var child : postBox.getChildren()) {
            if (child instanceof HBox && ((HBox) child).getStyleClass().contains("reply-input")) {
                postBox.getChildren().remove(child);
                return; // Toggle off
            }
        }

        // Create reply input area
        HBox replyInput = new HBox(8);
        replyInput.setPadding(new Insets(8, 0, 0, 0));
        replyInput.setAlignment(Pos.CENTER_RIGHT);
        replyInput.getStyleClass().add("reply-input");

        TextArea replyArea = new TextArea();
        replyArea.setPromptText("Write a reply…");
        replyArea.setPrefRowCount(2);
        replyArea.setPrefWidth(400);
        replyArea.setStyle("-fx-border-color: #e5e5e5; -fx-border-radius: 6px; -fx-background-radius: 6px; " +
                "-fx-padding: 8px 12px; -fx-font-size: 13px;");

        CheckBox privateReply = new CheckBox("🔒 Private");
        privateReply.setStyle("-fx-font-size: 12px;");

        Button submitReply = new Button("Post");
        submitReply.setStyle("-fx-background-color: #000000; -fx-text-fill: #ffffff; -fx-padding: 6px 20px; " +
                "-fx-border-radius: 6px; -fx-background-radius: 6px; -fx-font-size: 12px; -fx-font-weight: 600; -fx-cursor: hand;");
        submitReply.setOnAction(e -> {
            String text = replyArea.getText().trim();
            if (!text.isEmpty()) {
                PostData newReply = new PostData(
                        (int) (System.currentTimeMillis() / 1000),
                        "Dr. Smith", "DS", "Just now",
                        text + (privateReply.isSelected() ? " 🔒 Private" : "")
                );
                newReply.isPrivate = privateReply.isSelected();
                parentPost.replies.add(newReply);
                renderThread(currentGroup, currentTopic);
            }
        });

        Button cancelReply = new Button("Cancel");
        cancelReply.setStyle("-fx-background-color: transparent; -fx-text-fill: #666666; -fx-font-size: 12px; -fx-cursor: hand;");
        cancelReply.setOnAction(e -> {
            postBox.getChildren().remove(replyInput);
        });

        replyInput.getChildren().addAll(replyArea, privateReply, submitReply, cancelReply);

        // Find where to insert (before replies if any)
        int insertIndex = postBox.getChildren().size();
        for (int i = postBox.getChildren().size() - 1; i >= 0; i--) {
            if (postBox.getChildren().get(i) instanceof VBox) {
                insertIndex = i;
            }
        }
        postBox.getChildren().add(insertIndex, replyInput);
    }

    private VBox findParentPostBox(PostData post) {
        // Search in thread area
        for (var node : threadArea.getChildren()) {
            if (node instanceof ScrollPane) {
                ScrollPane sp = (ScrollPane) node;
                if (sp.getContent() instanceof VBox) {
                    VBox container = (VBox) sp.getContent();
                    for (var child : container.getChildren()) {
                        VBox found = findPostInVBox(child, post);
                        if (found != null) return found;
                    }
                }
            }
        }
        return null;
    }

    private VBox findPostInVBox(javafx.scene.Node node, PostData post) {
        if (node instanceof VBox) {
            VBox vbox = (VBox) node;
            // Check if this is the post we're looking for
            for (var child : vbox.getChildren()) {
                if (child instanceof HBox) {
                    HBox header = (HBox) child;
                    for (var inner : header.getChildren()) {
                        if (inner instanceof Label) {
                            Label label = (Label) inner;
                            if (label.getText().equals(post.author) && label.getStyleClass().contains("name")) {
                                return vbox;
                            }
                        }
                    }
                }
            }
            // Recursively search children
            for (var child : vbox.getChildren()) {
                VBox found = findPostInVBox(child, post);
                if (found != null) return found;
            }
        }
        return null;
    }

    // ==================== LIKE ====================

    private void toggleLike(PostData post, TopicData topic) {
        post.liked = !post.liked;
        post.likes += post.liked ? 1 : -1;
        renderThread(currentGroup, currentTopic);
    }

    // ==================== SHARE ====================

    private void shareTopic(TopicData topic) {
        Clipboard clipboard = Clipboard.getSystemClipboard();
        ClipboardContent content = new ClipboardContent();
        content.putString("Topic: " + topic.title + "\nAuthor: " + topic.author + "\nDate: " + topic.date);
        clipboard.setContent(content);
        showToast("Topic link copied to clipboard!");
    }

    private void sharePost(PostData post) {
        Clipboard clipboard = Clipboard.getSystemClipboard();
        ClipboardContent content = new ClipboardContent();
        content.putString("Post by " + post.author + ": " + post.text);
        clipboard.setContent(content);
        showToast("Post copied to clipboard!");
    }

    // ==================== EXPORT TO PDF ====================

    private void exportToPDF(TopicData topic) {
        try {
            String timestamp = LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyyMMdd_HHmmss"));
            String filename = "chat_export_" + topic.title.replaceAll(" ", "_") + "_" + timestamp + ".txt";
            File file = new File(System.getProperty("user.home") + "/Downloads/" + filename);
            
            try (FileWriter writer = new FileWriter(file)) {
                writer.write("=== " + topic.title + " ===\n");
                writer.write("Author: " + topic.author + "\n");
                writer.write("Date: " + topic.date + "\n\n");
                
                for (PostData post : topic.posts) {
                    appendPostToFile(writer, post, 0);
                }
            }
            
            showToast("✅ Chat exported to: " + file.getAbsolutePath());
        } catch (IOException e) {
            e.printStackTrace();
            showToast("❌ Error exporting chat: " + e.getMessage());
        }
    }

    private void appendPostToFile(FileWriter writer, PostData post, int indent) throws IOException {
        String indentStr = "  ".repeat(indent);
        writer.write(indentStr + post.author + " (" + post.date + "):\n");
        writer.write(indentStr + "  " + post.text + "\n");
        if (post.isPrivate) {
            writer.write(indentStr + "  [PRIVATE]\n");
        }
        for (PostData reply : post.replies) {
            appendPostToFile(writer, reply, indent + 1);
        }
    }

    // ==================== CREATE TOPIC ====================

    private void showCreateTopicDialog(GroupData group) {
        try {
            Stage createStage = new Stage();
            createStage.initModality(Modality.APPLICATION_MODAL);
            createStage.initStyle(StageStyle.UNDECORATED);
            createStage.setTitle("Create New Topic");

            VBox root = new VBox(16);
            root.setStyle("-fx-background-color: #ffffff; -fx-border-radius: 16px; -fx-background-radius: 16px; " +
                    "-fx-padding: 0; -fx-effect: dropshadow(gaussian, rgba(0,0,0,0.3), 24, 0, 0, 8);");
            root.setPrefWidth(480);

            HBox header = new HBox(10);
            header.setAlignment(Pos.CENTER_RIGHT);
            header.setStyle("-fx-background-color: #000000; -fx-padding: 16px 20px; " +
                    "-fx-border-radius: 16px 16px 0 0; -fx-background-radius: 16px 16px 0 0;");
            Label title = new Label("Create New Topic");
            title.setStyle("-fx-font-size: 16px; -fx-font-weight: 700; -fx-text-fill: #ffffff;");
            Region spacer = new Region();
            HBox.setHgrow(spacer, Priority.ALWAYS);
            Button closeBtn = new Button("✕");
            closeBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #ffffff; -fx-font-size: 16px; -fx-cursor: hand;");
            closeBtn.setOnAction(e -> createStage.close());
            header.getChildren().addAll(title, spacer, closeBtn);

            VBox body = new VBox(12);
            body.setPadding(new Insets(20));

            VBox titleField = new VBox(4);
            Label titleLabel = new Label("Topic Title");
            titleLabel.setStyle("-fx-font-size: 11px; -fx-font-weight: 600; -fx-text-fill: #000000;");
            TextField titleInput = new TextField();
            titleInput.setPromptText("Enter topic title");
            titleInput.setStyle("-fx-padding: 8px 12px; -fx-border-color: #e5e5e5; -fx-border-radius: 6px; " +
                    "-fx-background-radius: 6px; -fx-font-size: 14px;");
            titleField.getChildren().addAll(titleLabel, titleInput);

            VBox descField = new VBox(4);
            Label descLabel = new Label("Description (Optional)");
            descLabel.setStyle("-fx-font-size: 11px; -fx-font-weight: 600; -fx-text-fill: #000000;");
            TextArea descInput = new TextArea();
            descInput.setPromptText("Provide additional context for your topic");
            descInput.setPrefRowCount(3);
            descInput.setStyle("-fx-padding: 8px 12px; -fx-border-color: #e5e5e5; -fx-border-radius: 6px; " +
                    "-fx-background-radius: 6px; -fx-font-size: 14px;");
            descField.getChildren().addAll(descLabel, descInput);

            VBox visibilityField = new VBox(8);
            Label visibilityLabel = new Label("Visibility");
            visibilityLabel.setStyle("-fx-font-size: 11px; -fx-font-weight: 600; -fx-text-fill: #000000;");

            ToggleGroup visibilityGroup = new ToggleGroup();
            RadioButton publicRadio = new RadioButton("Public");
            publicRadio.setToggleGroup(visibilityGroup);
            publicRadio.setSelected(true);
            RadioButton privateRadio = new RadioButton("Private");
            privateRadio.setToggleGroup(visibilityGroup);

            VBox visibilityOptions = new VBox(4);
            visibilityOptions.getChildren().addAll(publicRadio, privateRadio);

            VBox memberField = new VBox(4);
            Label memberLabel = new Label("Select Members (for private topics)");
            memberLabel.setStyle("-fx-font-size: 11px; -fx-font-weight: 600; -fx-text-fill: #000000;");

            ListView<String> memberList = new ListView<>();
            memberList.getItems().addAll(group.members);
            memberList.getSelectionModel().setSelectionMode(javafx.scene.control.SelectionMode.MULTIPLE);
            memberList.setPrefHeight(80);
            memberList.setStyle("-fx-border-color: #e5e5e5; -fx-border-radius: 6px; -fx-background-radius: 6px;");

            privateRadio.selectedProperty().addListener((obs, oldVal, newVal) -> {
                memberField.setVisible(newVal);
                memberField.setManaged(newVal);
            });
            memberField.setVisible(false);
            memberField.setManaged(false);
            memberField.getChildren().addAll(memberLabel, memberList);

            visibilityField.getChildren().addAll(visibilityLabel, visibilityOptions, memberField);

            body.getChildren().addAll(titleField, descField, visibilityField);

            HBox footer = new HBox(8);
            footer.setAlignment(Pos.CENTER_RIGHT);
            footer.setStyle("-fx-padding: 12px 20px 20px 20px; -fx-border-color: #e5e5e5; -fx-border-width: 1px 0 0 0;");

            Button cancelBtn = new Button("Cancel");
            cancelBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #666666; -fx-font-size: 13px; " +
                    "-fx-font-weight: 600; -fx-padding: 8px 20px; -fx-border-radius: 6px; -fx-background-radius: 6px; -fx-cursor: hand;");
            cancelBtn.setOnAction(e -> createStage.close());

            Button createBtn = new Button("Create Topic");
            createBtn.setStyle("-fx-background-color: #000000; -fx-text-fill: #ffffff; -fx-font-size: 13px; " +
                    "-fx-font-weight: 600; -fx-padding: 8px 30px; -fx-border-radius: 6px; -fx-background-radius: 6px; -fx-cursor: hand;");
            createBtn.setOnAction(e -> {
                String topicTitle = titleInput.getText().trim();
                if (!topicTitle.isEmpty()) {
                    TopicData newTopic = new TopicData(
                            (int) (System.currentTimeMillis() / 1000),
                            topicTitle,
                            "Dr. Smith",
                            "Just now"
                    );
                    newTopic.isPublic = publicRadio.isSelected();
                    newTopic.isPrivate = privateRadio.isSelected();
                    if (privateRadio.isSelected()) {
                        newTopic.visibleToMembers.addAll(memberList.getSelectionModel().getSelectedItems());
                    }
                    group.topics.add(newTopic);
                    createStage.close();
                    renderTopics(group);
                    openTopic(group, newTopic);
                }
            });

            footer.getChildren().addAll(cancelBtn, createBtn);
            root.getChildren().addAll(header, body, footer);

            Scene scene = new Scene(root);
            scene.getStylesheets().add(getClass().getResource("/com/demo/style.css").toExternalForm());
            createStage.setScene(scene);
            createStage.showAndWait();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    // ==================== POST REPLY ====================

    @FXML
    public void handlePostReply() {
        if (currentTopic == null) return;

        String text = replyText.getText().trim();
        if (text.isEmpty()) {
            showToast("Please write a reply.");
            return;
        }

        PostData newPost = new PostData(
                (int) (System.currentTimeMillis() / 1000),
                "Dr. Smith", "DS", "Just now",
                text + (privateCheck.isSelected() ? " 🔒 Private" : "")
        );
        newPost.isPrivate = privateCheck.isSelected();
        currentTopic.posts.add(newPost);

        replyText.clear();
        privateCheck.setSelected(false);
        renderThread(currentGroup, currentTopic);
        renderTopics(currentGroup);
    }

    // ==================== QUIZ (Embedded) ====================

    /**
     * Starts a quiz and displays it embedded in the right panel.
     * @param quizIndex index of the quiz (0,1,2)
     * @param quizTitle title of the quiz
     */
   private void startQuiz(int quizIndex, String quizTitle) {
    try {
        FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/forum/Quiz.fxml"));
        Parent quizView = loader.load();
        QuizController controller = loader.getController();

        // === NEW ORDER ===
        // 1. Clear and add the view to the container (this attaches the Scene)
        quizActive = true;
        threadArea.getChildren().clear();
        threadArea.getChildren().add(quizView);
        VBox.setVgrow(quizView, Priority.ALWAYS);

        // 2. Now call the data setup – the view is already in a Scene
        controller.setQuizData(quizTitle, quizIndex, () -> {
            quizActive = false;
            showQuizzes(); // callback when quiz is closed
        });

    } catch (Exception e) {
        e.printStackTrace();
        showToast("Error starting quiz: " + e.getMessage());
    }
}

    // ==================== LOGOUT ====================

    @FXML
    public void handleLogout() {
        javafx.stage.Stage stage = (javafx.stage.Stage) contextTitle.getScene().getWindow();
        try {
            Parent loginRoot = FXMLLoader.load(getClass().getResource("/com/forum/Login.fxml"));
            Scene loginScene = new Scene(loginRoot, 400, 500);
            loginScene.getStylesheets().add(getClass().getResource("/com/forum/style.css").toExternalForm());
            stage.setScene(loginScene);
            stage.setResizable(true);
            stage.setTitle("Smart Discussion Forum");
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    // ==================== TOAST / NOTIFICATION ====================

    private void showToast(String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Notification");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}