package com.forum;

import java.io.File;
import java.util.List;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertTrue;
import org.junit.Test;

/**
 * Unit tests for the forum application.
 */
public class AppTest {
    @Test
    public void shouldAnswerWithTrue() {
        assertTrue(true);
    }

    @Test
    public void saveOfflinePostDraftCreatesSchemaWhenDatabaseIsMissing() {
        File dbFile = new File("forum.db");
        if (dbFile.exists() && !dbFile.delete()) {
            throw new IllegalStateException("Failed to delete existing test database file");
        }

        boolean saved = DatabaseHandler.saveOfflinePostDraft(1, 2, "hello from offline draft", true, "2026-06-25T00:00:00");
        assertTrue("Draft should be saved even when initialization is deferred", saved);

        List<DatabaseHandler.OfflinePostChange> pendingPosts = DatabaseHandler.getPendingUpstreamPosts();
        assertEquals(1, pendingPosts.size());
        assertEquals("hello from offline draft", pendingPosts.get(0).content);
    }
}
