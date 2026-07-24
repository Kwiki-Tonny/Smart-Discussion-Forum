package com.forum.services;

import com.forum.services.DatabaseHandler;
import com.forum.services.GlobalState;
import com.forum.models.Post;
import com.forum.services.ApiService;
import com.google.gson.JsonArray;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.Statement;
import java.util.*;

public class SyncWorker implements Runnable {

    private static final int SLEEP_INTERVAL = 15000;
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
        uploadPendingPosts();
        uploadPendingLikes();
        downloadNewPosts();
    }

    private void uploadPendingPosts() {
        List<DatabaseHandler.OfflinePostChange> pending = DatabaseHandler.getPendingUpstreamPosts();
        if (pending.isEmpty()) return;

        System.out.println("[SyncWorker] Uploading " + pending.size() + " pending posts...");

        List<Map<String, Object>> payload = new ArrayList<>();
        for (DatabaseHandler.OfflinePostChange p : pending) {
            Map<String, Object> postMap = new HashMap<>();
            postMap.put("topic_id", p.topicId);
            postMap.put("user_id", p.userId);
            postMap.put("content", p.content);
            postMap.put("is_private", p.isPrivate);
            postMap.put("created_at", p.createdAt);
            if (p.parentId != null) {
                postMap.put("parent_id", p.parentId);
            }
            payload.add(postMap);
        }

        try {
            List<Map<String, Object>> results = api.syncUpload(payload);
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

    private void uploadPendingLikes() {
        List<DatabaseHandler.PendingLike> pending = DatabaseHandler.getPendingUpstreamLikes();
        if (pending.isEmpty()) return;

        System.out.println("[SyncWorker] Uploading " + pending.size() + " pending likes...");

        for (DatabaseHandler.PendingLike like : pending) {
            try {
                api.toggleLike(like.postId);
                DatabaseHandler.resolvePendingLikeSync(like.id);
            } catch (Exception e) {
                System.err.println("[SyncWorker] Failed to upload like for post " + like.postId + ": " + e.getMessage());
            }
        }
    }

    private void downloadNewPosts() {
        String since = getLastSyncTimestamp();
        try {
            List<Post> posts = api.syncDownload(since);
            if (posts.isEmpty()) return;

            System.out.println("[SyncWorker] Downloading " + posts.size() + " new posts...");

            try (Connection conn = DatabaseHandler.getConnection()) {
                // Check existing server_ids
                Set<Integer> existingServerIds = new HashSet<>();
                String checkSql = "SELECT server_id FROM posts WHERE server_id IS NOT NULL";
                try (Statement stmt = conn.createStatement();
                     ResultSet rs = stmt.executeQuery(checkSql)) {
                    while (rs.next()) {
                        existingServerIds.add(rs.getInt("server_id"));
                    }
                }

                String insertSql = "INSERT INTO posts (server_id, topic_id, user_id, content, is_private, sync_status, created_at, updated_at) " +
                                   "VALUES (?, ?, ?, ?, ?, 'SYNCED', ?, ?)";
                try (PreparedStatement pstmt = conn.prepareStatement(insertSql)) {
                    int count = 0;
                    for (Post p : posts) {
                        if (existingServerIds.contains(p.id)) {
                            continue;
                        }
                        pstmt.setInt(1, p.id);
                        pstmt.setInt(2, p.topic_id);
                        pstmt.setInt(3, p.user_id);
                        pstmt.setString(4, p.content);
                        pstmt.setInt(5, p.is_private ? 1 : 0);
                        pstmt.setString(6, p.created_at != null ? p.created_at : "");
                        pstmt.setString(7, p.created_at != null ? p.created_at : "");
                        pstmt.executeUpdate();
                        count++;
                    }
                    System.out.println("[SyncWorker] Downloaded " + count + " new posts.");
                }
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