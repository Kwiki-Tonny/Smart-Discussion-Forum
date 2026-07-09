package com.forum;
import java.io.IOException;
import java.net.URL;
import java.util.ResourceBundle;
import javafx.fxml.FXML;
import javafx.scene.control.Button;

public class Lcontroller {

    @FXML
    private ResourceBundle resources;

    @FXML
    private URL location;

    @FXML
    private Button But;


     @FXML
    private void BtnOnClick() throws IOException {
        App.setRoot("main");
    }

    @FXML
    void initialize() {
        assert But != null : "fx:id=\"But\" was not injected: check your FXML file 'LogIn.fxml'.";

    }

}



    



    
