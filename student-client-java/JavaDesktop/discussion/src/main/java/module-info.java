module com.forum {
    requires javafx.controls;
    requires javafx.fxml;

    // Export the package containing your main class so JavaFX can access it
    exports com.forum;

    // If you have controllers in a subpackage, export that too
    exports com.forum.Controllers;

    // Open packages for FXML reflection (required for @FXML injection)
    opens com.forum.Controllers to javafx.fxml;
    opens com.forum to javafx.fxml;
}