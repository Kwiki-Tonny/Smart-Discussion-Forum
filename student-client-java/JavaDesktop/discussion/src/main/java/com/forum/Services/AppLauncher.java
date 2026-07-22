package com.forum.services;
public class AppLauncher {
    public static void main(String[] args) {
        System.out.println("================================================");
        System.out.println("[AppLauncher] Smart Discussion Forum - Network Watcher Test Harness");
        System.out.println("[AppLauncher] This runs the network watcher in the terminal.");
        System.out.println("[AppLauncher] Disconnect/reconnect your network to test status changes.");
        System.out.println("------------------------------------------------");

        // Start the network watcher background thread
        NetworkWatcher watcherTask = new NetworkWatcher();
        Thread heartbeatThread = new Thread(watcherTask);
        heartbeatThread.setName("NetworkWatcher-Heartbeat");
        heartbeatThread.setDaemon(true);  // Allows JVM to exit if main thread ends (but we'll keep running)
        heartbeatThread.start();

        System.out.println("[AppLauncher] NetworkWatcher is running on background thread.");
        System.out.println("[AppLauncher] Press Ctrl+C to stop the application.");
        System.out.println("------------------------------------------------");

        // Keep the main thread alive indefinitely
        try {
            // Wait forever (or until interrupted)
            while (true) {
                Thread.sleep(Long.MAX_VALUE);
            }
        } catch (InterruptedException e) {
            System.out.println("[AppLauncher] Main thread interrupted. Exiting.");
        }

        System.out.println("[AppLauncher] Test harness finished.");
    }
}