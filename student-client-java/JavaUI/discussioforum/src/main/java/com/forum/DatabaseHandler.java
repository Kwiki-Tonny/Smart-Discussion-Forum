package com.forum;
import java.io.File;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;

/**
 * DatabaseHandler manages the embedded local SQLite database (forum.db).
 * Enforces an offline-first architecture by tracking data mutation streams
 * using synchronization flags, bypassing server-side framework infrastructure.
 */

public class DatabaseHandler {
    private static final String DB_FILE = "forum.db";
    private static final String CONNECTION_URL = "jdbc:sqlite:" + DB_FILE;
    private static final Object INITIALIZATION_LOCK = new Object();
    private static volatile boolean databaseInitialized = false;

    static {
        // Explicitly load the Xerial SQLite JDBC Driver extension stack
        try {
            Class.forName("org.sqlite.JDBC");
        } catch (ClassNotFoundException e) {
            System.err.println("[-] Failed to locate SQLite JDBC Driver extension stack.");
            e.printStackTrace();
        }
    }

    /**
     * Establishes a raw connection context to the local database file.
     */
    public static Connection getConnection() throws SQLException {
        ensureDatabaseInitialized();
        return DriverManager.getConnection(CONNECTION_URL);
    }

    private static Connection openConnection() throws SQLException {
        return DriverManager.getConnection(CONNECTION_URL);
    }

    private static void ensureDatabaseInitialized() {
        if (databaseInitialized) {
            return;
        }

        synchronized (INITIALIZATION_LOCK) {
            if (databaseInitialized) {
                return;
            }
            initializeDatabase();
            databaseInitialized = true;
        }
    }

    /**
     * Initializes the local schema layout. Creates only the necessary tables,
     * including the content security privacy tracking tables.
     */
    public static void initializeDatabase() {
        synchronized (INITIALIZATION_LOCK) {
            File dbFile = new File(DB_FILE);
            boolean exists = dbFile.exists();

            if (!exists) {
                System.out.println("[+] Local pipeline storage target not found. Initializing forum.db instance...");
            }

            try (Connection conn = openConnection(); Statement stmt = conn.createStatement()) {
                // Enforce foreign key constraints inside the SQLite engine
                stmt.execute("PRAGMA foreign_keys = ON;");

                /*
                 * 1. USER PROFILES CACHE TABLE
                 * Houses only display and role attributes for cached authors.
                 */
                stmt.execute("CREATE TABLE IF NOT EXISTS user_profiles (" +
                        "id INTEGER PRIMARY KEY, " + // Directly maps to Central MySQL User ID
                        "email TEXT NOT NULL, " +
                        "role TEXT NOT NULL" +
                        ");");

                /*
                 * 2. DISCUSSION GROUPS TABLE
                 */
                stmt.execute("CREATE TABLE IF NOT EXISTS groups (" +
                        "id INTEGER PRIMARY KEY, " + // Central MySQL Server ID
                        "name TEXT NOT NULL, " +
                        "description TEXT, " +
                        "created_at TEXT, " +
                        "updated_at TEXT" +
                        ");");

                /*
                 * 3. GROUP MEMBERSHIP JOIN MAP
                 */
                stmt.execute("CREATE TABLE IF NOT EXISTS group_user (" +
                        "group_id INTEGER, " +
                        "user_id INTEGER, " +
                        "has_agreed_rules INTEGER DEFAULT 0, " + // 0 = False, 1 = True
                        "PRIMARY KEY (group_id, user_id), " +
                        "FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE" +
                        ");");

                /*
                 * 4. DISCUSSION TOPICS (THREADS) TABLE
                 */
                stmt.execute("CREATE TABLE IF NOT EXISTS topics (" +
                        "id INTEGER PRIMARY KEY, " + // Central MySQL Server ID
                        "group_id INTEGER, " +
                        "title TEXT NOT NULL, " +
                        "creator_id INTEGER, " +
                        "created_at TEXT, " +
                        "updated_at TEXT, " +
                        "FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE, " +
                        "FOREIGN KEY (creator_id) REFERENCES user_profiles(id)" +
                        ");");

                /*
                 * 5. POSTS / REPLIES TABLE
                 * Includes the 'is_private' column to trigger content security policies.
                 */
                stmt.execute("CREATE TABLE IF NOT EXISTS posts (" +
                        "local_id INTEGER PRIMARY KEY AUTOINCREMENT, " +
                        "server_id INTEGER UNIQUE, " +                   // Nullable while offline
                        "topic_id INTEGER NOT NULL, " +
                        "user_id INTEGER NOT NULL, " +
                        "content TEXT NOT NULL, " +
                        "is_private INTEGER DEFAULT 0, " +               // 0 = Public, 1 = Private (Triggers exclusions)
                        "sync_status TEXT NOT NULL DEFAULT 'SYNCED', " + // 'PENDING_CREATE', 'SYNCED'
                        "created_at TEXT NOT NULL, " +
                        "updated_at TEXT, " +
                        "FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE, " +
                        "FOREIGN KEY (user_id) REFERENCES user_profiles(id)" +
                        ");");

                /*
                 * 6. POST EXCLUSIONS PRIVACY TABLE (ADDED)
                 * Tracks which users are blacklisted/excluded from viewing a specific private post.
                 * Sync mechanics allow local enforcement of timeline isolation policies.
                 */
                stmt.execute("CREATE TABLE IF NOT EXISTS post_exclusions (" +
                        "local_id INTEGER PRIMARY KEY AUTOINCREMENT, " +
                        "server_id INTEGER UNIQUE, " +                   // Nullable until confirmed by central MySQL DB
                        "post_id INTEGER NOT NULL, " +                   // References local_id of the post
                        "excluded_user_id INTEGER NOT NULL, " +          // Binds to user_profiles.id
                        "sync_status TEXT NOT NULL DEFAULT 'SYNCED', " + // 'PENDING_CREATE', 'SYNCED'
                        "created_at TEXT NOT NULL, " +
                        "FOREIGN KEY (post_id) REFERENCES posts(local_id) ON DELETE CASCADE, " +
                        "FOREIGN KEY (excluded_user_id) REFERENCES user_profiles(id) ON DELETE CASCADE" +
                        ");");

                databaseInitialized = true;
                System.out.println("[+] Embedded forum.db structure verified and secured with Privacy Layer.");

            } catch (SQLException e) {
                System.err.println("[-] Critical structural configuration exception inside SQLite engine:");
                e.printStackTrace();
            }
        }
    }

    /**
     * Saves an offline discussion entry draft locally, capturing privacy markers.
     */
    public static boolean saveOfflinePostDraft(int topicId, int userId, String content, boolean isPrivate, String timestamp) {
        String sql = "INSERT INTO posts (topic_id, user_id, content, is_private, sync_status, created_at) VALUES (?, ?, ?, ?, 'PENDING_CREATE', ?);";
        
        try (Connection conn = getConnection(); PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setInt(1, topicId);
            pstmt.setInt(2, userId);
            pstmt.setString(3, content);
            pstmt.setInt(4, isPrivate ? 1 : 0);
            pstmt.setString(5, timestamp);
            pstmt.executeUpdate();
            return true;
        } catch (SQLException e) {
            System.err.println("[-] Failed to capture offline content modification stream draft:");
            e.printStackTrace();
            return false;
        }
    }

    /**
     * Creates a local exclusion rule for a post draft. 
     * Marked as 'PENDING_CREATE' so the synchronization worker can push the exclusion mapping upstream.
     */
    public static boolean saveOfflineExclusion(int localPostId, int excludedUserId, String timestamp) {
        String sql = "INSERT INTO post_exclusions (post_id, excluded_user_id, sync_status, created_at) VALUES (?, ?, 'PENDING_CREATE', ?);";
        
        try (Connection conn = getConnection(); PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setInt(1, localPostId);
            pstmt.setInt(2, excludedUserId);
            pstmt.setString(3, timestamp);
            pstmt.executeUpdate();
            return true;
        } catch (SQLException e) {
            System.err.println("[-] Failed to capture offline privacy exclusion rules maps:");
            e.printStackTrace();
            return false;
        }
    }

    /**
     * Retrieves all local posts waiting for upstream server synchronization.
     */
    public static List<OfflinePostChange> getPendingUpstreamPosts() {
        List<OfflinePostChange> changes = new ArrayList<>();
        String sql = "SELECT local_id, topic_id, user_id, content, is_private, created_at FROM posts WHERE sync_status = 'PENDING_CREATE';";

        try (Connection conn = getConnection(); Statement stmt = conn.createStatement(); ResultSet rs = stmt.executeQuery(sql)) {
            while (rs.next()) {
                changes.add(new OfflinePostChange(
                        rs.getInt("local_id"),
                        rs.getInt("topic_id"),
                        rs.getInt("user_id"),
                        rs.getString("content"),
                        rs.getInt("is_private") == 1,
                        rs.getString("created_at")
                ));
            }
        } catch (SQLException e) {
            System.err.println("[-] Failed to process queue analysis tracking maps for posts:");
            e.printStackTrace();
        }
        return changes;
    }

    /**
     * Retrieves all pending post privacy exclusion mapping entries to synchronize upstream.
     */
    public static List<OfflineExclusionChange> getPendingUpstreamExclusions() {
        List<OfflineExclusionChange> changes = new ArrayList<>();
        String sql = "SELECT pe.local_id, p.server_id AS server_post_id, pe.excluded_user_id, pe.created_at " +
                     "FROM post_exclusions pe " +
                     "JOIN posts p ON pe.post_id = p.local_id " +
                     "WHERE pe.sync_status = 'PENDING_CREATE' AND p.server_id IS NOT NULL;";

        try (Connection conn = getConnection(); Statement stmt = conn.createStatement(); ResultSet rs = stmt.executeQuery(sql)) {
            while (rs.next()) {
                changes.add(new OfflineExclusionChange(
                        rs.getInt("local_id"),
                        rs.getInt("server_post_id"),
                        rs.getInt("excluded_user_id"),
                        rs.getString("created_at")
                ));
            }
        } catch (SQLException e) {
            System.err.println("[-] Failed to extract tracking maps for pending upstream exclusions:");
            e.printStackTrace();
        }
        return changes;
    }

    /**
     * Resolves local post status once confirmed upstream.
     */
    public static void resolvePendingPostSync(int localId, int serverId) {
        String sql = "UPDATE posts SET server_id = ?, sync_status = 'SYNCED' WHERE local_id = ?;";

        try (Connection conn = getConnection(); PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setInt(1, serverId);
            pstmt.setInt(2, localId);
            pstmt.executeUpdate();
        } catch (SQLException e) {
            System.err.println("[-] Exception occurring during post status resolution operations:");
            e.printStackTrace();
        }
    }

    /**
     * Resolves local exclusion mapping items once verified by central MySQL endpoints.
     */
    public static void resolvePendingExclusionSync(int localExclusionId, int serverExclusionId) {
        String sql = "UPDATE post_exclusions SET server_id = ?, sync_status = 'SYNCED' WHERE local_id = ?;";

        try (Connection conn = getConnection(); PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setInt(1, serverExclusionId);
            pstmt.setInt(2, localExclusionId);
            pstmt.executeUpdate();
        } catch (SQLException e) {
            System.err.println("[-] Exception occurring during exclusion layer token updates:");
            e.printStackTrace();
        }
    }

    // --- CARRIER DTO OBJECTS FOR SYNCHRONIZATION RUNTIMES ---

    public static class OfflinePostChange {
        public final int localId;
        public final int topicId;
        public final int userId;
        public final String content;
        public final boolean isPrivate;
        public final String createdAt;

        public OfflinePostChange(int localId, int topicId, int userId, String content, boolean isPrivate, String createdAt) {
            this.localId = localId;
            this.topicId = topicId;
            this.userId = userId;
            this.content = content;
            this.isPrivate = isPrivate;
            this.createdAt = createdAt;
        }
    }

    public static class OfflineExclusionChange {
        public final int localExclusionId;
        public final int serverPostId;
        public final int excludedUserId;
        public final String createdAt;

        public OfflineExclusionChange(int localExclusionId, int serverPostId, int excludedUserId, String createdAt) {
            this.localExclusionId = localExclusionId;
            this.serverPostId = serverPostId;
            this.excludedUserId = excludedUserId;
            this.createdAt = createdAt;
        }
    }
}
    

