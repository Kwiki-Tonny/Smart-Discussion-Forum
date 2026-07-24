package com.forum.services;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.datatype.jsr310.JavaTimeModule;
import com.forum.services.GlobalState;
import com.forum.models.Group;
import com.forum.models.Post;
import com.forum.models.Topic;
import com.forum.models.User;
import com.forum.models.Quiz;
import com.forum.models.QuizAttempt;
import com.forum.models.QuizAttemptDetail;
import com.forum.models.UserStats;
import com.forum.models.Question;

import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.time.Duration;
import java.util.ArrayList;
import java.util.HashMap;
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

    public String getToken() {
        return state.getAuthToken();
    }

    public boolean isAuthenticated() {
        return state.isAuthenticated();
    }

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
            state.clearSession();
            throw new RuntimeException("Authentication expired. Please login again.");
        }
        if (response.statusCode() < 200 || response.statusCode() >= 300) {
            String errorMsg = "HTTP " + response.statusCode() + ": " + response.body();
            throw new RuntimeException(errorMsg);
        }
        return mapper.readTree(response.body());
    }

    // ─── AUTHENTICATION ──────────────────────────────────────────

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
        if (token.isEmpty()) throw new RuntimeException("No token in response");

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

    // ─── FORUM DATA ─────────────────────────────────────────────

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

    // ─── CREATE POST ─────────────────────────────────────────────

    public Post createPost(int topicId, int userId, String content, boolean isPrivate,
                           List<Integer> excludedUserIds, Integer parentId) throws Exception {
        Map<String, Object> payload = new HashMap<>();
        payload.put("topic_id", topicId);
        payload.put("user_id", userId);
        payload.put("content", content);
        payload.put("is_private", isPrivate);
        payload.put("excluded_user_ids", excludedUserIds != null ? excludedUserIds : List.of());
        if (parentId != null) {
            payload.put("parent_id", parentId);
        }
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

    // ─── LIKE ──────────────────────────────────────────────────

    public Post toggleLike(int postId) throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/posts/" + postId + "/like"))
                .header("Content-Type", "application/json")
                .POST(HttpRequest.BodyPublishers.noBody())
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);
        return mapper.treeToValue(root.path("data"), Post.class);
    }

    // ─── SYNC ──────────────────────────────────────────────────

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

    // ============================================================
    //  NEW STUDENT-SPECIFIC API METHODS
    // ============================================================

    // ─── GROUP MEMBERSHIP ────────────────────────────────────────

    public void joinGroup(int groupId) throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/groups/" + groupId + "/join"))
                .POST(HttpRequest.BodyPublishers.noBody())
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        if (response.statusCode() == 204) {
            return;
        }
        parseResponse(response);
    }

    public void leaveGroup(int groupId) throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/groups/" + groupId + "/leave"))
                .method("DELETE", HttpRequest.BodyPublishers.noBody())
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        if (response.statusCode() == 204) {
            return;
        }
        parseResponse(response);
    }

    public List<Group> searchGroups(String query) throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/groups/search?q=" + (query != null ? query : "")))
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

    public void acceptRules(int groupId) throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/groups/" + groupId + "/rules/accept"))
                .header("Content-Type", "application/json")
                .POST(HttpRequest.BodyPublishers.noBody())
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        if (response.statusCode() == 204 || response.statusCode() == 200) {
            return;
        }
        parseResponse(response);
    }

    // ─── USERS ─────────────────────────────────────────────────────────

    public List<User> getUsers() throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/users"))
                .GET()
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);
        JsonNode data = root.path("data");
        List<User> users = new ArrayList<>();
        if (data.isArray()) {
            for (JsonNode node : data) {
                users.add(mapper.treeToValue(node, User.class));
            }
        }
        return users;
    }

    // ─── QUIZZES ──────────────────────────────────────────────────

    public List<Quiz> getQuizzes() throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/quizzes"))
                .GET()
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);
        JsonNode data = root.path("data");
        List<Quiz> quizzes = new ArrayList<>();
        if (data.isArray()) {
            for (JsonNode node : data) {
                quizzes.add(mapper.treeToValue(node, Quiz.class));
            }
        }
        return quizzes;
    }

    public QuizAttempt startQuiz(int quizId) throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/quizzes/" + quizId + "/start"))
                .GET()
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        System.out.println("startQuiz response body: " + response.body()); // DEBUG
        JsonNode root = parseResponse(response);
        JsonNode data = root.path("data");
        return mapper.treeToValue(data, QuizAttempt.class);
    }

    public QuizAttemptDetail submitQuiz(int attemptId, Map<Integer, Object> answers) throws Exception {
        Map<String, Object> payload = new HashMap<>();
        payload.put("answers", answers);
        String json = mapper.writeValueAsString(payload);
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/quizzes/" + attemptId + "/submit"))
                .header("Content-Type", "application/json")
                .POST(HttpRequest.BodyPublishers.ofString(json))
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);
        JsonNode data = root.path("data");
        return mapper.treeToValue(data, QuizAttemptDetail.class);
    }

    // ─── STUDENT STATS & RESULTS ─────────────────────────────────

    public UserStats getUserStats() throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/user/stats"))
                .GET()
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);
        JsonNode data = root.path("data");
        return mapper.treeToValue(data, UserStats.class);
    }

    public List<QuizAttempt> getQuizAttempts() throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/user/quiz-attempts"))
                .GET()
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        System.out.println("getQuizAttempts response: " + response.body()); // DEBUG
        JsonNode root = parseResponse(response);
        JsonNode data = root.path("data");
        List<QuizAttempt> attempts = new ArrayList<>();
        if (data.isArray()) {
            for (JsonNode node : data) {
                attempts.add(mapper.treeToValue(node, QuizAttempt.class));
            }
        }
        return attempts;
    }

    public QuizAttemptDetail getQuizAttemptDetail(int attemptId) throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/user/quiz-attempts/" + attemptId))
                .GET()
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);
        JsonNode data = root.path("data");
        return mapper.treeToValue(data, QuizAttemptDetail.class);
    }

    // ─── HELPER DTO ────────────────────────────────────────────

    public static class LoginResponse {
        public String token;
        public User user;
        public LoginResponse(String token, User user) {
            this.token = token;
            this.user = user;
        }
    }
}