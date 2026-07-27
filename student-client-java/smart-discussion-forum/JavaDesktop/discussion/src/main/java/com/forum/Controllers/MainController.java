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

/**
 * MainController is the central hub for the primary user interface of the Smart Discussion Forum application.
 * 
 * <p><b>Architectural Role:</b>
 * This class acts as the Controller in the MVC (Model-View-Controller) pattern. It is responsible for:
 * <ul>
 *   <li>Managing the lifecycle and state of the main application view (Groups, Topics, Threads, Profile, Quizzes).</li>
 *   <li>Orchestrating asynchronous network requests via {@link ApiService} using JavaFX {@link Task} to prevent UI freezing.</li>
 *   <li>Maintaining an "offline-first" resilient architecture by falling back to {@link DatabaseHandler} when network requests fail.</li>
 *   <li>Dynamically generating and updating the JavaFX scene graph (e.g., building nested reply trees, rendering group lists).</li>
 *   <li>Enforcing application state constraints, such as the "Quiz Lockdown" mechanism which restricts navigation during active quizzes.</li>
 * </ul>
 * 
 * <p><b>Concurrency Model:</b>
 * All blocking operations (API calls, database reads) are executed on background threads via {@link Task}. 
 * Any mutation of the JavaFX scene graph is strictly delegated to the JavaFX Application Thread using {@link Platform#runLater(Runnable)} 
 * to ensure thread safety and prevent {@link IllegalStateException} crashes.
 * 
 * <p><b>State Management:</b>
 * Relies on the Singleton {@link GlobalState} for current user context, authentication status, and network connectivity status.
 * 
 * @author Forum Development Team
 * @version 2.0
 * @see ApiService
 * @see GlobalState
 * @see DatabaseHandler
 */
public class MainController {

    // =========================================================================
    // ─── FXML INJECTIONS ─────────────────────────────────────────────────────
    // =========================================================================
    // These fields are automatically populated by the FXMLLoader based on the 
    // fx:id attributes defined in the corresponding MainView.fxml file.
    // They represent the direct bridge between the Java logic and the UI layout.

    /** Displays the name of the currently authenticated user in the top navigation bar. */
    @FXML private Text userNameText;
    
    /** Displays the current contextual title (e.g., "Groups", "Computer Science 101", "Profile"). */
    @FXML private Text contextTitle;
    
    /** 
     * A dynamic action button whose purpose changes based on the current view. 
     * For example, it becomes a "+" button to create a new topic when inside a group.
     */
    @FXML private Button contextActionBtn;
    
    /** 
     * The left-hand sidebar container. It dynamically renders lists of Groups, Topics, 
     * or Quiz cards depending on the current navigation state.
     */
    @FXML private VBox contextList;
    
    /** 
     * The main content area on the right. It displays the active view's primary content, 
     * such as the thread discussion tree, profile statistics, or the active quiz interface.
     */
    @FXML private VBox threadArea;
    
    /** The container for the main reply form at the bottom of a thread view. */
    @FXML private VBox replyForm;
    
    /** The multi-line text input field where users type their main thread replies. */
    @FXML private TextArea replyText;
    
    /** Checkbox allowing the user to mark their post as private (visible only to selected users). */
    @FXML private CheckBox privateCheck;
    
    /** Button that opens the user selection dialog when a post is marked as private. */
    @FXML private Button selectUsersBtn;
    
    /** Visual indicator (green/red dot) showing the current network connectivity status. */
    @FXML private Circle statusDot;
    
    /** Text label accompanying the status dot, displaying "Online" or "Offline". */
    @FXML private Label statusLabel;
    
    /** Displays detailed synchronization status or error messages (e.g., "Offline - changes saved locally"). */
    @FXML private Text syncStatus;
    
    /** The search input field used to filter the displayed lists of groups or topics. */
    @FXML private TextField searchField;

    /** Navigation button to switch the main view to the Groups list. */
    @FXML private Button navGroups;
    
    /** Navigation button to switch the main view to the User Profile and statistics. */
    @FXML private Button navProfile;
    
    /** Navigation button to switch the main view to the available Quizzes list. */
    @FXML private Button navQuizzes;
    
    /** Navigation button to switch the main view to the historical Quiz Results. */
    @FXML private Button navResults;

    // =========================================================================
    // ─── CONSTANTS ───────────────────────────────────────────────────────────
    // =========================================================================

    /** 
     * The base URL for the backend REST API. 
     * Used for constructing absolute URLs when generating shareable links for topics and posts.
     */
    private static final String WEB_BASE_URL = "http://localhost:8000";

    // =========================================================================
    // ─── SERVICES & STATE ────────────────────────────────────────────────────
    // =========================================================================

    /** 
     * Singleton instance managing global application state, including the current 
     * authenticated user, network connectivity status, and event listeners.
     */
    private final GlobalState state = GlobalState.getInstance();
    
    /** 
     * Singleton instance responsible for all HTTP network communications with the backend server.
     */
    private final ApiService api = ApiService.getInstance();

    // --- Data Models (Cached from API) ---
    
    /** Complete list of all groups fetched from the server. */
    private List<Group> allGroups = new ArrayList<>();
    
    /** Observable list of groups the current user has already joined. Bound to the UI for reactive updates. */
    private ObservableList<Group> joinedGroups = FXCollections.observableArrayList();
    
    /** Observable list of groups the current user has not yet joined. Bound to the UI for reactive updates. */
    private ObservableList<Group> availableGroups = FXCollections.observableArrayList();
    
    /** 
     * Filtered wrapper around {@link #joinedGroups}. Allows real-time search filtering 
     * without mutating the underlying observable list.
     */
    private FilteredList<Group> filteredJoined;
    
    /** 
     * Filtered wrapper around {@link #availableGroups}. Allows real-time search filtering 
     * without mutating the underlying observable list.
     */
    private FilteredList<Group> filteredAvailable;

    /** Cached list of topics for the currently selected group. */
    private List<Topic> topics = new ArrayList<>();
    
    /** Cached list of posts for the currently selected topic. Represents the flattened or nested tree of posts. */
    private List<Post> currentPosts = new ArrayList<>();

    // --- Current Context Selections ---
    
    /** Tracks the current high-level view mode (e.g., "groups", "profile", "quizzes", "results"). */
    private String currentView = "groups";
    
    /** Reference to the group currently being viewed. Null if no group is selected. */
    private Group currentGroup;
    
    /** Reference to the topic currently being viewed. Null if no topic is selected. */
    private Topic currentTopic;

    // --- Inline Reply State Tracking ---
    
    /** Holds the reference to the specific post that the user is currently replying to inline. */
    private Post currentReplyTarget = null;
    
    /** Holds the UI node (VBox) of the currently active inline reply form to manage its visibility and focus. */
    private VBox currentInlineForm = null;

    // --- UI Helpers ---
    
    /** Label used in the main reply form to indicate who/what the user is replying to. */
    private Label replyToLabel;

    // --- Application State Flags ---
    
    /** 
     * Security/UX flag. When true, navigation and most interactions are disabled to prevent 
     * the user from leaving the quiz interface or interfering with quiz state.
     */
    private boolean isQuizActive = false;

    /** 
     * Stores the IDs of users to exclude from viewing a post when the "Private" checkbox is selected.
     * This is sent to the API to enforce server-side access control.
     */
    private List<Integer> excludedUserIds = new ArrayList<>();

    // =========================================================================
    // ─── INITIALIZATION ──────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Called automatically by the FXMLLoader after the FXML file has been loaded and 
     * all @FXML annotated fields have been injected.
     * 
     * <p><b>Responsibilities:</b>
     * <ul>
     *   <li>Initialize default UI states (e.g., reply labels).</li>
     *   <li>Fetch and display the current user's name from {@link GlobalState}.</li>
     *   <li>Setup event listeners for connection status, authentication changes, and search input.</li>
     *   <li>Configure the visibility logic for the "Private Post" user selection button.</li>
     *   <li>Setup a global click listener on the thread area to dismiss inline reply forms when clicking outside of them.</li>
     *   <li>Trigger the initial data load for groups.</li>
     * </ul>
     * 
     * @throws RuntimeException if a critical initialization failure occurs, preventing the app from functioning.
     */
    @FXML
    public void initialize() {
        try {
            System.out.println("MainController.initialize: start");

            // Initialize the main reply form's target label if the form exists in the FXML
            if (replyForm != null && replyForm.getChildren().size() > 0) {
                replyToLabel = new Label("Replying to: Thread");
                replyToLabel.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666; -fx-padding: 0 0 4 0;");
                // Insert at the top of the reply form
                replyForm.getChildren().add(0, replyToLabel);
            }

            // Safely retrieve the current user. Fallback to "Guest" if session is somehow uninitialized.
            User user = state.getCurrentUser();
            userNameText.setText(user != null ? user.name : "Guest");

            // Wire up core system listeners
            setupConnectionStatus();
            setupAuthListeners();
            setupSearchListener();

            // Bind the visibility of the "Select Users" button to the state of the "Private" checkbox.
            // If the user unchecks "Private", we must also clear the exclusion list to prevent stale data.
            privateCheck.selectedProperty().addListener((obs, oldVal, newVal) -> {
                selectUsersBtn.setVisible(newVal);
                selectUsersBtn.setManaged(newVal); // setManaged ensures it takes up layout space only when visible
                if (!newVal) {
                    excludedUserIds.clear();
                    updateSelectedUsersLabel();
                }
            });

            // Global click listener to dismiss inline reply forms when the user clicks elsewhere in the thread area.
            // This prevents multiple inline forms from being open simultaneously and improves UX.
            if (threadArea != null) {
                threadArea.setOnMouseClicked(e -> {
                    if (currentInlineForm != null) {
                        boolean clickedInside = false;
                        javafx.scene.Node target = (javafx.scene.Node) e.getTarget();
                        
                        // Traverse the node hierarchy upwards to check if the click originated inside the inline form
                        while (target != null) {
                            if (target.equals(currentInlineForm)) {
                                clickedInside = true;
                                break;
                            }
                            target = target.getParent();
                        }
                        
                        // If the click was outside the form, hide and un-manage it
                        if (!clickedInside) {
                            currentInlineForm.setVisible(false);
                            currentInlineForm.setManaged(false);
                            currentInlineForm = null;
                        }
                    }
                });
            }

            // Kick off the initial asynchronous data fetch for groups
            loadGroups();

            System.out.println("MainController.initialize: done");
        } catch (Exception e) {
            System.err.println("Exception in MainController.initialize:");
            e.printStackTrace();
            // Re-throw to ensure the application fails fast and visibly if initialization is broken
            throw e;
        }
    }

    // =========================================================================
    // ─── SEARCH LISTENER ─────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Configures the reactive search functionality for the Groups view.
     * 
     * <p><b>Implementation Detail:</b>
     * Instead of manually filtering and rebuilding the UI list on every keystroke, this method 
     * attaches a listener to the {@link #searchField}'s text property. It updates the predicate 
     * of the {@link FilteredList} wrappers ({@link #filteredJoined} and {@link #filteredAvailable}). 
     * JavaFX automatically handles the efficient re-rendering of the {@link #contextList} based on 
     * the filtered observable lists.
     */
    private void setupSearchListener() {
        searchField.textProperty().addListener((obs, oldVal, newVal) -> {
            // Normalize the query for case-insensitive matching
            String query = newVal.toLowerCase().trim();
            
            // Update the predicate for joined groups
            filteredJoined.setPredicate(group ->
                group.name.toLowerCase().contains(query)
            );
            
            // Update the predicate for available groups
            filteredAvailable.setPredicate(group ->
                group.name.toLowerCase().contains(query)
            );
            
            // Trigger a UI refresh of the group lists
            renderGroups();
        });
    }

    // =========================================================================
    // ─── CONNECTION & AUTH ───────────────────────────────────────────────────
    // =========================================================================

    /**
     * Initializes listeners to monitor the application's network connectivity status.
     * 
     * <p><b>Thread Safety Note:</b>
     * The {@link GlobalState.ConnectionListener} callbacks may be triggered from a background 
     * network monitoring thread. Therefore, all UI updates within the callback are strictly 
     * wrapped in {@link Platform#runLater(Runnable)} to ensure they execute on the JavaFX Application Thread.
     */
    private void setupConnectionStatus() {
        // Set initial UI state based on current known connectivity
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

    /**
     * Updates the visual indicators (status dot, label, sync text) to reflect the current network state.
     * 
     * @param isOnline {@code true} if the application has an active internet connection, {@code false} otherwise.
     */
    private void updateConnectionUI(boolean isOnline) {
        if (statusDot != null) {
            // Green (#16a34a) for online, Red (#dc2626) for offline
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

    /**
     * Initializes listeners to monitor the user's authentication state.
     * 
     * <p><b>Security Note:</b>
     * If the user is de-authenticated (e.g., logged out from another device) or the JWT token expires, 
     * this listener immediately forces a navigation back to the login screen to prevent unauthorized 
     * access to protected routes.
     */
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

    // =========================================================================
    // ─── REAL API DATA LOADING ───────────────────────────────────────────────
    // =========================================================================

    /**
     * Asynchronously fetches the list of all groups from the backend API.
     * 
     * <p><b>Concurrency Pattern:</b>
     * Uses {@link Task} to execute the blocking {@link ApiService#getGroups()} call on a background thread.
     * Upon success, it splits the data into joined/available lists and updates the UI on the JavaFX thread.
     * Upon failure, it gracefully degrades by showing an error state in the UI.
     */
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
        
        // Execute the task on a new background thread
        new Thread(task).start();
    }

    /**
     * Partitions the flat list of {@link #allGroups} into two distinct observable lists:
     * {@link #joinedGroups} and {@link #availableGroups}.
     * 
     * <p>This separation is required for the UI to render "My Groups" and "Discover Groups" 
     * as two visually distinct sections in the sidebar.
     */
    private void splitGroupsByMembership() {
        joinedGroups.clear();
        availableGroups.clear();
        for (Group g : allGroups) {
            System.out.println("Group: " + g.name + " isMember: " + g.isMember);
            if (g.isMember) {
                joinedGroups.add(g);
            } else {
                availableGroups.add(g);
            }
        }
    }

    /**
     * Initializes the {@link FilteredList} wrappers for the joined and available groups.
     * The initial predicate is set to {@code group -> true}, meaning all items are visible 
     * until the user types into the search field.
     */
    private void setupFilteredLists() {
        filteredJoined = new FilteredList<>(joinedGroups, group -> true);
        filteredAvailable = new FilteredList<>(availableGroups, group -> true);
    }

    /**
     * Asynchronously fetches the list of topics for a specific group.
     * 
     * @param group The {@link Group} object for which to fetch topics.
     */
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
                // Clear the main thread area and hide the reply form, as no specific topic is selected yet
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

    // =========================================================================
    // ─── FIXED: POST LOADING WITH TREE BUILDING FOR BOTH PATHS ───────────────
    // =========================================================================

    /**
     * Orchestrates the loading of posts for a given topic.
     * 
     * <p><b>Resilience Strategy:</b>
     * If the application is online, it attempts to fetch posts from the API. If the API call fails, 
     * it automatically falls back to {@link #loadLocalPosts(Topic)} to retrieve locally cached drafts 
     * or previously synced data, ensuring the user can still view content offline.
     * If the application is already offline, it bypasses the API and goes straight to the local database.
     * 
     * @param topic The {@link Topic} for which to load posts.
     */
    private void loadPostsForTopic(Topic topic) {
        if (state.isOnline()) {
            Task<List<Post>> task = new Task<>() {
                @Override
                protected List<Post> call() throws Exception {
                    return api.getPostsForTopic(topic.id);
                }
            };
            
            task.setOnSucceeded(e -> {
                List<Post> flatPosts = task.getValue();
                // ✅ BUILD TREE – Converts the flat list of posts into a hierarchical nested structure 
                // for proper UI rendering of threaded replies.
                List<Post> nestedPosts = buildReplyTree(flatPosts);
                displayPosts(nestedPosts, topic);
            });
            
            task.setOnFailed(e -> {
                Throwable ex = task.getException();
                if (ex != null) {
                    ex.printStackTrace();
                    System.err.println("Load posts error: " + ex.getMessage());
                }
                // Fallback to local storage on network failure
                loadLocalPosts(topic);
            });
            
            new Thread(task).start();
        } else {
            // Offline path: load directly from local SQLite/database cache
            loadLocalPosts(topic);
        }
    }

    /**
     * Retrieves posts for a topic from the local database cache.
     * 
     * @param topic The {@link Topic} for which to load local posts.
     */
    private void loadLocalPosts(Topic topic) {
        List<Post> localPosts = DatabaseHandler.getLocalPostsForTopic(topic.id);
        System.out.println("📂 Local posts loaded: " + localPosts.size());
        for (Post p : localPosts) {
            System.out.println("  Local post ID: " + p.id + ", parentId: " + p.parentId);
        }
        
        // Even local posts must be structured as a tree for the UI renderer
        List<Post> nestedPosts = buildReplyTree(localPosts);
        displayPosts(nestedPosts, topic);
    }

    /**
     * Prepares the UI for displaying posts.
     * 
     * @param posts The hierarchical list of posts to display.
     * @param topic The topic context.
     */
    private void displayPosts(List<Post> posts, Topic topic) {
        System.out.println("📊 displayPosts: " + posts.size() + " top-level posts");
        Platform.runLater(() -> {
            currentPosts = posts;
            renderThread(topic, posts);
            
            // Ensure the main reply form is visible and takes up layout space
            replyForm.setVisible(true);
            replyForm.setManaged(true);
            
            // Reset inline reply state
            currentReplyTarget = null;
            if (replyToLabel != null) {
                replyToLabel.setText("Replying to: Thread");
            }
        });
    }

    // =========================================================================
    // ─── BUILD REPLY TREE (NESTED REPLIES) ───────────────────────────────────
    // =========================================================================

    /**
     * Converts a flat list of {@link Post} objects into a hierarchical tree structure.
     * 
     * <p><b>Algorithm:</b>
     * <ol>
     *   <li>First pass: Populate a HashMap mapping {@code postId} to {@code Post} object for O(1) lookups. Initialize empty reply lists.</li>
     *   <li>Second pass: Iterate through all posts. If a post has a valid {@code parentId} (> 0), find the parent in the map and add the current post to the parent's {@code replies} list.</li>
     *   <li>Orphan Handling: If a parent is not found (e.g., due to pagination limits or deleted parents), the post is gracefully promoted to a top-level post to prevent data loss in the UI.</li>
     *   <li>Sorting: Sort both the top-level posts and all nested reply lists chronologically by {@code created_at}.</li>
     * </ol>
     * 
     * @param flatPosts The unstructured list of posts fetched from the API or database.
     * @return A list of top-level posts, with child posts nested inside their respective {@code replies} collections.
     */
    private List<Post> buildReplyTree(List<Post> flatPosts) {
        System.out.println("🌳 buildReplyTree: flatPosts size = " + flatPosts.size());
        Map<Integer, Post> postMap = new HashMap<>();
        
        // Pass 1: Index posts and initialize reply containers
        for (Post p : flatPosts) {
            postMap.put(p.id, p);
            p.replies = new ArrayList<>();
            System.out.println("  Post ID: " + p.id + ", parentId: " + p.parentId);
        }

        List<Post> topLevelPosts = new ArrayList<>();
        
        // Pass 2: Build the hierarchy
        for (Post p : flatPosts) {
            // Treat parentId as valid if > 0 (handles both local_id and server_id)
            if (p.parentId != null && p.parentId > 0) {
                Post parent = postMap.get(p.parentId);
                if (parent != null) {
                    parent.replies.add(p);
                    System.out.println("  ➜ Added reply " + p.id + " to parent " + p.parentId);
                } else {
                    // Orphan handling: Promote to top-level if parent is missing
                    topLevelPosts.add(p);
                    System.out.println("  ⚠️ Orphan post " + p.id + " with parent " + p.parentId + " (parent not found)");
                }
            } else {
                // Naturally a top-level post
                topLevelPosts.add(p);
                System.out.println("  ✓ Top-level post " + p.id);
            }
        }

        // Pass 3: Chronological sorting
        for (Post p : postMap.values()) {
            if (p.replies != null) {
                p.replies.sort(Comparator.comparing(p2 -> p2.created_at));
            }
        }
        topLevelPosts.sort(Comparator.comparing(p -> p.created_at));

        System.out.println("✅ buildReplyTree: " + topLevelPosts.size() + " top-level posts");
        return topLevelPosts;
    }

    // =========================================================================
    // ─── UI HELPERS ──────────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Clears the left sidebar ({@link #contextList}) and displays a centered, muted message.
     * Used for empty states (e.g., no groups, no search results).
     * 
     * @param message The user-friendly message to display.
     */
    private void showEmptyState(String message) {
        contextList.getChildren().clear();
        Label label = new Label(message);
        label.setStyle("-fx-padding: 40px; -fx-text-fill: #999; -fx-alignment: center;");
        contextList.getChildren().add(label);
    }

    /**
     * Clears the main content area ({@link #threadArea}) and displays a centered, red error message.
     * Used for critical failures in loading thread data.
     * 
     * @param message The error message to display.
     */
    private void showErrorInThread(String message) {
        threadArea.getChildren().clear();
        Label label = new Label("❌ " + message);
        label.setStyle("-fx-padding: 40px; -fx-text-fill: #dc2626; -fx-alignment: center;");
        threadArea.getChildren().add(label);
    }

    /**
     * Displays a standard JavaFX Information Alert to the user.
     * 
     * @param message The content text of the toast/notification.
     */
    private void showToast(String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Notification");
        alert.setHeaderText(null); // Remove default bold header for a cleaner "toast" look
        alert.setContentText(message);
        alert.showAndWait();
    }

    /**
     * Manages the visual "active" state of the left navigation buttons.
     * Removes the "active" CSS class from all nav buttons and applies it only to the specified button.
     * 
     * @param active The {@link Button} that should appear highlighted/active.
     */
    private void setActiveNav(Button active) {
        navGroups.getStyleClass().remove("active");
        navProfile.getStyleClass().remove("active");
        navQuizzes.getStyleClass().remove("active");
        navResults.getStyleClass().remove("active");
        active.getStyleClass().add("active");
    }

    // =========================================================================
    // ─── NAVIGATION ──────────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Action handler for the "Groups" navigation button.
     * Resets the UI to the default Groups view, clears thread data, and re-renders the group lists.
     * 
     * <p><b>Security Check:</b> Prevents navigation if a quiz is currently active ({@link #isQuizActive}).
     */
    @FXML
    public void showGroups() {
        if (isQuizActive) {
            showToast("🔒 Cannot navigate while quiz is in progress.");
            return;
        }
        
        currentView = "groups";
        setActiveNav(navGroups);
        contextTitle.setText("Groups");
        
        // Hide the context action button (e.g., the "+" button) as it's not applicable at the root groups level
        contextActionBtn.setVisible(false);
        contextActionBtn.setManaged(false);
        
        replyForm.setVisible(false);
        replyForm.setManaged(false);
        
        renderGroups();
        
        // Clear the main thread area and show a placeholder prompt
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

    // =========================================================================
    // ─── RENDER GROUPS ───────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Dynamically generates the UI nodes for the groups sidebar.
     * It renders two distinct sections: "My Groups" (joined) and "Discover Groups" (available),
     * respecting the current search filter predicates.
     */
    private void renderGroups() {
        contextList.getChildren().clear();

        // Render Joined Groups Section
        if (!filteredJoined.isEmpty()) {
            Label header = new Label("📚 My Groups");
            header.setStyle("-fx-font-size: 13px; -fx-font-weight: 700; -fx-text-fill: #1A7A64; -fx-padding: 8px 16px 4px 16px; -fx-background-color: #f5f5f5;");
            contextList.getChildren().add(header);
            for (Group group : filteredJoined) {
                contextList.getChildren().add(createGroupItem(group, true));
            }
        }

        // Render Available Groups Section
        if (!filteredAvailable.isEmpty()) {
            Label header = new Label("🔍 Discover Groups");
            header.setStyle("-fx-font-size: 13px; -fx-font-weight: 700; -fx-text-fill: #1A7A64; -fx-padding: 12px 16px 4px 16px; -fx-background-color: #f5f5f5; -fx-border-color: #e5e7eb; -fx-border-width: 1px 0 0 0;");
            contextList.getChildren().add(header);
            for (Group group : filteredAvailable) {
                contextList.getChildren().add(createGroupItem(group, false));
            }
        }

        // Handle the case where the search filter yields no results
        if (filteredJoined.isEmpty() && filteredAvailable.isEmpty()) {
            Label empty = new Label("No groups match your search.");
            empty.setStyle("-fx-padding: 40px; -fx-text-fill: #999; -fx-alignment: center;");
            contextList.getChildren().add(empty);
        }
    }

    /**
     * Factory method to create a single, styled UI card for a {@link Group}.
     * 
     * @param group The data model for the group.
     * @param isJoined {@code true} if the user is already a member (changes button text/color and click behavior).
     * @return A {@link VBox} representing the group card.
     */
    private VBox createGroupItem(Group group, boolean isJoined) {
        VBox item = new VBox(4);
        item.setStyle("-fx-background-color: #ffffff; -fx-border-color: #e5e5e5; -fx-border-width: 0 0 1px 0; " +
                "-fx-padding: 12px 16px; -fx-cursor: hand;");
        // Attach click handler for the entire card
        item.setOnMouseClicked(new GroupClickHandler(group));

        Label title = new Label(group.name);
        title.setStyle("-fx-font-size: 14px; -fx-font-weight: 600; -fx-text-fill: #000000;");

        Label desc = new Label(group.description != null ? group.description : "");
        desc.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");

        HBox metaRow = new HBox(12);
        metaRow.setAlignment(Pos.CENTER_RIGHT);

        Label topicsLabel = new Label("📄 " + group.topicsCount + " topics");
        topicsLabel.setStyle("-fx-font-size: 11px; -fx-text-fill: #999999;");

        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        // Join/Leave button with dynamic styling based on membership status
        Button joinBtn = new Button(isJoined ? "Leave" : "Join");
        joinBtn.setStyle("-fx-background-color: " + (isJoined ? "#dc3545" : "#1A7A64") + "; " +
                "-fx-text-fill: #ffffff; -fx-font-size: 11px; -fx-font-weight: 600; -fx-padding: 2px 14px; " +
                "-fx-border-radius: 12px; -fx-background-radius: 12px;");
        joinBtn.setOnAction(new JoinButtonHandler(group, isJoined));

        metaRow.getChildren().addAll(topicsLabel, spacer, joinBtn);
        item.getChildren().addAll(title, desc, metaRow);
        return item;
    }

    // =========================================================================
    // ─── HANDLER CLASSES FOR GROUP CLICKS AND JOIN/LEAVE ─────────────────────
    // =========================================================================

    /**
     * Inner class handler for group card click events.
     * Encapsulates the {@link Group} reference to avoid lambda capture issues and provides a clean separation of concerns.
     */
    private class GroupClickHandler implements EventHandler<MouseEvent> {
        private final Group group;
        
        GroupClickHandler(Group group) { 
            this.group = group; 
        }
        
        @Override
        public void handle(MouseEvent event) {
            // Respect quiz lockdown state
            if (!isQuizActive) {
                handleGroupClick(group);
            }
        }
    }

    /**
     * Inner class handler for the Join/Leave button within a group card.
     * Prevents event bubbling conflicts with the main card click handler.
     */
    private class JoinButtonHandler implements EventHandler<ActionEvent> {
        private final Group group;
        private final boolean isJoined;
        
        JoinButtonHandler(Group group, boolean isJoined) {
            this.group = group;
            this.isJoined = isJoined;
        }
        
        @Override
        public void handle(ActionEvent event) {
            // Consume event to prevent it from triggering the parent VBox's click handler
            event.consume();
            if (isJoined) {
                handleLeaveGroup(group);
            } else {
                showCommunityRules(group);
            }
        }
    }

    // =========================================================================
    // ─── JOIN / LEAVE ────────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Asynchronously processes a request to join a group and automatically accept its community rules.
     * 
     * @param group The {@link Group} to join.
     */
    private void handleJoinGroup(Group group) {
        Task<Void> task = new Task<>() {
            @Override
            protected Void call() throws Exception {
                api.joinGroup(group.id);
                api.acceptRules(group.id); // Auto-accept rules upon joining
                return null;
            }
        };
        
        task.setOnSucceeded(e -> {
            // Update local state immediately for a responsive UI (Optimistic UI update)
            group.isMember = true;
            availableGroups.remove(group);
            joinedGroups.add(group);
            
            // Re-sort alphabetically to maintain a clean UI
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

    /**
     * Asynchronously processes a request to leave a group.
     * 
     * @param group The {@link Group} to leave.
     */
    private void handleLeaveGroup(Group group) {
        Task<Void> task = new Task<>() {
            @Override
            protected Void call() throws Exception {
                api.leaveGroup(group.id);
                return null;
            }
        };
        
        task.setOnSucceeded(e -> {
            // Update local state immediately
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

    /**
     * Core logic for handling a user clicking on a group card.
     * If the user is not a member, it intercepts the click to show the Community Rules modal.
     * If they are a member, it proceeds to open the group's topics.
     * 
     * @param group The clicked {@link Group}.
     */
    private void handleGroupClick(Group group) {
        if (!group.isMember) {
            showCommunityRules(group);
            return;
        }
        openGroupTopics(group);
    }

    // =========================================================================
    // ─── COMMUNITY RULES ─────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Displays a modal dialog outlining the community rules for a specific group.
     * The user must explicitly click "Accept to Continue" to join the group, ensuring 
     * informed consent and adherence to platform guidelines.
     * 
     * @param group The {@link Group} whose rules are being displayed.
     */
    private void showCommunityRules(Group group) {
        try {
            Stage rulesStage = new Stage();
            rulesStage.initModality(Modality.APPLICATION_MODAL); // Block interaction with parent windows
            rulesStage.initStyle(StageStyle.UNDECORATED); // Custom chrome-less window for modern UI
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

            // Structured rule definitions for easy maintenance and consistent UI
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
                text.setMaxWidth(420); // Enforce text wrapping for readability
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
            // Inject global stylesheet for consistent typography and component styling
            scene.getStylesheets().add(getClass().getResource("/com/forum/css/style.css").toExternalForm());
            rulesStage.setScene(scene);
            rulesStage.showAndWait(); // Block execution until the dialog is closed

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    // =========================================================================
    // ─── OPEN GROUP & TOPICS ─────────────────────────────────────────────────
    // =========================================================================

    /**
     * Transitions the UI to display the topics within a specific group.
     * Updates the context title, reveals the "Create Topic" action button, and triggers the async load of topics.
     * 
     * @param group The {@link Group} to open.
     */
    private void openGroupTopics(Group group) {
        if (isQuizActive) {
            showToast("🔒 Cannot navigate while quiz is in progress.");
            return;
        }
        
        currentGroup = group;
        contextTitle.setText(group.name);
        
        // Reveal and configure the context action button for creating a new topic
        contextActionBtn.setVisible(true);
        contextActionBtn.setManaged(true);
        contextActionBtn.setText("+");
        contextActionBtn.setOnAction(new CreateTopicHandler(group));

        replyForm.setVisible(false);
        replyForm.setManaged(false);

        loadTopicsForGroup(group);

        // Clear the main thread area and show a placeholder prompt
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

    /**
     * Inner class handler for the "Create Topic" button.
     * Captures the current {@link Group} context to pass to the dialog.
     */
    private class CreateTopicHandler implements EventHandler<ActionEvent> {
        private final Group group;
        
        CreateTopicHandler(Group group) { 
            this.group = group; 
        }
        
        @Override
        public void handle(ActionEvent event) {
            showCreateTopicDialog(group);
        }
    }

    /**
     * Dynamically renders the list of topics for the currently selected group in the left sidebar.
     * Includes a "Back to Groups" navigation button at the top.
     * 
     * @param topicList The list of {@link Topic} objects to render.
     */
    private void renderTopics(List<Topic> topicList) {
        contextList.getChildren().clear();

        // Navigation breadcrumb
        HBox backRow = new HBox(8);
        backRow.setAlignment(Pos.CENTER_LEFT);
        backRow.setStyle("-fx-padding: 8px 16px; -fx-background-color: #f5f5f5; -fx-border-color: #e5e7eb; -fx-border-width: 0 0 1px 0;");
        Button backBtn = new Button("← Back to Groups");
        backBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #1A7A64; -fx-font-size: 13px; -fx-font-weight: 600; -fx-cursor: hand;");
        backBtn.setOnAction(new BackToGroupsHandler());
        backRow.getChildren().add(backBtn);
        contextList.getChildren().add(backRow);

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
            item.setOnMouseClicked(new TopicClickHandler(topic));

            Label title = new Label(topic.title);
            title.setStyle("-fx-font-size: 14px; -fx-font-weight: 600; -fx-text-fill: #000000;");

            // Safely extract creator name from JSON node, with fallback
            String creatorName = "Unknown";
            if (topic.creator != null && topic.creator.has("name")) {
                creatorName = topic.creator.path("name").asText("Unknown");
            }
            Label sub = new Label("by " + creatorName + " • " + (topic.created_at != null ? topic.created_at : ""));
            sub.setStyle("-fx-font-size: 12px; -fx-text-fill: #666666;");

            HBox metaRow = new HBox(12);
            metaRow.setAlignment(Pos.CENTER_LEFT);
            Label repliesLabel = new Label("💬 " + topic.postsCount + " replies");
            repliesLabel.setStyle("-fx-font-size: 11px; -fx-text-fill: #999999;");

            // Fallback for ML category if the backend hasn't classified it yet
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

    /**
     * Handler for the "Back to Groups" button in the topics view.
     */
    private class BackToGroupsHandler implements EventHandler<ActionEvent> {
        @Override
        public void handle(ActionEvent event) {
            showGroups();
        }
    }

    // =========================================================================
    // ─── TOPIC CLICK HANDLER ─────────────────────────────────────────────────
    // =========================================================================

    /**
     * Inner class handler for topic card click events.
     */
    private class TopicClickHandler implements EventHandler<MouseEvent> {
        private final Topic topic;
        
        TopicClickHandler(Topic topic) { 
            this.topic = topic; 
        }
        
        @Override
        public void handle(MouseEvent event) {
            if (!isQuizActive) {
                openTopic(topic);
            }
        }
    }

    /**
     * Transitions the UI to display the posts/replies for a specific topic.
     * 
     * @param topic The {@link Topic} to open.
     */
    private void openTopic(Topic topic) {
        if (isQuizActive) {
            showToast("🔒 Cannot navigate while quiz is in progress.");
            return;
        }
        
        currentTopic = topic;
        contextTitle.setText(currentGroup != null ? currentGroup.name : "Topic");
        
        // Keep the "+" button visible to allow creating new topics even while viewing one
        contextActionBtn.setVisible(true);
        contextActionBtn.setManaged(true);
        contextActionBtn.setText("+");
        contextActionBtn.setOnAction(new CreateTopicHandler(currentGroup));
        
        // Trigger the async load of posts for this topic
        loadPostsForTopic(topic);
    }

    // =========================================================================
    // ─── RENDER THREAD ───────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Renders the complete discussion thread UI for a given topic.
     * This includes the top navigation bar (Back, Share, Export), the topic title, 
     * and a scrollable container of nested post views.
     * 
     * @param topic The {@link Topic} being rendered.
     * @param posts The hierarchical list of {@link Post} objects to render.
     */
    private void renderThread(Topic topic, List<Post> posts) {
        threadArea.getChildren().clear();

        // Top action bar
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

        // Topic Title
        Label title = new Label(topic.title);
        title.setStyle("-fx-font-size: 20px; -fx-font-weight: 700; -fx-text-fill: #000000;");

        // Scrollable posts container
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
            // Recursively build the UI tree for each top-level post
            for (Post post : posts) {
                VBox postView = createPostView(post, 0);
                postsContainer.getChildren().add(postView);
            }
        }

        // Wrap in ScrollPane for long threads
        ScrollPane scrollPane = new ScrollPane(postsContainer);
        scrollPane.setFitToWidth(true);
        scrollPane.setStyle("-fx-background-color: transparent; -fx-background: transparent;");
        scrollPane.getStyleClass().add("thread-scroll");

        threadArea.getChildren().addAll(topBar, title, scrollPane);
        VBox.setVgrow(scrollPane, Priority.ALWAYS); // Allow scroll pane to consume all available vertical space
    }

    /**
     * Handler for the "Back" button in the thread view.
     * Returns the user to the topics list of the current group.
     */
    private class BackToTopicsHandler implements EventHandler<ActionEvent> {
        @Override
        public void handle(ActionEvent event) {
            if (currentGroup != null && !isQuizActive) {
                openGroupTopics(currentGroup);
            }
        }
    }

    /**
     * Handler for the "Share" button in the thread view.
     */
    private class ShareTopicHandler implements EventHandler<ActionEvent> {
        private final Topic topic;
        
        ShareTopicHandler(Topic topic) { 
            this.topic = topic; 
        }
        
        @Override
        public void handle(ActionEvent event) {
            shareTopic(topic);
        }
    }

    /**
     * Handler for the "Export PDF" button in the thread view.
     */
    private class ExportPdfHandler implements EventHandler<ActionEvent> {
        private final Topic topic;
        
        ExportPdfHandler(Topic topic) { 
            this.topic = topic; 
        }
        
        @Override
        public void handle(ActionEvent event) {
            exportToPDF(topic);
        }
    }

    // =========================================================================
    // ─── CREATE POST VIEW ────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Factory method to recursively generate the UI node for a single {@link Post} and its nested replies.
     * 
     * @param post The {@link Post} data model to render.
     * @param depth The current nesting level (0 for top-level, >0 for replies). Used to apply visual indentation.
     * @return A {@link VBox} containing the fully rendered post and its recursive children.
     */
    private VBox createPostView(Post post, int depth) {
        VBox postBox = new VBox(6);
        String style = "-fx-background-color: #ffffff; -fx-border-color: #1A7A64; -fx-border-radius: 8px; " +
                "-fx-background-radius: 8px; -fx-padding: 14px 18px;";
        
        // Apply distinct styling for nested replies (left border only, rounded right corners)
        if (depth > 0) {
            style += " -fx-border-width: 0 0 0 2px; -fx-border-radius: 0 8px 8px 0; -fx-background-radius: 0 8px 8px 0;";
        }
        postBox.setStyle(style);
        
        // Assign a unique ID to allow for programmatic lookup (e.g., scrolling to a specific post)
        postBox.setId("post-" + post.id);

        // Post Header (Avatar, Name, Time, Actions)
        HBox header = new HBox(10);
        header.setAlignment(Pos.CENTER_LEFT);

        String authorName = "Unknown";
        if (post.author != null && post.author.has("name")) {
            authorName = post.author.path("name").asText("Unknown");
        }
        
        // Generate initials for the avatar fallback
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

        Button replyBtn = new Button("Reply");
        replyBtn.setStyle("-fx-background-color: transparent; -fx-border-color: #e5e5e5; -fx-border-radius: 12px; " +
                "-fx-padding: 2px 10px; -fx-font-size: 11px; -fx-cursor: hand; -fx-text-fill: #333333;");

        Button sharePostBtn = new Button("📤 Share");
        sharePostBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #666666; -fx-font-size: 12px; " +
                "-fx-padding: 2px 10px; -fx-border-radius: 12px; -fx-cursor: hand;");
        sharePostBtn.setOnAction(e -> sharePost(post));

        header.getChildren().addAll(avatar, name, time, spacer, likeBtn, replyBtn, sharePostBtn);

        // Append "Private" badge if applicable
        if (post.is_private) {
            Label privateTag = new Label("🔒 Private");
            privateTag.setStyle("-fx-font-size: 9px; -fx-font-weight: 700; -fx-padding: 2px 10px; " +
                    "-fx-background-radius: 12px; -fx-background-color: #fef3c7; -fx-text-fill: #b45309;");
            header.getChildren().add(privateTag);
        }

        // Post Body Content
        Label body = new Label(post.content != null ? post.content : "");
        body.setStyle("-fx-font-size: 14px; -fx-text-fill: #1e293b; -fx-wrap-text: true;");
        body.setMaxWidth(Double.MAX_VALUE);

        postBox.getChildren().add(header);
        postBox.getChildren().add(body);

        // Inline Reply Form (Hidden by default)
        VBox inlineForm = createInlineReplyForm(post);
        inlineForm.setVisible(false);
        inlineForm.setManaged(false);
        
        // Attach the form to the button via UserData for easy retrieval during the click event
        replyBtn.setUserData(inlineForm);
        final Post replyPost = post;
        final String replyAuthor = authorName;
        replyBtn.setOnAction(e -> {
            Button source = (Button) e.getSource();
            VBox form = (VBox) source.getUserData();
            toggleInlineReply(replyPost, replyAuthor, form);
        });
        postBox.getChildren().add(inlineForm);

        // Recursive rendering of child replies
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

    // =========================================================================
    // ─── HANDLER CLASSES FOR POST ACTIONS ────────────────────────────────────
    // =========================================================================

    /**
     * Inner class handler for the "Like" button on a post.
     */
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

    /**
     * Inner class handler for the "Share" button on an individual post.
     */
    private class SharePostHandler implements EventHandler<ActionEvent> {
        private final Post post;
        
        SharePostHandler(Post post) { 
            this.post = post; 
        }
        
        @Override
        public void handle(ActionEvent event) {
            sharePost(post);
        }
    }

    // =========================================================================
    // ─── INLINE REPLY ────────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Factory method to generate the UI components for an inline reply form.
     * 
     * @param parentPost The {@link Post} being replied to.
     * @return A {@link VBox} containing the text area, private checkbox, and action buttons.
     */
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

    /**
     * Inner class handler for the "Post Reply" button within an inline form.
     */
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

    /**
     * Toggles the visibility of an inline reply form.
     * Ensures that only one inline form is open at a time by closing any previously opened form.
     * 
     * @param post The post being replied to.
     * @param author The author's name (for the "Replying to:" label).
     * @param form The {@link VBox} form to toggle.
     */
    private void toggleInlineReply(Post post, String author, VBox form) {
        // Close any currently open inline form
        if (currentInlineForm != null) {
            currentInlineForm.setVisible(false);
            currentInlineForm.setManaged(false);
            currentInlineForm = null;
        }

        // Open the new form
        if (form != null) {
            form.setVisible(true);
            form.setManaged(true);
            currentInlineForm = form;
            
            // Auto-focus the text area for immediate typing
            for (var node : form.getChildren()) {
                if (node instanceof TextArea) {
                    ((TextArea) node).requestFocus();
                    break;
                }
            }
        }

        // Update the main reply label to reflect the inline context
        if (replyToLabel != null) {
            replyToLabel.setText("Replying to: " + author);
        }
    }

    /**
     * Recursively searches the scene graph for a specific post's UI container by its ID.
     * 
     * @param postId The ID of the post to find.
     * @return The {@link VBox} representing the post, or {@code null} if not found.
     */
    private VBox findPostBox(int postId) {
        return findPostBoxRecursive(threadArea, postId);
    }

    /**
     * Helper for {@link #findPostBox(int)} that performs a depth-first search of the node tree.
     * 
     * @param parent The current {@link Parent} node being inspected.
     * @param postId The target post ID.
     * @return The matching {@link VBox}, or {@code null}.
     */
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

    // =========================================================================
    // ─── HANDLE INLINE REPLY ─────────────────────────────────────────────────
    // =========================================================================

    /**
     * Processes the submission of an inline reply.
     * 
     * <p><b>Offline-First Architecture:</b>
     * If online, it sends the data to the API. If offline, it generates a temporary local ID, 
     * saves the draft to the local database, and immediately updates the UI to reflect the new post, 
     * marking it for future synchronization.
     * 
     * @param parentPost The post being replied to.
     * @param ta The {@link TextArea} containing the reply content.
     * @param privateCb The {@link CheckBox} indicating if the reply is private.
     * @param form The {@link VBox} form to hide upon successful submission.
     */
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
                return; // User canceled the selection dialog
            }
            excludedUserIds = excludedIds;
        } else {
            excludedUserIds.clear();
        }

        // Capture parent ID. May be a temporary negative local ID if the parent post is an unsaved offline draft.
        final Integer parentId = parentPost.id; 
        final String timestamp = LocalDateTime.now().format(DateTimeFormatter.ISO_LOCAL_DATE_TIME);

        if (state.isOnline()) {
            final List<Integer> finalExcludedIds = excludedIds;
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
                // Reload the entire topic to ensure UI is perfectly synced with server state
                loadPostsForTopic(currentTopic);
            });
            
            task.setOnFailed(e -> {
                showToast("Failed to post reply: " + task.getException().getMessage());
                task.getException().printStackTrace();
            });
            
            new Thread(task).start();
        } else {
            // Offline path: save with local_id if parentId > 0, else null
            Integer finalParentId = (parentId != null && parentId > 0) ? parentId : null;
            int newLocalId = DatabaseHandler.saveOfflinePostDraftAndGetId(
                    currentTopic.id, userId, content, isPrivate, timestamp, finalParentId
            );

            if (newLocalId != -1) {
                ta.clear();
                privateCb.setSelected(false);
                excludedUserIds.clear();
                form.setVisible(false);
                form.setManaged(false);
                currentInlineForm = null;
                showToast("📶 Saved offline – will sync when online.");

                // Construct a mock Post object to immediately inject into the UI tree
                Post newPost = new Post();
                newPost.id = newLocalId;
                newPost.content = content;
                newPost.is_private = isPrivate;
                newPost.created_at = timestamp;
                newPost.author = null; // Will be resolved upon sync
                newPost.likes_count = 0;
                newPost.is_liked = false;
                newPost.parentId = finalParentId;

                if (parentPost.replies == null) parentPost.replies = new ArrayList<>();
                parentPost.replies.add(newPost);
                
                // Refresh the view to render the newly added offline post
                loadPostsForTopic(currentTopic);
            } else {
                showToast("Failed to save offline reply.");
            }
        }
    }

    // =========================================================================
    // ─── MAIN REPLY FORM ─────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Action handler for the main reply form at the bottom of the thread view.
     * Functions identically to {@link #handleInlineReply} but targets the root of the thread 
     * (or a specifically selected {@link #currentReplyTarget}) rather than an inline nested form.
     */
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
            final List<Integer> finalExcludedIds = excludedIds;
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
            // Offline path
            Integer finalParentId = (parentId != null && parentId > 0) ? parentId : null;
            int newLocalId = DatabaseHandler.saveOfflinePostDraftAndGetId(
                    currentTopic.id, userId, content, isPrivate, timestamp, finalParentId
            );

            if (newLocalId != -1) {
                replyText.clear();
                privateCheck.setSelected(false);
                excludedUserIds.clear();
                currentReplyTarget = null;
                if (replyToLabel != null) replyToLabel.setText("Replying to: Thread");
                updateSelectedUsersLabel();
                showToast("📶 Saved offline – will sync when online.");

                Post newPost = new Post();
                newPost.id = newLocalId;
                newPost.content = content;
                newPost.is_private = isPrivate;
                newPost.created_at = timestamp;
                newPost.author = null;
                newPost.likes_count = 0;
                newPost.is_liked = false;
                newPost.parentId = finalParentId;

                currentPosts.add(newPost);
                loadPostsForTopic(currentTopic);
            } else {
                showToast("Failed to save offline reply.");
            }
        }
    }

    // =========================================================================
    // ─── LIKE ────────────────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Handles the toggling of a "Like" on a post.
     * 
     * <p><b>Optimistic UI Update:</b>
     * If offline, the UI is updated immediately, and the action is queued in the local database 
     * via {@link DatabaseHandler#saveOfflineLike} to be synced when connectivity is restored.
     * 
     * @param post The {@link Post} to like/unlike.
     * @param likeBtn The UI button to update visually.
     */
    private void handleLike(Post post, Button likeBtn) {
        System.out.println("❤️ handleLike called for post " + post.id + ", current is_liked: " + post.is_liked);
        
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
                System.out.println("  ✅ API success: is_liked = " + post.is_liked + ", likes = " + post.likes_count);
                Platform.runLater(() -> updateLikeUI(post, likeBtn));
            });
            
            task.setOnFailed(e -> {
                System.out.println("  ⚠️ API failed, storing offline");
                storeLikeOffline(post, likeBtn);
            });
            
            new Thread(task).start();
        } else {
            storeLikeOffline(post, likeBtn);
        }
    }

    /**
     * Handles the local storage and immediate UI update of a like action when the application is offline.
     * 
     * @param post The {@link Post} being liked.
     * @param likeBtn The UI button to update.
     */
    private void storeLikeOffline(Post post, Button likeBtn) {
        boolean newLikeState = !post.is_liked;
        post.is_liked = newLikeState;
        if (post.likes_count == null) post.likes_count = 0;
        post.likes_count += newLikeState ? 1 : -1;
        System.out.println("  💾 Offline toggle: is_liked = " + post.is_liked + ", likes = " + post.likes_count);
        
        updateLikeUI(post, likeBtn);

        int userId = state.getCurrentUserId();
        if (userId != -1) {
            boolean saved = DatabaseHandler.saveOfflineLike(post.id, userId, newLikeState);
            if (saved) {
                showToast("📶 Like saved offline – will sync when online.");
            } else {
                showToast("❌ Failed to save like offline.");
            }
        } else {
            showToast("❌ User not logged in.");
        }
    }

    /**
     * Updates the visual state (text and CSS style) of a like button based on the post's current like data.
     * 
     * @param post The {@link Post} containing the like data.
     * @param likeBtn The {@link Button} to update.
     */
    private void updateLikeUI(Post post, Button likeBtn) {
        int newCount = (post.likes_count != null) ? post.likes_count : 0;
        likeBtn.setText("❤️ " + newCount);

        // Force style update with explicit colors to ensure JavaFX CSS engine applies the change
        if (post.is_liked != null && post.is_liked) {
            likeBtn.setStyle("-fx-text-fill: #dc2626; -fx-background-color: transparent; -fx-font-size: 13px; -fx-cursor: hand;");
        } else {
            likeBtn.setStyle("-fx-text-fill: #666666; -fx-background-color: transparent; -fx-font-size: 13px; -fx-cursor: hand;");
        }

        // Force immediate CSS application to prevent visual lag
        likeBtn.applyCss();
        System.out.println("  🎨 Updated like button: text = " + likeBtn.getText() + ", style = " + likeBtn.getStyle());
    }

    // =========================================================================
    // ─── USER SELECTION FOR PRIVATE POSTS ────────────────────────────────────
    // =========================================================================

    /**
     * Displays a modal dialog allowing the user to select specific users to exclude from a private post.
     * 
     * <p><b>Concurrency Note:</b>
     * Because the user list must be fetched asynchronously from the API, but the dialog must block 
     * the execution flow to return a synchronous {@code List<Integer>} result, this method utilizes 
     * a {@link CountDownLatch}. The background thread fetches the data, then uses {@link Platform#runLater} 
     * to show the UI. The main thread waits on {@code latch.await()} until the dialog is closed and 
     * {@code latch.countDown()} is called.
     * 
     * @return A list of user IDs to exclude, or an empty list if canceled.
     */
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
            
            // Filter out the current user, as they are implicitly the author and don't need to be excluded
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

                    stage.showAndWait(); // Blocks until the user closes the dialog
                    
                    selectedIds.addAll(controller.getSelectedUserIds());
                    if (!selectedIds.isEmpty()) {
                        excludedUserIds = selectedIds;
                        updateSelectedUsersLabel();
                    }
                    
                    // Signal the waiting thread that the operation is complete
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
            Throwable ex = task.getException();
            ex.printStackTrace();
            showToast("Failed to load users: " + ex.getMessage());
            latch.countDown();
        });

        new Thread(task).start();

        try {
            // Block the current thread until the background task and UI interaction are fully resolved
            latch.await();
        } catch (InterruptedException ex) {
            Thread.currentThread().interrupt();
        }
        
        return selectedIds;
    }

    /**
     * Updates the text of the {@link #selectUsersBtn} to reflect the number of currently excluded users.
     */
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

    // =========================================================================
    // ─── CREATE TOPIC DIALOG ─────────────────────────────────────────────────
    // =========================================================================

    /**
     * Displays a modal dialog for creating a new topic within a specific group.
     * 
     * @param group The {@link Group} in which the topic will be created.
     */
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
                        openGroupTopics(group); // Refresh the view to show the new topic
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

    // =========================================================================
    // ─── SHARE & EXPORT ──────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Generates a shareable text snippet containing the topic's URL and copies it to the system clipboard.
     * 
     * @param topic The {@link Topic} to share.
     */
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

    /**
     * Generates a shareable text snippet containing a specific post's content preview and URL, 
     * and copies it to the system clipboard.
     * 
     * @param post The {@link Post} to share.
     */
    private void sharePost(Post post) {
        try {
            String url = WEB_BASE_URL + "/groups/" + currentGroup.id + "/topics/" + currentTopic.id + "?post=" + post.id;
            String authorName = post.author != null && post.author.has("name") ?
                                post.author.path("name").asText("Unknown") : "Unknown";
            
            // Truncate long posts for a cleaner share preview
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

    /**
     * Utility method to write a string to the system clipboard.
     * 
     * @param text The text to copy.
     */
    private void copyToClipboard(String text) {
        Clipboard clipboard = Clipboard.getSystemClipboard();
        ClipboardContent content = new ClipboardContent();
        content.putString(text);
        clipboard.setContent(content);
    }

    /**
     * Exports the current topic and its entire nested post tree to a PDF file.
     * 
     * <p><b>Implementation Detail:</b>
     * Uses the iText library ({@link com.lowagie.text}) to construct the document. 
     * It recursively traverses the {@link #currentPosts} tree via {@link #appendPostToPdf}, 
     * applying indentation based on the nesting depth to visually represent the thread structure.
     * 
     * @param topic The {@link Topic} to export.
     */
    private void exportToPDF(Topic topic) {
        try {
            // Generate a unique, timestamped filename to prevent overwriting
            String timestamp = LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyyMMdd_HHmmss"));
            String filename = "chat_export_" + topic.title.replaceAll(" ", "_") + "_" + timestamp + ".pdf";
            File file = new File(System.getProperty("user.home") + "/Downloads/" + filename);
            file.getParentFile().mkdirs(); // Ensure the Downloads directory exists

            Document document = new Document(PageSize.A4);
            PdfWriter.getInstance(document, new FileOutputStream(file));
            document.open();

            // Define typography styles for the PDF
            Font titleFont = FontFactory.getFont(FontFactory.HELVETICA_BOLD, 18, Color.BLACK);
            Font headingFont = FontFactory.getFont(FontFactory.HELVETICA_BOLD, 14, Color.DARK_GRAY);
            Font authorFont = FontFactory.getFont(FontFactory.HELVETICA, 12, Color.GRAY);
            Font bodyFont = FontFactory.getFont(FontFactory.HELVETICA, 12, Color.BLACK);
            Font privateFont = FontFactory.getFont(FontFactory.HELVETICA, 10, Color.RED);
            Font replyFont = FontFactory.getFont(FontFactory.HELVETICA, 11, Color.DARK_GRAY);

            // Document Header
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

            // Document Body
            if (currentPosts.isEmpty()) {
                document.add(new Paragraph("No posts in this topic.", bodyFont));
            } else {
                for (Post post : currentPosts) {
                    appendPostToPdf(document, post, 0, bodyFont, authorFont, privateFont, replyFont);
                }
            }

            document.close();

            // Attempt to automatically open the generated PDF in the system's default viewer
            if (Desktop.isDesktopSupported()) {
                Desktop.getDesktop().open(file);
            }

            showToast("✅ PDF exported to: " + file.getAbsolutePath());

        } catch (Exception e) {
            e.printStackTrace();
            showToast("❌ Error exporting PDF: " + e.getMessage());
        }
    }

    /**
     * Recursive helper method for {@link #exportToPDF} that appends a post and its children to the PDF document.
     * 
     * @param document The iText {@link Document} being written to.
     * @param post The {@link Post} to append.
     * @param depth The current nesting level, used to calculate left indentation.
     * @param bodyFont Font for the post content.
     * @param authorFont Font for the author name and timestamp.
     * @param privateFont Font for the "[PRIVATE]" indicator.
     * @param replyFont Font reserved for future reply-specific styling (currently unused but available).
     * @throws DocumentException If an error occurs during PDF writing.
     */
    private void appendPostToPdf(Document document, Post post, int depth, Font bodyFont, Font authorFont, Font privateFont, Font replyFont) throws DocumentException {
        // 4 spaces per depth level for visual hierarchy
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

        // Recursively process child replies
        if (post.replies != null) {
            for (Post reply : post.replies) {
                appendPostToPdf(document, reply, depth + 1, bodyFont, authorFont, privateFont, replyFont);
            }
        }

        // Add vertical spacing between posts
        document.add(new Paragraph(" "));
    }

    // =========================================================================
    // ─── PROFILE ─────────────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Action handler for the "Profile" navigation button.
     * Fetches and displays the current user's statistics and account details.
     */
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
                new Label(role) {{ setStyle("-fx-background-color: #dbeafe; -fx-text-fill: #1d4ed8; -fx-font-size: 11px; -fx-font-weight: 600; -fx-padding: 2px 12px; -fx-background-radius: 12px"); }}
        );
        avatarRow.getChildren().addAll(avatar, infoBox);

        // Grid layout for statistics
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

        // Asynchronously fetch user statistics
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

                // Update the large grid numbers
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

    // =========================================================================
    // ─── QUIZZES ─────────────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Action handler for the "Quizzes" navigation button.
     * Fetches and displays the list of available quizzes for the user's groups.
     */
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
            System.out.println("Quizzes received: " + quizzes.size() + " quizzes");
            for (Quiz q : quizzes) {
                System.out.println("  Quiz ID: " + q.id + ", Title: '" + q.title + "'");
            }
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

    /**
     * Renders the list of fetched quizzes into the left sidebar.
     * 
     * @param quizzes The list of {@link Quiz} objects to render.
     */
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

    /**
     * Factory method to create a single, styled UI card for a {@link Quiz}.
     * 
     * @param quiz The {@link Quiz} data model.
     * @return A {@link VBox} representing the quiz card.
     */
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

        // Dynamic status badge styling
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

        // Button state logic based on quiz status and user history
        if ("started".equals(quiz.status) && !quiz.hasTaken) {
            startBtn.setDisable(false);
            startBtn.setOnAction(new QuizStartHandler(quiz));
        } else if (quiz.hasTaken) {
            startBtn.setDisable(true);
            startBtn.setText("✅ Done");
            startBtn.setStyle("-fx-background-color: #e5e5e5; -fx-text-fill: #16a34a; -fx-padding: 4px; " +
                    "-fx-border-radius: 6px; -fx-background-radius: 6px; -fx-font-size: 12px;");
        } else if ("ended".equals(quiz.status)) {
            startBtn.setDisable(true);
            startBtn.setText("🔒 Ended");
            startBtn.setStyle("-fx-background-color: #e5e5e5; -fx-text-fill: #999999; -fx-padding: 4px; " +
                    "-fx-border-radius: 6px; -fx-background-radius: 6px; -fx-font-size: 12px;");
        } else {
            // upcoming
            startBtn.setDisable(true);
            startBtn.setText("⏳ Coming Soon");
            startBtn.setStyle("-fx-background-color: #e5e5e5; -fx-text-fill: #999999; -fx-padding: 4px; " +
                    "-fx-border-radius: 6px; -fx-background-radius: 6px; -fx-font-size: 12px;");
        }

        card.getChildren().addAll(headerRow, infoLabel, startBtn);
        return card;
    }

    // =========================================================================
    // ─── QUIZ START HANDLER ──────────────────────────────────────────────────
    // =========================================================================

    /**
     * Inner class handler for the "Start Quiz" button.
     */
    private class QuizStartHandler implements EventHandler<ActionEvent> {
        private final Quiz quiz;
        
        QuizStartHandler(Quiz quiz) { 
            this.quiz = quiz; 
        }
        
        @Override
        public void handle(ActionEvent event) {
            startQuiz(quiz);
        }
    }

    /**
     * Initiates a quiz session.
     * 
     * <p><b>Lockdown Protocol:</b>
     * Upon successful retrieval of the {@link QuizAttempt}, this method sets {@link #isQuizActive} to true 
     * and calls {@link #setLockdown(boolean)} to disable all navigation and interaction outside the quiz view. 
     * This prevents cheating or accidental data loss during the timed assessment.
     * 
     * @param quiz The {@link Quiz} to start.
     */
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
                System.out.println("QuizAttempt received:");
                System.out.println("  ID: " + attempt.id);
                System.out.println("  startedAt: " + attempt.startedAt);
                System.out.println("  durationSeconds: " + attempt.durationSeconds);
                System.out.println("  quiz: " + attempt.quiz);
                if (attempt.quiz != null) {
                    System.out.println("  quiz.title: " + attempt.quiz.title);
                    System.out.println("  quiz.questions count: " + (attempt.quiz.questions != null ? attempt.quiz.questions.size() : 0));
                } else {
                    System.out.println("  attempt.quiz is NULL!");
                }
                
                Platform.runLater(() -> {
                    try {
                        FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/forum/fxmlfiles/Quiz.fxml"));
                        Parent quizView = loader.load();
                        QuizController controller = loader.getController();

                        // Engage lockdown
                        setLockdown(true);
                        isQuizActive = true;

                        threadArea.getChildren().clear();
                        threadArea.getChildren().add(quizView);
                        VBox.setVgrow(quizView, Priority.ALWAYS);

                        // Pass the attempt data and a completion callback to the QuizController
                        controller.setQuizData(attempt, () -> {
                            // Disengage lockdown upon quiz completion
                            setLockdown(false);
                            isQuizActive = false;
                            showQuizzes(); // Return to quiz list
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
                Throwable ex = task.getException();
                ex.printStackTrace();
                Platform.runLater(() -> {
                    showToast("Failed to start quiz: " + ex.getMessage());
                });
            });
            
            new Thread(task).start();

        } catch (Exception e) {
            e.printStackTrace();
            showToast("Error: " + e.getMessage());
        }
    }

    // =========================================================================
    // ─── RESULTS ─────────────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Action handler for the "Results" navigation button.
     * Fetches and displays the user's historical quiz attempts.
     */
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
                System.out.println("Attempts count: " + attempts.size());
                for (QuizAttempt a : attempts) {
                    System.out.println("  Attempt ID: " + a.id + ", quizTitle: " + a.quizTitle);
                }
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

    // =========================================================================
    // ─── LOCKDOWN ────────────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Toggles the "Quiz Lockdown" state across the entire UI.
     * 
     * <p><b>Security Rationale:</b>
     * When {@code enabled} is true, this method disables all navigation buttons, action buttons, 
     * and input fields. It also makes the main content areas {@code mouseTransparent} to prevent 
     * any click events from being registered. This ensures the user cannot navigate away from the 
     * quiz or interact with the forum while a timed assessment is active.
     * 
     * @param enabled {@code true} to engage lockdown, {@code false} to release it.
     */
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

        // Mouse transparency prevents any hidden click handlers from firing
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

    // =========================================================================
    // ─── LOGOUT ──────────────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Action handler for the Logout button.
     * 
     * <p><b>Safety Check:</b> Prevents logout if a quiz is currently active to avoid invalidating 
     * the session mid-assessment and losing the user's progress.
     */
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
            // Even if the API logout fails, clear the local session and force login for security
            state.clearSession();
            try {
                MainApp.switchToLogin();
            } catch (Exception ex) {
                ex.printStackTrace();
            }
        });
        
        new Thread(logoutTask).start();
    }

    // =========================================================================
    // ─── CREATE TOPIC (FXML action) ──────────────────────────────────────────
    // =========================================================================

    /**
     * FXML-bound action handler for creating a new topic.
     * Acts as a wrapper around {@link #showCreateTopicDialog(Group)}.
     */
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

    /**
     * FXML-bound action handler for opening the user selection dialog for private posts.
     */
    @FXML
    public void onSelectUsers() {
        if (privateCheck.isSelected()) {
            excludedUserIds = showUserSelectionDialog();
            updateSelectedUsersLabel();
        }
    }
}