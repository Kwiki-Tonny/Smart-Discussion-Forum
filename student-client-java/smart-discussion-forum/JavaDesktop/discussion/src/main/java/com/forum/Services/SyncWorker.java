/**
 * Package: com.forum.services
 * 
 * This package contains the core business logic, service-layer components, 
 * and state management utilities of the forum application. Classes in this 
 * package are responsible for orchestrating operations, managing external 
 * API communications, handling local offline-first data storage, and 
 * executing background reconciliation tasks to keep local and remote 
 * data stores in sync.
 */
package com.forum.services;

/**
 * Internal service dependencies required for local data access and API communication.
 */
import com.forum.services.DatabaseHandler;
import com.forum.services.GlobalState;
import com.forum.services.ApiService;

/**
 * Domain model representing a forum post, used here to map downloaded 
 * server-side data into local database records.
 */
import com.forum.models.Post;

/**
 * Google GSON library imports.
 * 
 * Architectural Note: While the current implementation relies on the ApiService 
 * to handle JSON deserialization into Maps/Objects, these imports are retained 
 * to support potential future enhancements requiring direct, low-level JSON 
 * tree manipulation or custom serialization logic within the sync pipeline.
 */
import com.google.gson.JsonArray;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;

/**
 * Standard Java SQL and Utility classes for database operations and 
 * collection management during the synchronization process.
 */
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.Statement;
import java.util.*;

/**
 * Background Data Reconciliation Engine (Bi-Directional Sync Worker).
 * 
 * This class implements the {@link Runnable} interface to operate as a dedicated 
 * background worker thread. Its primary responsibility is to continuously 
 * reconcile the local offline-first SQLite database with the remote backend server, 
 * ensuring data consistency across disconnected and reconnected states.
 * 
 * Key Architectural Patterns Employed:
 * 1. Bi-Directional Sync Strategy: The worker strictly follows an "Upload First, 
 *    Download Second" order. Uploading pending local changes first ensures that 
 *    the server has the latest client state before the client pulls down new 
 *    server-side data, minimizing conflict resolution complexity.
 * 2. Idempotency & Deduplication: The download phase utilizes a HashSet of 
 *    existing server_ids to prevent duplicate record insertion, ensuring that 
 *    running the sync process multiple times yields the same final database state.
 * 3. Cooperative Cancellation: The worker uses a boolean control flag (`running`) 
 *    combined with standard Thread interruption handling to allow the application 
 *    to gracefully shut down the background thread without data corruption.
 * 4. Conditional Execution: Sync operations are strictly gated behind both 
 *    network availability ({@link GlobalState#isOnline()}) and user authentication 
 *    ({@link GlobalState#isAuthenticated()}), preventing futile network calls 
 *    or unauthorized data exposure.
 */
public class SyncWorker implements Runnable {

    /**
     * The interval between consecutive synchronization cycles, in milliseconds.
     * 
     * Value: 15,000 ms (15 seconds).
     * Rationale: This interval provides a responsive experience for users 
     * transitioning from offline to online (their pending posts appear quickly), 
     * while remaining infrequent enough to avoid placing unnecessary load on 
     * the backend server or draining client battery life.
     */
    private static final int SLEEP_INTERVAL = 15000;

    /**
     * Control flag indicating whether the background worker should continue running.
     * 
     * Set to false via the {@link #stop()} method to initiate a graceful shutdown 
     * of the synchronization loop.
     */
    private boolean running = true;

    /**
     * Singleton instance of the API service, used to execute HTTP requests 
     * for uploading pending changes and downloading new server data.
     */
    private final ApiService api = ApiService.getInstance();

    /**
     * Singleton instance of the global state manager, used to gate sync 
     * operations based on current network connectivity and authentication status.
     */
    private final GlobalState state = GlobalState.getInstance();

    /**
     * The main execution loop for the background synchronization worker.
     * 
     * This method runs continuously, waking up at fixed intervals to check if 
     * conditions are met for a sync cycle. It handles thread interruption 
     * gracefully to support clean application shutdown.
     */
    @Override
    public void run() {
        System.out.println("[SyncWorker] Started.");
        
        while (running) {
            try {
                // Park the thread to enforce the polling interval and prevent CPU spinning.
                Thread.sleep(SLEEP_INTERVAL);
                
                // Gate the sync operation: only proceed if the network is reachable 
                // and the user has a valid, authenticated session.
                if (state.isOnline() && state.isAuthenticated()) {
                    sync();
                }
            } catch (InterruptedException e) {
                // Standard Java practice: restore the interrupted status of the thread 
                // to ensure that higher-level frameworks or thread pools can also 
                // react to the cancellation request, then break the loop to terminate.
                Thread.currentThread().interrupt();
                break;
            } catch (Exception e) {
                // Catching broad exceptions here ensures that a transient error in one 
                // sync cycle (e.g., a temporary API timeout) does not crash the 
                // background worker thread, allowing it to retry on the next interval.
                System.err.println("[SyncWorker] Error: " + e.getMessage());
            }
        }
        
        System.out.println("[SyncWorker] Stopped.");
    }

    /**
     * Orchestrates the bi-directional synchronization process.
     * 
     * Execution Order is critical:
     * 1. uploadPendingPosts(): Pushes local drafts to the server to obtain server IDs.
     * 2. uploadPendingLikes(): Pushes local interaction states to the server.
     * 3. downloadNewPosts(): Pulls down any new or updated posts from the server, 
     *    ensuring the local cache reflects the latest global state.
     */
    private void sync() {
        uploadPendingPosts();
        uploadPendingLikes();
        downloadNewPosts();
    }

    /**
     * Retrieves locally queued posts and uploads them to the remote server.
     * 
     * Upon successful upload, the server's response (containing the newly assigned 
     * server_id) is used to update the local SQLite database, transitioning the 
     * record's sync_status from 'PENDING_CREATE' to 'SYNCED'.
     */
    private void uploadPendingPosts() {
        // Fetch all posts marked with sync_status = 'PENDING_CREATE'
        List<DatabaseHandler.OfflinePostChange> pending = DatabaseHandler.getPendingUpstreamPosts();
        if (pending.isEmpty()) return;

        System.out.println("[SyncWorker] Uploading " + pending.size() + " pending posts...");

        // Transform local DTOs into the Map-based payload structure expected by the ApiService
        List<Map<String, Object>> payload = new ArrayList<>();
        for (DatabaseHandler.OfflinePostChange p : pending) {
            Map<String, Object> postMap = new HashMap<>();
            postMap.put("topic_id", p.topicId);
            postMap.put("user_id", p.userId);
            postMap.put("content", p.content);
            postMap.put("is_private", p.isPrivate);
            postMap.put("created_at", p.createdAt);
            
            // Conditionally include parent_id only for replies to maintain a clean payload
            if (p.parentId != null) {
                postMap.put("parent_id", p.parentId);
            }
            payload.add(postMap);
        }

        try {
            // Execute the batch upload via the API service
            List<Map<String, Object>> results = api.syncUpload(payload);
            
            // Reconcile the server's response with the local database
            for (int i = 0; i < results.size() && i < pending.size(); i++) {
                Object idObj = results.get(i).get("id");
                if (idObj != null) {
                    // Safe casting from generic Object to Number, then to int, 
                    // to handle potential JSON integer/long variations gracefully.
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

    /**
     * Retrieves locally queued like/unlike actions and replays them to the server.
     * 
     * Each pending action is processed individually. Upon successful API response, 
     * the local record is marked as 'SYNCED' to prevent duplicate processing.
     */
    private void uploadPendingLikes() {
        List<DatabaseHandler.PendingLike> pending = DatabaseHandler.getPendingUpstreamLikes();
        if (pending.isEmpty()) return;

        System.out.println("[SyncWorker] Uploading " + pending.size() + " pending likes...");

        for (DatabaseHandler.PendingLike like : pending) {
            try {
                // Replay the user's interaction state to the server
                api.toggleLike(like.postId);
                
                // Mark the local queue item as resolved
                DatabaseHandler.resolvePendingLikeSync(like.id);
            } catch (Exception e) {
                // Log the failure but continue processing the remaining queue items.
                // A single failed like should not block the synchronization of others.
                System.err.println("[SyncWorker] Failed to upload like for post " + like.postId + ": " + e.getMessage());
            }
        }
    }

    /**
     * Downloads new or updated posts from the server since the last successful sync.
     * 
     * This method implements an incremental delta-sync strategy to minimize 
     * bandwidth usage and database write operations. It employs a HashSet 
     * deduplication check to guarantee idempotency.
     */
    private void downloadNewPosts() {
        // Determine the temporal boundary for the download query
        String since = getLastSyncTimestamp();
        
        try {
            List<Post> posts = api.syncDownload(since);
            if (posts.isEmpty()) return;

            System.out.println("[SyncWorker] Downloading " + posts.size() + " new posts...");

            try (Connection conn = DatabaseHandler.getConnection()) {
                // Phase 1: Build a fast-lookup set of server_ids already present in the local DB.
                // This prevents UniqueConstraint violations or duplicate rows on subsequent syncs.
                Set<Integer> existingServerIds = new HashSet<>();
                String checkSql = "SELECT server_id FROM posts WHERE server_id IS NOT NULL";
                try (Statement stmt = conn.createStatement();
                     ResultSet rs = stmt.executeQuery(checkSql)) {
                    while (rs.next()) {
                        existingServerIds.add(rs.getInt("server_id"));
                    }
                }

                // Phase 2: Prepare the idempotent insert statement for new records.
                // Downloaded records are immediately marked as 'SYNCED' since they originate from the server.
                String insertSql = "INSERT INTO posts (server_id, topic_id, user_id, content, is_private, sync_status, created_at, updated_at) " +
                                   "VALUES (?, ?, ?, ?, ?, 'SYNCED', ?, ?)";
                try (PreparedStatement pstmt = conn.prepareStatement(insertSql)) {
                    int count = 0;
                    for (Post p : posts) {
                        // Skip records that have already been synced locally
                        if (existingServerIds.contains(p.id)) {
                            continue;
                        }
                        
                        pstmt.setInt(1, p.id);
                        pstmt.setInt(2, p.topic_id);
                        pstmt.setInt(3, p.user_id);
                        pstmt.setString(4, p.content);
                        pstmt.setInt(5, p.is_private ? 1 : 0); // Map boolean to SQLite INTEGER (1/0)
                        
                        // Fallback to empty string if created_at is unexpectedly null, 
                        // satisfying the NOT NULL constraint in the local schema.
                        String safeCreatedAt = p.created_at != null ? p.created_at : "";
                        pstmt.setString(6, safeCreatedAt);
                        
                        // Use created_at as a placeholder for updated_at for newly downloaded posts
                        pstmt.setString(7, safeCreatedAt);
                        
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

    /**
     * Determines the temporal starting point for the next download sync.
     * 
     * Queries the local database for the most recent 'SYNCED' post's creation 
     * timestamp. If the table is empty or an error occurs, it safely defaults 
     * to 30 days ago to ensure a reasonable sync window without overwhelming 
     * the server with a request for the entire historical dataset.
     *
     * @return An ISO-8601 formatted timestamp string representing the sync boundary.
     */
    private String getLastSyncTimestamp() {
        try (Connection conn = DatabaseHandler.getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery("SELECT MAX(created_at) FROM posts WHERE sync_status = 'SYNCED'")) {
            
            if (rs.next() && rs.getString(1) != null) {
                return rs.getString(1);
            }
        } catch (Exception e) {
            // Silently ignore database errors during timestamp retrieval and fall back 
            // to the default 30-day window to ensure the sync process can still attempt to run.
        }
        
        // Default fallback: 30 days prior to the current moment.
        return java.time.LocalDateTime.now().minusDays(30).toString();
    }

    /**
     * Signals the background worker to terminate its execution loop.
     * 
     * This is a cooperative cancellation method. It sets the control flag to false, 
     * allowing the current sleep cycle to finish and the while loop to exit naturally 
     * on the next iteration, ensuring no database transactions are abruptly severed.
     */
    public void stop() {
        running = false;
    }
}