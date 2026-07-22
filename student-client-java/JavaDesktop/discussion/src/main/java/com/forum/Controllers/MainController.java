package com.forum.Controllers;

import com.forum.GlobalState;
import com.forum.MainApp;
import com.forum.models.Group;
import com.forum.models.Topic;
import com.forum.models.Post;
import com.forum.models.User;
import com.forum.services.ApiService;
import javafx.application.Platform;
import javafx.concurrent.Task;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.shape.Circle;
import javafx.scene.text.Text;
import javafx.stage.Modality;
import javafx.stage.Stage;
import javafx.stage.StageStyle;

import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.List;

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

    private final GlobalState state = GlobalState.getInstance();
    private final ApiService api = ApiService.getInstance();
    
    // State
    private String currentView = "groups";
    private Group currentGroup;
    private Topic currentTopic;
    private List<Group> groups = new ArrayList<>();
    private List<Topic> topics = new ArrayList<>();
    private List<Post> currentPosts = new ArrayList<>();
    private boolean quizActive = false;
    private boolean lockdownActive = false;

    @FXML
    public void initialize() {
        try {
            System.out.println("MainController.initialize: start");
            
            // Set user name from global state
            User user = state.getCurrentUser();
            if (user != null) {
                userNameText.setText(user.name);
            } else {
                userNameText.setText("Guest");
            }
            
            // Setup connection status
            setupConnectionStatus();
            
            // Setup auth listeners
            setupAuthListeners();
            
            // Load groups from API
            loadGroups();
            
            System.out.println("MainController.initialize: done");
        } catch (Exception e) {
            System.err.println("Exception in MainController.initialize:");
            e.printStackTrace();
            throw e;
        }
    }

    // ==================== CONNECTION STATUS ====================
    
    private void setupConnectionStatus() {
        // Initial status
        updateConnectionUI(state.isOnline());
        
        // Listen for changes
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

    // ==================== AUTH LISTENERS ====================
    
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

    // ==================== DATA LOADING ====================
    
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
                    showGroups();
                }
            });
        });

        task.setOnFailed(e -> {
            Platform.runLater(() -> {
                showEmptyState("Failed to load groups. Check your connection.");
                System.err.println("Failed to load groups:");
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
                // Clear thread area
                threadArea.getChildren().clear();
                replyForm.setVisible(false);
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

    // ==================== UI HELPERS ====================
    
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

    private void renderGroups() {
        contextList.getChildren().clear();

        if (groups.isEmpty()) {
            showEmptyState("No groups available.");
            return;
        }

        for (Group group : groups) {
            VBox item = createGroupItem(group);
            contextList.getChildren().add(item);
        }
    }

    private VBox createGroupItem(Group group) {
        VBox item = new VBox(4);
        item.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-width: 0 0 1px 0; " +
                "-fx-padding: 12px 16px; -fx-cursor: hand;");
        item.setOnMouseClicked(e -> handleGroupClick(group));

        Label title = new Label(group.name);
        title.setStyle("-fx-font-size: 14px; -fx-font-weight: 600; -fx-text-fill: #000000;");

        Label desc = new Label(group.description != null ? group.description : "");
        desc.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");

        item.getChildren().addAll(title, desc);
        return item;
    }

    private void handleGroupClick(Group group) {
        currentGroup = group;
        contextTitle.setText(group.name);
        contextActionBtn.setVisible(true);
        contextActionBtn.setManaged(true);
        contextActionBtn.setText("+");
        contextActionBtn.setOnAction(e -> showCreateTopicDialog(group));

        replyForm.setVisible(false);
        replyForm.setManaged(false);

        loadTopicsForGroup(group);
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
            VBox item = createTopicItem(topic);
            contextList.getChildren().add(item);
        }
    }

    private VBox createTopicItem(Topic topic) {
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

        item.getChildren().addAll(title, sub);
        return item;
    }

    // ==================== TOPIC VIEW ====================

    private void openTopic(Topic topic) {
        currentTopic = topic;
        contextTitle.setText(currentGroup != null ? currentGroup.name : "Topic");
        contextActionBtn.setVisible(true);
        contextActionBtn.setManaged(true);
        contextActionBtn.setText("+");
        contextActionBtn.setOnAction(e -> showCreateTopicDialog(currentGroup));

        loadPostsForTopic(topic);
    }

    private void renderThread(Topic topic, List<Post> posts) {
        threadArea.getChildren().clear();

        // Back button
        HBox topBar = new HBox(12);
        topBar.setAlignment(Pos.CENTER_LEFT);
        topBar.setStyle("-fx-padding: 0 0 12 0;");

        Button backBtn = new Button("← Back");
        backBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #666666; -fx-font-size: 13px; -fx-cursor: hand;");
        backBtn.setOnAction(e -> {
            if (currentGroup != null) {
                handleGroupClick(currentGroup);
            }
        });

        topBar.getChildren().add(backBtn);

        // Title
        Label title = new Label(topic.title);
        title.setStyle("-fx-font-size: 20px; -fx-font-weight: 700; -fx-text-fill: #000000;");
        title.setPadding(new Insets(8, 0, 8, 0));

        // Posts container
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
                VBox postView = createPostView(post);
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

    private VBox createPostView(Post post) {
        VBox postBox = new VBox(6);
        postBox.setStyle("-fx-background-color: #ffffff; -fx-border-color: #1A7A64; -fx-border-radius: 8px; " +
                "-fx-background-radius: 8px; -fx-padding: 14px 18px;");

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
                "-fx-background-color: #e5e5e5; -fx-alignment: center; -fx-font-weight: 600; -fx-font-size: 12px; " +
                "-fx-text-fill: #000000;");

        Label name = new Label(authorName);
        name.setStyle("-fx-font-weight: 600; -fx-font-size: 14px; -fx-text-fill: #000000;");

        Label time = new Label(post.created_at != null ? post.created_at : "");
        time.setStyle("-fx-font-size: 12px; -fx-text-fill: #999999;");

        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        header.getChildren().addAll(avatar, name, time, spacer);

        if (post.is_private) {
            Label privateTag = new Label("🔒 Private");
            privateTag.setStyle("-fx-font-size: 9px; -fx-font-weight: 700; -fx-padding: 2px 10px; " +
                    "-fx-background-radius: 12px; -fx-background-color: #fef3c7; -fx-text-fill: #b45309;");
            header.getChildren().add(privateTag);
        }

        // Body
        Label body = new Label(post.content != null ? post.content : "");
        body.setStyle("-fx-font-size: 14px; -fx-text-fill: #1e293b; -fx-wrap-text: true;");
        body.setMaxWidth(Double.MAX_VALUE);

        postBox.getChildren().addAll(header, body);
        return postBox;
    }

    // ==================== CREATE TOPIC ====================

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
                    // Create topic via API
                    Task<Topic> createTask = new Task<>() {
                        @Override
                        protected Topic call() throws Exception {
                            return api.createTopic(group.id, topicTitle, descInput.getText().trim());
                        }
                    };
                    
                    createTask.setOnSucceeded(ev -> {
                        createStage.close();
                        showToast("Topic created successfully!");
                        // Reload topics
                        handleGroupClick(group);
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

    // ==================== POST REPLY ====================

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

        Task<Post> task = new Task<>() {
            @Override
            protected Post call() throws Exception {
                return api.createPost(currentTopic.id, userId, content, isPrivate, null);
            }
        };

        task.setOnSucceeded(e -> {
            replyText.clear();
            privateCheck.setSelected(false);
            showToast("Reply posted!");
            // Reload posts
            loadPostsForTopic(currentTopic);
        });

        task.setOnFailed(e -> {
            showToast("Failed to post reply: " + task.getException().getMessage());
            task.getException().printStackTrace();
        });

        new Thread(task).start();
    }

    // ==================== LOGOUT ====================

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
            // Even if API fails, clear local session
            state.clearSession();
            try {
                MainApp.switchToLogin();
            } catch (Exception ex) {
                ex.printStackTrace();
            }
        });

        new Thread(logoutTask).start();
    }

    // ==================== PLACEHOLDER METHODS ====================
    
    @FXML
    public void showProfile() {
        // Keep existing implementation or load from API
        currentView = "profile";
        setActiveNav(navProfile);
        contextTitle.setText("Profile");
        contextActionBtn.setVisible(false);
        contextActionBtn.setManaged(false);
        replyForm.setVisible(false);
        replyForm.setManaged(false);

        // Show user info from global state
        contextList.getChildren().clear();
        VBox profileBox = new VBox(16);
        profileBox.setPadding(new Insets(16));
        profileBox.setStyle("-fx-background-color: #ffffff;");
        
        User user = state.getCurrentUser();
        if (user != null) {
            Label nameLabel = new Label("👤 " + user.name);
            nameLabel.setStyle("-fx-font-size: 16px; -fx-font-weight: 700;");
            Label emailLabel = new Label("📧 " + user.email);
            emailLabel.setStyle("-fx-font-size: 14px; -fx-text-fill: #666;");
            Label roleLabel = new Label("Role: " + user.role);
            roleLabel.setStyle("-fx-font-size: 14px; -fx-text-fill: #666;");
            profileBox.getChildren().addAll(nameLabel, emailLabel, roleLabel);
        } else {
            Label notLoggedIn = new Label("Not logged in");
            notLoggedIn.setStyle("-fx-font-size: 14px; -fx-text-fill: #999;");
            profileBox.getChildren().add(notLoggedIn);
        }
        
        contextList.getChildren().add(profileBox);

        // Clear thread area
        threadArea.getChildren().clear();
        VBox placeholder = new VBox(12);
        placeholder.setAlignment(Pos.CENTER);
        placeholder.setPadding(new Insets(60, 20, 60, 20));
        Label icon = new Label("👤");
        icon.setStyle("-fx-font-size: 32px; -fx-text-fill: #999999;");
        Label msg = new Label("User Profile");
        msg.setStyle("-fx-text-fill: #999999; -fx-font-size: 14px;");
        placeholder.getChildren().addAll(icon, msg);
        threadArea.getChildren().add(placeholder);
    }

    @FXML
    public void showQuizzes() {
        // Keep existing implementation
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
        Label info = new Label("📝 Quizzes will be available soon.");
        info.setStyle("-fx-font-size: 14px; -fx-text-fill: #666; -fx-padding: 20px;");
        quizzesBox.getChildren().add(info);
        contextList.getChildren().add(quizzesBox);

        threadArea.getChildren().clear();
        VBox placeholder = new VBox(12);
        placeholder.setAlignment(Pos.CENTER);
        placeholder.setPadding(new Insets(60, 20, 60, 20));
        Label icon = new Label("📝");
        icon.setStyle("-fx-font-size: 32px; -fx-text-fill: #999999;");
        Label msg = new Label("Quizzes coming soon");
        msg.setStyle("-fx-text-fill: #999999; -fx-font-size: 14px;");
        placeholder.getChildren().addAll(icon, msg);
        threadArea.getChildren().add(placeholder);
    }

    @FXML
    public void showResults() {
        // Keep existing implementation
        currentView = "results";
        setActiveNav(navResults);
        contextTitle.setText("Quiz Results");
        contextActionBtn.setVisible(false);
        contextActionBtn.setManaged(false);
        replyForm.setVisible(false);
        replyForm.setManaged(false);

        contextList.getChildren().clear();
        VBox resultsBox = new VBox(8);
        resultsBox.setPadding(new Insets(12));
        Label info = new Label("📊 Results will be available here.");
        info.setStyle("-fx-font-size: 14px; -fx-text-fill: #666; -fx-padding: 20px;");
        resultsBox.getChildren().add(info);
        contextList.getChildren().add(resultsBox);

        threadArea.getChildren().clear();
        VBox placeholder = new VBox(12);
        placeholder.setAlignment(Pos.CENTER);
        placeholder.setPadding(new Insets(60, 20, 60, 20));
        Label icon = new Label("📊");
        icon.setStyle("-fx-font-size: 32px; -fx-text-fill: #999999;");
        Label msg = new Label("Quiz results coming soon");
        msg.setStyle("-fx-text-fill: #999999; -fx-font-size: 14px;");
        placeholder.getChildren().addAll(icon, msg);
        threadArea.getChildren().add(placeholder);
    }

    @FXML
    public void handleCreateTopic() {
        if (currentGroup != null) {
            showCreateTopicDialog(currentGroup);
        } else {
            showToast("Please select a group first.");
        }
    }

    // ==================== LOCKDOWN (for quizzes) ====================
    
    private void setLockdown(boolean enabled) {
        lockdownActive = enabled;
        navGroups.setDisable(enabled);
        navProfile.setDisable(enabled);
        navQuizzes.setDisable(enabled);
        navResults.setDisable(enabled);
        contextActionBtn.setDisable(enabled);
        replyForm.setDisable(enabled);
        replyText.setDisable(enabled);
        privateCheck.setDisable(enabled);
        contextList.setMouseTransparent(enabled);
        if (enabled) {
            contextList.setStyle("-fx-cursor: default;");
        } else {
            contextList.setStyle("-fx-cursor: hand;");
        }
    }
}