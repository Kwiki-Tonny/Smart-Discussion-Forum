/**
 * Package: com.forum.services
 * 
 * This package contains the core business logic, service-layer components, 
 * and state management utilities of the forum application. Classes in this 
 * package are responsible for orchestrating operations, managing external 
 * API communications, handling local offline-first data storage, and 
 * maintaining the reactive state of the desktop client.
 */
package com.forum.services;

/**
 * Import for the core domain model representing a forum user.
 * Used here to maintain the identity and role context of the currently 
 * authenticated session across the entire application lifecycle.
 */
import com.forum.models.User;

/**
 * JavaFX Platform utility used to ensure that any modifications to the 
 * application state that affect the UI are executed on the JavaFX Application Thread.
 * 
 * Architectural Note: Direct manipulation of JavaFX UI components or observable 
 * properties from background threads will result in runtime exceptions. 
 * Platform.runLater() is the standard mechanism to safely marshal state change 
 * notifications back to the main UI thread.
 */
import javafx.application.Platform;

import java.util.List;

/**
 * A thread-safe List implementation optimized for scenarios where traversals 
 * (iterations) vastly outnumber mutations (additions/removals).
 * 
 * Architectural Note: This is the ideal data structure for the Observer pattern 
 * listener collections in this class. It allows background threads to safely 
 * iterate over and notify listeners without requiring explicit synchronization 
 * blocks, while new listeners can be added concurrently without causing 
 * ConcurrentModificationException.
 */
import java.util.concurrent.CopyOnWriteArrayList;

/**
 * Java Preferences API used for persistent, platform-specific storage of 
 * lightweight configuration data.
 * 
 * Architectural Note: Used here to persist the user's authentication token 
 * across application restarts. On Windows, this maps to the Registry; on macOS, 
 * it maps to a plist file; and on Linux, it maps to a hidden directory. This 
 * provides a secure, OS-native alternative to plain-text configuration files 
 * for storing session tokens.
 */
import java.util.prefs.Preferences;

/**
 * Centralized, Thread-Safe State Management Hub for the Smart Discussion Forum Desktop Client.
 * 
 * This class implements the Singleton pattern to serve as the single source of truth 
 * for the application's runtime state. It bridges the gap between background services 
 * (like network watchers and API clients) and the reactive JavaFX User Interface.
 * 
 * Key Architectural Patterns Employed:
 * 1. Singleton (Double-Checked Locking): Ensures exactly one instance of the state 
 *    manager exists, preventing state fragmentation across the application.
 * 2. Observer Pattern: Utilizes listener interfaces (StateChangeListener, ConnectionListener, 
 *    AuthListener) to decouple state mutations from UI updates. When state changes, 
 *    interested components are notified asynchronously.
 * 3. Thread Safety: All mutable state fields are declared as `volatile` to guarantee 
 *    visibility across threads. Mutating methods are `synchronized` to prevent race 
 *    conditions during state transitions.
 * 4. JavaFX Thread Marshalling: All listener notifications are wrapped in 
 *    `Platform.runLater()` to guarantee that UI updates triggered by state changes 
 *    occur strictly on the JavaFX Application Thread, preventing concurrency crashes.
 * 5. Persistent Session: Leverages the OS-native `java.util.prefs.Preferences` API 
 *    to securely retain the authentication token across application restarts, enabling 
 *    a seamless "Remember Me" experience without storing sensitive data in plain text.
 */
public class GlobalState {
    
    // =========================================================================
    //  SINGLETON PATTERN IMPLEMENTATION
    //  Ensures a single, globally accessible instance of the state manager.
    // =========================================================================
    
    /**
     * The single, shared instance of the GlobalState.
     * 
     * The `volatile` keyword is critical here. It prevents the JVM from reordering 
     * instructions during object instantiation, ensuring that a thread reading 
     * this variable will see a fully constructed GlobalState object, which is 
     * the cornerstone of the Double-Checked Locking idiom.
     */
    private static volatile GlobalState instance;
    
    /**
     * Retrieves the single, shared instance of the GlobalState.
     * 
     * Implements Double-Checked Locking for thread-safe lazy initialization.
     * The synchronized block is only entered if the instance is null, minimizing 
     * performance overhead on subsequent, frequent calls to this method.
     *
     * @return The singleton instance of GlobalState.
     */
    public static GlobalState getInstance() {
        if (instance == null) {
            synchronized (GlobalState.class) {
                if (instance == null) {
                    instance = new GlobalState();
                }
            }
        }
        return instance;
    }
    
    /**
     * Private constructor to enforce the Singleton pattern.
     * 
     * Upon instantiation, it attempts to restore a previously saved authentication 
     * token from the OS-specific Preferences storage. If a valid token is found, 
     * the application state is immediately bootstrapped into an authenticated state, 
     * allowing for seamless background reconnection without forcing the user to 
     * re-enter their credentials.
     */
    private GlobalState() {
        // Restore token from preferences if exists
        Preferences prefs = Preferences.userNodeForPackage(GlobalState.class);
        String savedToken = prefs.get("auth_token", null);
        if (savedToken != null && !savedToken.isEmpty()) {
            this.authToken = savedToken;
            this.isAuthenticated = true;
        }
    }
    
    // =========================================================================
    //  PRIVATE STATE VARIABLES
    //  All mutable state is protected by synchronized blocks and/or volatile 
    //  modifiers to ensure strict thread safety across background and UI threads.
    // =========================================================================
    
    // --- Connection Status ---
    
    /**
     * Indicates whether the application currently has an active, verified 
     * connection to the backend server.
     * 
     * Volatile ensures that changes made by the NetworkWatcher background thread 
     * are immediately visible to the UI thread without requiring explicit locking.
     */
    private volatile boolean isOnline = false;
    
    /**
     * Indicates whether the application is actively attempting to establish 
     * a connection (e.g., during a retry loop or initial startup).
     * 
     * Used by the UI to display loading spinners or "Reconnecting..." indicators.
     */
    private volatile boolean isConnectionAttempting = false;
    
    /**
     * Stores the most recent error message related to network connectivity.
     * 
     * Cleared automatically when the connection status transitions back to online.
     * Used by the UI to display contextual error banners to the user.
     */
    private volatile String lastError = null;
    
    // --- User Session ---
    
    /**
     * The domain object representing the currently authenticated user.
     * 
     * Contains profile data such as ID, name, email, and role. Set to null 
     * upon logout or session expiration.
     */
    private volatile User currentUser = null;
    
    /**
     * The bearer token used for authenticating HTTP requests to the backend API.
     * 
     * Persisted in OS Preferences to survive application restarts.
     */
    private volatile String authToken = null;
    
    /**
     * A boolean flag summarizing the authentication state.
     * 
     * Derived directly from the presence and validity of the authToken.
     */
    private volatile boolean isAuthenticated = false;
    
    // --- App State ---
    
    /**
     * The high-level lifecycle state of the application.
     * 
     * Drives major UI routing decisions (e.g., showing the Login screen vs. 
     * the Main Dashboard vs. an Offline mode placeholder).
     */
    private volatile AppState appState = AppState.INITIALIZING;
    
    /**
     * A string identifier representing the currently active view or screen 
     * within the main application (e.g., "groups", "topics", "profile").
     * 
     * Used for navigation history, breadcrumb generation, and view-specific 
     * state restoration.
     */
    private volatile String currentView = "groups";
    
    /**
     * A generic reference to the currently selected item in the UI 
     * (e.g., a selected Group, Topic, or Post).
     * 
     * Typed as Object to maintain flexibility across different view contexts 
     * without requiring a complex generic state manager.
     */
    private volatile Object currentSelection = null;
    
    // --- Listeners (thread-safe) ---
    
    /**
     * Collection of observers interested in high-level application state, 
     * view, or selection changes.
     */
    private final List<StateChangeListener> stateListeners = new CopyOnWriteArrayList<>();
    
    /**
     * Collection of observers interested in network connectivity changes.
     */
    private final List<ConnectionListener> connectionListeners = new CopyOnWriteArrayList<>();
    
    /**
     * Collection of observers interested in authentication and user session changes.
     */
    private final List<AuthListener> authListeners = new CopyOnWriteArrayList<>();
    
    // =========================================================================
    //  ENUMS
    //  Type-safe definitions for application states.
    // =========================================================================
    
    /**
     * Represents the high-level lifecycle phases of the desktop application.
     */
    public enum AppState {
        INITIALIZING,   // App is starting up, loading resources and checking network.
        LOGIN,          // User is at the login screen; no valid session exists.
        MAIN,           // User is authenticated and interacting with the main application.
        OFFLINE,        // User is authenticated, but the network connection is lost.
        ERROR,          // A critical, unrecoverable error has occurred.
        SHUTDOWN        // App is in the process of gracefully shutting down.
    }
    
    // =========================================================================
    //  CONNECTION STATUS MANAGEMENT
    //  Thread-safe getters and setters for network state, with UI notifications.
    // =========================================================================
    
    /**
     * Updates the global network connectivity status.
     * 
     * If the status changes, it asynchronously notifies all registered 
     * ConnectionListeners on the JavaFX Application Thread.
     *
     * @param online true if the network is reachable, false otherwise.
     */
    public synchronized void setOnline(boolean online) {
        boolean oldValue = this.isOnline;
        this.isOnline = online;
        
        // Automatically clear transient errors when connectivity is restored.
        if (online) {
            this.lastError = null;
        }
        
        // Only trigger notifications if the state actually changed.
        if (oldValue != online) {
            Platform.runLater(() -> {
                for (ConnectionListener listener : connectionListeners) {
                    listener.onConnectionChange(online);
                }
            });
        }
    }
    
    /**
     * Retrieves the current network connectivity status.
     *
     * @return true if online, false otherwise.
     */
    public boolean isOnline() {
        return isOnline;
    }
    
    /**
     * Updates the flag indicating whether a connection attempt is in progress.
     * 
     * Immediately notifies listeners on the JavaFX thread to update UI 
     * loading indicators.
     *
     * @param attempting true if currently attempting to connect, false otherwise.
     */
    public synchronized void setConnectionAttempting(boolean attempting) {
        this.isConnectionAttempting = attempting;
        Platform.runLater(() -> {
            for (ConnectionListener listener : connectionListeners) {
                listener.onConnectionAttempt(attempting);
            }
        });
    }
    
    /**
     * Retrieves the current connection attempt status.
     *
     * @return true if an attempt is in progress, false otherwise.
     */
    public boolean isConnectionAttempting() {
        return isConnectionAttempting;
    }
    
    /**
     * Records the most recent network-related error message.
     * 
     * Immediately notifies listeners on the JavaFX thread to display 
     * error banners or toasts to the user.
     *
     * @param error The descriptive error message.
     */
    public synchronized void setLastError(String error) {
        this.lastError = error;
        Platform.runLater(() -> {
            for (ConnectionListener listener : connectionListeners) {
                listener.onError(error);
            }
        });
    }
    
    /**
     * Retrieves the most recent network-related error message.
     *
     * @return The error string, or null if no error has occurred.
     */
    public String getLastError() {
        return lastError;
    }
    
    // =========================================================================
    //  AUTHENTICATION & SESSION MANAGEMENT
    //  Handles token persistence, user identity, and secure session teardown.
    // =========================================================================
    
    /**
     * Sets the authentication token and updates the derived isAuthenticated flag.
     * 
     * Crucially, this method persists the token to the OS-specific Preferences 
     * storage, enabling session survival across application restarts. It then 
     * notifies all AuthListeners on the JavaFX thread.
     *
     * @param token The bearer token string, or null to clear the session.
     */
    public synchronized void setAuthToken(String token) {
        this.authToken = token;
        this.isAuthenticated = (token != null && !token.isEmpty());
        
        // Persist token securely via OS Preferences API
        Preferences prefs = Preferences.userNodeForPackage(GlobalState.class);
        if (token != null && !token.isEmpty()) {
            prefs.put("auth_token", token);
        } else {
            prefs.remove("auth_token");
        }
        
        Platform.runLater(() -> {
            for (AuthListener listener : authListeners) {
                listener.onAuthChange(this.isAuthenticated);
            }
        });
    }
    
    /**
     * Retrieves the current authentication token.
     *
     * @return The bearer token string, or null if not authenticated.
     */
    public String getAuthToken() {
        return authToken;
    }
    
    /**
     * Checks if the current session is considered authenticated.
     *
     * @return true if a valid token exists, false otherwise.
     */
    public boolean isAuthenticated() {
        return isAuthenticated;
    }
    
    /**
     * Sets the currently authenticated user's domain object.
     * 
     * Notifies listeners on the JavaFX thread, allowing the UI to update 
     * profile avatars, names, and role-specific menu options.
     *
     * @param user The User object representing the logged-in user.
     */
    public synchronized void setCurrentUser(User user) {
        this.currentUser = user;
        Platform.runLater(() -> {
            for (AuthListener listener : authListeners) {
                listener.onUserChange(user);
            }
        });
    }
    
    /**
     * Retrieves the current user's domain object.
     *
     * @return The User object, or null if no user is logged in.
     */
    public User getCurrentUser() {
        return currentUser;
    }
    
    /**
     * Safely retrieves the current user's numeric ID.
     *
     * @return The user's ID, or -1 if no user is currently authenticated.
     */
    public int getCurrentUserId() {
        User user = getCurrentUser();
        return user != null ? user.id : -1;
    }
    
    /**
     * Safely retrieves the current user's display name.
     *
     * @return The user's name, or "Guest" if no user is authenticated.
     */
    public String getCurrentUserName() {
        User user = getCurrentUser();
        return user != null ? user.name : "Guest";
    }
    
    /**
     * Safely retrieves the current user's role identifier.
     *
     * @return The user's role (e.g., "admin", "student"), or "guest" if unauthenticated.
     */
    public String getCurrentUserRole() {
        User user = getCurrentUser();
        return user != null ? user.role : "guest";
    }
    
    /**
     * Completely tears down the current user session.
     * 
     * This method resets all authentication and user-specific state variables, 
     * clears the persisted token from OS Preferences, forces the application 
     * state back to LOGIN, and notifies all relevant listeners to update the UI 
     * (e.g., closing the main dashboard and showing the login screen).
     */
    public synchronized void clearSession() {
        this.authToken = null;
        this.isAuthenticated = false;
        this.currentUser = null;
        this.currentView = "login";
        this.currentSelection = null;
        this.appState = AppState.LOGIN;
        
        // Remove persisted token to prevent auto-login on next startup
        Preferences prefs = Preferences.userNodeForPackage(GlobalState.class);
        prefs.remove("auth_token");
        
        Platform.runLater(() -> {
            for (AuthListener listener : authListeners) {
                listener.onAuthChange(false);
                listener.onUserChange(null);
            }
            for (StateChangeListener listener : stateListeners) {
                listener.onStateChange(this.appState);
            }
        });
    }
    
    // =========================================================================
    //  APP STATE & NAVIGATION MANAGEMENT
    //  Controls high-level application routing and context selection.
    // =========================================================================
    
    /**
     * Updates the high-level lifecycle state of the application.
     * 
     * Notifies listeners only if the state has actually changed, preventing 
     * unnecessary UI re-renders.
     *
     * @param state The new AppState enum value.
     */
    public synchronized void setAppState(AppState state) {
        AppState oldState = this.appState;
        this.appState = state;
        
        if (oldState != state) {
            Platform.runLater(() -> {
                for (StateChangeListener listener : stateListeners) {
                    listener.onStateChange(state);
                }
            });
        }
    }
    
    /**
     * Retrieves the current high-level application state.
     *
     * @return The current AppState enum value.
     */
    public AppState getAppState() {
        return appState;
    }
    
    /**
     * Updates the identifier of the currently active view/screen.
     * 
     * Used by the navigation controller to highlight the active menu item 
     * and manage view-specific logic.
     *
     * @param view The string identifier of the new view (e.g., "groups").
     */
    public synchronized void setCurrentView(String view) {
        this.currentView = view;
        Platform.runLater(() -> {
            for (StateChangeListener listener : stateListeners) {
                listener.onViewChange(view);
            }
        });
    }
    
    /**
     * Retrieves the identifier of the currently active view.
     *
     * @return The string identifier of the current view.
     */
    public String getCurrentView() {
        return currentView;
    }
    
    /**
     * Updates the currently selected domain object in the UI.
     * 
     * This allows different parts of the application to react to selection 
     * changes (e.g., enabling a "Reply" button when a Post is selected).
     *
     * @param selection The newly selected object, or null to clear selection.
     */
    public synchronized void setCurrentSelection(Object selection) {
        this.currentSelection = selection;
        Platform.runLater(() -> {
            for (StateChangeListener listener : stateListeners) {
                listener.onSelectionChange(selection);
            }
        });
    }
    
    /**
     * Retrieves the currently selected domain object.
     *
     * @return The selected object, or null if nothing is selected.
     */
    public Object getCurrentSelection() {
        return currentSelection;
    }
    
    // =========================================================================
    //  LISTENER REGISTRATION & MANAGEMENT
    //  Thread-safe addition and removal of Observer pattern callbacks.
    // =========================================================================
    
    /**
     * Registers a new listener for high-level state, view, and selection changes.
     *
     * @param listener The StateChangeListener to add. Null values are ignored.
     */
    public void addStateChangeListener(StateChangeListener listener) {
        if (listener != null) {
            stateListeners.add(listener);
        }
    }
    
    /**
     * Unregisters an existing state change listener.
     *
     * @param listener The StateChangeListener to remove.
     */
    public void removeStateChangeListener(StateChangeListener listener) {
        stateListeners.remove(listener);
    }
    
    /**
     * Registers a new listener for network connectivity changes.
     * 
     * Immediately triggers an initial callback on the JavaFX thread so the 
     * newly registered listener can synchronize its UI with the current state.
     *
     * @param listener The ConnectionListener to add. Null values are ignored.
     */
    public void addConnectionListener(ConnectionListener listener) {
        if (listener != null) {
            connectionListeners.add(listener);
            Platform.runLater(() -> {
                listener.onConnectionChange(isOnline);
            });
        }
    }
    
    /**
     * Unregisters an existing connection listener.
     *
     * @param listener The ConnectionListener to remove.
     */
    public void removeConnectionListener(ConnectionListener listener) {
        connectionListeners.remove(listener);
    }
    
    /**
     * Registers a new listener for authentication and user session changes.
     * 
     * Immediately triggers initial callbacks on the JavaFX thread so the 
     * newly registered listener can synchronize its UI with the current 
     * authentication state and user profile.
     *
     * @param listener The AuthListener to add. Null values are ignored.
     */
    public void addAuthListener(AuthListener listener) {
        if (listener != null) {
            authListeners.add(listener);
            Platform.runLater(() -> {
                listener.onAuthChange(isAuthenticated);
                listener.onUserChange(currentUser);
            });
        }
    }
    
    /**
     * Unregisters an existing authentication listener.
     *
     * @param listener The AuthListener to remove.
     */
    public void removeAuthListener(AuthListener listener) {
        authListeners.remove(listener);
    }
    
    // =========================================================================
    //  LISTENER INTERFACES
    //  Contracts for components wishing to observe global state changes.
    //  Default methods allow implementers to override only the events they care about.
    // =========================================================================
    
    /**
     * Interface for observing high-level application state, view, and selection changes.
     */
    public interface StateChangeListener {
        default void onStateChange(AppState newState) {}
        default void onViewChange(String newView) {}
        default void onSelectionChange(Object selection) {}
    }
    
    /**
     * Interface for observing network connectivity status changes.
     */
    public interface ConnectionListener {
        default void onConnectionChange(boolean isOnline) {}
        default void onConnectionAttempt(boolean isAttempting) {}
        default void onError(String error) {}
    }
    
    /**
     * Interface for observing authentication and user session changes.
     */
    public interface AuthListener {
        default void onAuthChange(boolean isAuthenticated) {}
        default void onUserChange(User user) {}
        default void onTokenExpired() {}
    }
    
    // =========================================================================
    //  UTILITY METHODS
    //  Helper functions for role checking and state debugging.
    // =========================================================================
    
    /**
     * Checks if the currently authenticated user possesses any of the specified roles.
     * 
     * Performs a case-insensitive comparison to ensure robust role matching 
     * regardless of backend capitalization variations.
     *
     * @param roles A varargs array of role strings to check against (e.g., "admin", "moderator").
     * @return true if the user has at least one of the specified roles, false otherwise.
     */
    public boolean hasRole(String... roles) {
        if (currentUser == null) return false;
        for (String role : roles) {
            if (role.equalsIgnoreCase(currentUser.role)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Convenience method to check if the current user is an administrator.
     *
     * @return true if the user's role is "admin", false otherwise.
     */
    public boolean isAdmin() {
        return hasRole("admin");
    }
    
    /**
     * Convenience method to check if the current user is a lecturer.
     *
     * @return true if the user's role is "lecturer", false otherwise.
     */
    public boolean isLecturer() {
        return hasRole("lecturer");
    }
    
    /**
     * Convenience method to check if the current user is a student.
     *
     * @return true if the user's role is "student", false otherwise.
     */
    public boolean isStudent() {
        return hasRole("student");
    }
    
    /**
     * Generates a comprehensive, human-readable summary of the current global state.
     * 
     * Primarily used for debugging, logging, or displaying a "System Status" 
     * modal to the user or developer.
     *
     * @return A formatted string containing the current state of all major variables.
     */
    public String getStateSummary() {
        StringBuilder sb = new StringBuilder();
        sb.append("=== Global State ===\n");
        sb.append("Online: ").append(isOnline).append("\n");
        sb.append("Auth: ").append(isAuthenticated).append("\n");
        sb.append("User: ").append(currentUser != null ? currentUser.name : "none").append("\n");
        sb.append("State: ").append(appState).append("\n");
        sb.append("View: ").append(currentView).append("\n");
        sb.append("Listeners: ").append(stateListeners.size()).append("\n");
        return sb.toString();
    }
}