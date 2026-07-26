/**
 * Package: com.forum.services
 * 
 * This package contains the core business logic and service-layer components 
 * of the forum application. Classes in this package are responsible for 
 * orchestrating operations, managing external communications (such as HTTP 
 * requests to the backend API), and maintaining application state.
 */
package com.forum.services;

/**
 * Jackson core and databind classes used for robust JSON parsing and serialization.
 * 
 * Architectural Note: JsonNode is used extensively in this service to handle 
 * dynamic or nested API responses safely, allowing us to extract specific 
 * "data" wrappers without requiring rigid, deeply nested DTO classes for every 
 * single endpoint variation.
 */
import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;

/**
 * Jackson module for handling Java 8 Date and Time API (JSR-310) serialization.
 * 
 * Architectural Note: Registered in the constructor to ensure that any 
 * LocalDateTime, ZonedDateTime, or Instant fields within our models are 
 * correctly parsed from ISO-8601 string formats provided by the backend.
 */
import com.fasterxml.jackson.datatype.jsr310.JavaTimeModule;

// Internal model imports representing the domain entities transferred via this API service.
import com.forum.models.Group;
import com.forum.models.Post;
import com.forum.models.Topic;
import com.forum.models.User;
import com.forum.models.Quiz;
import com.forum.models.QuizAttempt;
import com.forum.models.QuizAttemptDetail;
import com.forum.models.UserStats;
import com.forum.models.Question;

/**
 * Standard Java libraries for modern, non-blocking(ish) HTTP client operations.
 * 
 * Architectural Note: java.net.http.HttpClient (introduced in Java 11) is used 
 * here for its built-in connection pooling, fluent builder API, and native 
 * support for modern HTTP/2 features, replacing older, more verbose libraries 
 * like HttpURLConnection or Apache HttpClient.
 */
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.time.Duration;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * Centralized API Gateway Service for the Forum Application.
 * 
 * This class is implemented as a Thread-Safe Singleton to ensure efficient 
 * resource management. By sharing a single instance of the HttpClient and 
 * ObjectMapper across the entire application, we benefit from:
 * 1. Connection Pooling: Reusing underlying TCP connections for better performance.
 * 2. Consistent State: A single point of truth for authentication tokens and 
 *    session management via the GlobalState dependency.
 * 3. Centralized Error Handling: Uniform parsing and exception throwing for 
 *    all HTTP responses (e.g., automatic session clearing on 401 Unauthorized).
 * 
 * Design Patterns Employed:
 * - Singleton (Double-Checked Locking): Ensures only one instance exists, even 
 *   in multi-threaded environments, while minimizing synchronization overhead.
 * - Builder Pattern: Used extensively via HttpClient.newBuilder() and 
 *   HttpRequest.newBuilder() for readable, immutable request construction.
 * - Data Transfer Object (DTO): The inner class LoginResponse encapsulates 
 *   the specific return payload of the authentication flow.
 */
public class ApiService {

    /**
     * The single, shared instance of the ApiService.
     * Volatile is not strictly required here due to the synchronized block, 
     * but the double-checked locking pattern ensures thread-safe lazy initialization.
     */
    private static ApiService instance;

    /**
     * The underlying HTTP client responsible for executing network requests.
     * Configured once during initialization with a 10-second connection timeout 
     * to prevent the application from hanging indefinitely on unresponsive servers.
     */
    private final HttpClient client;

    /**
     * The JSON serialization/deserialization engine.
     * Configured with the JavaTimeModule to correctly handle modern Java date/time 
     * objects when mapping JSON payloads to domain models.
     */
    private final ObjectMapper mapper;

    /**
     * Reference to the application's global state manager.
     * Used to retrieve the current user's authentication token and to clear 
     * the session upon logout or authentication expiration.
     */
    private final GlobalState state = GlobalState.getInstance();

    /**
     * The base URL for all backend API endpoints.
     * 
     * Note: In a production environment, this should ideally be externalized to 
     * a configuration file or environment variable to support different 
     * environments (e.g., dev, staging, prod) without code changes.
     */
    private static final String BASE_URL = "http://localhost:8000/api/v1";

    /**
     * Private constructor to enforce the Singleton pattern.
     * 
     * Initializes the HttpClient with a 10-second connect timeout and configures 
     * the ObjectMapper with the JSR-310 JavaTimeModule for robust date/time 
     * parsing. This constructor is only called once during the application's lifecycle.
     */
    private ApiService() {
        this.client = HttpClient.newBuilder()
                .connectTimeout(Duration.ofSeconds(10))
                .build();
        this.mapper = new ObjectMapper();
        this.mapper.registerModule(new JavaTimeModule());
    }

    /**
     * Retrieves the single, shared instance of the ApiService.
     * 
     * Implements the Double-Checked Locking pattern to ensure thread-safe lazy 
     * initialization. The synchronized block is only entered if the instance 
     * is null, minimizing performance overhead on subsequent calls.
     *
     * @return The singleton instance of ApiService.
     */
    public static ApiService getInstance() {
        if (instance == null) {
            synchronized (ApiService.class) {
                if (instance == null) instance = new ApiService();
            }
        }
        return instance;
    }

    /**
     * Retrieves the current user's authentication token from the global state.
     *
     * @return The Bearer token string, or null if the user is not authenticated.
     */
    public String getToken() {
        return state.getAuthToken();
    }

    /**
     * Checks if the current session is considered authenticated.
     *
     * @return true if a valid token exists in the global state, false otherwise.
     */
    public boolean isAuthenticated() {
        return state.isAuthenticated();
    }

    /**
     * Constructs a pre-configured HttpRequest.Builder with mandatory authentication 
     * and content negotiation headers.
     * 
     * Security Note: This method actively validates the presence of a token before 
     * allowing the request to be built. If no token is present, it fails fast with 
     * an IllegalStateException, preventing malformed requests from reaching the server.
     *
     * @return A configured HttpRequest.Builder ready for a specific HTTP method (GET, POST, etc.).
     * @throws IllegalStateException if the user is not authenticated or the token is missing.
     */
    private HttpRequest.Builder authenticatedRequest() {
        String token = getToken();
        if (token == null || token.isEmpty()) {
            throw new IllegalStateException("No token available");
        }
        return HttpRequest.newBuilder()
                .header("Authorization", "Bearer " + token)
                .header("Accept", "application/json");
    }

    /**
     * Centralized HTTP response validation and JSON parsing mechanism.
     * 
     * This method enforces a consistent error-handling strategy across all API calls:
     * 1. Checks for 401 Unauthorized: Automatically clears the local session state 
     *    and throws a descriptive runtime exception, forcing the UI to redirect to login.
     * 2. Checks for non-2xx status codes: Aggregates the status code and response 
     *    body into a RuntimeException for upstream error handling.
     * 3. Parses valid responses: Uses the configured ObjectMapper to convert the 
     *    raw JSON string body into a traversable JsonNode tree.
     *
     * @param response The raw HttpResponse<String> returned by the HttpClient.
     * @return A Jackson JsonNode representing the parsed JSON payload.
     * @throws Exception if parsing fails, or a RuntimeException if the HTTP status indicates an error.
     */
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

    // =========================================================================
    //  AUTHENTICATION ENDPOINTS
    //  Handles user login, logout, and session validation.
    // =========================================================================

    /**
     * Authenticates a user with their email and password.
     * 
     * Upon successful authentication, this method extracts the access token and 
     * the user profile from the response, returning them as a cohesive LoginResponse 
     * object. The caller is responsible for saving this token to the GlobalState.
     *
     * @param email    The user's registered email address.
     * @param password The user's plaintext password (will be transmitted over HTTPS in production).
     * @return A LoginResponse containing the JWT/auth token and the User object.
     * @throws Exception if the network request fails, or if the response lacks a valid token.
     */
    public LoginResponse login(String email, String password) throws Exception {
        String jsonBody = mapper.writeValueAsString(Map.of("email", email, "password", password));
        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(BASE_URL + "/login"))
                .header("Content-Type", "application/json")
                .POST(HttpRequest.BodyPublishers.ofString(jsonBody))
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);

        // Flexible token extraction to handle slight backend API variations
        String token = root.path("access_token").asText();
        if (token.isEmpty()) token = root.path("token").asText();
        if (token.isEmpty()) throw new RuntimeException("No token in response");

        JsonNode userNode = root.path("user");
        User user = mapper.treeToValue(userNode, User.class);
        return new LoginResponse(token, user);
    }

    /**
     * Terminates the current user session on the backend and clears local state.
     * 
     * Note: The local state is cleared in a `finally` block to ensure that even 
     * if the backend logout endpoint fails or returns an error, the user is still 
     * logged out locally, preventing zombie sessions.
     *
     * @throws Exception if the network request fails unexpectedly.
     */
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

    /**
     * Fetches the profile details of the currently authenticated user.
     * 
     * @return A fully populated User object representing the current session's user.
     * @throws Exception if the network request fails or the user is not authenticated.
     */
    public User getCurrentUser() throws Exception {
        HttpRequest request = authenticatedRequest()
                .uri(URI.create(BASE_URL + "/user"))
                .GET()
                .build();
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        JsonNode root = parseResponse(response);
        return mapper.treeToValue(root, User.class);
    }

    // =========================================================================
    //  FORUM DATA ENDPOINTS
    //  Handles retrieval and creation of core forum entities: Groups, Topics, Posts.
    // =========================================================================

    /**
     * Retrieves a list of all available discussion groups.
     * 
     * @return A list of Group objects parsed from the "data" array in the JSON response.
     * @throws Exception if the network request fails.
     */
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

    /**
     * Retrieves all topics belonging to a specific discussion group.
     * 
     * @param groupId The unique identifier of the group.
     * @return A list of Topic objects associated with the specified group.
     * @throws Exception if the network request fails.
     */
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

    /**
     * Retrieves all posts (and replies) within a specific discussion topic.
     * 
     * @param topicId The unique identifier of the topic.
     * @return A list of Post objects representing the conversation thread.
     * @throws Exception if the network request fails.
     */
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

    /**
     * Creates a new discussion topic within a specified group.
     * 
     * @param groupId     The ID of the group where the topic will be created.
     * @param title       The title of the new topic.
     * @param description The initial content or description of the topic.
     * @return The newly created Topic object, as returned by the server.
     * @throws Exception if the network request fails.
     */
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

    // =========================================================================
    //  POST CREATION & INTERACTION ENDPOINTS
    //  Handles creating posts, replies, and user interactions like liking.
    // =========================================================================

    /**
     * Creates a new post or reply within an existing topic.
     * 
     * This method supports advanced features such as private posts and user 
     * exclusion lists, making it suitable for both standard public replies and 
     * targeted, restricted communications.
     *
     * @param topicId         The ID of the topic this post belongs to.
     * @param userId          The ID of the user creating the post.
     * @param content         The text content of the post.
     * @param isPrivate       Flag indicating if the post has restricted visibility.
     * @param excludedUserIds List of user IDs who should not see this post (if private).
     * @param parentId        The ID of the parent post (if this is a reply), or null for a top-level post.
     * @return The newly created Post object.
     * @throws Exception if the network request fails.
     */
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

    /**
     * Toggles the "like" status of a specific post for the current user.
     * 
     * @param postId The unique identifier of the post to like/unlike.
     * @return The updated Post object, reflecting the new like count/status.
     * @throws Exception if the network request fails.
     */
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

    // =========================================================================
    //  SYNC ENDPOINTS
    //  Supports offline-first capabilities by allowing batch downloads and 
    //  uploads of posts to reconcile local and server state.
    // =========================================================================

    /**
     * Downloads posts that have been created or modified since a specific timestamp.
     * 
     * @param since An ISO-8601 timestamp string indicating the last sync time.
     * @return A list of Post objects that have changed since the specified time.
     * @throws Exception if the network request fails.
     */
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

    /**
     * Uploads a batch of locally created/modified posts to the server.
     * 
     * @param pendingPosts A list of Maps representing the serialized pending posts.
     * @return A list of Maps containing the server's resolution for each uploaded post 
     *         (e.g., assigned server IDs, conflict resolutions).
     * @throws Exception if the network request fails.
     */
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

    // =========================================================================
    //  STUDENT-SPECIFIC API METHODS
    //  Handles group membership, user directory, and quiz/assessment workflows.
    // =========================================================================

    /**
     * Registers the current user as a member of a specific group.
     * 
     * @param groupId The ID of the group to join.
     * @throws Exception if the network request fails.
     */
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

    /**
     * Removes the current user's membership from a specific group.
     * 
     * @param groupId The ID of the group to leave.
     * @throws Exception if the network request fails.
     */
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

    /**
     * Searches for groups based on a text query.
     * 
     * @param query The search string to match against group names or descriptions.
     * @return A list of Group objects matching the search criteria.
     * @throws Exception if the network request fails.
     */
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

    /**
     * Records the current user's acceptance of a group's rules.
     * 
     * @param groupId The ID of the group whose rules are being accepted.
     * @throws Exception if the network request fails.
     */
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

    /**
     * Retrieves a directory list of users in the system.
     * 
     * @return A list of User objects.
     * @throws Exception if the network request fails.
     */
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

    /**
     * Retrieves a list of all available quizzes in the system.
     * 
     * @return A list of Quiz objects.
     * @throws Exception if the network request fails.
     */
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

    /**
     * Initiates a new attempt for a specific quiz.
     * 
     * @param quizId The unique identifier of the quiz to start.
     * @return A QuizAttempt object containing the attempt ID and start metadata.
     * @throws Exception if the network request fails.
     */
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

    /**
     * Submits the user's answers for a specific quiz attempt.
     * 
     * @param attemptId The unique identifier of the active quiz attempt.
     * @param answers   A map of question IDs to the user's selected answers.
     * @return A QuizAttemptDetail object containing the graded results and statistics.
     * @throws Exception if the network request fails.
     */
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

    /**
     * Retrieves aggregated engagement and performance statistics for the current user.
     * 
     * @return A UserStats object containing totals for posts, replies, topics, and quizzes.
     * @throws Exception if the network request fails.
     */
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

    /**
     * Retrieves a history of all quiz attempts made by the current user.
     * 
     * @return A list of QuizAttempt objects representing past quiz sessions.
     * @throws Exception if the network request fails.
     */
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

    /**
     * Retrieves the detailed, question-by-question breakdown of a specific quiz attempt.
     * 
     * @param attemptId The unique identifier of the quiz attempt to inspect.
     * @return A QuizAttemptDetail object containing the granular scoring metrics.
     * @throws Exception if the network request fails.
     */
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

    // =========================================================================
    //  HELPER DATA TRANSFER OBJECTS (DTOs)
    // =========================================================================

    /**
     * A simple Data Transfer Object encapsulating the response payload of a 
     * successful login operation.
     * 
     * Bundles the authentication token and the user's core profile data together, 
     * allowing the calling UI layer to update both the session manager and the 
     * local user state in a single, atomic operation.
     */
    public static class LoginResponse {
        /** The authentication token (e.g., JWT) to be used in subsequent requests. */
        public String token;
        
        /** The core profile data of the authenticated user. */
        public User user;
        
        /**
         * Constructs a new LoginResponse.
         * 
         * @param token The authentication token.
         * @param user  The user profile object.
         */
        public LoginResponse(String token, User user) {
            this.token = token;
            this.user = user;
        }
    }
}