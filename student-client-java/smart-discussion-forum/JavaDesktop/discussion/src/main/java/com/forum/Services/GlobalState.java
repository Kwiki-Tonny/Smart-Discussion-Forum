package com.forum.services;

import com.forum.models.User;
import javafx.application.Platform;
import java.util.List;
import java.util.concurrent.CopyOnWriteArrayList;
import java.util.prefs.Preferences;

/**
 * GlobalState - Central state management for the Smart Discussion Forum desktop client.
 * 
 * Thread Safety:
 * - All mutable state is protected by synchronized blocks
 * - Listeners use CopyOnWriteArrayList for thread-safe iteration
 * - UI updates are wrapped in Platform.runLater()
 * 
 * Persistence:
 * - Authentication token is stored in Preferences (OS-specific secure storage)
 * - User session data is transient (cleared on logout)
 */
public class GlobalState {
    
    // ==================== SINGLETON ====================
    private static volatile GlobalState instance;
    
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
    
    private GlobalState() {
        // Restore token from preferences if exists
        Preferences prefs = Preferences.userNodeForPackage(GlobalState.class);
        String savedToken = prefs.get("auth_token", null);
        if (savedToken != null && !savedToken.isEmpty()) {
            this.authToken = savedToken;
            this.isAuthenticated = true;
        }
    }
    
    // ==================== PRIVATE STATE ====================
    
    // --- Connection Status ---
    private volatile boolean isOnline = false;
    private volatile boolean isConnectionAttempting = false;
    private volatile String lastError = null;
    
    // --- User Session ---
    private volatile User currentUser = null;
    private volatile String authToken = null;
    private volatile boolean isAuthenticated = false;
    
    // --- App State ---
    private volatile AppState appState = AppState.INITIALIZING;
    private volatile String currentView = "groups";
    private volatile Object currentSelection = null;
    
    // --- Listeners (thread-safe) ---
    private final List<StateChangeListener> stateListeners = new CopyOnWriteArrayList<>();
    private final List<ConnectionListener> connectionListeners = new CopyOnWriteArrayList<>();
    private final List<AuthListener> authListeners = new CopyOnWriteArrayList<>();
    
    // ==================== ENUMS ====================
    
    public enum AppState {
        INITIALIZING,   // App is starting up
        LOGIN,          // User at login screen
        MAIN,           // User in main application
        OFFLINE,        // Connected but offline mode
        ERROR,          // Some error occurred
        SHUTDOWN        // App is shutting down
    }
    
    // ==================== CONNECTION STATUS ====================
    
    public synchronized void setOnline(boolean online) {
        boolean oldValue = this.isOnline;
        this.isOnline = online;
        
        if (online) {
            this.lastError = null;
        }
        
        if (oldValue != online) {
            Platform.runLater(() -> {
                for (ConnectionListener listener : connectionListeners) {
                    listener.onConnectionChange(online);
                }
            });
        }
    }
    
    public boolean isOnline() {
        return isOnline;
    }
    
    public synchronized void setConnectionAttempting(boolean attempting) {
        this.isConnectionAttempting = attempting;
        Platform.runLater(() -> {
            for (ConnectionListener listener : connectionListeners) {
                listener.onConnectionAttempt(attempting);
            }
        });
    }
    
    public boolean isConnectionAttempting() {
        return isConnectionAttempting;
    }
    
    public synchronized void setLastError(String error) {
        this.lastError = error;
        Platform.runLater(() -> {
            for (ConnectionListener listener : connectionListeners) {
                listener.onError(error);
            }
        });
    }
    
    public String getLastError() {
        return lastError;
    }
    
    // ==================== AUTHENTICATION ====================
    
    public synchronized void setAuthToken(String token) {
        this.authToken = token;
        this.isAuthenticated = (token != null && !token.isEmpty());
        
        // Persist token
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
    
    public String getAuthToken() {
        return authToken;
    }
    
    public boolean isAuthenticated() {
        return isAuthenticated;
    }
    
    public synchronized void setCurrentUser(User user) {
        this.currentUser = user;
        Platform.runLater(() -> {
            for (AuthListener listener : authListeners) {
                listener.onUserChange(user);
            }
        });
    }
    
    public User getCurrentUser() {
        return currentUser;
    }
    
    public int getCurrentUserId() {
        User user = getCurrentUser();
        return user != null ? user.id : -1;
    }
    
    public String getCurrentUserName() {
        User user = getCurrentUser();
        return user != null ? user.name : "Guest";
    }
    
    public String getCurrentUserRole() {
        User user = getCurrentUser();
        return user != null ? user.role : "guest";
    }
    
    public synchronized void clearSession() {
        this.authToken = null;
        this.isAuthenticated = false;
        this.currentUser = null;
        this.currentView = "login";
        this.currentSelection = null;
        this.appState = AppState.LOGIN;
        
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
    
    // ==================== APP STATE ====================
    
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
    
    public AppState getAppState() {
        return appState;
    }
    
    public synchronized void setCurrentView(String view) {
        this.currentView = view;
        Platform.runLater(() -> {
            for (StateChangeListener listener : stateListeners) {
                listener.onViewChange(view);
            }
        });
    }
    
    public String getCurrentView() {
        return currentView;
    }
    
    public synchronized void setCurrentSelection(Object selection) {
        this.currentSelection = selection;
        Platform.runLater(() -> {
            for (StateChangeListener listener : stateListeners) {
                listener.onSelectionChange(selection);
            }
        });
    }
    
    public Object getCurrentSelection() {
        return currentSelection;
    }
    
    // ==================== LISTENERS ====================
    
    public void addStateChangeListener(StateChangeListener listener) {
        if (listener != null) {
            stateListeners.add(listener);
        }
    }
    
    public void removeStateChangeListener(StateChangeListener listener) {
        stateListeners.remove(listener);
    }
    
    public void addConnectionListener(ConnectionListener listener) {
        if (listener != null) {
            connectionListeners.add(listener);
            Platform.runLater(() -> {
                listener.onConnectionChange(isOnline);
            });
        }
    }
    
    public void removeConnectionListener(ConnectionListener listener) {
        connectionListeners.remove(listener);
    }
    
    public void addAuthListener(AuthListener listener) {
        if (listener != null) {
            authListeners.add(listener);
            Platform.runLater(() -> {
                listener.onAuthChange(isAuthenticated);
                listener.onUserChange(currentUser);
            });
        }
    }
    
    public void removeAuthListener(AuthListener listener) {
        authListeners.remove(listener);
    }
    
    // ==================== LISTENER INTERFACES ====================
    
    public interface StateChangeListener {
        default void onStateChange(AppState newState) {}
        default void onViewChange(String newView) {}
        default void onSelectionChange(Object selection) {}
    }
    
    public interface ConnectionListener {
        default void onConnectionChange(boolean isOnline) {}
        default void onConnectionAttempt(boolean isAttempting) {}
        default void onError(String error) {}
    }
    
    public interface AuthListener {
        default void onAuthChange(boolean isAuthenticated) {}
        default void onUserChange(User user) {}
        default void onTokenExpired() {}
    }
    
    // ==================== UTILITY METHODS ====================
    
    public boolean hasRole(String... roles) {
        if (currentUser == null) return false;
        for (String role : roles) {
            if (role.equalsIgnoreCase(currentUser.role)) {
                return true;
            }
        }
        return false;
    }
    
    public boolean isAdmin() {
        return hasRole("admin");
    }
    
    public boolean isLecturer() {
        return hasRole("lecturer");
    }
    
    public boolean isStudent() {
        return hasRole("student");
    }
    
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