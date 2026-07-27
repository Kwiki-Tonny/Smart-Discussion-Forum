package com.forum.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;
import java.util.List;

/**
 * Quiz is a Data Transfer Object (DTO) / Model representing a complete quiz assessment 
 * within the Smart Discussion Forum application.
 * 
 * <p><b>Architectural Role:</b>
 * This class serves as the primary data structure for quiz metadata and content. 
 * It is deserialized from the backend API response and consumed by the quiz UI 
 * (e.g., {@code MainController} for the quiz list, and {@code QuizController} for 
 * the active quiz-taking interface). It aggregates core metadata (title, duration, status) 
 * alongside the actual list of {@link Question} objects to be presented to the user.
 * 
 * <p><b>JSON Deserialization Strategy:</b>
 * <ul>
 *   <li>{@link JsonIgnoreProperties}: Configured with {@code ignoreUnknown = true} to ensure 
 *       forward compatibility. If the backend adds new metadata to quizzes (e.g., passing score, 
 *       category tags), the Jackson ObjectMapper will safely ignore them rather than 
 *       throwing an {@code UnrecognizedPropertyException}.</li>
 *   <li>{@link JsonProperty}: Used extensively to explicitly map Java camelCase field names 
 *       to the backend's snake_case JSON keys (e.g., {@code total_questions} to {@code totalQuestions}). 
 *       This maintains clean, idiomatic Java naming conventions while ensuring accurate 
 *       data binding from the API contract.</li>
 * </ul>
 * 
 * @author Forum Development Team
 * @version 2.0
 * @see Question
 * @see QuizAttempt
 * @see com.forum.controllers.MainController
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class Quiz {

    // =========================================================================
    // ─── CORE IDENTIFIERS & METADATA ─────────────────────────────────────────
    // =========================================================================

    /**
     * The unique numerical identifier for this quiz, assigned by the backend database.
     * Used as the primary key for API requests (e.g., starting the quiz, fetching results).
     */
    public int id;

    /**
     * The display title of the quiz (e.g., "Java Concurrency Basics", "Midterm Assessment").
     * This is the primary text shown to the user in the quiz list UI.
     */
    public String title;

    /**
     * The current lifecycle state of the quiz, which dictates user interaction permissions.
     * 
     * <p><b>Expected Values:</b>
     * <ul>
     *   <li>{@code "upcoming"}: The quiz is scheduled but not yet available. UI should show "Coming Soon".</li>
     *   <li>{@code "started"}: The quiz is currently active and accepting submissions.</li>
     *   <li>{@code "ended"}: The quiz deadline has passed. UI should show "Ended" and prevent new attempts.</li>
     * </ul>
     */
    public String status;

    // =========================================================================
    // ─── CONFIGURATION & CONSTRAINTS ─────────────────────────────────────────
    // =========================================================================

    /**
     * The total number of questions contained within this quiz.
     * 
     * <p><b>JSON Mapping:</b> Explicitly mapped from the backend's snake_case key {@code "total_questions"}.
     * <p><b>UI Usage:</b> Used in the quiz list card to give users a quick overview of the quiz length 
     * (e.g., "15 questions · 30 min") and for calculating progress during the attempt.
     */
    @JsonProperty("total_questions")
    public int totalQuestions;

    /**
     * The maximum allowed time to complete the quiz, measured in minutes.
     * 
     * <p><b>JSON Mapping:</b> Explicitly mapped from the backend's snake_case key {@code "duration_minutes"}.
     * <p><b>UI Usage:</b> Consumed by the {@code QuizController} to initialize and drive the countdown 
     * timer displayed during the active quiz session.
     */
    @JsonProperty("duration_minutes")
    public int durationMinutes;

    // =========================================================================
    // ─── CONTENT & USER STATE ────────────────────────────────────────────────
    // =========================================================================

    /**
     * The ordered list of {@link Question} objects that make up this quiz.
     * 
     * <p><b>Data Loading Note:</b> Depending on the specific API endpoint called, this list 
     * may be fully populated (when fetching a specific quiz to take) or it may be {@code null}/empty 
     * (when fetching a high-level summary list of all available quizzes). The UI should handle 
     * potential {@code null} states gracefully.
     */
    public List<Question> questions;

    /**
     * A boolean flag indicating whether the currently authenticated user has already 
     * completed and submitted an attempt for this specific quiz.
     * 
     * <p><b>JSON Mapping:</b> Explicitly mapped from the backend's snake_case key {@code "has_taken"}.
     * <p><b>Business Logic:</b> This is a critical UI state driver. If {@code true}, the "Start Quiz" 
     * button in the UI is disabled and its text is changed to "✅ Done" to prevent duplicate submissions 
     * and enforce the "one attempt per user" rule.
     */
    @JsonProperty("has_taken")
    public boolean hasTaken;
}