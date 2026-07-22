package com.forum.models;

import com.forum.DatabaseHandler;
import com.forum.GlobalState;
import com.forum.services.ApiService;
import com.google.gson.JsonArray;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class SyncWorker implements Runnable {

    private static final int SLEEP_INTERVAL = 15000; // 15 seconds
    private boolean running = true;
    private final ApiService api = ApiService.getInstance();
    private final GlobalState state = GlobalState.getInstance();

    @Override
    public void run() {
        System.out.println("[SyncWorker] Started.");
        while (running) {
            try {
                Thread.sleep(SLEEP_INTERVAL);
                if (state.isOnline() && state.isAuthenticated()) {
                    sync();
                }
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                break;
            } catch (Exception e) {
                System.err.println("[SyncWorker] Error: " + e.getMessage());
            }
        }
        System.out.println("[SyncWorker] Stopped.");
    }

    private void sync() {
        // 1. Upload pending posts
        uploadPendingPosts();
        
        // 2. Upload pending exclusions
        uploadPendingExclusions();

        // 3. Download new posts
        downloadNewPosts();
    }

    private void uploadPendingPosts() {
        List<DatabaseHandler.OfflinePostChange> pending = DatabaseHandler.getPendingUpstreamPosts();
        if (pending.isEmpty()) return;

        System.out.println("[SyncWorker] Uploading " + pending.size() + " pending posts...");

        // Convert to map list for API
        List<Map<String, Object>> payload = new ArrayList<>();
        for (DatabaseHandler.OfflinePostChange p : pending) {
            Map<String, Object> postMap = new HashMap<>();
            postMap.put("topic_id", p.topicId);
            postMap.put("user_id", p.userId);
            postMap.put("content", p.content);
            postMap.put("is_private", p.isPrivate);
            postMap.put("created_at", p.createdAt);
            payload.add(postMap);
        }

        try {
            List<Map<String, Object>> results = api.syncUpload(payload);
            // results should contain server IDs for each
            for (int i = 0; i < results.size() && i < pending.size(); i++) {
                Object idObj = results.get(i).get("id");
                if (idObj != null) {
                    int serverId = ((Number) idObj).intValue();
                    DatabaseHandler.resolvePendingPostSync(pending.get(i).localId, serverId);
                }
            }
            System.out.println("[SyncWorker] Uploaded " + results.size() + " posts.");
        } catch (Exception e) {
            System.err.println("[SyncWorker] Upload failed: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private void uploadPendingExclusions() {
        // Implementation for uploading exclusions - to be added
        // Similar to uploadPendingPosts but using post_exclusions table
    }

    private void downloadNewPosts() {
        String since = getLastSyncTimestamp();
        try {
            List<Post> posts = api.syncDownload(since);
            if (posts.isEmpty()) return;

            System.out.println("[SyncWorker] Downloading " + posts.size() + " new posts...");

            // Save to local DB (insert with server_id and sync_status='SYNCED')
            try (Connection conn = DatabaseHandler.getConnection();
                 PreparedStatement pstmt = conn.prepareStatement(
                         "INSERT INTO posts (server_id, topic_id, user_id, content, is_private, sync_status, created_at, updated_at) " +
                                 "VALUES (?, ?, ?, ?, ?, 'SYNCED', ?, ?)")) {

                for (Post p : posts) {
                    pstmt.setInt(1, p.id);
                    pstmt.setInt(2, p.topic_id);
                    pstmt.setInt(3, p.user_id);
                    pstmt.setString(4, p.content);
                    pstmt.setInt(5, p.is_private ? 1 : 0);
                    pstmt.setString(6, p.created_at != null ? p.created_at : "");
                    pstmt.setString(7, p.created_at != null ? p.created_at : "");
                    pstmt.executeUpdate();
                }
                System.out.println("[SyncWorker] Downloaded " + posts.size() + " new posts.");
            }
        } catch (Exception e) {
            System.err.println("[SyncWorker] Download failed: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private String getLastSyncTimestamp() {
        try (Connection conn = DatabaseHandler.getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery("SELECT MAX(created_at) FROM posts WHERE sync_status = 'SYNCED'")) {
            if (rs.next() && rs.getString(1) != null) {
                return rs.getString(1);
            }
        } catch (Exception e) {
            // ignore
        }
        return java.time.LocalDateTime.now().minusDays(30).toString();
    }

    public void stop() {
        running = false;
    }
}