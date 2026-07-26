package com.forum.controllers;

import com.forum.services.GlobalState;
import com.forum.MainApp;
import com.forum.models.User;
import com.forum.services.ApiService;
import javafx.application.Platform;
import javafx.concurrent.Task;
import javafx.fxml.FXML;
import javafx.scene.control.*;

/**
 * Controller class responsible for handling user authentication, automated session updates,
 * network connectivity listeners, and navigation events within the JavaFX desktop application.
 */
public class LoginController {

    /* =========================================================================
     * FXML UI COMPONENTS
     * ========================================================================= */

    @FXML private TextField emailField;
    @FXML private PasswordField passwordField;
    @FXML private CheckBox rememberCheck;
    @FXML private Label errorLabel;
    @FXML private Button loginButton;
    @FXML private ProgressIndicator progressIndicator;

    /* =========================================================================
     * STATE MANAGEMENT & SERVICES
     * ========================================================================= */

    /** Shared application state instance tracking session token and network connectivity. */
    private final GlobalState state = GlobalState.getInstance();

    /** Singleton service managing REST API communications with the backend. */
    private final ApiService api = ApiService.getInstance();

    /**
     * Initializes the controller class. Triggered automatically after the FXML file is loaded.
     * Sets up connection status listeners and checks for existing authenticated sessions.
     */
    @FXML
    public void initialize() {
        // Attempt automatic login if a valid session token exists and network is available
        if (state.isAuthenticated() && state.isOnline()) {
            autoLogin();
        }

        // Register a listener to update the UI dynamically when network state changes
        state.addConnectionListener(new GlobalState.ConnectionListener() {
            @Override
            public void onConnectionChange(boolean isOnline) {
                Platform.runLater(() -> {
                    if (!isOnline) {
                        errorLabel.setText("⚠️ No internet connection. Please check your network.");
                        errorLabel.setVisible(true);
                    } else {
                        errorLabel.setVisible(false);
                    }
                });
            }
        });
    }

    /**
     * Handles manual login action triggered by the user clicking the login button.
     * Validates input fields, verifies network availability, and dispatches background
     * task to process API request asynchronously without freezing UI thread.
     */
    @FXML
    public void handleLogin() {
        String email = emailField.getText().trim();
        String password = passwordField.getText();

        // Validate presence of credentials
        if (email.isEmpty() || password.isEmpty()) {
            errorLabel.setText("Please fill in all fields.");
            errorLabel.setVisible(true);
            return;
        }

        // Verify active internet connection prior to HTTP call
        if (!state.isOnline()) {
            errorLabel.setText("⚠️ No internet connection. Please check your network.");
            errorLabel.setVisible(true);
            return;
        }

        // Lock interface controls and display loading indicator
        loginButton.setDisable(true);
        if (progressIndicator != null) progressIndicator.setVisible(true);
        errorLabel.setVisible(false);

        // Execute asynchronous authentication request on background thread
        Task<User> loginTask = new Task<>() {
            @Override
            protected User call() throws Exception {
                ApiService.LoginResponse response = api.login(email, password);
                // Persist retrieved Bearer token into central application state
                state.setAuthToken(response.token);
                return response.user;
            }
        };

        // Handle successful API response
        loginTask.setOnSucceeded(e -> {
            User user = loginTask.getValue();
            
            // Sync user data and state status before stage transition
            state.setCurrentUser(user);
            state.setAppState(GlobalState.AppState.MAIN);
            
            // Navigate user to the main application interface
            MainApp.switchToMain();
        });

        // Handle failed API call or network exceptions
        loginTask.setOnFailed(e -> {
            loginButton.setDisable(false);
            if (progressIndicator != null) progressIndicator.setVisible(false);
            
            Throwable ex = loginTask.getException();
            String msg = ex.getMessage();
            
            // Categorize error messages based on HTTP response status codes
            if (msg != null && msg.contains("401")) {
                errorLabel.setText("❌ Invalid email or password.");
            } else if (msg != null && msg.contains("403")) {
                errorLabel.setText("⛔ Your account is blacklisted. Contact support.");
            } else if (msg != null && msg.contains("timeout")) {
                errorLabel.setText("⏰ Connection timeout. Please try again.");
            } else {
                errorLabel.setText("⚠️ Error: " + (msg != null ? msg : "Unknown error"));
            }
            errorLabel.setVisible(true);
            ex.printStackTrace();
        });

        new Thread(loginTask).start();
    }

    /**
     * Executes asynchronous background task to verify cached session tokens
     * against backend API upon application startup.
     */
    private void autoLogin() {
        if (state.isAuthenticated() && state.isOnline()) {
            Task<User> verifyTask = new Task<>() {
                @Override
                protected User call() throws Exception {
                    return api.getCurrentUser();
                }
            };
            
            verifyTask.setOnSucceeded(e -> {
                User user = verifyTask.getValue();
                state.setCurrentUser(user);
                state.setAppState(GlobalState.AppState.MAIN);
                MainApp.switchToMain();
            });
            
            verifyTask.setOnFailed(e -> {
                // Clear state store if cached token is expired or revoked
                state.clearSession();
            });
            
            new Thread(verifyTask).start();
        }
    }

    /* =========================================================================
     * HELPER & EXTERNAL NAVIGATION HANDLERS
     * ========================================================================= */

    /** Prompts user with registration instructions for the web application. */
    @FXML
    public void handleRegister() {
        errorLabel.setText("Please register via the web application.");
        errorLabel.setVisible(true);
    }

    /** Displays password reset instructions to user. */
    @FXML
    public void handleForgotPassword() {
        errorLabel.setText("Password reset link will be sent to your registered email.");
        errorLabel.setVisible(true);
    }

    /** Displays administrative contact support email. */
    @FXML
    public void handleContactAdmin() {
        errorLabel.setText("Please email support@forum.com for assistance.");
        errorLabel.setVisible(true);
    }

    /** Renders application privacy policy overview. */
    @FXML
    public void handlePrivacyPolicy() {
        errorLabel.setText("Privacy Policy: Your data is protected.");
        errorLabel.setVisible(true);
    }

    /** Renders terms of service platform policy guidelines. */
    @FXML
    public void handleTermsOfService() {
        errorLabel.setText("Terms of Service: Use of this platform is subject to our terms.");
        errorLabel.setVisible(true);
    }
}