package com.forum;
    

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;

public class DatabaseHandler {
    private static final String DATABASE_URL = "jdbc:sqlite:local_forum.db";

    public DatabaseHandler() {
        initializeDatabase();
    }

    public Connection getConnection() throws SQLException {
        return DriverManager.getConnection(DATABASE_URL);
    }

    public void initializeDatabase() {
        try (Connection connection = getConnection();
             Statement statement = connection.createStatement()) {
            statement.execute("PRAGMA foreign_keys = ON");
            createUsersTable(statement);
            createGroupsTable(statement);
            createGroupUserTable(statement);
            createTopicsTable(statement);
            createPostsTable(statement);
            createPostExclusionsTable(statement);
            createUserInteractionsTable(statement);

        } catch (SQLException exception) {
            throw new IllegalStateException("Could not initialize local_forum.db", exception);
        }
    }

    private void createUsersTable(Statement statement) throws SQLException {
        statement.execute(
                "CREATE TABLE IF NOT EXISTS users ("
                        + "id INTEGER PRIMARY KEY AUTOINCREMENT,"
                        + "email TEXT NOT NULL UNIQUE,"
                        + "password TEXT NOT NULL,"
                        + "role TEXT NOT NULL CHECK(role IN ('admin', 'lecturer', 'student')),"
                        + "status TEXT NOT NULL DEFAULT 'active' CHECK(status IN ('active', 'warned_once', 'warned_twice', 'blacklisted')),"
                        + "last_communicated_at TEXT,"
                        + "created_at TEXT DEFAULT CURRENT_TIMESTAMP,"
                        + "updated_at TEXT DEFAULT CURRENT_TIMESTAMP"
                        + ")");
    }

    private void createGroupsTable(Statement statement) throws SQLException {
        statement.execute(
                "CREATE TABLE IF NOT EXISTS groups ("
                        + "id INTEGER PRIMARY KEY AUTOINCREMENT,"
                        + "name TEXT NOT NULL,"
                        + "description TEXT,"
                        + "created_at TEXT DEFAULT CURRENT_TIMESTAMP,"
                        + "updated_at TEXT DEFAULT CURRENT_TIMESTAMP"
                        + ")");
    }

    private void createGroupUserTable(Statement statement) throws SQLException {
        statement.execute(
                "CREATE TABLE IF NOT EXISTS group_user ("
                        + "id INTEGER PRIMARY KEY AUTOINCREMENT,"
                        + "group_id INTEGER NOT NULL,"
                        + "user_id INTEGER NOT NULL,"
                        + "has_agreed_rules INTEGER NOT NULL DEFAULT 0,"
                        + "created_at TEXT DEFAULT CURRENT_TIMESTAMP,"
                        + "updated_at TEXT DEFAULT CURRENT_TIMESTAMP,"
                        + "FOREIGN KEY(group_id) REFERENCES groups(id) ON DELETE CASCADE,"
                        + "FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,"
                        + "UNIQUE(group_id, user_id)"
                        + ")");
    }

    private void createTopicsTable(Statement statement) throws SQLException {
        statement.execute(
                "CREATE TABLE IF NOT EXISTS topics ("
                        + "id INTEGER PRIMARY KEY AUTOINCREMENT,"
                        + "group_id INTEGER NOT NULL,"
                        + "title TEXT NOT NULL,"
                        + "creator_id INTEGER,"
                        + "ml_category TEXT,"
                        + "created_at TEXT DEFAULT CURRENT_TIMESTAMP,"
                        + "updated_at TEXT DEFAULT CURRENT_TIMESTAMP,"
                        + "FOREIGN KEY(group_id) REFERENCES groups(id) ON DELETE CASCADE,"
                        + "FOREIGN KEY(creator_id) REFERENCES users(id) ON DELETE SET NULL"
                        + ")");
    }

    private void createPostsTable(Statement statement) throws SQLException {
        statement.execute(
                "CREATE TABLE IF NOT EXISTS posts ("
                        + "id INTEGER PRIMARY KEY AUTOINCREMENT,"
                        + "topic_id INTEGER NOT NULL,"
                        + "user_id INTEGER,"
                        + "content TEXT NOT NULL,"
                        + "is_private INTEGER NOT NULL DEFAULT 0,"
                        + "sync_status TEXT NOT NULL DEFAULT 'synced' CHECK(sync_status IN ('synced', 'pending_upload')),"
                        + "created_at TEXT DEFAULT CURRENT_TIMESTAMP,"
                        + "updated_at TEXT DEFAULT CURRENT_TIMESTAMP,"
                        + "FOREIGN KEY(topic_id) REFERENCES topics(id) ON DELETE CASCADE,"
                        + "FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL"
                        + ")");
    }

    private void createPostExclusionsTable(Statement statement) throws SQLException {
        statement.execute(
                "CREATE TABLE IF NOT EXISTS post_exclusions ("
                        + "id INTEGER PRIMARY KEY AUTOINCREMENT,"
                        + "post_id INTEGER NOT NULL,"
                        + "excluded_user_id INTEGER NOT NULL,"
                        + "created_at TEXT DEFAULT CURRENT_TIMESTAMP,"
                        + "updated_at TEXT DEFAULT CURRENT_TIMESTAMP,"
                        + "FOREIGN KEY(post_id) REFERENCES posts(id) ON DELETE CASCADE,"
                        + "FOREIGN KEY(excluded_user_id) REFERENCES users(id) ON DELETE CASCADE,"
                        + "UNIQUE(post_id, excluded_user_id)"
                        + ")");
    }

    private void createUserInteractionsTable(Statement statement) throws SQLException {
        statement.execute(
                "CREATE TABLE IF NOT EXISTS user_interactions ("
                        + "id INTEGER PRIMARY KEY AUTOINCREMENT,"
                        + "user_id INTEGER NOT NULL,"
                        + "topic_id INTEGER NOT NULL,"
                        + "action_type TEXT NOT NULL CHECK(action_type IN ('view', 'like', 'download', 'comment')),"
                        + "created_at TEXT DEFAULT CURRENT_TIMESTAMP,"
                        + "FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,"
                        + "FOREIGN KEY(topic_id) REFERENCES topics(id) ON DELETE CASCADE"
                        + ")");
    }
}
