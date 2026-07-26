package com.forum.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;
import java.util.List;

/**
 * Question is a Data Transfer Object (DTO) / Model representing a single question 
 * within a {@link Quiz} in the Smart Discussion Forum application.
 * 
 * <p><b>Architectural Role:</b>
 * This class serves as the fundamental data structure for quiz assessments. 
 * It is deserialized from the backend API response and consumed by the quiz UI 
 * (e.g., {@code QuizController}) to dynamically render the appropriate input 
 * controls based on the question type (e.g., radio buttons for single-choice, 
 * checkboxes for multiple-choice, or a text area for open-ended text responses).
 * 
 * <p><b>JSON Deserialization Strategy:</b>
 * <ul>
 *   <li>{@link JsonIgnoreProperties}: Configured with {@code ignoreUnknown = true} to ensure 
 *       forward compatibility. If the backend adds new metadata to questions (e.g., difficulty 
 *       level, point value), the Jackson ObjectMapper will safely ignore them rather than 
 *       throwing an {@code UnrecognizedPropertyException}.</li>
 *   <li>{@link JsonProperty}: Used to explicitly map the backend's generic JSON key 
 *       {@code "question"} to the more semantically clear Java field name {@code text}. 
 *       This maintains clean, readable Java code while ensuring accurate data binding 
 *       from the API payload.</li>
 * </ul>
 * 
 * @author Forum Development Team
 * @version 2.0
 * @see Quiz
 * @see com.forum.controllers.QuizController
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class Question {

    // =========================================================================
    // ─── CORE IDENTIFIERS ────────────────────────────────────────────────────
    // =========================================================================

    /**
     * The unique numerical identifier for this question, assigned by the backend database.
     * Used to track user answers and correlate responses during quiz submission.
     */
    public int id;

    // =========================================================================
    // ─── QUESTION CONTENT & METADATA ─────────────────────────────────────────
    // =========================================================================

    /**
     * The actual text/prompt of the question presented to the user.
     * 
     * <p><b>JSON Mapping:</b> Explicitly mapped from the backend's JSON key {@code "question"} 
     * to the more descriptive Java field name {@code text}. This avoids awkward phrasing 
     * like {@code question.question} in the Java codebase while maintaining perfect 
     * alignment with the API contract.
     */
    @JsonProperty("question")
    public String text;

    /**
     * The format type of this question, which dictates how the UI should render the input controls.
     * 
     * <p><b>Allowed Values:</b>
     * <ul>
     *   <li>{@code "single"}: Render as radio buttons (only one valid selection).</li>
     *   <li>{@code "multiple"}: Render as checkboxes (multiple valid selections allowed).</li>
     *   <li>{@code "text"}: Render as a free-form text input or text area (no predefined options).</li>
     * </ul>
     */
    public String type;

    /**
     * A list of predefined answer choices for this question.
     * 
     * <p><b>Business Logic:</b>
     * <ul>
     *   <li>For {@code "single"} and {@code "multiple"} choice types, this list contains 
     *       the string values that will be displayed as selectable options to the user.</li>
     *   <li>For {@code "text"} type questions, this list will typically be {@code null} 
     *       or empty, as the user provides a custom, free-form response.</li>
     * </ul>
     * 
     * <p><b>Safety Note:</b> The UI rendering logic should always check for {@code null} 
     * or empty states before attempting to iterate over this list to prevent 
     * {@code NullPointerException} crashes.
     */
    public List<String> options;
}