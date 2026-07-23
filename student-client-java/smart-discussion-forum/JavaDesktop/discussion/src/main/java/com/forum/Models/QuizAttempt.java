package com.forum.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

@JsonIgnoreProperties(ignoreUnknown = true)
public class QuizAttempt {
    public int id;
    @JsonProperty("started_at")
    public String startedAt;
    @JsonProperty("duration_seconds")
    public int durationSeconds;
    public Quiz quiz;

    // For attempts list
    @JsonProperty("quiz_title")
    public String quizTitle;
    public int score;
    @JsonProperty("total_questions")
    public int totalQuestions;
    public String date;
}