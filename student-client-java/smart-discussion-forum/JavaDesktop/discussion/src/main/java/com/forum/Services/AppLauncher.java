/**
 * Package: com.forum.services
 * 
 * This package contains the core business logic, service-layer components, 
 * and utility launchers of the forum application. While primarily focused on 
 * API orchestration and state management, it also houses standalone execution 
 * entry points (like AppLauncher) used for testing and validating specific 
 * background services in isolation.
 */
package com.forum.services;

/**
 * Standalone Application Launcher and Test Harness for the Network Watcher.
 * 
 * This class serves as a dedicated, lightweight entry point specifically designed 
 * to validate the functionality of the {@link NetworkWatcher} service. Instead of 
 * bootstrapping the entire graphical user interface or web server, this harness 
 * isolates the network monitoring logic, allowing developers to observe real-time 
 * network state transitions (connect/disconnect events) directly in the terminal.
 * 
 * Threading Architecture:
 * 1. Background Worker: The {@link NetworkWatcher} is executed on a dedicated 
 *    background thread named "NetworkWatcher-Heartbeat". This thread is configured 
 *    as a Daemon thread, meaning it will not prevent the Java Virtual Machine (JVM) 
 *    from exiting if all non-daemon threads have terminated.
 * 2. Main Thread Parking: The main thread is intentionally parked indefinitely 
 *    using {@link Thread#sleep(long)} with {@link Long#MAX_VALUE}. This provides 
 *    a near-zero CPU overhead mechanism to keep the JVM alive and the background 
 *    daemon thread running, while remaining responsive to OS-level interrupt 
 *    signals (such as Ctrl+C).
 * 
 * Usage:
 * Run this class directly from your IDE or via the command line. Observe the 
 * console output while manually disconnecting and reconnecting your machine's 
 * network interface to verify that the watcher correctly detects and logs the 
 * state changes.
 */
public class AppLauncher {

    /**
     * The main entry point for the Network Watcher Test Harness.
     * 
     * This method orchestrates the startup sequence of the testing environment:
     * 1. Prints an informational banner to guide the developer on how to use the harness.
     * 2. Instantiates and configures the NetworkWatcher background thread.
     * 3. Parks the main execution thread indefinitely to prevent premature JVM termination.
     * 4. Gracefully handles interruption signals to provide clean shutdown logging.
     *
     * @param args Command-line arguments passed to the application (currently unused, 
     *             but retained for standard Java main method signature compliance).
     */
    public static void main(String[] args) {
        // =========================================================================
        // 1. INITIALIZATION & BANNER OUTPUT
        // Provide clear, immediate context to the developer running the harness.
        // =========================================================================
        System.out.println("================================================");
        System.out.println("[AppLauncher] Smart Discussion Forum - Network Watcher Test Harness");
        System.out.println("[AppLauncher] This runs the network watcher in the terminal.");
        System.out.println("[AppLauncher] Disconnect/reconnect your network to test status changes.");
        System.out.println("------------------------------------------------");

        // =========================================================================
        // 2. BACKGROUND THREAD CONFIGURATION & STARTUP
        // Isolate the network monitoring logic to prevent blocking the main thread.
        // =========================================================================
        
        // Instantiate the core network monitoring task.
        NetworkWatcher watcherTask = new NetworkWatcher();
        
        // Wrap the task in a dedicated Thread for concurrent execution.
        Thread heartbeatThread = new Thread(watcherTask);
        
        // Assign a descriptive name to the thread. This is a critical best practice 
        // for debugging and profiling, as it allows the thread to be easily identified 
        // in thread dumps, IDE debuggers, and logging frameworks.
        heartbeatThread.setName("NetworkWatcher-Heartbeat");
        
        // Configure as a Daemon thread. 
        // Rationale: Daemon threads are background service providers for user threads. 
        // If the main thread were to somehow terminate unexpectedly, the JVM would be 
        // allowed to shut down cleanly without hanging on this background monitoring task.
        heartbeatThread.setDaemon(true);
        
        // Initiate the execution of the background thread.
        heartbeatThread.start();

        // =========================================================================
        // 3. MAIN THREAD PARKING & LIFECYCLE MANAGEMENT
        // Keep the JVM alive to allow the daemon thread to continue its monitoring.
        // =========================================================================
        System.out.println("[AppLauncher] NetworkWatcher is running on background thread.");
        System.out.println("[AppLauncher] Press Ctrl+C to stop the application.");
        System.out.println("------------------------------------------------");

        // Keep the main thread alive indefinitely.
        try {
            // Wait forever (or until interrupted).
            // Using Thread.sleep(Long.MAX_VALUE) is the standard, most CPU-efficient 
            // way to park a thread indefinitely in Java. It avoids the high CPU 
            // consumption of a "busy-wait" loop (e.g., while(true) {}) while still 
            // keeping the JVM process active to service the background daemon thread.
            while (true) {
                Thread.sleep(Long.MAX_VALUE);
            }
        } catch (InterruptedException e) {
            // This catch block is triggered when the thread receives an interrupt signal, 
            // which typically occurs when the user presses Ctrl+C in the terminal. 
            // Catching this allows us to log a graceful shutdown message rather than 
            // letting the JVM print a noisy, unhandled stack trace.
            System.out.println("[AppLauncher] Main thread interrupted. Exiting.");
            
            // Restore the interrupted status of the thread as a best practice, 
            // ensuring that higher-level frameworks (if any were attached) can 
            // also react to the cancellation request.
            Thread.currentThread().interrupt();
        }

        // =========================================================================
        // 4. SHUTDOWN SEQUENCE
        // Final logging to confirm the application lifecycle has concluded.
        // =========================================================================
        System.out.println("[AppLauncher] Test harness finished.");
    }
}