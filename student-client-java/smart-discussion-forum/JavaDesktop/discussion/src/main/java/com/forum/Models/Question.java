package com.forum.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class Question {
    public int id;
    public String text;
    public String type;           // "single", "multiple", "text"
    public List<String> options;  // Used for single and multiple choice
}