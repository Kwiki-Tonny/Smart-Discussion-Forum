package com.forum.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

@JsonIgnoreProperties(ignoreUnknown = true)
public class User {
    public int id;
    public String name;
    public String email;
    public String role;
    public String status;
}
