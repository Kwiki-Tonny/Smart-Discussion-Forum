package com.forum.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.databind.JsonNode;

@JsonIgnoreProperties(ignoreUnknown = true)
public class Topic {
    public int id;
    public int group_id;
    public String title;
    public String description;
    public int creator_id;
    public String created_at;
    public JsonNode creator;
}
