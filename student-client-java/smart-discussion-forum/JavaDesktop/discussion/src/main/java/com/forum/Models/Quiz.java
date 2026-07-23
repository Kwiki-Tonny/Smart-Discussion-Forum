package com.forum.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;
import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class Quiz {
    public int id;
    public String title;
    public String status;
    @JsonProperty("total_questions")
    public int totalQuestions;
    @JsonProperty("duration_minutes")
    public int durationMinutes;
    public List<Question> questions;
}