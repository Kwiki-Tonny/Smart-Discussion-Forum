/**
 * Package: com.forum.models
 * 
 * This package contains the core domain models and data transfer objects (DTOs) 
 * used throughout the forum application. Classes in this package are primarily 
 * responsible for representing the state of business entities, facilitating 
 * data binding from JSON payloads (via Jackson), and mapping to database records.
 */
package com.forum.models;

/**
 * Jackson annotation used to indicate that unknown properties in the incoming 
 * JSON payload should be ignored during deserialization.
 * 
 * Architectural Note: This is a critical resilience pattern for analytics and 
 * statistics endpoints. As the application evolves, new metrics may be added 
 * to the frontend or database. This annotation ensures that the backend will 
 * not throw an UnrecognizedPropertyException if it encounters a new statistical 
 * field it doesn't yet know about, guaranteeing forward compatibility and 
 * preventing cascading failures in user profile rendering.
 */
import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

/**
 * Jackson annotation used to explicitly map a JSON property name to a Java field.
 * 
 * Architectural Note: This bridges the gap between standard Java naming 
 * conventions (camelCase) and standard JSON/API naming conventions (snake_case). 
 * This ensures the external API remains RESTful, predictable, and consistent 
 * with typical JSON schema designs, while keeping the internal Java codebase idiomatic.
 */
import com.fasterxml.jackson.annotation.JsonProperty;

/**
 * Represents an aggregated summary of a user's activity and engagement metrics 
 * within the forum application.
 * 
 * This model is specifically designed as a lightweight, read-optimized Data 
 * Transfer Object (DTO) for user profiles, dashboards, and leaderboard systems. 
 * Instead of forcing the database to perform expensive COUNT() aggregate queries 
 * across multiple tables (topics, posts, replies, quizzes) every time a user 
 * profile is viewed, this object is intended to hold pre-calculated or 
 * materialized view statistics.
 * 
 * Key Architectural Patterns in this Model:
 * 1. Read Optimization: Provides O(1) access to key user metrics without 
 *    triggering N+1 query problems or complex JOIN operations.
 * 2. Separation of Concerns: Keeps statistical data decoupled from the core 
 *    {@link User} identity model, preventing the base user object from becoming 
 *    bloated with transient or frequently changing aggregate data.
 * 3. Gamification Support: These metrics directly feed into frontend gamification 
 *    elements, such as badges, reputation scores, and engagement tier calculations.
 * 
 * Serialization Behavior:
 * - Unknown JSON properties are safely ignored (@JsonIgnoreProperties).
 * - All fields are explicitly mapped to snake_case JSON keys to maintain a 
 *   consistent, professional external API contract.
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class UserStats {

    /**
     * The total number of standalone posts or articles authored by this user.
     * 
     * JSON Mapping: "total_posts"
     * Purpose: Measures the user's primary content creation volume. In many 
     * forum architectures, a "post" may refer to top-level content items or 
     * major contributions. This metric is often used to calculate user reputation, 
     * unlock "contributor" badges, or determine eligibility for moderation privileges.
     */
    @JsonProperty("total_posts")
    public int totalPosts;

    /**
     * The total number of replies or comments made by this user in response 
     * to existing topics or posts.
     * 
     * JSON Mapping: "total_replies"
     * Purpose: Measures the user's conversational engagement and community 
     * interaction. A high reply count relative to topic count often indicates 
     * a user who is highly active in helping others, answering questions, or 
     * participating in ongoing discussions, which is a key metric for community health.
     */
    @JsonProperty("total_replies")
    public int totalReplies;

    /**
     * The total number of discussion topics or threads initiated by this user.
     * 
     * JSON Mapping: "total_topics"
     * Purpose: Measures the user's initiative and leadership in starting new 
     * conversations. This metric is distinct from general posts, as creating a 
     * topic requires formulating a new subject, which is often weighted differently 
     * in gamification or reputation systems compared to simply replying to an 
     * existing thread.
     */
    @JsonProperty("total_topics")
    public int totalTopics;

    /**
     * The total number of quizzes attempted or completed by this user.
     * 
     * JSON Mapping: "total_quizzes"
     * Purpose: Tracks the user's engagement with the educational or assessment 
     * features of the platform. This metric is crucial for learning management 
     * contexts, allowing the frontend to display progress bars, learning streaks, 
     * or mastery levels based on the volume of assessments the user has engaged with.
     */
    @JsonProperty("total_quizzes")
    public int totalQuizzes;
}