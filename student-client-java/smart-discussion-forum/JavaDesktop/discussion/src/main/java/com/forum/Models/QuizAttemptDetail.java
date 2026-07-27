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
 * Architectural Note: This is a critical resilience pattern. It ensures that if 
 * the API client sends additional fields (or if the frontend evolves to send 
 * newer analytical metrics), the backend deserialization will not fail with an 
 * UnrecognizedPropertyException. This provides forward and backward compatibility 
 * for the API contract without requiring immediate backend schema updates.
 */
import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

/**
 * Jackson annotation used to explicitly map a JSON property name to a Java field.
 * 
 * Architectural Note: This is used here to bridge the gap between standard 
 * Java naming conventions (camelCase) and standard JSON/API naming conventions 
 * (snake_case). This ensures the API remains RESTful and consistent with 
 * typical JSON schema designs while keeping the Java codebase idiomatic.
 */
import com.fasterxml.jackson.annotation.JsonProperty;

/**
 * Represents the granular statistical breakdown and summary metrics of a specific 
 * quiz attempt result.
 * 
 * This model is specifically designed to power "Results" or "Analytics" views 
 * after a user has completed a quiz. Unlike the {@link QuizAttempt} model, which 
 * focuses on temporal metadata (start time, duration) and general identity, this 
 * class focuses exclusively on performance metrics and scoring breakdowns.
 * 
 * Usage Context:
 * - Returned in detailed result endpoints to show the user exactly how they 
 *   performed (correct vs. incorrect vs. unanswered).
 * - Used by the frontend to render visual analytics, such as donut charts, 
 *   progress bars, or targeted feedback messages based on the percentage.
 * 
 * Serialization Behavior:
 * - Unknown JSON properties are safely ignored (@JsonIgnoreProperties).
 * - Specific fields are explicitly mapped to snake_case JSON keys to maintain 
 *   a consistent external API contract.
 * 
 * @see QuizAttempt
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class QuizAttemptDetail {

    /**
     * The total number of questions answered correctly in this quiz attempt.
     * 
     * Purpose: This is the primary positive performance metric. It is used by 
     * the frontend to highlight user success, calculate final grades, and 
     * potentially unlock achievements or next-level content based on thresholds.
     */
    public int correct;

    /**
     * The total number of questions answered incorrectly in this quiz attempt.
     * 
     * Purpose: This metric identifies knowledge gaps. In advanced UI implementations, 
     * this number may be used to trigger specific feedback loops, such as 
     * recommending review materials or highlighting specific topics that need 
     * further study.
     */
    public int incorrect;

    /**
     * The total number of questions that were skipped or left unanswered by the user.
     * 
     * Purpose: Tracking unanswered questions is critical for accurate assessment. 
     * Depending on the quiz grading rules, unanswered questions may be treated 
     * as incorrect, or they may be neutral. Providing this distinct count allows 
     * the frontend to give precise feedback (e.g., "You skipped 3 questions") 
     * rather than lumping them in with incorrect answers.
     */
    public int unanswered;

    /**
     * The overall score of the quiz attempt, expressed as a percentage.
     * 
     * Purpose: This is the definitive, normalized metric for user performance. 
     * Using a double allows for fractional percentages (e.g., 83.33) when the 
     * total number of questions does not divide evenly into 100. 
     * 
     * Expected Range: Typically 0.0 to 100.0. This value is heavily utilized by 
     * the frontend for rendering visual progress indicators, determining pass/fail 
     * status, and sorting historical attempts by performance.
     */
    public double percentage;

    /**
     * The total number of questions that were part of this specific quiz attempt.
     * 
     * JSON Mapping: "total_questions"
     * Purpose: This serves as the denominator for all statistical calculations 
     * (correct + incorrect + unanswered should ideally equal this number). 
     * It is explicitly included in this detail object to ensure the frontend 
     * has complete context for the metrics without needing to reference the 
     * parent Quiz entity, preventing race conditions or missing data in the UI.
     */
    @JsonProperty("total_questions")
    public int totalQuestions;

    /**
     * The title of the quiz associated with this detailed result.
     * 
     * JSON Mapping: "quiz_title"
     * Purpose: A denormalized field provided for immediate contextual display. 
     * When a user views their detailed results, showing the quiz title prominently 
     * is essential for user orientation. Including it here avoids the need for 
     * the backend to perform an additional join or fetch the full Quiz object, 
     * optimizing the payload size and response time for the results endpoint.
     */
    @JsonProperty("quiz_title")
    public String quizTitle;
}