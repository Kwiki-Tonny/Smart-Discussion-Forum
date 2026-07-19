package com.forum.service;

import java.net.HttpURLConnection;
import java.net.URL;
import java.io.OutputStream;
import java.io.InputStreamReader;
import java.io.BufferedReader;
import java.util.ArrayList;
import java.util.List;
import com.google.gson.JsonObject;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonParser;

/**
 * Forum Service - Handles all forum-related API calls to the backend
 * Manages groups, topics, and posts
 * 
 * Usage:
 *   List<GroupData> groups = ForumService.getGroups();
 *   List<TopicData> topics = ForumService.getTopicsByGroup(groupId);
 *   ForumService.createTopic(groupId, "Title", "Description", false);
 */
public class ForumService {
    private static final String API_BASE_URL = "https://api.yourdomain.com/api";
    private static final String FORUM_ENDPOINT = "/v1";
    private static final int TIMEOUT = 10000;

    // ===========================
    // GROUP OPERATIONS
    // ===========================

    /**
     * Fetch all groups from backend
     * @return List of GroupData objects
     */
    public static List<GroupData> getGroups() {
        try {
            URL url = new URL(API_BASE_URL + FORUM_ENDPOINT + "/groups");
            HttpURLConnection connection = createAuthenticatedRequest(url, "GET");
            
            if (connection.getResponseCode() == 200) {
                try (BufferedReader br = new BufferedReader(
                        new InputStreamReader(connection.getInputStream(), "utf-8"))) {
                    JsonArray groupsArray = JsonParser.parseReader(br).getAsJsonArray();
                    
                    List<GroupData> groups = new ArrayList<>();
                    for (JsonElement element : groupsArray) {
                        JsonObject obj = element.getAsJsonObject();
                        groups.add(new GroupData(
                            obj.get("id").getAsInt(),
                            obj.get("name").getAsString(),
                            obj.get("description").getAsString()
                        ));
                    }
                    System.out.println("[FORUM] Fetched " + groups.size() + " groups");
                    return groups;
                }
            } else {
                System.err.println("[FORUM] Error fetching groups: HTTP " + connection.getResponseCode());
            }
        } catch (Exception e) {
            System.err.println("[FORUM] Error in getGroups: " + e.getMessage());
            e.printStackTrace();
        }
        return new ArrayList<>();
    }

    /**
     * Join a group
     * @param groupId ID of group to join
     * @return true if successful
     */
    public static boolean joinGroup(int groupId) {
        try {
            URL url = new URL(API_BASE_URL + FORUM_ENDPOINT + "/groups/" + groupId + "/join");
            HttpURLConnection connection = createAuthenticatedRequest(url, "POST");
            
            JsonObject request = new JsonObject();
            
            try (OutputStream os = connection.getOutputStream()) {
                byte[] input = request.toString().getBytes("utf-8");
                os.write(input, 0, input.length);
            }
            
            if (connection.getResponseCode() == 200) {
                System.out.println("[FORUM] Successfully joined group: " + groupId);
                return true;
            } else {
                System.err.println("[FORUM] Error joining group: HTTP " + connection.getResponseCode());
            }
        } catch (Exception e) {
            System.err.println("[FORUM] Error in joinGroup: " + e.getMessage());
            e.printStackTrace();
        }
        return false;
    }

    // ===========================
    // TOPIC OPERATIONS
    // ===========================

    /**
     * Fetch topics for a specific group
     * @param groupId ID of the group
     * @return List of TopicData objects
     */
    public static List<TopicData> getTopicsByGroup(int groupId) {
        try {
            URL url = new URL(API_BASE_URL + FORUM_ENDPOINT + "/groups/" + groupId + "/topics");
            HttpURLConnection connection = createAuthenticatedRequest(url, "GET");
            
            if (connection.getResponseCode() == 200) {
                try (BufferedReader br = new BufferedReader(
                        new InputStreamReader(connection.getInputStream(), "utf-8"))) {
                    JsonArray topicsArray = JsonParser.parseReader(br).getAsJsonArray();
                    
                    List<TopicData> topics = new ArrayList<>();
                    for (JsonElement element : topicsArray) {
                        JsonObject obj = element.getAsJsonObject();
                        topics.add(new TopicData(
                            obj.get("id").getAsInt(),
                            obj.get("title").getAsString(),
                            obj.get("creator").getAsJsonObject().get("name").getAsString(),
                            obj.get("created_at").getAsString()
                        ));
                    }
                    System.out.println("[FORUM] Fetched " + topics.size() + " topics for group " + groupId);
                    return topics;
                }
            }
        } catch (Exception e) {
            System.err.println("[FORUM] Error in getTopicsByGroup: " + e.getMessage());
            e.printStackTrace();
        }
        return new ArrayList<>();
    }

    /**
     * Create a new topic
     * @param groupId ID of the group
     * @param title Topic title
     * @param description Topic description
     * @param isPrivate Whether topic is private
     * @return true if successful
     */
    public static boolean createTopic(int groupId, String title, String description, boolean isPrivate) {
        try {
            URL url = new URL(API_BASE_URL + FORUM_ENDPOINT + "/topics");
            HttpURLConnection connection = createAuthenticatedRequest(url, "POST");
            
            JsonObject request = new JsonObject();
            request.addProperty("group_id", groupId);
            request.addProperty("title", title);
            request.addProperty("description", description);
            request.addProperty("is_private", isPrivate);
            
            try (OutputStream os = connection.getOutputStream()) {
                byte[] input = request.toString().getBytes("utf-8");
                os.write(input, 0, input.length);
            }
            
            if (connection.getResponseCode() == 201) {
                System.out.println("[FORUM] Topic created successfully: " + title);
                return true;
            } else {
                System.err.println("[FORUM] Error creating topic: HTTP " + connection.getResponseCode());
            }
        } catch (Exception e) {
            System.err.println("[FORUM] Error in createTopic: " + e.getMessage());
            e.printStackTrace();
        }
        return false;
    }

    // ===========================
    // POST OPERATIONS
    // ===========================

    /**
     * Fetch posts for a specific topic
     * Privacy filters are applied server-side
     * @param topicId ID of the topic
     * @return List of PostData objects
     */
    public static List<PostData> getPostsByTopic(int topicId) {
        try {
            URL url = new URL(API_BASE_URL + FORUM_ENDPOINT + "/topics/" + topicId + "/posts");
            HttpURLConnection connection = createAuthenticatedRequest(url, "GET");
            
            if (connection.getResponseCode() == 200) {
                try (BufferedReader br = new BufferedReader(
                        new InputStreamReader(connection.getInputStream(), "utf-8"))) {
                    JsonArray postsArray = JsonParser.parseReader(br).getAsJsonArray();
                    
                    List<PostData> posts = new ArrayList<>();
                    for (JsonElement element : postsArray) {
                        JsonObject obj = element.getAsJsonObject();
                        posts.add(new PostData(
                            obj.get("id").getAsInt(),
                            obj.get("author").getAsJsonObject().get("name").getAsString(),
                            obj.get("content").getAsString(),
                            obj.get("created_at").getAsString()
                        ));
                    }
                    System.out.println("[FORUM] Fetched " + posts.size() + " posts for topic " + topicId);
                    return posts;
                }
            }
        } catch (Exception e) {
            System.err.println("[FORUM] Error in getPostsByTopic: " + e.getMessage());
            e.printStackTrace();
        }
        return new ArrayList<>();
    }

    /**
     * Create a new post in a topic
     * @param topicId ID of the topic
     * @param content Post content
     * @param isPrivate Whether post is private
     * @return true if successful
     */
    public static boolean createPost(int topicId, String content, boolean isPrivate) {
        try {
            URL url = new URL(API_BASE_URL + FORUM_ENDPOINT + "/posts/publish");
            HttpURLConnection connection = createAuthenticatedRequest(url, "POST");
            
            JsonObject request = new JsonObject();
            request.addProperty("topic_id", topicId);
            request.addProperty("content", content);
            request.addProperty("is_private", isPrivate);
            
            try (OutputStream os = connection.getOutputStream()) {
                byte[] input = request.toString().getBytes("utf-8");
                os.write(input, 0, input.length);
            }
            
            if (connection.getResponseCode() == 201) {
                System.out.println("[FORUM] Post created successfully");
                return true;
            } else {
                System.err.println("[FORUM] Error creating post: HTTP " + connection.getResponseCode());
            }
        } catch (Exception e) {
            System.err.println("[FORUM] Error in createPost: " + e.getMessage());
            e.printStackTrace();
        }
        return false;
    }

    /**
     * Like a post
     * @param postId ID of the post
     * @return true if successful
     */
    public static boolean likePost(int postId) {
        try {
            URL url = new URL(API_BASE_URL + FORUM_ENDPOINT + "/posts/" + postId + "/like");
            HttpURLConnection connection = createAuthenticatedRequest(url, "POST");
            
            JsonObject request = new JsonObject();
            
            try (OutputStream os = connection.getOutputStream()) {
                byte[] input = request.toString().getBytes("utf-8");
                os.write(input, 0, input.length);
            }
            
            if (connection.getResponseCode() == 200) {
                System.out.println("[FORUM] Post liked successfully");
                return true;
            }
        } catch (Exception e) {
            System.err.println("[FORUM] Error in likePost: " + e.getMessage());
            e.printStackTrace();
        }
        return false;
    }

    // ===========================
    // HELPER METHODS
    // ===========================

    /**
     * Create an authenticated HTTP connection with JWT token
     */
    private static HttpURLConnection createAuthenticatedRequest(URL url, String method) throws Exception {
        HttpURLConnection connection = (HttpURLConnection) url.openConnection();
        connection.setRequestMethod(method);
        connection.setRequestProperty("Authorization", "Bearer " + AuthenticationService.getAuthToken());
        connection.setRequestProperty("Content-Type", "application/json");
        connection.setConnectTimeout(TIMEOUT);
        connection.setReadTimeout(TIMEOUT);
        connection.setDoOutput(true);
        return connection;
    }

    // ===========================
    // DATA CLASSES (Move to separate files in production)
    // ===========================

    public static class GroupData {
        public int id;
        public String name;
        public String description;
        
        public GroupData(int id, String name, String description) {
            this.id = id;
            this.name = name;
            this.description = description;
        }
    }

    public static class TopicData {
        public int id;
        public String title;
        public String creator;
        public String createdAt;
        
        public TopicData(int id, String title, String creator, String createdAt) {
            this.id = id;
            this.title = title;
            this.creator = creator;
            this.createdAt = createdAt;
        }
    }

    public static class PostData {
        public int id;
        public String author;
        public String content;
        public String createdAt;
        
        public PostData(int id, String author, String content, String createdAt) {
            this.id = id;
            this.author = author;
            this.content = content;
            this.createdAt = createdAt;
        }
    }
}
