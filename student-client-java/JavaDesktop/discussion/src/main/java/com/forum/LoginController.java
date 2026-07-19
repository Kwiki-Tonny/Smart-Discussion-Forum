package com.forum;

import javafx.fxml.FXML;
import javafx.scene.control.CheckBox;
import javafx.scene.control.Label;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;

public class LoginController {
    @FXML private TextField emailField;
    @FXML private PasswordField passwordField;
    @FXML private CheckBox rememberCheck;
    @FXML private Label errorLabel;

    @FXML
    public void handleLogin() {
        try {
            String email = emailField.getText().trim();
            String password = passwordField.getText().trim();

            if (email.equals("demo@forum.com") && password.equals("password")) {
                errorLabel.setVisible(false);
                // In real app, handle remember me
                boolean remember = rememberCheck.isSelected();
                MainApp.switchToMain();
            } else {
                errorLabel.setVisible(true);
            }
        } catch (Exception e) {
            System.err.println("Exception in handleLogin:");
            e.printStackTrace();
            errorLabel.setText("Unexpected error: " + e.getClass().getSimpleName());
            errorLabel.setVisible(true);
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