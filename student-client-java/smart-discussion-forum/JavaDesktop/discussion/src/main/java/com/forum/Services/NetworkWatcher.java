package com.forum.services;

import java.net.HttpURLConnection;
import java.net.URL;

public class NetworkWatcher implements Runnable {
    // Uses Laravel's health-check endpoint (adjust port if needed)
    private static final String SERVER_PING_URL = "http://127.0.0.1:8000/api/v1/health-check";
    private static final int CHECK_INTERVAL_MS = 10000;
    private static final int CONNECTION_TIMEOUT_MS = 5000;
    
    private final GlobalState state = GlobalState.getInstance();

    @Override
    public void run() {
        System.out.println("[NetworkWatcher] Heartbeat started.");
        System.out.println("[NetworkWatcher] Checking server every 10 seconds...");
        System.out.println("[NetworkWatcher] Target: " + SERVER_PING_URL);
        System.out.println("------------------------------------------------");

        while (true) {
            checkConnection();
            try {
                Thread.sleep(CHECK_INTERVAL_MS);
            } catch (InterruptedException e) {
                System.out.println("[NetworkWatcher] Heartbeat shutting down.");
                break;
            }
        }
    }

    private void checkConnection() {
        HttpURLConnection connection = null;
        try {
            state.setConnectionAttempting(true);
            
            URL url = new URL(SERVER_PING_URL);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.setConnectTimeout(CONNECTION_TIMEOUT_MS);
            connection.setReadTimeout(CONNECTION_TIMEOUT_MS);

            int responseCode = connection.getResponseCode();

            if (responseCode == HttpURLConnection.HTTP_OK) {
                state.setOnline(true);
                state.setLastError(null);
                System.out.println("[NetworkWatcher] Status: ONLINE");
            } else {
                state.setOnline(false);
                state.setLastError("Server returned: " + responseCode);
                System.out.println("[NetworkWatcher] Status: OFFLINE (Server returned: " + responseCode + ")");
            }
        } catch (Exception e) {
            state.setOnline(false);
            state.setLastError(e.getMessage());
            System.out.println("[NetworkWatcher] Status: OFFLINE (" + e.getMessage() + ")");
        } finally {
            state.setConnectionAttempting(false);
            if (connection != null) {
                connection.disconnect();
            }
        }
    }
}