package com.forum.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.databind.JsonNode;

import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class Post {
    public int id;
    public int topic_id;
    public int user_id;
    public String content;
    public boolean is_private;          // primitive boolean (no null)
    public String created_at;
    public JsonNode author;
    public JsonNode excluded_users;
    public Integer likes_count;         // can be null
    public Boolean is_liked;            // can be null
    public List<Post> replies;          // for nested replies
}