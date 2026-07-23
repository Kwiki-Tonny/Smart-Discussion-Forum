package com.forum.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

@JsonIgnoreProperties(ignoreUnknown = true)
public class UserStats {
    @JsonProperty("total_posts")
    public int totalPosts;

    @JsonProperty("total_replies")
    public int totalReplies;

    @JsonProperty("total_topics")
    public int totalTopics;

    @JsonProperty("total_quizzes")
    public int totalQuizzes;
}