package com.forum.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

/**
 * Group is a Data Transfer Object (DTO) / Model representing a discussion group 
 * within the Smart Discussion Forum application.
 * 
 * <p><b>Architectural Role:</b>
 * This class serves as the primary data structure for group-related information, 
 * bridging the gap between the backend REST API's JSON responses and the JavaFX 
 * frontend UI components. Instances of this class are used to populate group lists, 
 * determine membership status, and provide context for topics and posts.
 * 
 * <p><b>JSON Deserialization Strategy:</b>
 * <ul>
 *   <li>{@link JsonIgnoreProperties}: Configured with {@code ignoreUnknown = true} to ensure 
 *       forward compatibility. If the backend API adds new fields to the group payload in the 
 *       future, the Jackson ObjectMapper will safely ignore them rather than throwing a 
 *       {@code UnrecognizedPropertyException} and crashing the deserialization process.</li>
 *   <li>{@link JsonProperty}: Used to explicitly map Java camelCase field names to the 
 *       backend's snake_case JSON keys (e.g., {@code is_member} to {@code isMember}), 
 *       adhering to Java naming conventions while maintaining API compatibility.</li>
 * </ul>
 * 
 * @author Forum Development Team
 * @version 2.0
 * @see com.forum.controllers.MainController
 * @see com.forum.services.ApiService
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class Group {

    // =========================================================================
    // ─── CORE IDENTIFIERS & METADATA ─────────────────────────────────────────
    // =========================================================================

    /**
     * The unique numerical identifier for this group, assigned by the backend database.
     * Used as the primary key for API requests (e.g., fetching topics for this specific group).
     */
    public int id;

    /**
     * The display name of the group (e.g., "Computer Science 101", "General Discussion").
     * This is the primary text shown to the user in the group list UI.
     */
    public String name;

    /**
     * An optional, brief summary or description of the group's purpose and rules.
     * May be {@code null} if the group creator did not provide one.
     */
    public String description;

    /**
     * The ISO 8601 formatted timestamp indicating when this group was created on the server.
     * Example format: "2023-10-27T10:00:00Z"
     */
    public String created_at;

    // =========================================================================
    // ─── DYNAMIC STATE & STATISTICS ──────────────────────────────────────────
    // =========================================================================

    /**
     * A boolean flag indicating whether the currently authenticated user is a member of this group.
     * 
     * <p><b>JSON Mapping:</b> Explicitly mapped from the backend's snake_case key {@code "is_member"} 
     * to adhere to Java camelCase naming conventions.
     */
    @JsonProperty("is_member")
    public boolean isMember;

    /**
     * The total number of discussion topics currently active or archived within this group.
     * Used in the UI to give users a quick sense of the group's activity level.
     * 
     * <p><b>JSON Mapping:</b> Explicitly mapped from the backend's snake_case key {@code "topics_count"}.
     */
    @JsonProperty("topics_count")
    public int topicsCount;

    /**
     * The total number of registered users who have joined this group.
     * Used in the UI to indicate the size and community scale of the group.
     * 
     * <p><b>JSON Mapping:</b> Explicitly mapped from the backend's snake_case key {@code "users_count"}.
     */
    @JsonProperty("users_count")
    public int usersCount;
}