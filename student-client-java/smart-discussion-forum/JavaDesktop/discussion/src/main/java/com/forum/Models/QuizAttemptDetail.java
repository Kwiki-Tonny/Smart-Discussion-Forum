package com.forum.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

@JsonIgnoreProperties(ignoreUnknown = true)
public class QuizAttemptDetail {
    public int correct;
    public int incorrect;
    public int unanswered;
    public double percentage;
    @JsonProperty("total_questions")
    public int totalQuestions;
    @JsonProperty("quiz_title")
    public String quizTitle;
}