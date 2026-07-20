package com.forum.Controllers;

import javafx.application.Application;
import javafx.event.Event;
import javafx.event.EventDispatchChain;
import javafx.event.EventDispatcher;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;
import javafx.stage.StageStyle;


public class MainApp extends Application {
    private static Stage primaryStage;

    @Override
    public void start(Stage primaryStage) throws Exception {
        MainApp.primaryStage = primaryStage;
        // Ensure uncaught exceptions on any thread are logged for easier debugging
        Thread.setDefaultUncaughtExceptionHandler((t, e) -> {
            System.err.println("Uncaught exception in thread " + t.getName());
            e.printStackTrace();
        });
        
        // Load Login Screen
        FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/forum/Login.fxml"));
        Parent root;
        try {
            root = loader.load();
        } catch (Exception e) {
            System.err.println("Failed to load Login.fxml");
            e.printStackTrace();
            throw e;
        }
        
        Scene scene = new Scene(root, 400, 500);
        // Wrap the scene's EventDispatcher to log any exception thrown by event handlers
        final EventDispatcher originalDispatcher = scene.getEventDispatcher();
        scene.setEventDispatcher(new EventDispatcher() {
            @Override
            public Event dispatchEvent(Event event, EventDispatchChain tail) {
                try {
                    return originalDispatcher.dispatchEvent(event, tail);
                } catch (Throwable t) {
                    System.err.println("Exception during event dispatch:");
                    t.printStackTrace();
                    throw t instanceof RuntimeException ? (RuntimeException) t : new RuntimeException(t);
                }
            }
        });
        scene.getStylesheets().add(getClass().getResource("/com/forum/style.css").toExternalForm());
        
        primaryStage.setTitle("Smart Discussion Forum");
        primaryStage.setScene(scene);
        primaryStage.setResizable(true);
        primaryStage.setMinWidth(400);
        primaryStage.setMinHeight(550);
        primaryStage.show();
    }

    public static void switchToMain() {
        try {
            System.out.println("switchToMain: loading Main.fxml");
            FXMLLoader loader = new FXMLLoader(MainApp.class.getResource("/com/forum/Main.fxml"));
            Parent root = loader.load();
            
            Scene scene = new Scene(root, 1200, 800);
            // Wrap the scene's EventDispatcher to log exceptions from event handlers
            final EventDispatcher originalDispatcher = scene.getEventDispatcher();
            scene.setEventDispatcher(new EventDispatcher() {
                @Override
                public Event dispatchEvent(Event event, EventDispatchChain tail) {
                    try {
                        return originalDispatcher.dispatchEvent(event, tail);
                    } catch (Throwable t) {
                        System.err.println("Exception during event dispatch:");
                        t.printStackTrace();
                        throw t instanceof RuntimeException ? (RuntimeException) t : new RuntimeException(t);
                    }
                }
            });
            scene.getStylesheets().add(MainApp.class.getResource("/com/forum/style.css").toExternalForm());
            
            primaryStage.setScene(scene);
            primaryStage.setResizable(true);
            primaryStage.setMinWidth(900);
            primaryStage.setMinHeight(600);
            primaryStage.show();
        } catch (Exception e) {
            System.err.println("Exception in switchToMain:");
            e.printStackTrace();
            Throwable cause = e.getCause();
            while (cause != null) {
                System.err.println("Caused by: " + cause.getClass().getName() + ": " + cause.getMessage());
                cause.printStackTrace();
                cause = cause.getCause();
            }
        }
    }

    public static void main(String[] args) {
        launch(args);
    }
}