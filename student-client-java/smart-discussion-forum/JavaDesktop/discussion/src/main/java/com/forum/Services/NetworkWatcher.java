/**
 * Package: com.forum.services
 * 
 * This package contains the core business logic, service-layer components, 
 * and state management utilities of the forum application. Classes in this 
 * package are responsible for orchestrating operations, managing external 
 * API communications, handling local offline-first data storage, and 
 * maintaining the reactive state of the desktop client via background workers.
 */
package com.forum.services;

/**
 * Standard Java SE class for representing a Uniform Resource Locator (URL).
 * Used here to construct the target endpoint for the network connectivity probe.
 */
import java.net.URL;

/**
 * Standard Java SE class for handling HTTP-specific URL connections.
 * 
 * Architectural Note: java.net.HttpURLConnection is intentionally chosen here 
 * over third-party libraries (like OkHttp or Apache HttpClient) for this specific 
 * background task. It provides a lightweight, zero-dependency mechanism for 
 * simple GET requests, minimizing the memory footprint of the background daemon 
 * thread that runs continuously throughout the application's lifecycle.
 */
import java.net.HttpURLConnection;

/**
 * Background Network Connectivity Monitor (Heartbeat Service).
 * 
 * This class implements the {@link Runnable} interface to operate as a dedicated 
 * background daemon thread. Its primary responsibility is to continuously probe 
 * the backend server's health-check endpoint to determine the application's 
 * network connectivity status.
 * 
 * Role in the Offline-First Architecture:
 * 1. State Propagation: Upon detecting a change in connectivity, it immediately 
 *    updates the centralized {@link GlobalState} singleton. 
 * 2. Reactive UI: The {@link GlobalState} mutation triggers Observer pattern 
 *    callbacks, allowing the JavaFX UI to instantly reflect "Online" or "Offline" 
 *    states (e.g., showing connection banners, disabling remote-only actions).
 * 3. Sync Triggering: A transition from OFFLINE to ONLINE can be used by other 
 *    services (like the sync manager) to automatically flush the local SQLite 
 *    queue of pending posts and likes to the remote server.
 * 
 * Threading & Safety:
 * - Designed to run on a daemon thread (configured in {@link AppLauncher}).
 * - Gracefully handles {@link InterruptedException} to allow for clean shutdown 
 *   when the application is terminated.
 * - Utilizes strict timeout configurations to prevent thread starvation in the 
 *   event of a "black hole" network (where packets are dropped without ICMP rejection).
 */
public class NetworkWatcher implements Runnable {

    /**
     * The target endpoint for the network connectivity probe.
     * 
     * Architectural Note: Points to a dedicated, lightweight health-check endpoint 
     * on the Laravel backend. Using a specific health route (rather than pinging 
     * a heavy data endpoint like /users or /topics) ensures the probe executes 
     * rapidly on the server side, avoiding unnecessary database queries or 
     * authentication overhead during frequent polling.
     */
    private static final String SERVER_PING_URL = "http://127.0.0.1:8000/api/v1/health-check";

    /**
     * The interval between consecutive connectivity checks, in milliseconds.
     * 
     * Value: 10,000 ms (10 seconds).
     * Rationale: This provides a balance between responsive UI updates (the user 
     * knows within 10 seconds if their connection dropped) and network efficiency 
     * (preventing excessive polling that could resemble a DDoS attack or drain 
     * laptop battery life on metered connections).
     */
    private static final int CHECK_INTERVAL_MS = 10000;

    /**
     * The maximum time to wait for a connection to be established or data to be 
     * read, in milliseconds.
     * 
     * Value: 5,000 ms (5 seconds).
     * Rationale: Implements a "fail-fast" strategy. If the server does not respond 
     * within 5 seconds, the network is considered effectively unreachable for the 
     * purposes of the application. This prevents the background thread from hanging 
     * indefinitely and blocking subsequent health checks.
     */
    private static final int CONNECTION_TIMEOUT_MS = 5000;
    
    /**
     * Reference to the centralized application state manager.
     * 
     * Used to publish connectivity status updates, which are then broadcast to 
     * all registered UI listeners via the Observer pattern.
     */
    private final GlobalState state = GlobalState.getInstance();

    /**
     * The main execution loop for the background network monitoring thread.
     * 
     * This method runs indefinitely until the thread is explicitly interrupted 
     * (e.g., during application shutdown). It orchestrates the periodic execution 
     * of the connectivity check and manages the thread's sleep cycle.
     */
    @Override
    public void run() {
        // Initial telemetry to confirm successful thread startup in the console.
        System.out.println("[NetworkWatcher] Heartbeat started.");
        System.out.println("[NetworkWatcher] Checking server every 10 seconds...");
        System.out.println("[NetworkWatcher] Target: " + SERVER_PING_URL);
        System.out.println("------------------------------------------------");

        // Infinite loop for continuous monitoring.
        while (true) {
            // Execute the network probe.
            checkConnection();
            
            try {
                // Park the thread for the configured interval to prevent CPU spinning.
                Thread.sleep(CHECK_INTERVAL_MS);
            } catch (InterruptedException e) {
                // Interruption is the standard Java mechanism for requesting a thread 
                // to terminate. Catching it allows us to log a graceful shutdown 
                // message and break the infinite loop, allowing the run() method 
                // to complete and the thread to die cleanly.
                System.out.println("[NetworkWatcher] Heartbeat shutting down.");
                break;
            }
        }
    }

    /**
     * Executes a single HTTP GET request to the server's health-check endpoint 
     * to determine current network reachability.
     * 
     * Lifecycle of this method:
     * 1. Signals to the GlobalState that a connection attempt is in progress 
     *    (useful for UI loading indicators).
     * 2. Configures and opens an HttpURLConnection with strict timeout limits.
     * 3. Evaluates the HTTP response code to determine success (200 OK) or failure.
     * 4. Updates the GlobalState with the resulting online/offline status and 
     *    any relevant error messages.
     * 5. Guarantees resource cleanup in the finally block to prevent socket leaks.
     */
    private void checkConnection() {
        // Declare connection reference outside the try block to ensure it is 
        // accessible in the finally block for proper resource deallocation.
        HttpURLConnection connection = null;
        
        try {
            // Notify the UI and state manager that a probe is actively occurring.
            state.setConnectionAttempting(true);
            
            // Construct the URL and open the connection.
            URL url = new URL(SERVER_PING_URL);
            connection = (HttpURLConnection) url.openConnection();
            
            // Configure request parameters for a lightweight, fast-failing probe.
            connection.setRequestMethod("GET");
            connection.setConnectTimeout(CONNECTION_TIMEOUT_MS);
            connection.setReadTimeout(CONNECTION_TIMEOUT_MS);

            // Execute the request and retrieve the HTTP status code.
            // Note: getResponseCode() implicitly triggers the actual network I/O.
            int responseCode = connection.getResponseCode();

            // Evaluate the response to determine connectivity status.
            if (responseCode == HttpURLConnection.HTTP_OK) {
                // 200 OK indicates the server is reachable and functioning.
                state.setOnline(true);
                state.setLastError(null); // Clear any previous network errors.
                System.out.println("[NetworkWatcher] Status: ONLINE");
            } else {
                // Non-200 responses indicate the server is reachable but returning 
                // an error (e.g., 500 Internal Server Error, 503 Service Unavailable).
                // We treat this as "offline" from the application's perspective, 
                // as the API is not in a state to reliably process requests.
                state.setOnline(false);
                state.setLastError("Server returned: " + responseCode);
                System.out.println("[NetworkWatcher] Status: OFFLINE (Server returned: " + responseCode + ")");
            }
        } catch (Exception e) {
            // Catching the broad Exception class is intentional here. 
            // Network operations can throw a variety of unchecked exceptions 
            // (e.g., UnknownHostException, ConnectException, SocketTimeoutException). 
            // Catching them broadly ensures that a single network anomaly does not 
            // crash the background daemon thread, allowing the next scheduled 
            // check to attempt recovery.
            state.setOnline(false);
            state.setLastError(e.getMessage());
            System.out.println("[NetworkWatcher] Status: OFFLINE (" + e.getMessage() + ")");
        } finally {
            // Crucial cleanup step: Always reset the "attempting" flag, regardless 
            // of success or failure, to prevent the UI from being stuck in a 
            // perpetual "connecting..." state.
            state.setConnectionAttempting(false);
            
            // Explicitly disconnect the HttpURLConnection to release the underlying 
            // network socket and file descriptor resources back to the OS.
            if (connection != null) {
                connection.disconnect();
            }
        }
    }
}