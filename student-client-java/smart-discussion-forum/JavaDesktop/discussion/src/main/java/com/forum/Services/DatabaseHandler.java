/**
 * Package: com.forum.services
 * 
 * This package contains the core business logic, service-layer components, 
 * and data persistence handlers of the forum application. Classes in this 
 * package are responsible for orchestrating operations, managing external 
 * API communications, and handling local offline-first data storage.
 */
package com.forum.services;

/**
 * Import for the core domain model representing a forum post.
 * Used here to map raw SQLite ResultSet data back into strongly-typed 
 * domain objects for local offline rendering.
 */
import com.forum.models.Post;

import java.io.File;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

/**
 * Core Offline-First Data Persistence Layer for the Forum Application.
 * 
 * This class manages all local SQLite database operations, enabling the 
 * application to function seamlessly in disconnected or low-connectivity 
 * environments. It implements an "Offline-First" architecture pattern, 
 * where user actions (like creating posts or liking content) are immediately 
 * persisted to a local database with a 'PENDING_CREATE' or 'PENDING_UPDATE' 
 * sync status. A background synchronization process later reconciles these 
 * local records with the remote server, updating them to 'SYNCED' and mapping 
 * local surrogate keys to remote server IDs.
 * 
 * Concurrency & Initialization:
 * - Employs the Double-Checked Locking pattern with a volatile boolean flag 
 *   and a dedicated lock object to ensure thread-safe, one-time initialization 
 *   of the database schema, even under heavy concurrent access.
 * - Uses standard JDBC practices with try-with-resources blocks to guarantee 
 *   proper closure of Connections, Statements, and ResultSets, preventing 
 *   resource leaks and database locks.
 * 
 * Schema Design Highlights:
 * - Dual-ID System: Tables like 'posts' maintain both a 'local_id' (auto-incrementing 
 *   primary key for immediate local reference) and a 'server_id' (nullable, unique 
 *   identifier populated only after successful server synchronization).
 * - Soft State Tracking: The 'sync_status' column acts as a state machine, driving 
 *   the background sync engine's decision on which records require upstream propagation.
 */
public class DatabaseHandler {

    /**
     * The relative file path for the local SQLite database.
     * Stored in the application's working directory for easy access and portability.
     */
    private static final String DB_FILE = "forum.db";

    /**
     * The JDBC connection string configured for the SQLite dialect.
     * Instructs the DriverManager to locate or create the database file specified in DB_FILE.
     */
    private static final String CONNECTION_URL = "jdbc:sqlite:" + DB_FILE;

    /**
     * Dedicated lock object for synchronizing database initialization.
     * Using a dedicated object (rather than synchronizing on the class itself) 
     * is a best practice to prevent external code from accidentally acquiring 
     * the same lock and causing deadlocks.
     */
    private static final Object INITIALIZATION_LOCK = new Object();

    /**
     * Volatile flag indicating whether the database schema has been successfully 
     * initialized. The 'volatile' keyword ensures that changes to this variable 
     * are immediately visible to all threads, which is critical for the 
     * double-checked locking pattern to function correctly without race conditions.
     */
    private static volatile boolean databaseInitialized = false;

    /**
     * Static initialization block responsible for loading the SQLite JDBC driver.
     * 
     * Architectural Note: While modern JDBC (4.0+) often auto-discovers drivers 
     * via the Service Provider Interface (SPI), explicitly calling Class.forName 
     * remains a robust fallback to guarantee the driver is registered in the 
     * DriverManager before any connection attempts are made, especially in 
     * constrained or custom classloader environments.
     */
    static {
        try {
            Class.forName("org.sqlite.JDBC");
        } catch (ClassNotFoundException e) {
            System.err.println("[-] Failed to locate SQLite JDBC Driver extension stack.");
            e.printStackTrace();
        }
    }

    /**
     * Retrieves a new, active connection to the local SQLite database.
     * 
     * This method first guarantees that the database schema is initialized 
     * before handing out a connection. Each call returns a fresh connection 
     * instance, which the caller is responsible for closing (typically via 
     * a try-with-resources block).
     *
     * @return A valid java.sql.Connection object.
     * @throws SQLException if the database cannot be accessed or initialized.
     */
    public static Connection getConnection() throws SQLException {
        ensureDatabaseInitialized();
        return DriverManager.getConnection(CONNECTION_URL);
    }

    /**
     * Internal helper method to open a raw database connection.
     * Used primarily during the initial schema setup phase before the 
     * initialization flag is officially set.
     *
     * @return A valid java.sql.Connection object.
     * @throws SQLException if the connection fails.
     */
    private static Connection openConnection() throws SQLException {
        return DriverManager.getConnection(CONNECTION_URL);
    }

    /**
     * Thread-safe guard method to ensure the database schema is initialized 
     * exactly once, regardless of how many threads attempt to access the 
     * database concurrently.
     * 
     * Implements Double-Checked Locking:
     * 1. Fast path: Check the volatile flag without locking.
     * 2. Slow path: If null, acquire the lock and check again to prevent 
     *    duplicate initialization by racing threads.
     */
    private static void ensureDatabaseInitialized() {
        if (databaseInitialized) return;
        synchronized (INITIALIZATION_LOCK) {
            if (databaseInitialized) return;
            initializeDatabase();
            databaseInitialized = true;
        }
    }

    /**
     * Constructs the foundational schema for the offline forum database.
     * 
     * This method executes a series of idempotent "CREATE TABLE IF NOT EXISTS" 
     * statements. It is safe to run multiple times. It also enforces foreign 
     * key constraints via PRAGMA, which is disabled by default in SQLite.
     */
    public static void initializeDatabase() {
        synchronized (INITIALIZATION_LOCK) {
            File dbFile = new File(DB_FILE);
            if (!dbFile.exists()) {
                System.out.println("[+] Local pipeline storage target not found. Initializing forum.db instance...");
            }

            try (Connection conn = openConnection(); Statement stmt = conn.createStatement()) {
                // Enforce referential integrity. SQLite disables this by default.
                stmt.execute("PRAGMA foreign_keys = ON;");

                // 1. USER PROFILES
                // Stores minimal local user data required for offline post attribution.
                stmt.execute("CREATE TABLE IF NOT EXISTS user_profiles (" +
                        "id INTEGER PRIMARY KEY, " +
                        "email TEXT NOT NULL, " +
                        "role TEXT NOT NULL" +
                        ");");

                // 2. GROUPS
                // Caches group metadata locally to allow offline browsing of group structures.
                stmt.execute("CREATE TABLE IF NOT EXISTS groups (" +
                        "id INTEGER PRIMARY KEY, " +
                        "name TEXT NOT NULL, " +
                        "description TEXT, " +
                        "created_at TEXT, " +
                        "updated_at TEXT" +
                        ");");

                // 3. GROUP_USER
                // Junction table tracking user membership and rule acceptance status per group.
                stmt.execute("CREATE TABLE IF NOT EXISTS group_user (" +
                        "group_id INTEGER, " +
                        "user_id INTEGER, " +
                        "has_agreed_rules INTEGER DEFAULT 0, " +
                        "PRIMARY KEY (group_id, user_id), " +
                        "FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE" +
                        ");");

                // 4. TOPICS
                // Caches topic headers locally. Cascading deletes ensure orphaned topics 
                // are removed if their parent group is deleted.
                stmt.execute("CREATE TABLE IF NOT EXISTS topics (" +
                        "id INTEGER PRIMARY KEY, " +
                        "group_id INTEGER, " +
                        "title TEXT NOT NULL, " +
                        "creator_id INTEGER, " +
                        "created_at TEXT, " +
                        "updated_at TEXT, " +
                        "FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE, " +
                        "FOREIGN KEY (creator_id) REFERENCES user_profiles(id)" +
                        ");");

                // 5. POSTS
                // The core offline-first table. Uses 'local_id' for immediate local reference 
                // and 'server_id' (nullable) to map to the remote database after sync.
                // 'sync_status' drives the background uploader ('PENDING_CREATE', 'SYNCED').
                stmt.execute("CREATE TABLE IF NOT EXISTS posts (" +
                        "local_id INTEGER PRIMARY KEY AUTOINCREMENT, " +
                        "server_id INTEGER UNIQUE, " +
                        "topic_id INTEGER NOT NULL, " +
                        "user_id INTEGER NOT NULL, " +
                        "content TEXT NOT NULL, " +
                        "is_private INTEGER DEFAULT 0, " +
                        "sync_status TEXT NOT NULL DEFAULT 'SYNCED', " +
                        "parent_id INTEGER, " +
                        "created_at TEXT NOT NULL, " +
                        "updated_at TEXT, " +
                        "FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE, " +
                        "FOREIGN KEY (user_id) REFERENCES user_profiles(id), " +
                        "FOREIGN KEY (parent_id) REFERENCES posts(local_id) ON DELETE CASCADE" +
                        ");");

                // 6. POST_EXCLUSIONS
                // Supports the 'is_private' feature by tracking which specific users 
                // are explicitly blocked from viewing a given post.
                stmt.execute("CREATE TABLE IF NOT EXISTS post_exclusions (" +
                        "local_id INTEGER PRIMARY KEY AUTOINCREMENT, " +
                        "server_id INTEGER UNIQUE, " +
                        "post_id INTEGER NOT NULL, " +
                        "excluded_user_id INTEGER NOT NULL, " +
                        "sync_status TEXT NOT NULL DEFAULT 'SYNCED', " +
                        "created_at TEXT NOT NULL, " +
                        "FOREIGN KEY (post_id) REFERENCES posts(local_id) ON DELETE CASCADE, " +
                        "FOREIGN KEY (excluded_user_id) REFERENCES user_profiles(id) ON DELETE CASCADE" +
                        ");");

                // 7. PENDING_LIKES
                // Queues user like/unlike actions performed while offline. 
                // The 'liked' integer acts as a boolean (1 = liked, 0 = unliked).
                stmt.execute("CREATE TABLE IF NOT EXISTS pending_likes (" +
                        "id INTEGER PRIMARY KEY AUTOINCREMENT, " +
                        "post_id INTEGER NOT NULL, " +
                        "user_id INTEGER NOT NULL, " +
                        "liked INTEGER NOT NULL, " +
                        "sync_status TEXT NOT NULL DEFAULT 'PENDING_CREATE', " +
                        "created_at TEXT NOT NULL" +
                        ");");

                databaseInitialized = true;
                System.out.println("[+] Embedded forum.db structure verified with pending_likes and local post support.");

            } catch (SQLException e) {
                System.err.println("[-] Critical structural configuration exception inside SQLite engine:");
                e.printStackTrace();
            }
        }
    }

    // =========================================================================
    //  OFFLINE POSTS MANAGEMENT
    //  Handles local drafting, retrieval, and sync-state resolution of posts.
    // =========================================================================

    /**
     * Convenience overload for saving an offline post draft without a parent ID.
     * Delegates to the primary method, passing null for the parentId.
     *
     * @param topicId   The ID of the topic the post belongs to.
     * @param userId    The ID of the user creating the post.
     * @param content   The text content of the post.
     * @param isPrivate Boolean flag indicating restricted visibility.
     * @param timestamp The ISO-8601 creation timestamp.
     * @return true if the draft was successfully saved, false otherwise.
     */
    public static boolean saveOfflinePostDraft(int topicId, int userId, String content, boolean isPrivate, String timestamp) {
        return saveOfflinePostDraft(topicId, userId, content, isPrivate, timestamp, null);
    }

    /**
     * Persists a new post or reply to the local database with a 'PENDING_CREATE' status.
     * This enables the user to continue interacting with the app while offline, 
     * with the assurance that the data will be synced when connectivity is restored.
     *
     * @param topicId   The ID of the topic the post belongs to.
     * @param userId    The ID of the user creating the post.
     * @param content   The text content of the post.
     * @param isPrivate Boolean flag indicating restricted visibility.
     * @param timestamp The ISO-8601 creation timestamp.
     * @param parentId  The local_id of the parent post (if this is a reply), or null.
     * @return true if the draft was successfully saved, false if a SQLException occurred.
     */
    public static boolean saveOfflinePostDraft(int topicId, int userId, String content,
                                               boolean isPrivate, String timestamp, Integer parentId) {
        String sql = "INSERT INTO posts (topic_id, user_id, content, is_private, sync_status, created_at, parent_id) " +
                     "VALUES (?, ?, ?, ?, 'PENDING_CREATE', ?, ?);";
        try (Connection conn = getConnection(); PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setInt(1, topicId);
            pstmt.setInt(2, userId);
            pstmt.setString(3, content);
            pstmt.setInt(4, isPrivate ? 1 : 0); // Map boolean to SQLite INTEGER (1/0)
            pstmt.setString(5, timestamp);
            if (parentId != null) {
                pstmt.setInt(6, parentId);
            } else {
                pstmt.setNull(6, java.sql.Types.INTEGER);
            }
            pstmt.executeUpdate();
            return true;
        } catch (SQLException e) {
            System.err.println("[-] Failed to capture offline post draft:");
            e.printStackTrace();
            return false;
        }
    }

    /**
     * Retrieves all posts that have been created locally but not yet synchronized 
     * with the remote server.
     * 
     * @return A list of OfflinePostChange DTOs representing pending upstream posts.
     */
    public static List<OfflinePostChange> getPendingUpstreamPosts() {
        List<OfflinePostChange> changes = new ArrayList<>();
        String sql = "SELECT local_id, topic_id, user_id, content, is_private, created_at, parent_id FROM posts WHERE sync_status = 'PENDING_CREATE';";
        try (Connection conn = getConnection(); Statement stmt = conn.createStatement(); ResultSet rs = stmt.executeQuery(sql)) {
            while (rs.next()) {
                changes.add(new OfflinePostChange(
                        rs.getInt("local_id"),
                        rs.getInt("topic_id"),
                        rs.getInt("user_id"),
                        rs.getString("content"),
                        rs.getInt("is_private") == 1,
                        rs.getString("created_at"),
                        rs.getObject("parent_id") != null ? rs.getInt("parent_id") : null
                ));
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return changes;
    }

    /**
     * Updates a locally saved post to reflect successful synchronization with the server.
     * Assigns the remote server's ID to the record and changes the sync_status to 'SYNCED'.
     *
     * @param localId  The local_id of the post to update.
     * @param serverId The newly assigned server_id from the remote API response.
     */
    public static void resolvePendingPostSync(int localId, int serverId) {
        String sql = "UPDATE posts SET server_id = ?, sync_status = 'SYNCED' WHERE local_id = ?;";
        try (Connection conn = getConnection(); PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setInt(1, serverId);
            pstmt.setInt(2, localId);
            pstmt.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    // =========================================================================
    //  OFFLINE LIKES MANAGEMENT
    //  Handles queuing and resolving of user like/unlike actions performed offline.
    // =========================================================================

    /**
     * Records a user's like or unlike action on a post while offline.
     * The action is queued with a 'PENDING_CREATE' status for later batch synchronization.
     *
     * @param postId The local_id (or server_id) of the target post.
     * @param userId The ID of the user performing the action.
     * @param liked  true if the user liked the post, false if they unliked it.
     * @return true if the action was successfully queued, false otherwise.
     */
    public static boolean saveOfflineLike(int postId, int userId, boolean liked) {
        String sql = "INSERT INTO pending_likes (post_id, user_id, liked, sync_status, created_at) " +
                     "VALUES (?, ?, ?, 'PENDING_CREATE', ?)";
        try (Connection conn = getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setInt(1, postId);
            pstmt.setInt(2, userId);
            pstmt.setInt(3, liked ? 1 : 0); // Map boolean to SQLite INTEGER (1/0)
            pstmt.setString(4, LocalDateTime.now().toString());
            pstmt.executeUpdate();
            return true;
        } catch (SQLException e) {
            System.err.println("[-] Failed to save offline like:");
            e.printStackTrace();
            return false;
        }
    }

    /**
     * Retrieves all queued like/unlike actions that have not yet been synchronized 
     * with the remote server.
     *
     * @return A list of PendingLike DTOs representing pending upstream like actions.
     */
    public static List<PendingLike> getPendingUpstreamLikes() {
        List<PendingLike> likes = new ArrayList<>();
        String sql = "SELECT id, post_id, user_id, liked FROM pending_likes WHERE sync_status = 'PENDING_CREATE'";
        try (Connection conn = getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {
            while (rs.next()) {
                likes.add(new PendingLike(
                        rs.getInt("id"),
                        rs.getInt("post_id"),
                        rs.getInt("user_id"),
                        rs.getInt("liked") == 1
                ));
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return likes;
    }

    /**
     * Marks a specific queued like action as successfully synchronized.
     *
     * @param localId The primary key 'id' of the record in the pending_likes table.
     */
    public static void resolvePendingLikeSync(int localId) {
        String sql = "UPDATE pending_likes SET sync_status = 'SYNCED' WHERE id = ?";
        try (Connection conn = getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setInt(1, localId);
            pstmt.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    // =========================================================================
    //  LOCAL RETRIEVAL FOR OFFLINE USE
    //  Provides methods to render cached data when the network is unavailable.
    // =========================================================================

    /**
     * Retrieves all locally stored posts (both synced and pending) for a specific topic.
     * This is the primary method used to render a topic's discussion thread when 
     * the application is in offline mode.
     *
     * @param topicId The ID of the topic to fetch posts for.
     * @return A list of Post domain objects, ordered chronologically by creation time.
     */
    public static List<Post> getLocalPostsForTopic(int topicId) {
        List<Post> posts = new ArrayList<>();
        String sql = "SELECT local_id, server_id, topic_id, user_id, content, is_private, created_at, parent_id " +
                     "FROM posts WHERE topic_id = ? ORDER BY created_at ASC";
        try (Connection conn = getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setInt(1, topicId);
            ResultSet rs = pstmt.executeQuery();
            while (rs.next()) {
                Post post = new Post();
                // Use local_id as the main id (server_id may be null offline)
                post.id = rs.getInt("local_id");
                post.topic_id = rs.getInt("topic_id");
                post.user_id = rs.getInt("user_id");
                post.content = rs.getString("content");
                post.is_private = rs.getInt("is_private") == 1;
                post.created_at = rs.getString("created_at");
                
                // Safely handle nullable parent_id for threaded replies
                int parentId = rs.getInt("parent_id");
                if (!rs.wasNull()) {
                    post.parentId = parentId;
                }
                
                // Note: Author info is not stored in this simplified local cache. 
                // The UI layer is expected to handle missing author data gracefully (e.g., as "Unknown").
                posts.add(post);
            }
        } catch (SQLException e) {
            System.err.println("[-] Failed to get local posts for topic " + topicId);
            e.printStackTrace();
        }
        return posts;
    }

    /**
     * Alternative post-saving method that returns the newly generated local_id.
     * This is useful when the application needs to immediately reference the 
     * newly created post (e.g., to link a reply to it, or to update the UI 
     * optimistically before the server sync occurs).
     *
     * @param topicId   The ID of the topic.
     * @param userId    The ID of the user.
     * @param content   The post content.
     * @param isPrivate Boolean flag for restricted visibility.
     * @param timestamp The creation timestamp.
     * @param parentId  The local_id of the parent post, or null/negative if top-level.
     * @return The auto-generated local_id of the new post, or -1 if the operation failed.
     */
    public static int saveOfflinePostDraftAndGetId(int topicId, int userId, String content, boolean isPrivate, String timestamp, Integer parentId) {
        String sql = "INSERT INTO posts (topic_id, user_id, content, is_private, sync_status, created_at, parent_id) " +
                    "VALUES (?, ?, ?, ?, 'PENDING_CREATE', ?, ?);";
        try (Connection conn = getConnection(); PreparedStatement pstmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            pstmt.setInt(1, topicId);
            pstmt.setInt(2, userId);
            pstmt.setString(3, content);
            pstmt.setInt(4, isPrivate ? 1 : 0);
            pstmt.setString(5, timestamp);
            if (parentId != null && parentId > 0) {
                pstmt.setInt(6, parentId);
            } else {
                pstmt.setNull(6, java.sql.Types.INTEGER);
            }
            pstmt.executeUpdate();
            
            // Retrieve the auto-incremented local_id
            ResultSet rs = pstmt.getGeneratedKeys();
            if (rs.next()) {
                return rs.getInt(1);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return -1;
    }

    // =========================================================================
    //  DATA TRANSFER OBJECTS (DTOs)
    //  Internal classes used to bridge SQLite ResultSets and the API Sync Layer.
    // =========================================================================

    /**
     * Immutable Data Transfer Object representing a post that exists locally 
     * but has not yet been synchronized with the remote server.
     * 
     * Instances of this class are generated by {@link #getPendingUpstreamPosts()} 
     * and consumed by the background synchronization service to construct the 
     * JSON payload for the upstream API call.
     */
    public static class OfflinePostChange {
        public final int localId;
        public final int topicId;
        public final int userId;
        public final String content;
        public final boolean isPrivate;
        public final String createdAt;
        public final Integer parentId;

        /**
         * Constructs a new OfflinePostChange DTO.
         */
        public OfflinePostChange(int localId, int topicId, int userId, String content, boolean isPrivate, String createdAt, Integer parentId) {
            this.localId = localId;
            this.topicId = topicId;
            this.userId = userId;
            this.content = content;
            this.isPrivate = isPrivate;
            this.createdAt = createdAt;
            this.parentId = parentId;
        }
    }

    /**
     * Immutable Data Transfer Object representing a queued like/unlike action 
     * performed while offline.
     * 
     * Instances of this class are generated by {@link #getPendingUpstreamLikes()} 
     * and consumed by the background synchronization service to replay the 
     * user's interaction state to the remote server.
     */
    public static class PendingLike {
        public final int id;
        public final int postId;
        public final int userId;
        public final boolean liked;

        /**
         * Constructs a new PendingLike DTO.
         */
        public PendingLike(int id, int postId, int userId, boolean liked) {
            this.id = id;
            this.postId = postId;
            this.userId = userId;
            this.liked = liked;
        }
    }
}