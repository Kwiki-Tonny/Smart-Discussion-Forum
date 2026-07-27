/**
 * Package: com.forum
 * 
 * This is the root package for the Smart Discussion Forum desktop client.
 * It contains the primary application entry point and orchestrates the 
 * initialization of the JavaFX lifecycle, global error handling, background 
 * services, and the main navigation routing between FXML-based views.
 */
package com.forum;

/**
 * Internal service dependencies for background orchestration.
 * 
 * Architectural Note: These services are instantiated and launched as daemon 
 * threads during application startup, ensuring they run continuously in the 
 * background without preventing the Java Virtual Machine (JVM) from shutting 
 * down gracefully when the main JavaFX application thread terminates.
 */
import com.forum.services.NetworkWatcher;
import com.forum.services.SyncWorker;

/**
 * Core JavaFX framework imports for application lifecycle, UI rendering, 
 * and event handling.
 */
import javafx.application.Application;
import javafx.event.Event;
import javafx.event.EventDispatchChain;
import javafx.event.EventDispatcher;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;

/**
 * Central Orchestrator and Entry Point for the Smart Discussion Forum Desktop Client.
 * 
 * This class extends {@link Application} and serves as the primary bootstrap 
 * mechanism for the JavaFX user interface. Its core responsibilities include:
 * 
 * 1. Lifecycle Management: Initializing the primary Stage and managing the 
 *    transition between major application views (Login vs. Main Workspace).
 * 2. Global Error Handling: Installing a default uncaught exception handler 
 *    to prevent silent failures in background threads, and wrapping the JavaFX 
 *    EventDispatcher to catch and log UI-thread exceptions that would otherwise 
 *    be swallowed by the framework.
 * 3. Background Service Initialization: Spawning and configuring daemon threads 
 *    for the {@link NetworkWatcher} (connectivity monitoring) and {@link SyncWorker} 
 *    (offline-first data reconciliation) immediately upon application startup.
 * 4. Resource Loading: Centralizing the loading of FXML layouts and CSS stylesheets 
 *    to ensure consistent UI theming and structure across the application.
 */
public class MainApp extends Application {

    /**
     * Static reference to the primary application window (Stage).
     * 
     * Architectural Note: While storing the Stage as a static variable is generally 
     * discouraged in strict dependency-injection architectures, it is used here as 
     * a pragmatic solution to allow the static navigation helper methods 
     * ({@link #switchToLogin()} and {@link #switchToMain()}) to easily access 
     * and modify the primary window without requiring complex controller-to-controller 
     * communication or singleton Stage managers.
     */
    private static Stage primaryStage;

    /**
     * The main entry point for all JavaFX applications.
     * 
     * This method is called by the JavaFX launcher after the underlying toolkit 
     * has been initialized. It orchestrates the initial setup sequence:
     * 1. Captures the primary stage reference.
     * 2. Installs a global safety net for uncaught background thread exceptions.
     * 3. Displays the initial Login view.
     * 4. Spawns critical background daemon threads for network monitoring and data sync.
     *
     * @param primaryStage The primary stage for this application, provided by the JavaFX runtime.
     * @throws Exception if FXML loading or thread initialization fails critically.
     */
    @Override
    public void start(Stage primaryStage) throws Exception {
        MainApp.primaryStage = primaryStage;
        
        // =========================================================================
        // 1. GLOBAL EXCEPTION HANDLING SETUP
        // =========================================================================
        // Install a default uncaught exception handler for all threads.
        // This is a critical safety net for background threads (like SyncWorker) 
        // that might encounter unexpected runtime exceptions. Without this, such 
        // exceptions would be printed to stderr and the thread would silently die, 
        // potentially leaving the application in an inconsistent state.
        Thread.setDefaultUncaughtExceptionHandler((t, e) -> {
            System.err.println("Uncaught exception in thread " + t.getName());
            e.printStackTrace();
        });

        // =========================================================================
        // 2. INITIAL VIEW BOOTSTRAPPING
        // =========================================================================
        // Display the login screen as the default starting point for the application.
        showLogin();

        // =========================================================================
        // 3. BACKGROUND SERVICE INITIALIZATION
        // =========================================================================
        // Start NetworkWatcher background thread
        // Configured as a daemon thread so it does not block JVM shutdown when 
        // the user closes the main application window.
        Thread watcherThread = new Thread(new NetworkWatcher());
        watcherThread.setDaemon(true);
        watcherThread.setName("NetworkWatcher");
        watcherThread.start();
        System.out.println("NetworkWatcher thread started successfully");

        // Start SyncWorker background thread
        // Also configured as a daemon thread. It will periodically wake up to 
        // reconcile local SQLite data with the remote server when online.
        Thread syncThread = new Thread(new SyncWorker());
        syncThread.setDaemon(true);
        syncThread.setName("SyncWorker");
        syncThread.start();
        System.out.println("SyncWorker thread started successfully");
    }

    /**
     * Navigates the user to the Login screen.
     * 
     * This static helper method is typically invoked by controllers when a user 
     * logs out, when a session expires, or when the application first starts. 
     * It completely replaces the current scene with the Login FXML layout and 
     * resizes the window to appropriate dimensions for an authentication form.
     */
    public static void switchToLogin() {
        try {
            System.out.println("switchToLogin: loading Login.fxml");
            
            // Load the FXML resource from the classpath
            FXMLLoader loader = new FXMLLoader(MainApp.class.getResource("/com/forum/fxmlfiles/Login.fxml"));
            Parent root = loader.load();
            
            // Create the scene with a custom event dispatcher wrapper and specific dimensions
            Scene scene = createScene(root, 400, 500);
            
            // Update the primary stage properties for the login context
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
     * Navigates the user to the Main Workspace screen.
     * 
     * This static helper method is invoked upon successful authentication. 
     * It replaces the current scene with the comprehensive Main FXML layout, 
     * expanding the window dimensions to accommodate the full forum dashboard, 
     * navigation sidebars, and content areas.
     */
    public static void switchToMain() {
        try {
            System.out.println("switchToMain: loading Main.fxml");
            
            // Load the FXML resource from the classpath
            FXMLLoader loader = new FXMLLoader(MainApp.class.getResource("/com/forum/fxmlfiles/Main.fxml"));
            Parent root = loader.load();
            
            // Create the scene with larger dimensions suitable for the main workspace
            Scene scene = createScene(root, 1200, 800);
            
            // Update the primary stage properties for the main application context
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
     * Advanced Scene creation helper with robust Event Dispatch wrapping.
     * 
     * Architectural Note: JavaFX has a known behavior where exceptions thrown 
     * during UI event handling (e.g., button clicks) are sometimes swallowed by 
     * the internal event loop, making debugging extremely difficult. 
     * 
     * To solve this, this method implements the Decorator Pattern on the 
     * {@link EventDispatcher}. It intercepts all events, attempts to dispatch 
     * them through the original dispatcher, and explicitly catches, logs, and 
     * re-throws any Throwables. This guarantees that no UI exception fails silently.
     *
     * @param root   The root Parent node loaded from FXML.
     * @param width  The initial width of the scene.
     * @param height The initial height of the scene.
     * @return A fully configured Scene with enhanced error logging and global CSS.
     */
    private static Scene createScene(Parent root, double width, double height) {
        Scene scene = new Scene(root, width, height);
        
        // Capture the default JavaFX event dispatcher
        final EventDispatcher originalDispatcher = scene.getEventDispatcher();
        
        // Wrap it in a custom dispatcher to intercept and log exceptions
        scene.setEventDispatcher(new EventDispatcher() {
            @Override
            public Event dispatchEvent(Event event, EventDispatchChain tail) {
                try {
                    // Attempt normal event processing
                    return originalDispatcher.dispatchEvent(event, tail);
                } catch (Throwable t) {
                    // Log the failure explicitly to the console for debugging
                    System.err.println("Exception during event dispatch:");
                    t.printStackTrace();
                    
                    // Re-throw, preserving the original exception type if it's a 
                    // RuntimeException, otherwise wrapping it to satisfy the method signature.
                    throw t instanceof RuntimeException ? (RuntimeException) t : new RuntimeException(t);
                }
            }
        });
        
        // Apply the global stylesheet to ensure consistent theming across all views
        scene.getStylesheets().add(MainApp.class.getResource("/com/forum/css/style.css").toExternalForm());
        
        return scene;
    }

    /**
     * Initial view bootstrapping helper.
     * 
     * Called directly from the {@link #start(Stage)} method to set up the 
     * very first screen the user sees. It shares logic with {@link #switchToLogin()} 
     * but is kept separate to maintain a clean initialization flow distinct from 
     * mid-session navigation events.
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
            // Fatal error during initial startup; wrap in RuntimeException to halt launch.
            throw new RuntimeException(e);
        }
    }

    /**
     * Standard Java main method entry point.
     * 
     * Delegates execution to the JavaFX Application.launch() method, which 
     * handles the complex underlying initialization of the JavaFX toolkit, 
     * creates the primary Stage, and subsequently calls the {@link #start(Stage)} 
     * method defined above.
     *
     * @param args Command-line arguments passed to the application.
     */
    public static void main(String[] args) {
        launch(args);
    }
}