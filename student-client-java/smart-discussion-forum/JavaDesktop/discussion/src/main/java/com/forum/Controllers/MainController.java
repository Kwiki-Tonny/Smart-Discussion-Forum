package com.forum.controllers;

import com.forum.MainApp;
import com.forum.models.Group;
import com.forum.models.Post;
import com.forum.models.Topic;
import com.forum.models.User;
import com.forum.models.Quiz;
import com.forum.models.QuizAttempt;
import com.forum.models.UserStats;
import com.forum.services.ApiService;
import com.forum.services.DatabaseHandler;
import com.forum.services.GlobalState;
import javafx.application.Platform;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.collections.transformation.FilteredList;
import javafx.concurrent.Task;
import javafx.event.ActionEvent;
import javafx.event.EventHandler;
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
import javafx.scene.input.MouseEvent;
import javafx.scene.layout.*;
import javafx.scene.shape.Circle;
import javafx.scene.text.Text;
import javafx.stage.Modality;
import javafx.stage.Stage;
import javafx.stage.StageStyle;

import com.lowagie.text.Document;
import com.lowagie.text.DocumentException;
import com.lowagie.text.Font;
import com.lowagie.text.FontFactory;
import com.lowagie.text.Paragraph;
import com.lowagie.text.PageSize;
import com.lowagie.text.Element;
import com.lowagie.text.pdf.PdfWriter;

import java.awt.Color;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.*;
import java.util.concurrent.CountDownLatch;
import java.awt.Desktop;

public class MainController {

    // ─── FXML INJECTIONS ──────────────────────────────────────────
    @FXML private Text userNameText;
    @FXML private Text contextTitle;
    @FXML private Button contextActionBtn;
    @FXML private VBox contextList;
    @FXML private VBox threadArea;
    @FXML private VBox replyForm;
    @FXML private TextArea replyText;
    @FXML private CheckBox privateCheck;
    @FXML private Button selectUsersBtn;
    @FXML private Circle statusDot;
    @FXML private Label statusLabel;
    @FXML private Text syncStatus;
    @FXML private TextField searchField;

    @FXML private Button navGroups;
    @FXML private Button navProfile;
    @FXML private Button navQuizzes;
    @FXML private Button navResults;

    // ─── CONSTANTS ─────────────────────────────────────────────────
    private static final String WEB_BASE_URL = "http://localhost:8000";

    // ─── SERVICES & STATE ─────────────────────────────────────────
    private final GlobalState state = GlobalState.getInstance();
    private final ApiService api = ApiService.getInstance();

    // Data from API
    private List<Group> allGroups = new ArrayList<>();
    private ObservableList<Group> joinedGroups = FXCollections.observableArrayList();
    private ObservableList<Group> availableGroups = FXCollections.observableArrayList();
    private FilteredList<Group> filteredJoined;
    private FilteredList<Group> filteredAvailable;

    private List<Topic> topics = new ArrayList<>();
    private List<Post> currentPosts = new ArrayList<>();

    // Current selections
    private String currentView = "groups";
    private Group currentGroup;
    private Topic currentTopic;

    // Inline reply tracking
    private Post currentReplyTarget = null;
    private VBox currentInlineForm = null;

    // UI helper
    private Label replyToLabel;

    // Quiz lockdown flag
    private boolean isQuizActive = false;

    // Private post exclusions
    private List<Integer> excludedUserIds = new ArrayList<>();

    // ─── INITIALIZATION ────────────────────────────────────────────

    @FXML
    public void initialize() {
        try {
            System.out.println("MainController.initialize: start");

            if (replyForm != null && replyForm.getChildren().size() > 0) {
                replyToLabel = new Label("Replying to: Thread");
                replyToLabel.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666; -fx-padding: 0 0 4 0;");
                replyForm.getChildren().add(0, replyToLabel);
            }

            User user = state.getCurrentUser();
            userNameText.setText(user != null ? user.name : "Guest");

            setupConnectionStatus();
            setupAuthListeners();
            setupSearchListener();

            privateCheck.selectedProperty().addListener((obs, oldVal, newVal) -> {
                selectUsersBtn.setVisible(newVal);
                selectUsersBtn.setManaged(newVal);
                if (!newVal) {
                    excludedUserIds.clear();
                    updateSelectedUsersLabel();
                }
            });

            // ─── AUTO-CLOSE INLINE REPLY ON CLICK OUTSIDE ────────────
            if (threadArea != null) {
                threadArea.setOnMouseClicked(e -> {
                    if (currentInlineForm != null) {
                        // Check if the click target is inside the inline form
                        boolean clickedInside = false;
                        javafx.scene.Node target = (javafx.scene.Node) e.getTarget();
                        while (target != null) {
                            if (target.equals(currentInlineForm)) {
                                clickedInside = true;
                                break;
                            }
                            target = target.getParent();
                        }
                        if (!clickedInside) {
                            currentInlineForm.setVisible(false);
                            currentInlineForm.setManaged(false);
                            currentInlineForm = null;
                        }
                    }
                });
            }

            loadGroups();

            System.out.println("MainController.initialize: done");
        } catch (Exception e) {
            System.err.println("Exception in MainController.initialize:");
            e.printStackTrace();
            throw e;
        }
    }

    // ─── SEARCH LISTENER ──────────────────────────────────────────

    private void setupSearchListener() {
        searchField.textProperty().addListener((obs, oldVal, newVal) -> {
            String query = newVal.toLowerCase().trim();
            filteredJoined.setPredicate(group ->
                group.name.toLowerCase().contains(query)
            );
            filteredAvailable.setPredicate(group ->
                group.name.toLowerCase().contains(query)
            );
            renderGroups();
        });
    }

    // ─── CONNECTION & AUTH ────────────────────────────────────────

    private void setupConnectionStatus() {
        updateConnectionUI(state.isOnline());
        state.addConnectionListener(new GlobalState.ConnectionListener() {
            @Override
            public void onConnectionChange(boolean isOnline) {
                Platform.runLater(() -> updateConnectionUI(isOnline));
            }
            @Override
            public void onError(String error) {
                Platform.runLater(() -> {
                    if (error != null && syncStatus != null) {
                        syncStatus.setText("⚠️ " + error);
                    }
                });
            }
        });
    }

    private void updateConnectionUI(boolean isOnline) {
        if (statusDot != null) {
            statusDot.setStyle(isOnline ? "-fx-fill: #16a34a;" : "-fx-fill: #dc2626;");
        }
        if (statusLabel != null) {
            statusLabel.setText(isOnline ? "Online" : "Offline");
            statusLabel.setStyle(isOnline ? "-fx-text-fill: #16a34a;" : "-fx-text-fill: #dc2626;");
        }
        if (syncStatus != null) {
            syncStatus.setText(isOnline ? "Connected" : "Offline - changes saved locally");
        }
    }

    private void setupAuthListeners() {
        state.addAuthListener(new GlobalState.AuthListener() {
            @Override
            public void onAuthChange(boolean isAuthenticated) {
                if (!isAuthenticated) {
                    Platform.runLater(() -> {
                        try {
                            MainApp.switchToLogin();
                        } catch (Exception e) {
                            e.printStackTrace();
                        }
                    });
                }
            }
            @Override
            public void onTokenExpired() {
                Platform.runLater(() -> {
                    showToast("Your session has expired. Please login again.");
                    try {
                        MainApp.switchToLogin();
                    } catch (Exception e) {
                        e.printStackTrace();
                    }
                });
            }
        });
    }

    // ─── REAL API DATA LOADING ────────────────────────────────────

    private void loadGroups() {
        Task<List<Group>> task = new Task<>() {
            @Override
            protected List<Group> call() throws Exception {
                return api.getGroups();
            }
        };
        task.setOnSucceeded(e -> {
            allGroups = task.getValue();
            Platform.runLater(() -> {
                if (allGroups.isEmpty()) {
                    showEmptyState("No groups available.");
                } else {
                    splitGroupsByMembership();
                    setupFilteredLists();
                    renderGroups();
                }
            });
        });
        task.setOnFailed(e -> {
            Platform.runLater(() -> {
                showEmptyState("Failed to load groups. Check your connection.");
                task.getException().printStackTrace();
            });
        });
        new Thread(task).start();
    }

    private void splitGroupsByMembership() {
        joinedGroups.clear();
        availableGroups.clear();
        for (Group g : allGroups) {
            // Debug: verify isMember is correctly received
            System.out.println("Group: " + g.name + " isMember: " + g.isMember);
            if (g.isMember) {
                joinedGroups.add(g);
            } else {
                availableGroups.add(g);
            }
        }
    }

    private void setupFilteredLists() {
        filteredJoined = new FilteredList<>(joinedGroups, group -> true);
        filteredAvailable = new FilteredList<>(availableGroups, group -> true);
    }

    private void loadTopicsForGroup(Group group) {
        Task<List<Topic>> task = new Task<>() {
            @Override
            protected List<Topic> call() throws Exception {
                return api.getTopicsForGroup(group.id);
            }
        };
        task.setOnSucceeded(e -> {
            topics = task.getValue();
            Platform.runLater(() -> {
                renderTopics(topics);
                threadArea.getChildren().clear();
                replyForm.setVisible(false);
                replyForm.setManaged(false);
            });
        });
        task.setOnFailed(e -> {
            Platform.runLater(() -> {
                showEmptyState("Failed to load topics.");
                task.getException().printStackTrace();
            });
        });
        new Thread(task).start();
    }

    private void loadPostsForTopic(Topic topic) {
        Task<List<Post>> task = new Task<>() {
            @Override
            protected List<Post> call() throws Exception {
                return api.getPostsForTopic(topic.id);
            }
        };
        task.setOnSucceeded(e -> {
            List<Post> flatPosts = task.getValue();
            // Build the tree
            List<Post> nestedPosts = buildReplyTree(flatPosts);
            Platform.runLater(() -> {
                currentPosts = nestedPosts;
                renderThread(topic, nestedPosts);
                replyForm.setVisible(true);
                replyForm.setManaged(true);
                currentReplyTarget = null;
                if (replyToLabel != null) {
                    replyToLabel.setText("Replying to: Thread");
                }
            });
        });
        task.setOnFailed(e -> {
            Platform.runLater(() -> {
                showErrorInThread("Failed to load posts.");
                task.getException().printStackTrace();
            });
        });
        new Thread(task).start();
    }

    private List<Post> buildReplyTree(List<Post> flatPosts) {
    // Map each post by its id
    Map<Integer, Post> postMap = new HashMap<>();
    for (Post p : flatPosts) {
        postMap.put(p.id, p);
        p.replies = new ArrayList<>(); // ensure replies list exists
    }

    List<Post> topLevelPosts = new ArrayList<>();
    for (Post p : flatPosts) {
        if (p.parentId != null && p.parentId != 0) {
            Post parent = postMap.get(p.parentId);
            if (parent != null) {
                parent.replies.add(p);
            } else {
                // orphan – treat as top-level
                topLevelPosts.add(p);
            }
        } else {
            topLevelPosts.add(p);
        }
    }

    // Sort replies by created_at (optional)
    for (Post p : postMap.values()) {
        if (p.replies != null) {
            p.replies.sort(Comparator.comparing(p2 -> p2.created_at));
        }
    }
    topLevelPosts.sort(Comparator.comparing(p -> p.created_at));

    return topLevelPosts;
}

    // ─── UI HELPERS ──────────────────────────────────────────────

    private void showEmptyState(String message) {
        contextList.getChildren().clear();
        Label label = new Label(message);
        label.setStyle("-fx-padding: 40px; -fx-text-fill: #999; -fx-alignment: center;");
        contextList.getChildren().add(label);
    }

    private void showErrorInThread(String message) {
        threadArea.getChildren().clear();
        Label label = new Label("❌ " + message);
        label.setStyle("-fx-padding: 40px; -fx-text-fill: #dc2626; -fx-alignment: center;");
        threadArea.getChildren().add(label);
    }

    private void showToast(String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Notification");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void setActiveNav(Button active) {
        navGroups.getStyleClass().remove("active");
        navProfile.getStyleClass().remove("active");
        navQuizzes.getStyleClass().remove("active");
        navResults.getStyleClass().remove("active");
        active.getStyleClass().add("active");
    }

    // ─── NAVIGATION ───────────────────────────────────────────────

    @FXML
    public void showGroups() {
        if (isQuizActive) {
            showToast("🔒 Cannot navigate while quiz is in progress.");
            return;
        }
        currentView = "groups";
        setActiveNav(navGroups);
        contextTitle.setText("Groups");
        contextActionBtn.setVisible(false);
        contextActionBtn.setManaged(false);
        replyForm.setVisible(false);
        replyForm.setManaged(false);
        renderGroups();
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
        searchField.clear();
    }

    // ─── RENDER GROUPS ─────────────────────────────────────────────

    private void renderGroups() {
        contextList.getChildren().clear();

        if (!filteredJoined.isEmpty()) {
            Label header = new Label("📚 My Groups");
            header.setStyle("-fx-font-size: 13px; -fx-font-weight: 700; -fx-text-fill: #1A7A64; -fx-padding: 8px 16px 4px 16px; -fx-background-color: #f5f5f5;");
            contextList.getChildren().add(header);
            for (Group group : filteredJoined) {
                contextList.getChildren().add(createGroupItem(group, true));
            }
        }

        if (!filteredAvailable.isEmpty()) {
            Label header = new Label("🔍 Discover Groups");
            header.setStyle("-fx-font-size: 13px; -fx-font-weight: 700; -fx-text-fill: #1A7A64; -fx-padding: 12px 16px 4px 16px; -fx-background-color: #f5f5f5; -fx-border-color: #e5e7eb; -fx-border-width: 1px 0 0 0;");
            contextList.getChildren().add(header);
            for (Group group : filteredAvailable) {
                contextList.getChildren().add(createGroupItem(group, false));
            }
        }

        if (filteredJoined.isEmpty() && filteredAvailable.isEmpty()) {
            Label empty = new Label("No groups match your search.");
            empty.setStyle("-fx-padding: 40px; -fx-text-fill: #999; -fx-alignment: center;");
            contextList.getChildren().add(empty);
        }
    }

    private VBox createGroupItem(Group group, boolean isJoined) {
        VBox item = new VBox(4);
        item.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-width: 0 0 1px 0; " +
                "-fx-padding: 12px 16px; -fx-cursor: hand;");
        item.setOnMouseClicked(new GroupClickHandler(group));

        Label title = new Label(group.name);
        title.setStyle("-fx-font-size: 14px; -fx-font-weight: 600; -fx-text-fill: #000000;");

        Label desc = new Label(group.description != null ? group.description : "");
        desc.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");

        HBox metaRow = new HBox(12);
        metaRow.setAlignment(Pos.CENTER_RIGHT);

        // ─── Dynamic topic count ──────────────────────────────────────
        Label topicsLabel = new Label("📄 " + group.topicsCount + " topics");
        topicsLabel.setStyle("-fx-font-size: 11px; -fx-text-fill: #999999;");

        // ─── Dynamic member count (optional – uncomment if needed) ──
        // Label membersLabel = new Label("👤 " + group.usersCount + " members");
        // membersLabel.setStyle("-fx-font-size: 11px; -fx-text-fill: #999999;");

        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        Button joinBtn = new Button(isJoined ? "Leave" : "Join");
        joinBtn.setStyle("-fx-background-color: " + (isJoined ? "#dc3545" : "#1A7A64") + "; " +
                "-fx-text-fill: #ffffff; -fx-font-size: 11px; -fx-font-weight: 600; -fx-padding: 2px 14px; " +
                "-fx-border-radius: 12px; -fx-background-radius: 12px;");
        joinBtn.setOnAction(new JoinButtonHandler(group, isJoined));

        // ─── Add to metaRow ──────────────────────────────────────────
        // If you want to include members, add membersLabel before spacer
        metaRow.getChildren().addAll(topicsLabel, spacer, joinBtn);
        item.getChildren().addAll(title, desc, metaRow);
        return item;
    }

    // ─── HANDLER CLASSES FOR GROUP CLICKS AND JOIN/LEAVE ────────

    private class GroupClickHandler implements EventHandler<MouseEvent> {
        private final Group group;
        GroupClickHandler(Group group) { this.group = group; }
        @Override
        public void handle(MouseEvent event) {
            if (!isQuizActive) handleGroupClick(group);
        }
    }

    private class JoinButtonHandler implements EventHandler<ActionEvent> {
        private final Group group;
        private final boolean isJoined;
        JoinButtonHandler(Group group, boolean isJoined) {
            this.group = group;
            this.isJoined = isJoined;
        }
        @Override
        public void handle(ActionEvent event) {
            if (isJoined) {
                handleLeaveGroup(group);
            } else {
                showCommunityRules(group);
            }
        }
    }

    // ─── JOIN / LEAVE ─────────────────────────────────────────────

    private void handleJoinGroup(Group group) {
        Task<Void> task = new Task<>() {
            @Override
            protected Void call() throws Exception {
                api.joinGroup(group.id);
                api.acceptRules(group.id);
                return null;
            }
        };
        task.setOnSucceeded(e -> {
            group.isMember = true;
            availableGroups.remove(group);
            joinedGroups.add(group);
            FXCollections.sort(joinedGroups, Comparator.comparing(g -> g.name));
            FXCollections.sort(availableGroups, Comparator.comparing(g -> g.name));
            renderGroups();
            showToast("✅ Joined group: " + group.name);
        });
        task.setOnFailed(e -> {
            showToast("❌ Failed to join group: " + task.getException().getMessage());
            task.getException().printStackTrace();
        });
        new Thread(task).start();
    }

    private void handleLeaveGroup(Group group) {
        Task<Void> task = new Task<>() {
            @Override
            protected Void call() throws Exception {
                api.leaveGroup(group.id);
                return null;
            }
        };
        task.setOnSucceeded(e -> {
            group.isMember = false;
            joinedGroups.remove(group);
            availableGroups.add(group);
            FXCollections.sort(joinedGroups, Comparator.comparing(g -> g.name));
            FXCollections.sort(availableGroups, Comparator.comparing(g -> g.name));
            renderGroups();
            showToast("✅ Left group: " + group.name);
        });
        task.setOnFailed(e -> {
            showToast("❌ Failed to leave group: " + task.getException().getMessage());
            task.getException().printStackTrace();
        });
        new Thread(task).start();
    }

    private void handleGroupClick(Group group) {
        if (!group.isMember) {
            showCommunityRules(group);
            return;
        }
        openGroupTopics(group);
    }

    // ─── COMMUNITY RULES ─────────────────────────────────────────

    private void showCommunityRules(Group group) {
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
            header.setStyle("-fx-background-color: #1A7A64; -fx-padding: 16px 20px; " +
                    "-fx-border-radius: 16px 16px 0 0; -fx-background-radius: 16px 16px 0 0;");
            Label title = new Label("Community Rules");
            title.setStyle("-fx-font-size: 16px; -fx-font-weight: 700; -fx-text-fill: #ffffff;");
            header.getChildren().add(title);

            VBox body = new VBox(12);
            body.setPadding(new Insets(20));

            String[][] rules = {
                    {"📜", "Be respectful — Maintain professional discourse at all times."},
                    {"🚫", "No spam — Keep the environment clean and relevant."},
                    {"🎯", "Stay on topic — Ensure contributions align with the group's purpose."},
                    {"🔒", "Protect Privacy — Do not share sensitive internal data."}
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
            acceptBtn.setStyle("-fx-background-color: #1A7A64; -fx-text-fill: #ffffff; -fx-font-size: 13px; " +
                    "-fx-font-weight: 600; -fx-padding: 10px 30px; -fx-border-radius: 6px; -fx-background-radius: 6px; -fx-cursor: hand;");
            acceptBtn.setOnAction(e -> {
                rulesStage.close();
                handleJoinGroup(group);
            });

            footer.getChildren().addAll(declineBtn, footerSpacer, acceptBtn);
            root.getChildren().addAll(header, body, footer);

            Scene scene = new Scene(root);
            scene.getStylesheets().add(getClass().getResource("/com/forum/css/style.css").toExternalForm());
            rulesStage.setScene(scene);
            rulesStage.showAndWait();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    // ─── OPEN GROUP & TOPICS ──────────────────────────────────────

    private void openGroupTopics(Group group) {
        if (isQuizActive) {
            showToast("🔒 Cannot navigate while quiz is in progress.");
            return;
        }
        currentGroup = group;
        contextTitle.setText(group.name);
        contextActionBtn.setVisible(true);
        contextActionBtn.setManaged(true);
        contextActionBtn.setText("+");
        contextActionBtn.setOnAction(new CreateTopicHandler(group));

        replyForm.setVisible(false);
        replyForm.setManaged(false);

        loadTopicsForGroup(group);

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

    private class CreateTopicHandler implements EventHandler<ActionEvent> {
        private final Group group;
        CreateTopicHandler(Group group) { this.group = group; }
        @Override
        public void handle(ActionEvent event) {
            showCreateTopicDialog(group);
        }
    }

    private void renderTopics(List<Topic> topicList) {
        contextList.getChildren().clear();

        // ─── Back button ──────────────────────────────────────────────
        HBox backRow = new HBox(8);
        backRow.setAlignment(Pos.CENTER_LEFT);
        backRow.setStyle("-fx-padding: 8px 16px; -fx-background-color: #f5f5f5; -fx-border-color: #e5e7eb; -fx-border-width: 0 0 1px 0;");
        Button backBtn = new Button("← Back to Groups");
        backBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #1A7A64; -fx-font-size: 13px; -fx-font-weight: 600; -fx-cursor: hand;");
        backBtn.setOnAction(new BackToGroupsHandler());
        backRow.getChildren().add(backBtn);
        contextList.getChildren().add(backRow);

        // ─── Empty state ──────────────────────────────────────────────
        if (topicList.isEmpty()) {
            Label empty = new Label("No topics yet. Start a new discussion!");
            empty.setStyle("-fx-padding: 40px 20px; -fx-text-fill: #999999; -fx-font-size: 14px;");
            empty.setAlignment(Pos.CENTER);
            contextList.getChildren().add(empty);
            return;
        }

        // ─── Topic items ──────────────────────────────────────────────
        for (Topic topic : topicList) {
            VBox item = new VBox(4);
            item.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-width: 0 0 1px 0; " +
                    "-fx-padding: 12px 16px; -fx-cursor: hand;");
            item.setOnMouseClicked(new TopicClickHandler(topic));

            Label title = new Label(topic.title);
            title.setStyle("-fx-font-size: 14px; -fx-font-weight: 600; -fx-text-fill: #000000;");

            // ─── Author & date ──────────────────────────────────────
            String creatorName = "Unknown";
            if (topic.creator != null && topic.creator.has("name")) {
                creatorName = topic.creator.path("name").asText("Unknown");
            }
            Label sub = new Label("by " + creatorName + " • " + (topic.created_at != null ? topic.created_at : ""));
            sub.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");

            // ─── Meta row: replies + category tag ──────────────────
            HBox metaRow = new HBox(12);
            metaRow.setAlignment(Pos.CENTER_LEFT);

            // Dynamic reply count
            Label repliesLabel = new Label("💬 " + topic.postsCount + " replies");
            repliesLabel.setStyle("-fx-font-size: 11px; -fx-text-fill: #999999;");

            // Dynamic ML category (fallback to "General")
            String category = (topic.mlCategory != null && !topic.mlCategory.isEmpty())
                    ? topic.mlCategory
                    : "General";
            Label tagLabel = new Label(category);
            tagLabel.setStyle("-fx-font-size: 9px; -fx-font-weight: 700; -fx-padding: 1px 10px; " +
                    "-fx-background-radius: 12px; -fx-background-color: #e5e5e5; -fx-text-fill: #333333;");


            metaRow.getChildren().addAll(repliesLabel, tagLabel);
            item.getChildren().addAll(title, sub, metaRow);
            contextList.getChildren().add(item);
        }
    }

    private class BackToGroupsHandler implements EventHandler<ActionEvent> {
        @Override
        public void handle(ActionEvent event) {
            showGroups();
        }
    }

    // ─── TOPIC CLICK HANDLER ──────────────────────────────────────

    private class TopicClickHandler implements EventHandler<MouseEvent> {
        private final Topic topic;
        TopicClickHandler(Topic topic) { this.topic = topic; }
        @Override
        public void handle(MouseEvent event) {
            if (!isQuizActive) openTopic(topic);
        }
    }

    private void openTopic(Topic topic) {
        if (isQuizActive) {
            showToast("🔒 Cannot navigate while quiz is in progress.");
            return;
        }
        currentTopic = topic;
        contextTitle.setText(currentGroup != null ? currentGroup.name : "Topic");
        contextActionBtn.setVisible(true);
        contextActionBtn.setManaged(true);
        contextActionBtn.setText("+");
        contextActionBtn.setOnAction(new CreateTopicHandler(currentGroup));
        loadPostsForTopic(topic);
    }

    // ─── RENDER THREAD ────────────────────────────────────────────

    private void renderThread(Topic topic, List<Post> posts) {
        threadArea.getChildren().clear();

        HBox topBar = new HBox(12);
        topBar.setAlignment(Pos.CENTER_LEFT);
        topBar.setStyle("-fx-padding: 0 0 12 0;");

        Button backBtn = new Button("← Back");
        backBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #666666; -fx-font-size: 13px; -fx-cursor: hand;");
        backBtn.setOnAction(new BackToTopicsHandler());

        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        Button shareBtn = new Button("📤 Share");
        shareBtn.setStyle("-fx-background-color: #666666; -fx-border-color: #e5e5e5; -fx-border-radius: 6px; " +
                "-fx-padding: 4px 14px; -fx-font-size: 12px; -fx-text-fill: #000000; -fx-cursor: hand;");
        shareBtn.setOnAction(new ShareTopicHandler(topic));

        Button exportBtn = new Button("📄 Export PDF");
        exportBtn.setStyle("-fx-background-color: #1A7A64; -fx-text-fill: #ffffff; -fx-border-radius: 6px; " +
                "-fx-padding: 4px 14px; -fx-font-size: 12px; -fx-cursor: hand;");
        exportBtn.setOnAction(new ExportPdfHandler(topic));

        topBar.getChildren().addAll(backBtn, spacer, shareBtn, exportBtn);

        Label title = new Label(topic.title);
        title.setStyle("-fx-font-size: 20px; -fx-font-weight: 700; -fx-text-fill: #000000;");

        VBox postsContainer = new VBox(10);
        postsContainer.setPadding(new Insets(0, 0, 16, 0));

        if (posts.isEmpty()) {
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
            for (Post post : posts) {
                VBox postView = createPostView(post, 0);
                postsContainer.getChildren().add(postView);
            }
        }

        ScrollPane scrollPane = new ScrollPane(postsContainer);
        scrollPane.setFitToWidth(true);
        scrollPane.setStyle("-fx-background-color: transparent; -fx-background: transparent;");
        scrollPane.getStyleClass().add("thread-scroll");

        threadArea.getChildren().addAll(topBar, title, scrollPane);
        VBox.setVgrow(scrollPane, Priority.ALWAYS);
    }

    private class BackToTopicsHandler implements EventHandler<ActionEvent> {
        @Override
        public void handle(ActionEvent event) {
            if (currentGroup != null && !isQuizActive) openGroupTopics(currentGroup);
        }
    }

    private class ShareTopicHandler implements EventHandler<ActionEvent> {
        private final Topic topic;
        ShareTopicHandler(Topic topic) { this.topic = topic; }
        @Override
        public void handle(ActionEvent event) {
            shareTopic(topic);
        }
    }

    private class ExportPdfHandler implements EventHandler<ActionEvent> {
        private final Topic topic;
        ExportPdfHandler(Topic topic) { this.topic = topic; }
        @Override
        public void handle(ActionEvent event) {
            exportToPDF(topic);
        }
    }

    // ─── CREATE POST VIEW ─────────────────────────────────────────

    private VBox createPostView(Post post, int depth) {
        VBox postBox = new VBox(6);
        String style = "-fx-background-color: #ffffff; -fx-border-color: #1A7A64; -fx-border-radius: 8px; " +
                "-fx-background-radius: 8px; -fx-padding: 14px 18px;";
        if (depth > 0) {
            style += " -fx-border-width: 0 0 0 2px; -fx-border-radius: 0 8px 8px 0; -fx-background-radius: 0 8px 8px 0;";
        }
        postBox.setStyle(style);
        postBox.setId("post-" + post.id);

        HBox header = new HBox(10);
        header.setAlignment(Pos.CENTER_LEFT);

        String authorName = "Unknown";
        if (post.author != null && post.author.has("name")) {
            authorName = post.author.path("name").asText("Unknown");
        }
        String initials = authorName.length() >= 2 ?
                authorName.substring(0, 1) + authorName.substring(authorName.indexOf(" ") + 1, authorName.indexOf(" ") + 2) :
                "??";

        Label avatar = new Label(initials);
        avatar.setStyle("-fx-min-width: 28px; -fx-min-height: 28px; -fx-background-radius: 50%; " +
                "-fx-background-color: #1A7A64; -fx-alignment: center; -fx-font-weight: 600; -fx-font-size: 12px; " +
                "-fx-text-fill: #ffffff;");

        Label name = new Label(authorName);
        name.setStyle("-fx-font-weight: 600; -fx-font-size: 14px; -fx-text-fill: #000000;");

        Label time = new Label(post.created_at != null ? post.created_at : "");
        time.setStyle("-fx-font-size: 12px; -fx-text-fill: #999999;");

        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        int likeCount = (post.likes_count != null) ? post.likes_count : 0;
        Button likeBtn = new Button("❤️ " + likeCount);
        likeBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #666666; -fx-font-size: 13px; -fx-cursor: hand;");
        if (post.is_liked != null && post.is_liked) {
            likeBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #dc2626; -fx-font-size: 13px; -fx-cursor: hand;");
        }
        likeBtn.setOnAction(new LikeButtonHandler(post, likeBtn));

        // ─── REPLY BUTTON ──────────────────────────────────────────
        Button replyBtn = new Button("Reply");
        replyBtn.setStyle("-fx-background-color: transparent; -fx-border-color: #e5e5e5; -fx-border-radius: 12px; " +
                "-fx-padding: 2px 10px; -fx-font-size: 11px; -fx-cursor: hand; -fx-text-fill: #333333;");

        // ─── SHARE BUTTON ──────────────────────────────────────────
        Button sharePostBtn = new Button("📤 Share");
        sharePostBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #666666; -fx-font-size: 12px; " +
                "-fx-padding: 2px 10px; -fx-border-radius: 12px; -fx-cursor: hand;");
        sharePostBtn.setOnAction(e -> sharePost(post));

        header.getChildren().addAll(avatar, name, time, spacer, likeBtn, replyBtn, sharePostBtn);

        if (post.is_private) {
            Label privateTag = new Label("🔒 Private");
            privateTag.setStyle("-fx-font-size: 9px; -fx-font-weight: 700; -fx-padding: 2px 10px; " +
                    "-fx-background-radius: 12px; -fx-background-color: #fef3c7; -fx-text-fill: #b45309;");
            header.getChildren().add(privateTag);
        }

        Label body = new Label(post.content != null ? post.content : "");
        body.setStyle("-fx-font-size: 14px; -fx-text-fill: #1e293b; -fx-wrap-text: true;");
        body.setMaxWidth(Double.MAX_VALUE);

        postBox.getChildren().add(header);
        postBox.getChildren().add(body);

        // ─── INLINE REPLY FORM ──────────────────────────────────────
        VBox inlineForm = createInlineReplyForm(post);
        inlineForm.setVisible(false);
        inlineForm.setManaged(false);

        // Store the form in the reply button's user data
        replyBtn.setUserData(inlineForm);

        // Set the action for replyBtn – uses the stored form
        final Post replyPost = post;
        final String replyAuthor = authorName;
        replyBtn.setOnAction(e -> {
            Button source = (Button) e.getSource();
            VBox form = (VBox) source.getUserData();
            toggleInlineReply(replyPost, replyAuthor, form);
        });

        postBox.getChildren().add(inlineForm);

        // ─── NESTED REPLIES ──────────────────────────────────────────
        if (post.replies != null && !post.replies.isEmpty()) {
            VBox repliesContainer = new VBox(8);
            repliesContainer.setStyle("-fx-padding: 8px 0 0 16px; -fx-border-color: #1A7A64; -fx-border-width: 0 0 0 2px;");
            for (Post child : post.replies) {
                VBox childView = createPostView(child, depth + 1);
                repliesContainer.getChildren().add(childView);
            }
            postBox.getChildren().add(repliesContainer);
        }

        return postBox;
    }

    // ─── HANDLER CLASSES FOR POST ACTIONS ────────────────────────

    private class LikeButtonHandler implements EventHandler<ActionEvent> {
        private final Post post;
        private final Button button;
        LikeButtonHandler(Post post, Button button) {
            this.post = post;
            this.button = button;
        }
        @Override
        public void handle(ActionEvent event) {
            handleLike(post, button);
        }
    }

/*     private class ReplyButtonHandler implements EventHandler<ActionEvent> {
        private final Post post;
        private final String author;
        ReplyButtonHandler(Post post, String author) {
            this.post = post;
            this.author = author;
        }
        @Override
        public void handle(ActionEvent event) {
            toggleInlineReply(post, author);
        }
    } */

    private class SharePostHandler implements EventHandler<ActionEvent> {
        private final Post post;
        SharePostHandler(Post post) { this.post = post; }
        @Override
        public void handle(ActionEvent event) {
            sharePost(post);
        }
    }

    // ─── INLINE REPLY ─────────────────────────────────────────────

    private VBox createInlineReplyForm(Post parentPost) {
        VBox form = new VBox(6);
        form.setPadding(new Insets(8, 0, 0, 0));
        form.setStyle("-fx-border-color: #1A7A64; -fx-border-width: 1px 0 0 0; -fx-padding: 8 0 0 0;");

        TextArea ta = new TextArea();
        ta.setPromptText("Write a reply...");
        ta.setPrefRowCount(2);
        ta.setStyle("-fx-padding: 8px 12px; -fx-border-color: #d0d5dd; -fx-border-radius: 6px; -fx-background-radius: 6px; -fx-font-size: 13px; -fx-background-color: #fafbfc;");
        ta.setWrapText(true);

        CheckBox privateCb = new CheckBox("🔒 Private");
        privateCb.setStyle("-fx-font-size: 12px;");

        HBox buttonRow = new HBox(12);
        buttonRow.setAlignment(Pos.CENTER_LEFT);

        Button cancelBtn = new Button("Cancel");
        cancelBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #666666; -fx-font-size: 12px; -fx-cursor: hand; -fx-padding: 4px 12px;");
        cancelBtn.setOnAction(e -> {
            form.setVisible(false);
            form.setManaged(false);
            currentInlineForm = null;
        });

        Button postInlineBtn = new Button("Post Reply");
        postInlineBtn.setStyle("-fx-background-color: #1A7A64; -fx-text-fill: #ffffff; -fx-padding: 4px 16px; -fx-border-radius: 4px; -fx-background-radius: 4px; -fx-cursor: hand; -fx-font-weight: 600;");
        postInlineBtn.setUserData(form);
        postInlineBtn.setOnAction(new InlineReplyHandler(parentPost, ta, privateCb, form));

        buttonRow.getChildren().addAll(privateCb, cancelBtn, postInlineBtn);

        form.getChildren().addAll(ta, buttonRow);
        return form;
    }

    private class InlineReplyHandler implements EventHandler<ActionEvent> {
        private final Post parentPost;
        private final TextArea textArea;
        private final CheckBox privateCheckBox;
        private final VBox form;
        InlineReplyHandler(Post parentPost, TextArea textArea, CheckBox privateCheckBox, VBox form) {
            this.parentPost = parentPost;
            this.textArea = textArea;
            this.privateCheckBox = privateCheckBox;
            this.form = form;
        }
        @Override
        public void handle(ActionEvent event) {
            handleInlineReply(parentPost, textArea, privateCheckBox, form);
        }
    }

    private void toggleInlineReply(Post post, String author, VBox form) {
        // Hide currently visible inline form
        if (currentInlineForm != null) {
            currentInlineForm.setVisible(false);
            currentInlineForm.setManaged(false);
            currentInlineForm = null;
        }

        // Show the passed form
        if (form != null) {
            form.setVisible(true);
            form.setManaged(true);
            currentInlineForm = form;
            // Focus the text area
            for (var node : form.getChildren()) {
                if (node instanceof TextArea) {
                    ((TextArea) node).requestFocus();
                    break;
                }
            }
        }

        if (replyToLabel != null) {
            replyToLabel.setText("Replying to: " + author);
        }
    }

    private VBox findPostBox(int postId) {
        return findPostBoxRecursive(threadArea, postId);
    }

    private VBox findPostBoxRecursive(Parent parent, int postId) {
        for (var node : parent.getChildrenUnmodifiable()) {
            if (node instanceof VBox && node.getId() != null && node.getId().equals("post-" + postId)) {
                return (VBox) node;
            }
            if (node instanceof Parent) {
                VBox found = findPostBoxRecursive((Parent) node, postId);
                if (found != null) return found;
            }
        }
        return null;
    }

    private void handleInlineReply(Post parentPost, TextArea ta, CheckBox privateCb, VBox form) {
        String content = ta.getText().trim();
        if (content.isEmpty()) {
            showToast("Please write a reply.");
            return;
        }
        boolean isPrivate = privateCb.isSelected();
        int userId = state.getCurrentUserId();
        if (userId == -1) {
            showToast("User not logged in.");
            return;
        }

        List<Integer> excludedIds = new ArrayList<>();
        if (isPrivate) {
            excludedIds = showUserSelectionDialog();
            if (excludedIds == null) {
                return;
            }
            excludedUserIds = excludedIds;
        } else {
            excludedUserIds.clear();
        }

        final Integer parentId = parentPost.id;
        final String timestamp = LocalDateTime.now().format(DateTimeFormatter.ISO_LOCAL_DATE_TIME);

        if (state.isOnline()) {
            final List<Integer> finalExcludedIds = excludedIds; // final copy for inner class
            Task<Post> task = new Task<>() {
                @Override
                protected Post call() throws Exception {
                    return api.createPost(currentTopic.id, userId, content, isPrivate, finalExcludedIds, parentId);
                }
            };
            task.setOnSucceeded(e -> {
                ta.clear();
                privateCb.setSelected(false);
                excludedUserIds.clear();
                form.setVisible(false);
                form.setManaged(false);
                currentInlineForm = null;
                showToast("Reply posted!");
                loadPostsForTopic(currentTopic);
            });
            task.setOnFailed(e -> {
                showToast("Failed to post reply: " + task.getException().getMessage());
                task.getException().printStackTrace();
            });
            new Thread(task).start();
        } else {
            boolean saved = DatabaseHandler.saveOfflinePostDraft(
                    currentTopic.id, userId, content, isPrivate, timestamp, parentId
            );
            if (saved) {
                ta.clear();
                privateCb.setSelected(false);
                excludedUserIds.clear();
                form.setVisible(false);
                form.setManaged(false);
                currentInlineForm = null;
                showToast("📶 Saved offline – will sync when online.");

                Post newPost = new Post();
                newPost.id = -1;
                newPost.content = content;
                newPost.is_private = isPrivate;
                newPost.created_at = timestamp;
                newPost.author = null;
                newPost.likes_count = 0;
                newPost.is_liked = false;

                if (parentPost.replies == null) parentPost.replies = new ArrayList<>();
                parentPost.replies.add(newPost);
                loadPostsForTopic(currentTopic);
            } else {
                showToast("Failed to save offline reply.");
            }
        }
    }

    // ─── LIKE ─────────────────────────────────────────────────────

    private void handleLike(Post post, Button likeBtn) {
        if (state.isOnline()) {
            Task<Post> task = new Task<>() {
                @Override
                protected Post call() throws Exception {
                    return api.toggleLike(post.id);
                }
            };
            task.setOnSucceeded(e -> {
                Post updated = task.getValue();
                // Safely update the post object
                post.is_liked = updated.is_liked;
                post.likes_count = updated.likes_count;
                Platform.runLater(() -> updateLikeUI(post, likeBtn));
            });
            task.setOnFailed(e -> {
                Throwable ex = task.getException();
                String msg = ex.getMessage();
                if (msg != null && msg.contains("401")) {
                    showToast("Session expired. Please login again.");
                    state.clearSession();
                    try { MainApp.switchToLogin(); } catch (Exception ex2) { ex2.printStackTrace(); }
                } else {
                    showToast("Failed to like: " + msg);
                }
                ex.printStackTrace();
            });
            new Thread(task).start();
        } else {
            // Offline toggle
            post.is_liked = !post.is_liked;
            if (post.likes_count == null) post.likes_count = 0;
            post.likes_count += post.is_liked ? 1 : -1;
            updateLikeUI(post, likeBtn);
            showToast("📶 Like saved offline – will sync when online.");
        }
    }

    private void updateLikeUI(Post post, Button likeBtn) {
        int newCount = (post.likes_count != null) ? post.likes_count : 0;
        likeBtn.setText("❤️ " + newCount);
        if (post.is_liked != null && post.is_liked) {
            likeBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #dc2626; -fx-font-size: 13px; -fx-cursor: hand;");
        } else {
            likeBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #666666; -fx-font-size: 13px; -fx-cursor: hand;");
        }
    }

    // ─── USER SELECTION FOR PRIVATE POSTS ────────────────────────

    private List<Integer> showUserSelectionDialog() {
        List<Integer> selectedIds = new ArrayList<>();
        CountDownLatch latch = new CountDownLatch(1);

        Alert loadingAlert = new Alert(Alert.AlertType.INFORMATION);
        loadingAlert.setTitle("Loading");
        loadingAlert.setHeaderText(null);
        loadingAlert.setContentText("Loading users...");
        loadingAlert.show();

        Task<List<User>> task = new Task<>() {
            @Override
            protected List<User> call() throws Exception {
                return api.getUsers();
            }
        };

        task.setOnSucceeded(e -> {
            loadingAlert.close();
            List<User> users = task.getValue();
            User currentUser = state.getCurrentUser();
            if (currentUser != null) {
                users.removeIf(u -> u.id == currentUser.id);
            }
            Platform.runLater(() -> {
                try {
                    FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/forum/fxmlfiles/user_selection_dialog.fxml"));
                    Parent root = loader.load();
                    UserSelectionDialog controller = loader.getController();
                    controller.setUsers(users);

                    Stage stage = new Stage();
                    stage.initModality(Modality.APPLICATION_MODAL);
                    stage.initStyle(StageStyle.UNDECORATED);
                    stage.setTitle("Select Users");
                    Scene scene = new Scene(root);
                    scene.getStylesheets().add(getClass().getResource("/com/forum/css/style.css").toExternalForm());
                    stage.setScene(scene);

                    stage.showAndWait();
                    selectedIds.addAll(controller.getSelectedUserIds());
                    if (!selectedIds.isEmpty()) {
                        excludedUserIds = selectedIds;
                        updateSelectedUsersLabel();
                    }
                    latch.countDown();
                } catch (Exception ex) {
                    ex.printStackTrace();
                    showToast("Error loading user selection: " + ex.getMessage());
                    latch.countDown();
                }
            });
        });

        task.setOnFailed(e -> {
            loadingAlert.close();
            showToast("Failed to load users: " + task.getException().getMessage());
            task.getException().printStackTrace();
            latch.countDown();
        });

        new Thread(task).start();

        try {
            latch.await();
        } catch (InterruptedException ex) {
            Thread.currentThread().interrupt();
        }
        return selectedIds;
    }

    private void updateSelectedUsersLabel() {
        if (excludedUserIds.isEmpty()) {
            if (selectUsersBtn != null) {
                selectUsersBtn.setText("👤 Select Users");
            }
        } else {
            if (selectUsersBtn != null) {
                selectUsersBtn.setText("👤 " + excludedUserIds.size() + " users excluded");
            }
        }
    }

    // ─── MAIN REPLY FORM ──────────────────────────────────────────

    @FXML
    public void handlePostReply() {
        if (currentTopic == null || currentGroup == null) {
            showToast("Please select a topic first.");
            return;
        }
        String content = replyText.getText().trim();
        if (content.isEmpty()) {
            showToast("Please write a reply.");
            return;
        }
        boolean isPrivate = privateCheck.isSelected();
        int userId = state.getCurrentUserId();
        if (userId == -1) {
            showToast("User not logged in.");
            return;
        }

        List<Integer> excludedIds = new ArrayList<>();
        if (isPrivate) {
            excludedIds = showUserSelectionDialog();
            if (excludedIds == null) {
                return;
            }
            excludedUserIds = excludedIds;
        } else {
            excludedUserIds.clear();
        }

        final Integer parentId = (currentReplyTarget != null) ? currentReplyTarget.id : null;
        final String timestamp = LocalDateTime.now().format(DateTimeFormatter.ISO_LOCAL_DATE_TIME);

        if (state.isOnline()) {
            final List<Integer> finalExcludedIds = excludedIds; // final copy for inner class
            Task<Post> task = new Task<>() {
                @Override
                protected Post call() throws Exception {
                    return api.createPost(currentTopic.id, userId, content, isPrivate, finalExcludedIds, parentId);
                }
            };
            task.setOnSucceeded(e -> {
                replyText.clear();
                privateCheck.setSelected(false);
                excludedUserIds.clear();
                currentReplyTarget = null;
                if (replyToLabel != null) replyToLabel.setText("Replying to: Thread");
                updateSelectedUsersLabel();
                showToast("Reply posted!");
                loadPostsForTopic(currentTopic);
            });
            task.setOnFailed(e -> {
                showToast("Failed to post reply: " + task.getException().getMessage());
                task.getException().printStackTrace();
            });
            new Thread(task).start();
        } else {
            boolean saved = DatabaseHandler.saveOfflinePostDraft(
                    currentTopic.id, userId, content, isPrivate, timestamp, parentId
            );
            if (saved) {
                replyText.clear();
                privateCheck.setSelected(false);
                excludedUserIds.clear();
                currentReplyTarget = null;
                if (replyToLabel != null) replyToLabel.setText("Replying to: Thread");
                updateSelectedUsersLabel();
                showToast("📶 Saved offline – will sync when online.");

                Post newPost = new Post();
                newPost.id = -1;
                newPost.content = content;
                newPost.is_private = isPrivate;
                newPost.created_at = timestamp;
                newPost.author = null;
                newPost.likes_count = 0;
                newPost.is_liked = false;
                currentPosts.add(newPost);
                loadPostsForTopic(currentTopic);
            } else {
                showToast("Failed to save offline reply.");
            }
        }
    }

    // ─── CREATE TOPIC DIALOG ──────────────────────────────────────

    private void showCreateTopicDialog(Group group) {
        if (group == null) {
            showToast("Please select a group first.");
            return;
        }
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
            header.setStyle("-fx-background-color: #1A7A64; -fx-padding: 16px 20px; " +
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

            body.getChildren().addAll(titleField, descField);

            HBox footer = new HBox(8);
            footer.setAlignment(Pos.CENTER_RIGHT);
            footer.setStyle("-fx-padding: 12px 20px 20px 20px; -fx-border-color: #e5e5e5; -fx-border-width: 1px 0 0 0;");

            Button cancelBtn = new Button("Cancel");
            cancelBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #666666; -fx-font-size: 13px; " +
                    "-fx-font-weight: 600; -fx-padding: 8px 20px; -fx-border-radius: 6px; -fx-background-radius: 6px; -fx-cursor: hand;");
            cancelBtn.setOnAction(e -> createStage.close());

            Button createBtn = new Button("Create Topic");
            createBtn.setStyle("-fx-background-color: #1A7A64; -fx-text-fill: #ffffff; -fx-font-size: 13px; " +
                    "-fx-font-weight: 600; -fx-padding: 8px 30px; -fx-border-radius: 6px; -fx-background-radius: 6px; -fx-cursor: hand;");
            createBtn.setOnAction(e -> {
                String topicTitle = titleInput.getText().trim();
                if (!topicTitle.isEmpty()) {
                    Task<Topic> createTask = new Task<>() {
                        @Override
                        protected Topic call() throws Exception {
                            return api.createTopic(group.id, topicTitle, descInput.getText().trim());
                        }
                    };
                    createTask.setOnSucceeded(ev -> {
                        createStage.close();
                        showToast("Topic created successfully!");
                        openGroupTopics(group);
                    });
                    createTask.setOnFailed(ev -> {
                        createStage.close();
                        showToast("Failed to create topic: " + createTask.getException().getMessage());
                        createTask.getException().printStackTrace();
                    });
                    new Thread(createTask).start();
                }
            });

            footer.getChildren().addAll(cancelBtn, createBtn);
            root.getChildren().addAll(header, body, footer);

            Scene scene = new Scene(root);
            scene.getStylesheets().add(getClass().getResource("/com/forum/css/style.css").toExternalForm());
            createStage.setScene(scene);
            createStage.showAndWait();

        } catch (Exception e) {
            e.printStackTrace();
            showToast("Error: " + e.getMessage());
        }
    }

    // ─── SHARE & EXPORT ───────────────────────────────────────────

    private void shareTopic(Topic topic) {
        try {
            String url = WEB_BASE_URL + "/groups/" + currentGroup.id + "/topics/" + topic.id;
            String shareText = "📚 " + currentGroup.name + " – " + topic.title + "\n" +
                               "🔗 " + url + "\n\n" +
                               "Join the discussion on Smart Discussion Forum!";
            copyToClipboard(shareText);
            showToast("✅ Topic link copied to clipboard!");
        } catch (Exception e) {
            e.printStackTrace();
            showToast("❌ Failed to copy link: " + e.getMessage());
        }
    }

    private void sharePost(Post post) {
        try {
            String url = WEB_BASE_URL + "/groups/" + currentGroup.id + "/topics/" + currentTopic.id + "?post=" + post.id;
            String authorName = post.author != null && post.author.has("name") ?
                                post.author.path("name").asText("Unknown") : "Unknown";
            String shareText = "💬 " + authorName + " said:\n" +
                               "\"" + (post.content.length() > 100 ? post.content.substring(0, 100) + "..." : post.content) + "\"\n" +
                               "🔗 " + url + "\n\n" +
                               "Reply on Smart Discussion Forum!";
            copyToClipboard(shareText);
            showToast("✅ Post link copied to clipboard!");
        } catch (Exception e) {
            e.printStackTrace();
            showToast("❌ Failed to copy link: " + e.getMessage());
        }
    }

    private void copyToClipboard(String text) {
        Clipboard clipboard = Clipboard.getSystemClipboard();
        ClipboardContent content = new ClipboardContent();
        content.putString(text);
        clipboard.setContent(content);
    }

    private void exportToPDF(Topic topic) {
        try {
            String timestamp = LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyyMMdd_HHmmss"));
            String filename = "chat_export_" + topic.title.replaceAll(" ", "_") + "_" + timestamp + ".pdf";
            File file = new File(System.getProperty("user.home") + "/Downloads/" + filename);
            file.getParentFile().mkdirs();

            Document document = new Document(PageSize.A4);
            PdfWriter.getInstance(document, new FileOutputStream(file));
            document.open();

            Font titleFont = FontFactory.getFont(FontFactory.HELVETICA_BOLD, 18, Color.BLACK);
            Font headingFont = FontFactory.getFont(FontFactory.HELVETICA_BOLD, 14, Color.DARK_GRAY);
            Font authorFont = FontFactory.getFont(FontFactory.HELVETICA, 12, Color.GRAY);
            Font bodyFont = FontFactory.getFont(FontFactory.HELVETICA, 12, Color.BLACK);
            Font privateFont = FontFactory.getFont(FontFactory.HELVETICA, 10, Color.RED);
            Font replyFont = FontFactory.getFont(FontFactory.HELVETICA, 11, Color.DARK_GRAY);

            Paragraph titlePara = new Paragraph("Topic: " + topic.title, titleFont);
            titlePara.setAlignment(Element.ALIGN_CENTER);
            document.add(titlePara);

            String creatorName = "Unknown";
            if (topic.creator != null && topic.creator.has("name")) {
                creatorName = topic.creator.path("name").asText("Unknown");
            }
            Paragraph meta = new Paragraph("Author: " + creatorName + "  |  Date: " + topic.created_at, authorFont);
            meta.setAlignment(Element.ALIGN_CENTER);
            document.add(meta);
            document.add(new Paragraph(" "));
            document.add(new Paragraph("=".repeat(60), headingFont));
            document.add(new Paragraph(" "));

            if (currentPosts.isEmpty()) {
                document.add(new Paragraph("No posts in this topic.", bodyFont));
            } else {
                for (Post post : currentPosts) {
                    appendPostToPdf(document, post, 0, bodyFont, authorFont, privateFont, replyFont);
                }
            }

            document.close();

            if (Desktop.isDesktopSupported()) {
                Desktop.getDesktop().open(file);
            }

            showToast("✅ PDF exported to: " + file.getAbsolutePath());

        } catch (Exception e) {
            e.printStackTrace();
            showToast("❌ Error exporting PDF: " + e.getMessage());
        }
    }

    private void appendPostToPdf(Document document, Post post, int depth, Font bodyFont, Font authorFont, Font privateFont, Font replyFont) throws DocumentException {
        String indent = "    ".repeat(depth);
        String authorName = post.author != null && post.author.has("name") ? post.author.path("name").asText("Unknown") : "Unknown";

        Paragraph authorPara = new Paragraph(indent + authorName + " (" + post.created_at + "):", authorFont);
        authorPara.setIndentationLeft((float) depth * 20);
        document.add(authorPara);

        Paragraph contentPara = new Paragraph(indent + "  " + post.content, bodyFont);
        contentPara.setIndentationLeft((float) depth * 20);
        document.add(contentPara);

        if (post.is_private) {
            Paragraph privatePara = new Paragraph(indent + "  [PRIVATE]", privateFont);
            privatePara.setIndentationLeft((float) depth * 20);
            document.add(privatePara);
        }

        if (post.replies != null) {
            for (Post reply : post.replies) {
                appendPostToPdf(document, reply, depth + 1, bodyFont, authorFont, privateFont, replyFont);
            }
        }

        document.add(new Paragraph(" "));
    }

    // ─── PROFILE ──────────────────────────────────────────────────

    @FXML
    public void showProfile() {
        if (isQuizActive) {
            showToast("🔒 Cannot navigate while quiz is in progress.");
            return;
        }
        currentView = "profile";
        setActiveNav(navProfile);
        contextTitle.setText("Profile");
        contextActionBtn.setVisible(false);
        contextActionBtn.setManaged(false);
        replyForm.setVisible(false);
        replyForm.setManaged(false);

        contextList.getChildren().clear();
        VBox statsBox = new VBox(16);
        statsBox.setPadding(new Insets(16));
        statsBox.setStyle("-fx-background-color: #ffffff;");
        Label statsTitle = new Label("📊 Performance");
        statsTitle.setStyle("-fx-font-size: 14px; -fx-font-weight: 700; -fx-text-fill: #1A7A64;");
        statsBox.getChildren().add(statsTitle);

        Label loadingLabel = new Label("Loading stats...");
        loadingLabel.setStyle("-fx-font-size: 13px; -fx-text-fill: #999999;");
        statsBox.getChildren().add(loadingLabel);
        contextList.getChildren().add(statsBox);

        threadArea.getChildren().clear();
        VBox outerWrapper = new VBox();
        outerWrapper.setAlignment(Pos.CENTER);
        outerWrapper.setPadding(new Insets(20));
        outerWrapper.setFillWidth(true);

        VBox profileBox = new VBox(20);
        profileBox.setPadding(new Insets(30));
        profileBox.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-radius: 12px; -fx-background-radius: 12px;");
        profileBox.setMaxWidth(700);
        profileBox.setAlignment(Pos.CENTER);

        User user = state.getCurrentUser();
        String name = user != null ? user.name : "Guest";
        String email = user != null ? user.email : "guest@forum.com";
        String role = user != null ? user.role : "Member";
        String initials = name.length() >= 2 ? name.substring(0, 1) + name.substring(name.indexOf(" ") + 1, name.indexOf(" ") + 2) : "??";

        HBox avatarRow = new HBox(16);
        avatarRow.setAlignment(Pos.CENTER_LEFT);
        Label avatar = new Label(initials);
        avatar.setStyle("-fx-min-width: 64px; -fx-min-height: 64px; -fx-background-radius: 50%; " +
                "-fx-background-color: #000000; -fx-text-fill: #ffffff; -fx-font-size: 28px; -fx-font-weight: 600; -fx-alignment: center;");
        VBox infoBox = new VBox(4);
        infoBox.getChildren().addAll(
                new Label(name) {{ setStyle("-fx-font-size: 20px; -fx-font-weight: 700;"); }},
                new Label(email) {{ setStyle("-fx-text-fill: #666666;"); }},
                new Label(role) {{ setStyle("-fx-background-color: #dbeafe; -fx-text-fill: #1d4ed8; -fx-font-size: 11px; -fx-font-weight: 600; -fx-padding: 2px 12px; -fx-background-radius: 12px;"); }}
        );
        avatarRow.getChildren().addAll(avatar, infoBox);

        GridPane statsGrid = new GridPane();
        statsGrid.setHgap(20);
        statsGrid.setVgap(12);
        statsGrid.setPadding(new Insets(20, 0, 0, 0));
        statsGrid.setAlignment(Pos.CENTER);
        for (int i = 0; i < 4; i++) {
            ColumnConstraints col = new ColumnConstraints();
            col.setPercentWidth(25);
            col.setHalignment(HPos.CENTER);
            statsGrid.getColumnConstraints().add(col);
        }

        Label topicsLabel = new Label("📚");
        Label topicsValue = new Label("0");
        topicsValue.setStyle("-fx-font-size: 26px; -fx-font-weight: 700; -fx-text-fill: #1A7A64;");
        Label topicsDesc = new Label("Topics");
        topicsDesc.setStyle("-fx-font-size: 13px; -fx-text-fill: #666666;");
        VBox topicsBox = new VBox(4, topicsLabel, topicsValue, topicsDesc);
        topicsBox.setAlignment(Pos.CENTER);
        statsGrid.add(topicsBox, 0, 0);

        Label postsLabel = new Label("📝");
        Label postsValue = new Label("0");
        postsValue.setStyle("-fx-font-size: 26px; -fx-font-weight: 700; -fx-text-fill: #1A7A64;");
        Label postsDesc = new Label("Posts");
        postsDesc.setStyle("-fx-font-size: 13px; -fx-text-fill: #666666;");
        VBox postsBox = new VBox(4, postsLabel, postsValue, postsDesc);
        postsBox.setAlignment(Pos.CENTER);
        statsGrid.add(postsBox, 1, 0);

        Label repliesLabel = new Label("💬");
        Label repliesValue = new Label("0");
        repliesValue.setStyle("-fx-font-size: 26px; -fx-font-weight: 700; -fx-text-fill: #1A7A64;");
        Label repliesDesc = new Label("Replies");
        repliesDesc.setStyle("-fx-font-size: 13px; -fx-text-fill: #666666;");
        VBox repliesBox = new VBox(4, repliesLabel, repliesValue, repliesDesc);
        repliesBox.setAlignment(Pos.CENTER);
        statsGrid.add(repliesBox, 2, 0);

        Label quizzesLabel = new Label("📊");
        Label quizzesValue = new Label("0");
        quizzesValue.setStyle("-fx-font-size: 26px; -fx-font-weight: 700; -fx-text-fill: #1A7A64;");
        Label quizzesDesc = new Label("Quizzes");
        quizzesDesc.setStyle("-fx-font-size: 13px; -fx-text-fill: #666666;");
        VBox quizzesBox = new VBox(4, quizzesLabel, quizzesValue, quizzesDesc);
        quizzesBox.setAlignment(Pos.CENTER);
        statsGrid.add(quizzesBox, 3, 0);

        profileBox.getChildren().addAll(avatarRow, statsGrid);
        outerWrapper.getChildren().add(profileBox);
        threadArea.getChildren().add(outerWrapper);

        Task<UserStats> statsTask = new Task<>() {
            @Override
            protected UserStats call() throws Exception {
                return api.getUserStats();
            }
        };
        statsTask.setOnSucceeded(e -> {
            UserStats stats = statsTask.getValue();
            Platform.runLater(() -> {
                statsBox.getChildren().clear();
                statsBox.getChildren().add(statsTitle);
                String[][] statRows = {
                        {"📝", "Total Posts", String.valueOf(stats.totalPosts)},
                        {"💬", "Total Replies", String.valueOf(stats.totalReplies)},
                        {"📈", "Topics Created", String.valueOf(stats.totalTopics)},
                        {"📊", "Quizzes Taken", String.valueOf(stats.totalQuizzes)}
                };
                for (String[] row : statRows) {
                    HBox rowBox = new HBox(12);
                    rowBox.setAlignment(Pos.CENTER_LEFT);
                    rowBox.setStyle("-fx-padding: 8px 0; -fx-border-color: #f0f0f0; -fx-border-width: 0 0 1px 0;");
                    rowBox.getChildren().addAll(
                            new Label(row[0]) {{ setStyle("-fx-font-size: 16px;"); }},
                            new Label(row[1]) {{ setStyle("-fx-font-size: 13px; -fx-text-fill: #333333;"); }},
                            new Region() {{ HBox.setHgrow(this, Priority.ALWAYS); }},
                            new Label(row[2]) {{ setStyle("-fx-font-size: 13px; -fx-font-weight: 600; -fx-text-fill: #000000;"); }}
                    );
                    statsBox.getChildren().add(rowBox);
                }

                topicsValue.setText(String.valueOf(stats.totalTopics));
                postsValue.setText(String.valueOf(stats.totalPosts));
                repliesValue.setText(String.valueOf(stats.totalReplies));
                quizzesValue.setText(String.valueOf(stats.totalQuizzes));
            });
        });
        statsTask.setOnFailed(e -> {
            Platform.runLater(() -> {
                statsBox.getChildren().clear();
                statsBox.getChildren().add(statsTitle);
                Label errorLabel = new Label("❌ Failed to load stats");
                errorLabel.setStyle("-fx-font-size: 13px; -fx-text-fill: #dc2626;");
                statsBox.getChildren().add(errorLabel);
            });
            statsTask.getException().printStackTrace();
        });
        new Thread(statsTask).start();
    }

    // ─── QUIZZES ──────────────────────────────────────────────────

    @FXML
    public void showQuizzes() {
        if (isQuizActive) {
            showToast("🔒 Cannot navigate while quiz is in progress.");
            return;
        }
        currentView = "quizzes";
        setActiveNav(navQuizzes);
        contextTitle.setText("Quizzes");
        contextActionBtn.setVisible(false);
        contextActionBtn.setManaged(false);
        replyForm.setVisible(false);
        replyForm.setManaged(false);

        contextList.getChildren().clear();
        VBox loadingBox = new VBox(12);
        loadingBox.setAlignment(Pos.CENTER);
        loadingBox.setPadding(new Insets(40));
        Label loadingLabel = new Label("📝 Loading quizzes...");
        loadingLabel.setStyle("-fx-font-size: 14px; -fx-text-fill: #666666;");
        loadingBox.getChildren().add(loadingLabel);
        contextList.getChildren().add(loadingBox);

        threadArea.getChildren().clear();
        VBox placeholder = new VBox(12);
        placeholder.setAlignment(Pos.CENTER);
        placeholder.setPadding(new Insets(60, 20, 60, 20));
        placeholder.getChildren().addAll(
                new Label("📝") {{ setStyle("-fx-font-size: 32px; -fx-text-fill: #999999;"); }},
                new Label("Select a quiz to start") {{ setStyle("-fx-text-fill: #999999; -fx-font-size: 14px;"); }}
        );
        threadArea.getChildren().add(placeholder);

        Task<List<Quiz>> task = new Task<>() {
            @Override
            protected List<Quiz> call() throws Exception {
                return api.getQuizzes();
            }
        };
        task.setOnSucceeded(e -> {
            List<Quiz> quizzes = task.getValue();
            Platform.runLater(() -> {
                renderQuizList(quizzes);
            });
        });
        task.setOnFailed(e -> {
            Platform.runLater(() -> {
                contextList.getChildren().clear();
                Label errorLabel = new Label("❌ Failed to load quizzes: " + task.getException().getMessage());
                errorLabel.setStyle("-fx-padding: 40px; -fx-text-fill: #dc2626; -fx-alignment: center; -fx-wrap-text: true;");
                contextList.getChildren().add(errorLabel);
                task.getException().printStackTrace();
            });
        });
        new Thread(task).start();
    }

    private void renderQuizList(List<Quiz> quizzes) {
        contextList.getChildren().clear();
        if (quizzes.isEmpty()) {
            Label empty = new Label("No quizzes available for your groups.");
            empty.setStyle("-fx-padding: 40px; -fx-text-fill: #999999; -fx-font-size: 14px; -fx-alignment: center;");
            contextList.getChildren().add(empty);
            return;
        }

        VBox quizzesBox = new VBox(8);
        quizzesBox.setPadding(new Insets(12));

        for (Quiz quiz : quizzes) {
            VBox card = createQuizCard(quiz);
            quizzesBox.getChildren().add(card);
        }

        contextList.getChildren().add(quizzesBox);
    }

    private VBox createQuizCard(Quiz quiz) {
        VBox card = new VBox(6);
        card.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-radius: 8px; -fx-background-radius: 8px; -fx-padding: 12px 14px;");
        card.setPrefWidth(240);

        HBox headerRow = new HBox();
        headerRow.setAlignment(Pos.CENTER_LEFT);
        Label titleLabel = new Label(quiz.title);
        titleLabel.setStyle("-fx-font-size: 14px; -fx-font-weight: 600;");
        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        String statusText;
        String statusColor;
        String bgColor;
        switch (quiz.status) {
            case "started":
                statusText = "🔵 Started";
                statusColor = "#1d4ed8";
                bgColor = "#dbeafe";
                break;
            case "ended":
                statusText = "🔴 Ended";
                statusColor = "#dc2626";
                bgColor = "#fef2f2";
                break;
            case "upcoming":
            default:
                statusText = "🟡 Upcoming";
                statusColor = "#b45309";
                bgColor = "#fef3c7";
                break;
        }
        Label statusLabel = new Label(statusText);
        statusLabel.setStyle("-fx-background-color: " + bgColor + "; -fx-text-fill: " + statusColor + "; " +
                "-fx-font-size: 9px; -fx-font-weight: 600; -fx-padding: 2px 10px; -fx-background-radius: 12px;");
        headerRow.getChildren().addAll(titleLabel, spacer, statusLabel);

        Label infoLabel = new Label(quiz.totalQuestions + " questions · " + quiz.durationMinutes + " min");
        infoLabel.setStyle("-fx-text-fill: #666666; -fx-font-size: 12px;");

        Button startBtn = new Button("▶ Start Quiz");
        startBtn.setStyle("-fx-background-color: #1A7A64; -fx-text-fill: #ffffff; -fx-padding: 4px; " +
                "-fx-border-radius: 6px; -fx-background-radius: 6px; -fx-font-size: 12px;");

        if ("started".equals(quiz.status)) {
            startBtn.setDisable(false);
            startBtn.setOnAction(new QuizStartHandler(quiz));
        } else {
            startBtn.setDisable(true);
            startBtn.setStyle("-fx-background-color: #e5e5e5; -fx-text-fill: #999999; -fx-padding: 4px; " +
                    "-fx-border-radius: 6px; -fx-background-radius: 6px; -fx-font-size: 12px;");
            if ("ended".equals(quiz.status)) {
                startBtn.setText("🔒 Ended");
            } else {
                startBtn.setText("⏳ Coming Soon");
            }
        }

        card.getChildren().addAll(headerRow, infoLabel, startBtn);
        return card;
    }

    // ─── QUIZ START HANDLER ──────────────────────────────────────

    private class QuizStartHandler implements EventHandler<ActionEvent> {
        private final Quiz quiz;
        QuizStartHandler(Quiz quiz) { this.quiz = quiz; }
        @Override
        public void handle(ActionEvent event) {
            startQuiz(quiz);
        }
    }

    private void startQuiz(Quiz quiz) {
        if (!state.isOnline()) {
            showToast("🌐 Quizzes require an internet connection. Please connect and try again.");
            return;
        }

        try {
            Task<QuizAttempt> task = new Task<>() {
                @Override
                protected QuizAttempt call() throws Exception {
                    return api.startQuiz(quiz.id);
                }
            };
            task.setOnSucceeded(e -> {
                QuizAttempt attempt = task.getValue();
                Platform.runLater(() -> {
                    try {
                        FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/forum/fxmlfiles/Quiz.fxml"));
                        Parent quizView = loader.load();
                        QuizController controller = loader.getController();

                        setLockdown(true);
                        isQuizActive = true;

                        threadArea.getChildren().clear();
                        threadArea.getChildren().add(quizView);
                        VBox.setVgrow(quizView, Priority.ALWAYS);

                        controller.setQuizData(attempt, () -> {
                            setLockdown(false);
                            isQuizActive = false;
                            showQuizzes();
                        });

                    } catch (Exception ex) {
                        ex.printStackTrace();
                        showToast("Error loading quiz: " + ex.getMessage());
                        setLockdown(false);
                        isQuizActive = false;
                    }
                });
            });
            task.setOnFailed(e -> {
                Platform.runLater(() -> {
                    showToast("Failed to start quiz: " + task.getException().getMessage());
                    task.getException().printStackTrace();
                });
            });
            new Thread(task).start();

        } catch (Exception e) {
            e.printStackTrace();
            showToast("Error: " + e.getMessage());
        }
    }

    // ─── RESULTS ──────────────────────────────────────────────────

    @FXML
    public void showResults() {
        if (isQuizActive) {
            showToast("🔒 Cannot navigate while quiz is in progress.");
            return;
        }
        currentView = "results";
        setActiveNav(navResults);
        contextTitle.setText("Quiz Results");
        contextActionBtn.setVisible(false);
        contextActionBtn.setManaged(false);
        replyForm.setVisible(false);
        replyForm.setManaged(false);

        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/forum/fxmlfiles/quiz_result.fxml"));
            Parent listView = loader.load();

            QuizResultsController controller = loader.getController();
            controller.setRightPanel(threadArea);

            contextList.getChildren().clear();
            contextList.getChildren().add(listView);
            VBox.setVgrow(listView, Priority.ALWAYS);

            Task<List<QuizAttempt>> task = new Task<>() {
                @Override
                protected List<QuizAttempt> call() throws Exception {
                    return api.getQuizAttempts();
                }
            };
            task.setOnSucceeded(e -> {
                List<QuizAttempt> attempts = task.getValue();
                Platform.runLater(() -> {
                    controller.setAttempts(attempts);
                });
            });
            task.setOnFailed(e -> {
                Platform.runLater(() -> {
                    controller.showError("Failed to load attempts: " + task.getException().getMessage());
                    task.getException().printStackTrace();
                });
            });
            new Thread(task).start();

        } catch (Exception e) {
            e.printStackTrace();
            Label error = new Label("Could not load quiz results.");
            error.setStyle("-fx-padding: 20px; -fx-text-fill: #dc2626;");
            contextList.getChildren().add(error);
        }

        threadArea.getChildren().clear();
        VBox placeholder = new VBox(12);
        placeholder.setAlignment(Pos.CENTER);
        placeholder.setPadding(new Insets(60, 20, 60, 20));
        placeholder.getChildren().addAll(
                new Label("📊") {{ setStyle("-fx-font-size: 32px; -fx-text-fill: #999999;"); }},
                new Label("Select a quiz result to view detailed analytics") {{ setStyle("-fx-text-fill: #999999; -fx-font-size: 14px;"); }}
        );
        threadArea.getChildren().add(placeholder);
    }

    // ─── LOCKDOWN ──────────────────────────────────────────────────

    private void setLockdown(boolean enabled) {
        navGroups.setDisable(enabled);
        navProfile.setDisable(enabled);
        navQuizzes.setDisable(enabled);
        navResults.setDisable(enabled);
        contextActionBtn.setDisable(enabled);
        replyForm.setDisable(enabled);
        replyText.setDisable(enabled);
        privateCheck.setDisable(enabled);
        selectUsersBtn.setDisable(enabled);

        contextList.setMouseTransparent(enabled);
        contextList.setDisable(enabled);

        threadArea.setMouseTransparent(enabled);
        threadArea.setDisable(enabled);

        searchField.setDisable(enabled);

        if (enabled) {
            contextList.setStyle("-fx-cursor: default;");
            threadArea.setStyle("-fx-cursor: default; -fx-background-color: #f0f0f0;");
            contextTitle.setText("🔒 Quiz in Progress");
        } else {
            contextList.setStyle("-fx-cursor: hand;");
            threadArea.setStyle("-fx-cursor: default; -fx-background-color: #f9f9f9;");
            if (currentGroup != null) {
                contextTitle.setText(currentGroup.name);
            } else {
                contextTitle.setText("Groups");
            }
        }
    }

    // ─── LOGOUT ────────────────────────────────────────────────────

    @FXML
    public void handleLogout() {
        if (isQuizActive) {
            showToast("🔒 Cannot logout while quiz is in progress. Please complete or close the quiz first.");
            return;
        }
        Task<Void> logoutTask = new Task<>() {
            @Override
            protected Void call() throws Exception {
                api.logout();
                return null;
            }
        };
        logoutTask.setOnSucceeded(e -> {
            state.clearSession();
            try {
                MainApp.switchToLogin();
            } catch (Exception ex) {
                ex.printStackTrace();
            }
        });
        logoutTask.setOnFailed(e -> {
            state.clearSession();
            try {
                MainApp.switchToLogin();
            } catch (Exception ex) {
                ex.printStackTrace();
            }
        });
        new Thread(logoutTask).start();
    }

    // ─── CREATE TOPIC (FXML action) ──────────────────────────────

    @FXML
    public void handleCreateTopic() {
        if (isQuizActive) {
            showToast("🔒 Cannot create topic while quiz is in progress.");
            return;
        }
        if (currentGroup != null) {
            showCreateTopicDialog(currentGroup);
        } else {
            showToast("Please select a group first.");
        }
    }

    @FXML
    public void onSelectUsers() {
        if (privateCheck.isSelected()) {
            excludedUserIds = showUserSelectionDialog();
            updateSelectedUsersLabel();
        }
    }
}