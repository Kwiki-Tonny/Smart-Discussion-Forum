package com.forum.services;

import com.forum.models.Post;   // ✅ FIX: Import Post model
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

public class DatabaseHandler {

    private static final String DB_FILE = "forum.db";
    private static final String CONNECTION_URL = "jdbc:sqlite:" + DB_FILE;
    private static final Object INITIALIZATION_LOCK = new Object();
    private static volatile boolean databaseInitialized = false;

    static {
        try {
            Class.forName("org.sqlite.JDBC");
        } catch (ClassNotFoundException e) {
            System.err.println("[-] Failed to locate SQLite JDBC Driver extension stack.");
            e.printStackTrace();
        }
    }

    public static Connection getConnection() throws SQLException {
        ensureDatabaseInitialized();
        return DriverManager.getConnection(CONNECTION_URL);
    }

    private static Connection openConnection() throws SQLException {
        return DriverManager.getConnection(CONNECTION_URL);
    }

    private static void ensureDatabaseInitialized() {
        if (databaseInitialized) return;
        synchronized (INITIALIZATION_LOCK) {
            if (databaseInitialized) return;
            initializeDatabase();
            databaseInitialized = true;
        }
    }

    public static void initializeDatabase() {
        synchronized (INITIALIZATION_LOCK) {
            File dbFile = new File(DB_FILE);
            if (!dbFile.exists()) {
                System.out.println("[+] Local pipeline storage target not found. Initializing forum.db instance...");
            }

            try (Connection conn = openConnection(); Statement stmt = conn.createStatement()) {
                stmt.execute("PRAGMA foreign_keys = ON;");

                // 1. USER PROFILES
                stmt.execute("CREATE TABLE IF NOT EXISTS user_profiles (" +
                        "id INTEGER PRIMARY KEY, " +
                        "email TEXT NOT NULL, " +
                        "role TEXT NOT NULL" +
                        ");");

                // 2. GROUPS
                stmt.execute("CREATE TABLE IF NOT EXISTS groups (" +
                        "id INTEGER PRIMARY KEY, " +
                        "name TEXT NOT NULL, " +
                        "description TEXT, " +
                        "created_at TEXT, " +
                        "updated_at TEXT" +
                        ");");

                // 3. GROUP_USER
                stmt.execute("CREATE TABLE IF NOT EXISTS group_user (" +
                        "group_id INTEGER, " +
                        "user_id INTEGER, " +
                        "has_agreed_rules INTEGER DEFAULT 0, " +
                        "PRIMARY KEY (group_id, user_id), " +
                        "FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE" +
                        ");");

                // 4. TOPICS
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

                // 5. POSTS (with parent_id)
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

    // ─── OFFLINE POSTS ────────────────────────────────────────────

    public static boolean saveOfflinePostDraft(int topicId, int userId, String content, boolean isPrivate, String timestamp) {
        return saveOfflinePostDraft(topicId, userId, content, isPrivate, timestamp, null);
    }

    public static boolean saveOfflinePostDraft(int topicId, int userId, String content,
                                               boolean isPrivate, String timestamp, Integer parentId) {
        String sql = "INSERT INTO posts (topic_id, user_id, content, is_private, sync_status, created_at, parent_id) " +
                     "VALUES (?, ?, ?, ?, 'PENDING_CREATE', ?, ?);";
        try (Connection conn = getConnection(); PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setInt(1, topicId);
            pstmt.setInt(2, userId);
            pstmt.setString(3, content);
            pstmt.setInt(4, isPrivate ? 1 : 0);
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

    // ─── OFFLINE LIKES ─────────────────────────────────────────────

    public static boolean saveOfflineLike(int postId, int userId, boolean liked) {
        String sql = "INSERT INTO pending_likes (post_id, user_id, liked, sync_status, created_at) " +
                     "VALUES (?, ?, ?, 'PENDING_CREATE', ?)";
        try (Connection conn = getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setInt(1, postId);
            pstmt.setInt(2, userId);
            pstmt.setInt(3, liked ? 1 : 0);
            pstmt.setString(4, LocalDateTime.now().toString());
            pstmt.executeUpdate();
            return true;
        } catch (SQLException e) {
            System.err.println("[-] Failed to save offline like:");
            e.printStackTrace();
            return false;
        }
    }

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

    // ─── LOCAL RETRIEVAL FOR OFFLINE USE ──────────────────────────

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
                int parentId = rs.getInt("parent_id");
                if (!rs.wasNull()) post.parentId = parentId;
                // Author info not stored locally – will be handled as "Unknown" in UI
                posts.add(post);
            }
        } catch (SQLException e) {
            System.err.println("[-] Failed to get local posts for topic " + topicId);
            e.printStackTrace();
        }
        return posts;
    }

    // ─── DTOs ──────────────────────────────────────────────────────

    public static class OfflinePostChange {
        public final int localId;
        public final int topicId;
        public final int userId;
        public final String content;
        public final boolean isPrivate;
        public final String createdAt;
        public final Integer parentId;

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
            ResultSet rs = pstmt.getGeneratedKeys();
            if (rs.next()) {
                return rs.getInt(1);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return -1;
    }

    public static class PendingLike {
        public final int id;
        public final int postId;
        public final int userId;
        public final boolean liked;

        public PendingLike(int id, int postId, int userId, boolean liked) {
            this.id = id;
            this.postId = postId;
            this.userId = userId;
            this.liked = liked;
        }
    }
}