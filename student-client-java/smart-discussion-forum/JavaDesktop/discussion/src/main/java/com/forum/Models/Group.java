package com.forum.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

@JsonIgnoreProperties(ignoreUnknown = true)
public class Group {
    public int id;
    public String name;
    public String description;
    public String created_at;
    
    @JsonProperty("is_member")
    public boolean isMember;
}