package com.forum.controllers;

import com.forum.MainApp;
import com.forum.models.Group;
import com.forum.models.Post;
import com.forum.models.Topic;
import com.forum.models.User;
import com.forum.services.ApiService;
import com.forum.services.DatabaseHandler;
import com.forum.services.GlobalState;
import javafx.application.Platform;
import javafx.concurrent.Task;
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

    // ─── FXML INJECTIONS ──────────────────────────────────────────
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

    // ─── SERVICES & STATE ─────────────────────────────────────────
    private final GlobalState state = GlobalState.getInstance();
    private final ApiService api = ApiService.getInstance();

    // Data from API
    private List<Group> groups = new ArrayList<>();
    private List<Topic> topics = new ArrayList<>();
    private List<Post> currentPosts = new ArrayList<>();

    // Current selections
    private String currentView = "groups";
    private Group currentGroup;
    private Topic currentTopic;

    // Track joined groups (local only – extend to real API later)
    private final Set<Integer> joinedGroupIds = new HashSet<>();

    // Inline reply tracking
    private Post currentReplyTarget = null;
    private VBox currentInlineForm = null;

    // UI helper
    private Label replyToLabel;

    // ─── INITIALIZATION ────────────────────────────────────────────

    @FXML
    public void initialize() {
        try {
            System.out.println("MainController.initialize: start");

            // Add "Replying to" label to the main reply form
            if (replyForm != null && replyForm.getChildren().size() > 0) {
                replyToLabel = new Label("Replying to: Thread");
                replyToLabel.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666; -fx-padding: 0 0 4 0;");
                replyForm.getChildren().add(0, replyToLabel);
            }

            User user = state.getCurrentUser();
            userNameText.setText(user != null ? user.name : "Guest");

            setupConnectionStatus();
            setupAuthListeners();

            // Load real groups from API
            loadGroups();

            System.out.println("MainController.initialize: done");
        } catch (Exception e) {
            System.err.println("Exception in MainController.initialize:");
            e.printStackTrace();
            throw e;
        }
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
            groups = task.getValue();
            Platform.runLater(() -> {
                if (groups.isEmpty()) {
                    showEmptyState("No groups available.");
                } else {
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
            currentPosts = task.getValue();
            Platform.runLater(() -> {
                renderThread(topic, currentPosts);
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

    // ─── RENDER GROUPS (Option B style + real API) ──────────────

    private void renderGroups() {
        contextList.getChildren().clear();
        if (groups.isEmpty()) {
            showEmptyState("No groups available.");
            return;
        }

        for (Group group : groups) {
            VBox item = new VBox(4);
            item.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-width: 0 0 1px 0; " +
                    "-fx-padding: 12px 16px; -fx-cursor: hand;");
            item.setOnMouseClicked(e -> handleGroupClick(group));

            Label title = new Label(group.name);
            title.setStyle("-fx-font-size: 14px; -fx-font-weight: 600; -fx-text-fill: #000000;");

            Label desc = new Label(group.description != null ? group.description : "");
            desc.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");

            HBox metaRow = new HBox(12);
            metaRow.setAlignment(Pos.CENTER_RIGHT);

            // We don't have member counts from API, so we show a placeholder
            Label topicsLabel = new Label("📄 0 topics");
            topicsLabel.setStyle("-fx-font-size: 11px; -fx-text-fill: #999999;");
            Label membersLabel = new Label("👤 0 members");
            membersLabel.setStyle("-fx-font-size: 11px; -fx-text-fill: #999999;");

            Region spacer = new Region();
            HBox.setHgrow(spacer, Priority.ALWAYS);

            boolean isJoined = joinedGroupIds.contains(group.id);
            Button joinBtn = new Button(isJoined ? "Leave" : "Join");
            joinBtn.setStyle("-fx-background-color: " + (isJoined ? "#dc3545" : "#1A7A64") + "; " +
                    "-fx-text-fill: #ffffff; -fx-font-size: 11px; -fx-font-weight: 600; -fx-padding: 2px 14px; " +
                    "-fx-border-radius: 12px; -fx-background-radius: 12px;");
            joinBtn.setOnAction(e -> {
                e.consume();
                if (!isJoined) {
                    showCommunityRules(group);
                } else {
                    joinedGroupIds.remove(group.id);
                    renderGroups();
                }
            });

            metaRow.getChildren().addAll(topicsLabel, membersLabel, spacer, joinBtn);
            item.getChildren().addAll(title, desc, metaRow);
            contextList.getChildren().add(item);
        }
    }

    private void handleGroupClick(Group group) {
        if (!joinedGroupIds.contains(group.id)) {
            showCommunityRules(group);
            return;
        }
        openGroupTopics(group);
    }

    // ─── COMMUNITY RULES (Option B modal) ────────────────────────

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
                joinedGroupIds.add(group.id);
                rulesStage.close();
                renderGroups();
                openGroupTopics(group);
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

    // ─── OPEN GROUP & RENDER TOPICS ──────────────────────────────

    private void openGroupTopics(Group group) {
        currentGroup = group;
        contextTitle.setText(group.name);
        contextActionBtn.setVisible(true);
        contextActionBtn.setManaged(true);
        contextActionBtn.setText("+");
        contextActionBtn.setOnAction(e -> showCreateTopicDialog(group));

        replyForm.setVisible(false);
        replyForm.setManaged(false);

        loadTopicsForGroup(group);

        // Clear right panel
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

    private void renderTopics(List<Topic> topicList) {
        contextList.getChildren().clear();
        if (topicList.isEmpty()) {
            Label empty = new Label("No topics yet. Start a new discussion!");
            empty.setStyle("-fx-padding: 40px 20px; -fx-text-fill: #999999; -fx-font-size: 14px;");
            empty.setAlignment(Pos.CENTER);
            contextList.getChildren().add(empty);
            return;
        }
        for (Topic topic : topicList) {
            VBox item = new VBox(4);
            item.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-width: 0 0 1px 0; " +
                    "-fx-padding: 12px 16px; -fx-cursor: hand;");
            item.setOnMouseClicked(e -> openTopic(topic));

            Label title = new Label(topic.title);
            title.setStyle("-fx-font-size: 14px; -fx-font-weight: 600; -fx-text-fill: #000000;");

            String creatorName = "Unknown";
            if (topic.creator != null && topic.creator.has("name")) {
                creatorName = topic.creator.path("name").asText("Unknown");
            }
            Label sub = new Label("by " + creatorName + " • " + (topic.created_at != null ? topic.created_at : ""));
            sub.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");

            HBox metaRow = new HBox(12);
            metaRow.setAlignment(Pos.CENTER_LEFT);
            Label repliesLabel = new Label("💬 0 replies"); // will be updated when posts load
            repliesLabel.setStyle("-fx-font-size: 11px; -fx-text-fill: #999999;");

            Label tagLabel = new Label("General");
            tagLabel.setStyle("-fx-font-size: 9px; -fx-font-weight: 700; -fx-padding: 1px 10px; " +
                    "-fx-background-radius: 12px; -fx-background-color: #e5e5e5; -fx-text-fill: #333333;");

            metaRow.getChildren().addAll(repliesLabel, tagLabel);
            item.getChildren().addAll(title, sub, metaRow);
            contextList.getChildren().add(item);
        }
    }

    private void openTopic(Topic topic) {
        currentTopic = topic;
        contextTitle.setText(currentGroup != null ? currentGroup.name : "Topic");
        contextActionBtn.setVisible(true);
        contextActionBtn.setManaged(true);
        contextActionBtn.setText("+");
        contextActionBtn.setOnAction(e -> showCreateTopicDialog(currentGroup));
        loadPostsForTopic(topic);
    }

    // ─── RENDER THREAD (Posts) ────────────────────────────────────

    private void renderThread(Topic topic, List<Post> posts) {
        threadArea.getChildren().clear();

        // Top bar: Back, Share, Export
        HBox topBar = new HBox(12);
        topBar.setAlignment(Pos.CENTER_LEFT);
        topBar.setStyle("-fx-padding: 0 0 12 0;");

        Button backBtn = new Button("← Back");
        backBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #666666; -fx-font-size: 13px; -fx-cursor: hand;");
        backBtn.setOnAction(e -> {
            if (currentGroup != null) openGroupTopics(currentGroup);
        });

        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        Button shareBtn = new Button("📤 Share");
        shareBtn.setStyle("-fx-background-color: #87cefa; -fx-border-color: #e5e5e5; -fx-border-radius: 6px; " +
                "-fx-padding: 4px 14px; -fx-font-size: 12px; -fx-text-fill: #000000; -fx-cursor: hand;");
        shareBtn.setOnAction(e -> shareTopic(topic));

        Button exportBtn = new Button("📄 Export PDF");
        exportBtn.setStyle("-fx-background-color: #1A7A64; -fx-text-fill: #ffffff; -fx-border-radius: 6px; " +
                "-fx-padding: 4px 14px; -fx-font-size: 12px; -fx-cursor: hand;");
        exportBtn.setOnAction(e -> exportToPDF(topic));

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

    // ─── CREATE POST VIEW (Flat Card Style) ──────────────────────

    private VBox createPostView(Post post, int depth) {
        VBox postBox = new VBox(6);
        String style = "-fx-background-color: #ffffff; -fx-border-color: #1A7A64; -fx-border-radius: 8px; " +
                "-fx-background-radius: 8px; -fx-padding: 14px 18px;";
        if (depth > 0) {
            style += " -fx-border-width: 0 0 0 2px; -fx-border-radius: 0 8px 8px 0; -fx-background-radius: 0 8px 8px 0;";
        }
        postBox.setStyle(style);
        postBox.setId("post-" + post.id);

        // Header
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
        final Post postRef = post;
        final Button likeBtnRef = likeBtn;
        likeBtn.setOnAction(e -> handleLike(postRef, likeBtnRef));

        Button replyBtn = new Button("Reply");
        replyBtn.setStyle("-fx-background-color: transparent; -fx-border-color: #e5e5e5; -fx-border-radius: 12px; " +
                "-fx-padding: 2px 10px; -fx-font-size: 11px; -fx-cursor: hand; -fx-text-fill: #333333;");
        final Post replyPost = post;
        final String replyAuthor = authorName;
        replyBtn.setOnAction(e -> toggleInlineReply(replyPost, replyAuthor));

        header.getChildren().addAll(avatar, name, time, spacer, likeBtn, replyBtn);

        if (post.is_private) {
            Label privateTag = new Label("🔒 Private");
            privateTag.setStyle("-fx-font-size: 9px; -fx-font-weight: 700; -fx-padding: 2px 10px; " +
                    "-fx-background-radius: 12px; -fx-background-color: #fef3c7; -fx-text-fill: #b45309;");
            header.getChildren().add(privateTag);
        }

        Label body = new Label(post.content != null ? post.content : "");
        body.setStyle("-fx-font-size: 14px; -fx-text-fill: #1e293b; -fx-wrap-text: true;");
        body.setMaxWidth(Double.MAX_VALUE);

        postBox.getChildren().addAll(header, body);

        // Inline reply form (hidden by default)
        VBox inlineForm = createInlineReplyForm(post);
        inlineForm.setVisible(false);
        inlineForm.setManaged(false);
        postBox.getChildren().add(inlineForm);

        // Nested replies
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

    // ─── INLINE REPLY ─────────────────────────────────────────────

    private VBox createInlineReplyForm(Post parentPost) {
        VBox form = new VBox(6);
        form.setPadding(new Insets(8, 0, 0, 0));
        form.setStyle("-fx-border-color: #1A7A64; -fx-border-width: 1 0 0 0; -fx-padding: 8 0 0 0;");

        TextArea ta = new TextArea();
        ta.setPromptText("Write a reply...");
        ta.setPrefRowCount(2);
        ta.setStyle("-fx-padding: 8px 12px; -fx-border-color: #d0d5dd; -fx-border-radius: 6px; -fx-background-radius: 6px;");

        CheckBox privateCb = new CheckBox("Private");
        privateCb.setStyle("-fx-font-size: 12px;");

        Button postInlineBtn = new Button("Post Reply");
        postInlineBtn.setStyle("-fx-background-color: #1A7A64; -fx-text-fill: #ffffff; -fx-padding: 4px 16px; -fx-border-radius: 4px; -fx-background-radius: 4px; -fx-cursor: hand;");

        HBox buttonRow = new HBox(12, privateCb, postInlineBtn);
        buttonRow.setAlignment(Pos.CENTER_LEFT);

        form.getChildren().addAll(ta, buttonRow);
        postInlineBtn.setUserData(form);
        postInlineBtn.setOnAction(e -> handleInlineReply(parentPost, ta, privateCb, form));

        return form;
    }

    private void toggleInlineReply(Post post, String author) {
        if (currentInlineForm != null) {
            currentInlineForm.setVisible(false);
            currentInlineForm.setManaged(false);
            currentInlineForm = null;
        }

        VBox parentBox = findPostBox(post.id);
        if (parentBox != null) {
            for (var child : parentBox.getChildren()) {
                if (child instanceof VBox && child.getStyle().contains("border-color: #1A7A64; -fx-border-width: 1 0 0 0;")) {
                    VBox form = (VBox) child;
                    form.setVisible(true);
                    form.setManaged(true);
                    currentInlineForm = form;
                    for (var node : form.getChildren()) {
                        if (node instanceof TextArea) {
                            ((TextArea) node).requestFocus();
                            break;
                        }
                    }
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

        final Integer parentId = parentPost.id;
        final String timestamp = LocalDateTime.now().format(DateTimeFormatter.ISO_LOCAL_DATE_TIME);

        if (state.isOnline()) {
            Task<Post> task = new Task<>() {
                @Override
                protected Post call() throws Exception {
                    return api.createPost(currentTopic.id, userId, content, isPrivate, null, parentId);
                }
            };
            task.setOnSucceeded(e -> {
                ta.clear();
                privateCb.setSelected(false);
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
                post.is_liked = updated.is_liked;
                post.likes_count = updated.likes_count;
                Platform.runLater(() -> updateLikeUI(post, likeBtn));
            });
            task.setOnFailed(e -> {
                showToast("Failed to like: " + task.getException().getMessage());
            });
            new Thread(task).start();
        } else {
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

        final Integer parentId = (currentReplyTarget != null) ? currentReplyTarget.id : null;
        final String timestamp = LocalDateTime.now().format(DateTimeFormatter.ISO_LOCAL_DATE_TIME);

        if (state.isOnline()) {
            Task<Post> task = new Task<>() {
                @Override
                protected Post call() throws Exception {
                    return api.createPost(currentTopic.id, userId, content, isPrivate, null, parentId);
                }
            };
            task.setOnSucceeded(e -> {
                replyText.clear();
                privateCheck.setSelected(false);
                currentReplyTarget = null;
                if (replyToLabel != null) replyToLabel.setText("Replying to: Thread");
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
                currentReplyTarget = null;
                if (replyToLabel != null) replyToLabel.setText("Replying to: Thread");
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
        Clipboard clipboard = Clipboard.getSystemClipboard();
        ClipboardContent content = new ClipboardContent();
        content.putString("Topic: " + topic.title + "\nAuthor: " + topic.creator + "\nDate: " + topic.created_at);
        clipboard.setContent(content);
        showToast("Topic link copied to clipboard!");
    }

    private void exportToPDF(Topic topic) {
        try {
            String timestamp = LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyyMMdd_HHmmss"));
            String filename = "chat_export_" + topic.title.replaceAll(" ", "_") + "_" + timestamp + ".txt";
            File file = new File(System.getProperty("user.home") + "/Downloads/" + filename);

            try (FileWriter writer = new FileWriter(file)) {
                writer.write("=== " + topic.title + " ===\n");
                writer.write("Author: " + topic.creator + "\n");
                writer.write("Date: " + topic.created_at + "\n\n");
                for (Post post : currentPosts) {
                    appendPostToFile(writer, post, 0);
                }
            }
            showToast("✅ Chat exported to: " + file.getAbsolutePath());
        } catch (IOException e) {
            e.printStackTrace();
            showToast("❌ Error exporting chat: " + e.getMessage());
        }
    }

    private void appendPostToFile(FileWriter writer, Post post, int indent) throws IOException {
        String indentStr = "  ".repeat(indent);
        String authorName = post.author != null && post.author.has("name") ? post.author.path("name").asText("Unknown") : "Unknown";
        writer.write(indentStr + authorName + " (" + post.created_at + "):\n");
        writer.write(indentStr + "  " + post.content + "\n");
        if (post.is_private) {
            writer.write(indentStr + "  [PRIVATE]\n");
        }
        if (post.replies != null) {
            for (Post reply : post.replies) {
                appendPostToFile(writer, reply, indent + 1);
            }
        }
    }

    // ─── PROFILE (Option B Beautiful Centered Card) ──────────────

    @FXML
    public void showProfile() {
        currentView = "profile";
        setActiveNav(navProfile);
        contextTitle.setText("Profile");
        contextActionBtn.setVisible(false);
        contextActionBtn.setManaged(false);
        replyForm.setVisible(false);
        replyForm.setManaged(false);

        // Left panel: Performance stats (mock)
        contextList.getChildren().clear();
        VBox statsBox = new VBox(16);
        statsBox.setPadding(new Insets(16));
        statsBox.setStyle("-fx-background-color: #ffffff;");
        Label statsTitle = new Label("📊 Performance");
        statsTitle.setStyle("-fx-font-size: 14px; -fx-font-weight: 700; -fx-text-fill: #1A7A64;");
        statsBox.getChildren().add(statsTitle);
        String[][] stats = {{"📝","Total Posts","42"},{"💬","Total Replies","78"},{"📈","Insights","+12%"},{"📊","Analytics","4 groups"}};
        for (String[] stat : stats) {
            HBox row = new HBox(12);
            row.setAlignment(Pos.CENTER_LEFT);
            row.setStyle("-fx-padding: 8px 0; -fx-border-color: #f0f0f0; -fx-border-width: 0 0 1px 0;");
            row.getChildren().addAll(
                    new Label(stat[0]) {{ setStyle("-fx-font-size: 16px;"); }},
                    new Label(stat[1]) {{ setStyle("-fx-font-size: 13px; -fx-text-fill: #333333;"); }},
                    new Region() {{ HBox.setHgrow(this, Priority.ALWAYS); }},
                    new Label(stat[2]) {{ setStyle("-fx-font-size: 13px; -fx-font-weight: 600; -fx-text-fill: #000000;"); }}
            );
            statsBox.getChildren().add(row);
        }
        contextList.getChildren().add(statsBox);

        // Right panel: Centered Profile Card
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

        String[][] profileStats = {{"📚","Topics","15"},{"📝","Posts","42"},{"💬","Replies","78"},{"📊","Quizzes","5"}};
        GridPane statsGrid = createStatsGrid(profileStats);

        profileBox.getChildren().addAll(avatarRow, statsGrid);
        outerWrapper.getChildren().add(profileBox);
        threadArea.getChildren().add(outerWrapper);
    }

    private GridPane createStatsGrid(String[][] stats) {
        GridPane grid = new GridPane();
        grid.setHgap(20);
        grid.setVgap(12);
        grid.setPadding(new Insets(20, 0, 0, 0));
        grid.setAlignment(Pos.CENTER);
        for (int i = 0; i < 4; i++) {
            ColumnConstraints col = new ColumnConstraints();
            col.setPercentWidth(25);
            col.setHalignment(HPos.CENTER);
            grid.getColumnConstraints().add(col);
        }
        for (int i = 0; i < stats.length; i++) {
            VBox statBox = new VBox(4);
            statBox.setAlignment(Pos.CENTER);
            statBox.getChildren().addAll(
                    new Label(stats[i][0]) {{ setStyle("-fx-font-size: 20px;"); }},
                    new Label(stats[i][2]) {{ setStyle("-fx-font-size: 26px; -fx-font-weight: 700; -fx-text-fill: #1A7A64;"); }},
                    new Label(stats[i][1]) {{ setStyle("-fx-font-size: 13px; -fx-text-fill: #666666;"); }}
            );
            grid.add(statBox, i, 0);
        }
        return grid;
    }

    // ─── QUIZZES (Option B Card Layout) ──────────────────────────
    // FIXED: loop variable captured in lambda now uses final local copy

    @FXML
    public void showQuizzes() {
        currentView = "quizzes";
        setActiveNav(navQuizzes);
        contextTitle.setText("Quizzes");
        contextActionBtn.setVisible(false);
        contextActionBtn.setManaged(false);
        replyForm.setVisible(false);
        replyForm.setManaged(false);

        contextList.getChildren().clear();
        VBox quizzesBox = new VBox(8);
        quizzesBox.setPadding(new Insets(12));

        String[][] quizzes = {
                {"Physics 101 Midterm", "10 questions · 3 min", "Due soon", "#fef2f2", "#dc2626"},
                {"Chemistry Lab Quiz", "8 questions · 2 min", "Open", "#dbeafe", "#1d4ed8"},
                {"Mathematics Week 5", "12 questions · 4 min", "Upcoming", "#d1fae5", "#065f46"}
        };

        for (int i = 0; i < quizzes.length; i++) {
            final int index = i;  // ✅ create final copy for lambda

            VBox card = new VBox(6);
            card.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-radius: 8px; -fx-background-radius: 8px; -fx-padding: 12px 14px;");
            card.setPrefWidth(240);

            HBox headerRow = new HBox();
            headerRow.setAlignment(Pos.CENTER_LEFT);
            headerRow.getChildren().addAll(
                    new Label(quizzes[index][0]) {{ setStyle("-fx-font-size: 14px; -fx-font-weight: 600;"); }},
                    new Region() {{ HBox.setHgrow(this, Priority.ALWAYS); }},
                    new Label(quizzes[index][2]) {{ setStyle("-fx-background-color: " + quizzes[index][3] + "; -fx-text-fill: " + quizzes[index][4] + "; -fx-font-size: 9px; -fx-font-weight: 600; -fx-padding: 2px 10px; -fx-background-radius: 12px;"); }}
            );

            Label infoLabel = new Label(quizzes[index][1]);
            infoLabel.setStyle("-fx-text-fill: #666666; -fx-font-size: 12px;");

            Button startBtn = new Button("▶ Start Quiz");
            startBtn.setStyle("-fx-background-color: #1A7A64; -fx-text-fill: #ffffff; -fx-padding: 4px; -fx-border-radius: 6px; -fx-background-radius: 6px; -fx-font-size: 12px;");
            startBtn.setMaxWidth(Double.MAX_VALUE);
            startBtn.setOnAction(e -> startQuiz(index, quizzes[index][0]));  // ✅ uses final index

            card.getChildren().addAll(headerRow, infoLabel, startBtn);
            quizzesBox.getChildren().add(card);
        }
        contextList.getChildren().add(quizzesBox);

        threadArea.getChildren().clear();
        VBox placeholder = new VBox(12);
        placeholder.setAlignment(Pos.CENTER);
        placeholder.setPadding(new Insets(60, 20, 60, 20));
        placeholder.getChildren().addAll(
                new Label("📝") {{ setStyle("-fx-font-size: 32px; -fx-text-fill: #999999;"); }},
                new Label("Select a quiz to start") {{ setStyle("-fx-text-fill: #999999; -fx-font-size: 14px;"); }}
        );
        threadArea.getChildren().add(placeholder);
    }

    private void startQuiz(int quizIndex, String quizTitle) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/forum/fxmlfiles/Quiz.fxml"));
            Parent quizView = loader.load();
            com.forum.controllers.QuizController controller = loader.getController();

            threadArea.getChildren().clear();
            threadArea.getChildren().add(quizView);
            VBox.setVgrow(quizView, Priority.ALWAYS);

            controller.setQuizData(quizTitle, quizIndex, () -> {
                showQuizzes();
            });

        } catch (Exception e) {
            e.printStackTrace();
            showToast("Error starting quiz: " + e.getMessage());
        }
    }

    // ─── RESULTS (Option B style) ────────────────────────────────

    @FXML
    public void showResults() {
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

            com.forum.controllers.QuizResultsController controller = loader.getController();
            controller.setRightPanel(threadArea);

            contextList.getChildren().clear();
            contextList.getChildren().add(listView);
            VBox.setVgrow(listView, Priority.ALWAYS);

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

    // ─── LOGOUT ────────────────────────────────────────────────────

    @FXML
    public void handleLogout() {
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
        if (currentGroup != null) {
            showCreateTopicDialog(currentGroup);
        } else {
            showToast("Please select a group first.");
        }
    }
}