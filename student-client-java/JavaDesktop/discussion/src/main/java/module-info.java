module com.forum {
    requires javafx.controls;
    requires javafx.fxml;

    opens com.forum to javafx.fxml;
    exports com.forum;
}
