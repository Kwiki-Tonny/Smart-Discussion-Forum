package com.forum.controllers;

import com.forum.models.User;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.layout.VBox;
import javafx.stage.Stage;

import java.util.ArrayList;
import java.util.List;

public class UserSelectionDialog {

    @FXML private ListView<User> userListView;
    @FXML private TextField searchField;
    @FXML private Button selectAllBtn;
    @FXML private Button deselectAllBtn;
    @FXML private Button confirmBtn;
    @FXML private Button cancelBtn;
    @FXML private Label selectedCountLabel;

    private ObservableList<User> allUsers = FXCollections.observableArrayList();
    private List<Integer> selectedUserIds = new ArrayList<>();
    private Runnable onConfirm;

    public void setUsers(List<User> users) {
        allUsers.setAll(users);
        userListView.setItems(allUsers);
        userListView.getSelectionModel().setSelectionMode(SelectionMode.MULTIPLE);
        updateSelectedCount();
    }

    public void setOnConfirm(Runnable onConfirm) {
        this.onConfirm = onConfirm;
    }

    public List<Integer> getSelectedUserIds() {
        return selectedUserIds;
    }

    @FXML
    public void initialize() {
        // Search filter
        searchField.textProperty().addListener((obs, oldVal, newVal) -> {
            String query = newVal.toLowerCase().trim();
            if (query.isEmpty()) {
                userListView.setItems(allUsers);
            } else {
                ObservableList<User> filtered = FXCollections.observableArrayList();
                for (User u : allUsers) {
                    if (u.name.toLowerCase().contains(query) || u.email.toLowerCase().contains(query)) {
                        filtered.add(u);
                    }
                }
                userListView.setItems(filtered);
            }
        });

        // Update count on selection change
        userListView.getSelectionModel().selectedItemProperty().addListener((obs, oldVal, newVal) -> {
            updateSelectedCount();
        });
    }

    private void updateSelectedCount() {
        int count = userListView.getSelectionModel().getSelectedItems().size();
        selectedCountLabel.setText("Selected: " + count + " users");
    }

    @FXML
    public void handleSelectAll() {
        userListView.getSelectionModel().selectAll();
        updateSelectedCount();
    }

    @FXML
    public void handleDeselectAll() {
        userListView.getSelectionModel().clearSelection();
        updateSelectedCount();
    }

    @FXML
    public void handleConfirm() {
        selectedUserIds.clear();
        for (User u : userListView.getSelectionModel().getSelectedItems()) {
            selectedUserIds.add(u.id);
        }
        if (onConfirm != null) {
            onConfirm.run();
        }
        closeDialog();
    }

    @FXML
    public void handleCancel() {
        closeDialog();
    }

    private void closeDialog() {
        Stage stage = (Stage) confirmBtn.getScene().getWindow();
        stage.close();
    }
}