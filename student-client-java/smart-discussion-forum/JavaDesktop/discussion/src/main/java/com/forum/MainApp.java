package com.forum;

import com.forum.services.NetworkWatcher;   // <-- added
import com.forum.services.SyncWorker;
import javafx.application.Application;
import javafx.event.Event;
import javafx.event.EventDispatchChain;
import javafx.event.EventDispatcher;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;

public class MainApp extends Application {
    private static Stage primaryStage;

    @Override
    public void start(Stage primaryStage) throws Exception {
        MainApp.primaryStage = primaryStage;
        Thread.setDefaultUncaughtExceptionHandler((t, e) -> {
            System.err.println("Uncaught exception in thread " + t.getName());
            e.printStackTrace();
        });

        // Load Login Screen
        showLogin();

        // Start NetworkWatcher background thread
        Thread watcherThread = new Thread(new NetworkWatcher());
        watcherThread.setDaemon(true);
        watcherThread.setName("NetworkWatcher");
        watcherThread.start();
        System.out.println("NetworkWatcher thread started successfully");

        // Start SyncWorker background thread
        Thread syncThread = new Thread(new SyncWorker());
        syncThread.setDaemon(true);
        syncThread.setName("SyncWorker");
        syncThread.start();
        System.out.println("SyncWorker thread started successfully");
    }

    /**
     * Switches the current scene to the Login screen.
     */
    public static void switchToLogin() {
        try {
            System.out.println("switchToLogin: loading Login.fxml");
            FXMLLoader loader = new FXMLLoader(MainApp.class.getResource("/com/forum/fxmlfiles/Login.fxml"));
            Parent root = loader.load();
            Scene scene = createScene(root, 400, 500);
            primaryStage.setScene(scene);
            primaryStage.setTitle("Smart Discussion Forum");
            primaryStage.setResizable(true);
            primaryStage.setMinWidth(400);
            primaryStage.setMinHeight(550);
            primaryStage.show();
        } catch (Exception e) {
            System.err.println("Exception in switchToLogin:");
            e.printStackTrace();
        }
    }

    /**
     * Switches the current scene to the Main workspace.
     */
    public static void switchToMain() {
        try {
            System.out.println("switchToMain: loading Main.fxml");
            FXMLLoader loader = new FXMLLoader(MainApp.class.getResource("/com/forum/fxmlfiles/Main.fxml"));
            Parent root = loader.load();
            Scene scene = createScene(root, 1200, 800);
            primaryStage.setScene(scene);
            primaryStage.setTitle("Smart Discussion Forum – Workspace");
            primaryStage.setResizable(true);
            primaryStage.setMinWidth(900);
            primaryStage.setMinHeight(600);
            primaryStage.show();
        } catch (Exception e) {
            System.err.println("Exception in switchToMain:");
            e.printStackTrace();
        }
    }

    /**
     * Helper to create a Scene with wrapped EventDispatcher for logging.
     */
    private static Scene createScene(Parent root, double width, double height) {
        Scene scene = new Scene(root, width, height);
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
        scene.getStylesheets().add(MainApp.class.getResource("/com/forum/css/style.css").toExternalForm());
        return scene;
    }

    /**
     * Helper to load the Login screen (used in start).
     */
    private void showLogin() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/forum/fxmlfiles/Login.fxml"));
            Parent root = loader.load();
            Scene scene = createScene(root, 400, 500);
            primaryStage.setScene(scene);
            primaryStage.setTitle("Smart Discussion Forum");
            primaryStage.setResizable(true);
            primaryStage.setMinWidth(400);
            primaryStage.setMinHeight(550);
            primaryStage.show();
        } catch (Exception e) {
            System.err.println("Failed to load Login.fxml");
            e.printStackTrace();
            throw new RuntimeException(e);
        }
    }

    public static void main(String[] args) {
        launch(args);
    }
}