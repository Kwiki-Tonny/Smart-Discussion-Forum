package main.java.com.forum.Models;



import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

@JsonIgnoreProperties(ignoreUnknown = true)
public class Group {
    public int id;
    public String name;
    public String description;
    public String created_at;
}
