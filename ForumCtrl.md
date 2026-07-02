# ForumController and Forum Models

This document explains the forum API controller and the main Eloquent models used by the discussion system.

## 1. ForumController

The controller file is located at [backend-api-laravel/app/Http/Controllers/Api/V1/ForumController.php](backend-api-laravel/app/Http/Controllers/Api/V1/ForumController.php).

It provides the backend endpoints for the forum feature and is responsible for returning forum data and saving new posts.

### Main Methods

#### getGroups()
- Fetches all forum groups from the database.
- Returns a JSON response containing:
  - status
  - total count
  - group data
- This endpoint is used to load the available discussion spaces for the client app.

#### getTopicsByGroup($groupId)
- Finds a specific group by its ID.
- Loads all topics belonging to that group.
- Uses eager loading to fetch the topic creator information efficiently.
- Returns the group name together with the topic list in JSON format.

#### createPost(Request $request)
- Validates incoming request data before saving anything.
- Ensures that:
  - a topic exists
  - a user exists
  - content is provided and long enough
  - the privacy flag is a valid boolean
- Creates a new post record in the database.
- Returns a success response with the created post data.

## 2. Main Models

### Group Model

File: [backend-api-laravel/app/Models/Group.php](backend-api-laravel/app/Models/Group.php)

The Group model represents a forum space or learning cohort.

It defines:
- a fillable set of fields: name and description
- a relationship to many topics
- a relationship to many users through the group_user table

This model is used to organize discussion topics into groups.

### Topic Model

File: [backend-api-laravel/app/Models/Topic.php](backend-api-laravel/app/Models/Topic.php)

The Topic model represents a discussion thread inside a group.

It defines:
- a relationship to its parent group
- a relationship to its creator user
- a relationship to all posts under that topic

This model stores the main conversation thread information.

### Post Model

File: [backend-api-laravel/app/Models/Post.php](backend-api-laravel/app/Models/Post.php)

The Post model represents a single reply or message inside a topic.

It defines:
- fillable fields for topic, user, content, and privacy status
- a boolean cast for is_private
- a relationship to its parent topic
- a relationship to the author user
- a relationship to excluded users for private content restrictions

This model is central to the forum conversation system.

### User Model

File: [backend-api-laravel/app/Models/User.php](backend-api-laravel/app/Models/User.php)

The User model represents a forum participant.

It is used as:
- the author of topics and posts
- a member of groups
- the identity behind forum actions

## 3. Relationship Summary

The forum system works through these key relationships:

- Group has many Topics
- Topic belongs to one Group
- Topic has many Posts
- Post belongs to one Topic
- Post belongs to one User author
- User participates in groups and can create content

## 4. Purpose of the Forum Flow

The flow is:
1. Load available groups
2. Open a selected group
3. View topics inside it
4. Create posts under a topic

This structure keeps the forum logic organized and makes the API easy to extend.
