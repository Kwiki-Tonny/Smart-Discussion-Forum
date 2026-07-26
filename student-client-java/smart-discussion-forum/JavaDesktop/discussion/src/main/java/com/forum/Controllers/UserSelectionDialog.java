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

/**
 * UserSelectionDialog manages the modal dialog interface for selecting specific users.
 * 
 * <p><b>Architectural Role:</b>
 * This controller is responsible for the "Select Users" modal, which is primarily invoked 
 * when a user attempts to create a "Private" post or reply. It allows the author to explicitly 
 * choose which users should be excluded from viewing the content.
 * 
 * <p><b>Design Pattern:</b>
 * It operates as a decoupled, reusable dialog component. Instead of tightly coupling the 
 * confirmation logic to this class, it accepts a {@link Runnable} callback via {@link #setOnConfirm(Runnable)}. 
 * This allows the parent controller (e.g., {@link MainController}) to define the post-confirmation 
 * behavior (such as updating the UI label or proceeding with the API call) while this class 
 * strictly handles the UI state and data extraction.
 * 
 * <p><b>UI Features:</b>
 * <ul>
 *   <li><b>Custom Cell Rendering:</b> Displays both the user's name and email for unambiguous identification.</li>
 *   <li><b>Real-time Search Filtering:</b> Dynamically filters the list based on case-insensitive matches against name or email.</li>
 *   <li><b>Bulk Actions:</b> Provides "Select All" and "Deselect All" shortcuts for improved UX.</li>
 *   <li><b>Reactive Feedback:</b> Continuously updates a counter label to show the number of currently selected users.</li>
 * </ul>
 * 
 * @author Forum Development Team
 * @version 2.0
 * @see User
 * @see MainController
 */
public class UserSelectionDialog {

    // =========================================================================
    // ─── FXML INJECTIONS ─────────────────────────────────────────────────────
    // =========================================================================
    // These fields are automatically populated by the FXMLLoader based on the 
    // fx:id attributes defined in the user_selection_dialog.fxml file.

    /** 
     * The primary list view displaying the available users. 
     * Configured for multiple selections.
     */
    @FXML 
    private ListView<User> userListView;

    /** 
     * Text input field for real-time filtering of the user list by name or email.
     */
    @FXML 
    private TextField searchField;

    /** 
     * Button to quickly select every user currently visible in the filtered list.
     */
    @FXML 
    private Button selectAllBtn;

    /** 
     * Button to quickly clear all current selections in the list view.
     */
    @FXML 
    private Button deselectAllBtn;

    /** 
     * Button to confirm the current selection, extract the IDs, and close the dialog.
     */
    @FXML 
    private Button confirmBtn;

    /** 
     * Button to cancel the operation, discard any selections, and close the dialog.
     */
    @FXML 
    private Button cancelBtn;

    /** 
     * Label providing real-time feedback on how many users are currently selected.
     */
    @FXML 
    private Label selectedCountLabel;

    // =========================================================================
    // ─── STATE MANAGEMENT ────────────────────────────────────────────────────
    // =========================================================================

    /** 
     * The master list of all users available for selection. 
     * Wrapped in an ObservableList to support dynamic UI updates and filtering.
     */
    private ObservableList<User> allUsers = FXCollections.observableArrayList();

    /** 
     * The final extracted list of user IDs that were selected by the user 
     * at the time the "Confirm" button was clicked.
     */
    private List<Integer> selectedUserIds = new ArrayList<>();

    /** 
     * Callback function to be executed upon successful confirmation. 
     * Injected by the parent controller to handle post-dialog logic.
     */
    private Runnable onConfirm;

    // =========================================================================
    // ─── INITIALIZATION & CONFIGURATION ──────────────────────────────────────
    // =========================================================================

    /**
     * Populates the dialog with the provided list of users and configures the 
     * initial state of the list view.
     * 
     * <p><b>Behavior:</b>
     * <ol>
     *   <li>Replaces the contents of the master {@link #allUsers} list.</li>
     *   <li>Binds the master list to the {@link #userListView}.</li>
     *   <li>Enables multiple selection mode to allow choosing several users at once.</li>
     *   <li>Initializes the selection count label.</li>
     * </ol>
     * 
     * @param users The list of {@link User} objects to display in the dialog.
     */
    public void setUsers(List<User> users) {
        allUsers.setAll(users);
        userListView.setItems(allUsers);
        
        // Enable multiple selection to support excluding multiple users simultaneously
        userListView.getSelectionModel().setSelectionMode(SelectionMode.MULTIPLE);
        
        updateSelectedCount();
    }

    /**
     * Sets the callback action to be executed when the user clicks the "Confirm" button.
     * 
     * @param onConfirm The {@link Runnable} task to execute upon confirmation.
     */
    public void setOnConfirm(Runnable onConfirm) {
        this.onConfirm = onConfirm;
    }

    /**
     * Retrieves the list of user IDs that were selected when the dialog was confirmed.
     * 
     * @return A {@link List} of {@link Integer} representing the selected user IDs.
     */
    public List<Integer> getSelectedUserIds() {
        return selectedUserIds;
    }

    /**
     * Called automatically by the FXMLLoader after the FXML file has been loaded.
     * 
     * <p><b>Responsibilities:</b>
     * <ul>
     *   <li>Configures a custom {@link ListCell} factory to display both name and email.</li>
     *   <li>Attaches a listener to the search field for real-time, case-insensitive filtering.</li>
     *   <li>Attaches a listener to the selection model to keep the "Selected: X users" label in sync.</li>
     * </ul>
     */
    @FXML
    public void initialize() {
        // ─── CELL FACTORY: Custom rendering for user list items ──────────────
        // Overrides the default toString() behavior to show a more informative 
        // "Name (email)" format, reducing ambiguity when users have similar names.
        userListView.setCellFactory(param -> new ListCell<User>() {
            @Override
            protected void updateItem(User user, boolean empty) {
                super.updateItem(user, empty);
                if (empty || user == null) {
                    setText(null);
                } else {
                    setText(user.name + " (" + user.email + ")");
                }
            }
        });

        // ─── SEARCH FILTER: Real-time, case-insensitive filtering ────────────
        searchField.textProperty().addListener((obs, oldVal, newVal) -> {
            // Normalize the query for consistent matching
            String query = newVal.toLowerCase().trim();
            
            if (query.isEmpty()) {
                // If the search is cleared, restore the full master list
                userListView.setItems(allUsers);
            } else {
                // Build a new filtered list based on name or email matches
                ObservableList<User> filtered = FXCollections.observableArrayList();
                for (User u : allUsers) {
                    if (u.name.toLowerCase().contains(query) || u.email.toLowerCase().contains(query)) {
                        filtered.add(u);
                    }
                }
                userListView.setItems(filtered);
            }
        });

        // ─── SELECTION COUNT UPDATE: Reactive UI feedback ────────────────────
        // Listens for any change in the selected items and updates the counter label
        userListView.getSelectionModel().selectedItemProperty().addListener((obs, oldVal, newVal) -> {
            updateSelectedCount();
        });
    }

    // =========================================================================
    // ─── UI HELPERS ──────────────────────────────────────────────────────────
    // =========================================================================

    /**
     * Updates the {@link #selectedCountLabel} to reflect the current number of 
     * selected items in the list view's selection model.
     */
    private void updateSelectedCount() {
        int count = userListView.getSelectionModel().getSelectedItems().size();
        selectedCountLabel.setText("Selected: " + count + " users");
    }

    // =========================================================================
    // ─── ACTION HANDLERS ─────────────────────────────────────────────────────
    // =========================================================================

    /**
     * FXML-bound action handler for the "Select All" button.
     * Selects every item currently visible in the list view (respecting any active search filter)
     * and updates the selection count label.
     */
    @FXML
    public void handleSelectAll() {
        userListView.getSelectionModel().selectAll();
        updateSelectedCount();
    }

    /**
     * FXML-bound action handler for the "Deselect All" button.
     * Clears all current selections in the list view and updates the selection count label.
     */
    @FXML
    public void handleDeselectAll() {
        userListView.getSelectionModel().clearSelection();
        updateSelectedCount();
    }

    /**
     * FXML-bound action handler for the "Confirm" button.
     * 
     * <p><b>Execution Flow:</b>
     * <ol>
     *   <li>Clears any previously stored IDs to prevent stale data accumulation.</li>
     *   <li>Iterates through the currently selected {@link User} models and extracts their {@code id}.</li>
     *   <li>Executes the {@link #onConfirm} callback (if provided by the parent controller).</li>
     *   <li>Closes the modal dialog window.</li>
     * </ol>
     */
    @FXML
    public void handleConfirm() {
        selectedUserIds.clear();
        
        // Extract the integer IDs from the selected User model objects
        for (User u : userListView.getSelectionModel().getSelectedItems()) {
            selectedUserIds.add(u.id);
        }
        
        // Trigger the parent controller's continuation logic
        if (onConfirm != null) {
            onConfirm.run();
        }
        
        closeDialog();
    }

    /**
     * FXML-bound action handler for the "Cancel" button.
     * Discards any selections and closes the modal dialog without triggering the confirm callback.
     */
    @FXML
    public void handleCancel() {
        closeDialog();
    }

    /**
     * Utility method to safely retrieve the current {@link Stage} (window) associated 
     * with this dialog's scene and close it.
     * 
     * <p><b>Note:</b> This relies on the {@link #confirmBtn} being part of the scene graph. 
     * Since both confirm and cancel buttons share the same scene, either can be used as the anchor.
     */
    private void closeDialog() {
        Stage stage = (Stage) confirmBtn.getScene().getWindow();
        stage.close();
    }
}