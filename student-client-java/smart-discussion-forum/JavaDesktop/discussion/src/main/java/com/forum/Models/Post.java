package com.forum.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.databind.JsonNode;

import java.util.List;
import java.util.ArrayList;

@JsonIgnoreProperties(ignoreUnknown = true)
public class Post {
    public int id;
    public int topic_id;
    public int user_id;
    public String content;
    public boolean is_private;
    public String created_at;
    public JsonNode author;
    public JsonNode excluded_users;
    public Integer likes_count;
    public Boolean is_liked;

    // ─── NESTED REPLY SUPPORT ───────────────────────────────────
    @JsonProperty("parent_id")   // maps JSON "parent_id" to this field
    public Integer parentId;      // null means top‑level post

    public List<Post> replies;    // child replies (populated after tree building)

    // ─── CONSTRUCTORS ──────────────────────────────────────────
    public Post() {
        this.replies = new ArrayList<>();
    }

    // ─── HELPER METHODS ────────────────────────────────────────
    public List<Integer> getExcludedUserIds() {
        if (excluded_users == null || !excluded_users.isArray()) {
            return new ArrayList<>();
        }
        List<Integer> ids = new ArrayList<>();
        for (JsonNode node : excluded_users) {
            if (node.has("id")) {
                ids.add(node.path("id").asInt());
            }
        }
        return ids;
    }

    // Optional: convenience method to check if it's a top‑level post
    public boolean isTopLevel() {
        return parentId == null || parentId == 0;
    }
}