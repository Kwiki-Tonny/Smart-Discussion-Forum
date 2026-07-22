package main.java.com.forum.Models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.databind.JsonNode;

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
}