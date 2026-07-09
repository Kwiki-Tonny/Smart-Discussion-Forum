package com.forum;

 
    import java.net.URL;
import java.util.ResourceBundle;
import javafx.fxml.FXML;

public class MainController {

    @FXML
    private ResourceBundle resources;

    @FXML
    private URL location;

    @FXML
    void initialize() {
        assert resources != null : "fx:id=\"resources\" was not injected: check your FXML file 'Main.fxml'.";
        
    }

}


