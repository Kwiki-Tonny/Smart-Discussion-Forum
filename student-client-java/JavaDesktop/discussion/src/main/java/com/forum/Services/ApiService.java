package com.forum.services;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.datatype.jsr310.JavaTimeModule;
import com.forum.GlobalState;
import com.forum.models.Group;
import com.forum.models.Post;
import com.forum.models.Topic;
import com.forum.models.User;

import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.time.Duration;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;

public class ApiService {
    private static ApiService instance;
    private final HttpClient client;
    private final ObjectMapper mapper;
    private final GlobalState state = GlobalState.getInstance();

    private static final String BASE_URL = "http://localhost:8000/api/v1";

    private ApiService() {
        this.client = HttpClient.newBuilder()
                .connectTimeout(Duration.ofSeconds(10))
                .build();
        this.mapper = new ObjectMapper();
        this.mapper.registerModule(new JavaTimeModule());
    }

    public static ApiService getInstance() {
        if (instance == null) {
            synchronized (ApiService.class) {
                if (instance == null) instance = new ApiService();
            }
        }
        return instance;
    }

    // ---------- Token Management (using GlobalState) ----------
    public String getToken() {
        return state.getAuthToken();
    }

    public boolean isAuthenticated() {
        return state.isAuthenticated();
    }

    // ---------- Generic Request Helpers ----------
    private HttpRequest.Builder authenticatedRequest() {
        String token = getToken();
        if (token == null || token.isEmpty()) {
            throw new IllegalStateException("No token available");
        }
        return HttpRequest.newBuilder()
                .header("Authorization", "Bearer " + token)
                .header("Accept", "application/json");
    }

    private JsonNode parseResponse(HttpResponse<String> response) throws Exception {
        if (response.statusCode() == 401) {
            // Token expired or invalid
            state.clearSession();
            throw new RuntimeException("Authentication expired. Please login again.");
        }
        if (response.statusCode() < 200 || response.statusCode() >= 300) {
            String errorMsg = "HTTP " + response.statusCode() + ": " + response.body();
            throw new RuntimeException(errorMsg);
        }
        return mapper.readTree(response.body());
    }

    // ---------- Authentication ----------
    public LoginResponse login(String email, String password) throws Exception {
        String jsonBody = mapper.writeValueAsString(Map.of("email", email, "password", password));

        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(BASE_URL + "/login"))
                .header("Content-Type", "application/json")
                .POST(HttpRequest.BodyPublishers.ofString(jsonBody))
                .build();

        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);

        String token = root.path("access_token").asText();
        if (token.isEmpty()) token = root.path("token").asText();

        if (token.isEmpty()) {
            throw new RuntimeException("No token in response");
        }

        JsonNode userNode = root.path("user");
        User user = mapper.treeToValue(userNode, User.class);

        return new LoginResponse(token, user);
    }

    public void logout() throws Exception {
        if (!isAuthenticated()) return;

        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/logout"))
                .POST(HttpRequest.BodyPublishers.noBody())
                .build();

        try {
            HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
            if (response.statusCode() >= 300) {
                System.err.println("Logout warning: " + response.body());
            }
        } finally {
            // Always clear local token even if server call fails
            state.clearSession();
        }
    }

    public User getCurrentUser() throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/user"))
                .GET()
                .build();

        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);
        return mapper.treeToValue(root, User.class);
    }

    // ---------- Forum Data ----------
    public List<Group> getGroups() throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/groups"))
                .GET()
                .build();

        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);
        JsonNode data = root.path("data");
        List<Group> groups = new ArrayList<>();
        if (data.isArray()) {
            for (JsonNode node : data) {
                groups.add(mapper.treeToValue(node, Group.class));
            }
        }
        return groups;
    }

    public List<Topic> getTopicsForGroup(int groupId) throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/groups/" + groupId + "/topics"))
                .GET()
                .build();

        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);
        JsonNode data = root.path("data");
        List<Topic> topics = new ArrayList<>();
        if (data.isArray()) {
            for (JsonNode node : data) {
                topics.add(mapper.treeToValue(node, Topic.class));
            }
        }
        return topics;
    }

    public List<Post> getPostsForTopic(int topicId) throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/topics/" + topicId + "/posts"))
                .GET()
                .build();

        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);
        JsonNode data = root.path("data");
        List<Post> posts = new ArrayList<>();
        if (data.isArray()) {
            for (JsonNode node : data) {
                posts.add(mapper.treeToValue(node, Post.class));
            }
        }
        return posts;
    }

    public Topic createTopic(int groupId, String title, String description) throws Exception {
        Map<String, Object> payload = Map.of(
                "group_id", groupId,
                "title", title,
                "description", description != null ? description : ""
        );
        String json = mapper.writeValueAsString(payload);

        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/topics"))
                .header("Content-Type", "application/json")
                .POST(HttpRequest.BodyPublishers.ofString(json))
                .build();

        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);
        return mapper.treeToValue(root.path("data"), Topic.class);
    }

    public Post createPost(int topicId, int userId, String content, boolean isPrivate, List<Integer> excludedUserIds) throws Exception {
        Map<String, Object> payload = Map.of(
                "topic_id", topicId,
                "user_id", userId,
                "content", content,
                "is_private", isPrivate,
                "excluded_user_ids", excludedUserIds != null ? excludedUserIds : List.of()
        );
        String json = mapper.writeValueAsString(payload);

        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/posts/publish"))
                .header("Content-Type", "application/json")
                .POST(HttpRequest.BodyPublishers.ofString(json))
                .build();

        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);
        return mapper.treeToValue(root.path("data"), Post.class);
    }

    // ---------- Sync Endpoints ----------
    public List<Post> syncDownload(String since) throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/sync/download?since=" + since))
                .GET()
                .build();

        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);
        JsonNode data = root.path("data");
        List<Post> posts = new ArrayList<>();
        if (data.isArray()) {
            for (JsonNode node : data) {
                posts.add(mapper.treeToValue(node, Post.class));
            }
        }
        return posts;
    }

    public List<Map<String, Object>> syncUpload(List<Map<String, Object>> pendingPosts) throws Exception {
        Map<String, Object> payload = Map.of("posts", pendingPosts);
        String json = mapper.writeValueAsString(payload);

        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/sync/upload"))
                .header("Content-Type", "application/json")
                .POST(HttpRequest.BodyPublishers.ofString(json))
                .build();

        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);
        List<Map<String, Object>> results = new ArrayList<>();
        JsonNode data = root.path("data");
        if (data.isArray()) {
            for (JsonNode node : data) {
                results.add(mapper.convertValue(node, Map.class));
            }
        }
        return results;
    }

    // ---------- Helper DTO ----------
    public static class LoginResponse {
        public String token;
        public User user;
        public LoginResponse(String token, User user) {
            this.token = token;
            this.user = user;
        }
    }
}