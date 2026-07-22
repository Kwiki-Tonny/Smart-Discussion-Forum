package com.forum.models;

import com.forum.services.ApiClient;
import com.google.gson.JsonArray;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.Statement;
import java.util.List;

public class SyncWorker implements Runnable {

    private static final int SLEEP_INTERVAL = 15000; // 15 seconds
    private boolean running = true;
    private final ApiClient apiClient = new ApiClient();

    @Override
    public void run() {
        while (running) {
            try {
                Thread.sleep(SLEEP_INTERVAL);
                if (GlobalState.isOnline() && GlobalState.getToken() != null) {
                    sync();
                }
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                break;
            } catch (Exception e) {
                System.err.println("[SyncWorker] Error: " + e.getMessage());
            }
        }
    }

    private void sync() {
        // 1. Upload pending posts
        List<DatabaseHandler.OfflinePostChange> pending = DatabaseHandler.getPendingUpstreamPosts();
        if (!pending.isEmpty()) {
            uploadPending(pending);
        }

        // 2. Download new posts
        downloadNewPosts();
    }

    private void uploadPending(List<DatabaseHandler.OfflinePostChange> pending) {
        JsonArray arr = new JsonArray();
        for (DatabaseHandler.OfflinePostChange p : pending) {
            JsonObject obj = new JsonObject();
            obj.addProperty("topic_id", p.topicId);
            obj.addProperty("user_id", p.userId);
            obj.addProperty("content", p.content);
            obj.addProperty("is_private", p.isPrivate);
            obj.addProperty("created_at", p.createdAt);
            arr.add(obj);
        }

        apiClient.syncUpload(arr.toString(), new ApiClient.ApiCallback() {
            @Override
            public void onSuccess(String response) {
                try {
                    JsonArray respArr = JsonParser.parseString(response).getAsJsonArray();
                    for (int i = 0; i < respArr.size() && i < pending.size(); i++) {
                        int serverId = respArr.get(i).getAsJsonObject().get("id").getAsInt();
                        DatabaseHandler.resolvePendingPostSync(pending.get(i).localId, serverId);
                    }
                    System.out.println("[SyncWorker] Uploaded " + respArr.size() + " posts.");
                } catch (Exception e) {
                    e.printStackTrace();
                }
            }

            @Override
            public void onError(String message) {
                System.err.println("[SyncWorker] Upload failed: " + message);
            }
        });
    }

    private void downloadNewPosts() {
        String since = getLastSyncTimestamp();
        apiClient.syncDownload(since, new ApiClient.ApiCallback() {
            @Override
            public void onSuccess(String response) {
                try {
                    JsonArray arr = JsonParser.parseString(response).getAsJsonArray();
                    if (arr.size() == 0) return;

                    try (Connection conn = DatabaseHandler.getConnection();
                         PreparedStatement pstmt = conn.prepareStatement(
                             "INSERT INTO posts (server_id, topic_id, user_id, content, is_private, sync_status, created_at, updated_at) " +
                             "VALUES (?, ?, ?, ?, ?, 'SYNCED', ?, ?)")) {

                        for (int i = 0; i < arr.size(); i++) {
                            JsonObject obj = arr.get(i).getAsJsonObject();
                            pstmt.setInt(1, obj.get("id").getAsInt());
                            pstmt.setInt(2, obj.get("topic_id").getAsInt());
                            pstmt.setInt(3, obj.get("user_id").getAsInt());
                            pstmt.setString(4, obj.get("content").getAsString());
                            pstmt.setInt(5, obj.get("is_private").getAsInt());
                            pstmt.setString(6, obj.get("created_at").getAsString());
                            pstmt.setString(7, obj.get("created_at").getAsString());
                            pstmt.executeUpdate();
                        }
                        System.out.println("[SyncWorker] Downloaded " + arr.size() + " new posts.");
                    }
                } catch (Exception e) {
                    e.printStackTrace();
                }
            }

            @Override
            public void onError(String message) {
                System.err.println("[SyncWorker] Download failed: " + message);
            }
        });
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