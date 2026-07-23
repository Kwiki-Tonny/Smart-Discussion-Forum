package com.forum.controllers;

import com.forum.services.GlobalState;
import com.forum.MainApp;
import com.forum.models.User;
import com.forum.services.ApiService;
import javafx.application.Platform;
import javafx.concurrent.Task;
import javafx.fxml.FXML;
import javafx.scene.control.*;

public class LoginController {
    @FXML private TextField emailField;
    @FXML private PasswordField passwordField;
    @FXML private CheckBox rememberCheck;
    @FXML private Label errorLabel;
    @FXML private Button loginButton;
    @FXML private ProgressIndicator progressIndicator;

    private final GlobalState state = GlobalState.getInstance();
    private final ApiService api = ApiService.getInstance();

    @FXML
    public void initialize() {
        // Check if we already have a valid token
        if (state.isAuthenticated() && state.isOnline()) {
            autoLogin();
        }

        // Listen for connection changes
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

    @FXML
    public void handleLogin() {
        String email = emailField.getText().trim();
        String password = passwordField.getText();

        if (email.isEmpty() || password.isEmpty()) {
            errorLabel.setText("Please fill in all fields.");
            errorLabel.setVisible(true);
            return;
        }

        // Check online status first
        if (!state.isOnline()) {
            errorLabel.setText("⚠️ No internet connection. Please check your network.");
            errorLabel.setVisible(true);
            return;
        }

        // Disable UI
        loginButton.setDisable(true);
        if (progressIndicator != null) progressIndicator.setVisible(true);
        errorLabel.setVisible(false);

        // Perform login in background
        Task<User> loginTask = new Task<>() {
            @Override
            protected User call() throws Exception {
                ApiService.LoginResponse response = api.login(email, password);
                // Store token in global state
                state.setAuthToken(response.token);
                return response.user;
            }
        };

        loginTask.setOnSucceeded(e -> {
            User user = loginTask.getValue();
            
            // Update global state
            state.setCurrentUser(user);
            state.setAppState(GlobalState.AppState.MAIN);
            
            // Navigate to main
            MainApp.switchToMain();
        });

        loginTask.setOnFailed(e -> {
            loginButton.setDisable(false);
            if (progressIndicator != null) progressIndicator.setVisible(false);
            
            Throwable ex = loginTask.getException();
            String msg = ex.getMessage();
            
            // Handle specific error cases
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

    private void autoLogin() {
        // Attempt to auto-login with stored token
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
                // Token expired or invalid
                state.clearSession();
                // Stay on login screen
            });
            
            new Thread(verifyTask).start();
        }
    }

    @FXML
    public void handleRegister() {
        errorLabel.setText("Please register via the web application.");
        errorLabel.setVisible(true);
    }

    @FXML
    public void handleForgotPassword() {
        errorLabel.setText("Password reset link will be sent to your registered email.");
        errorLabel.setVisible(true);
    }

    @FXML
    public void handleContactAdmin() {
        errorLabel.setText("Please email support@forum.com for assistance.");
        errorLabel.setVisible(true);
    }

    @FXML
    public void handlePrivacyPolicy() {
        errorLabel.setText("Privacy Policy: Your data is protected.");
        errorLabel.setVisible(true);
    }

    @FXML
    public void handleTermsOfService() {
        errorLabel.setText("Terms of Service: Use of this platform is subject to our terms.");
        errorLabel.setVisible(true);
    }
}