# API Reference

This document describes all API endpoints currently defined in the `backend-api-laravel` backend for the Smart Discussion Forum project.

## Base API URL

- Local development base: `http://localhost:8000/api`
- API version prefix: `/v1`

## Authentication

- A default Laravel API route exists at `/user` and is protected by `auth:sanctum` middleware.
- The current custom forum endpoints do not require authentication in `routes/api.php`.

## Endpoints

### 1. Health Check

- Method: `GET`
- URL: `/api/v1/health-check`
- Description: Verifies that the API backend is running and returns a simple status payload.

#### Response

```json
{
  "status": "online",
  "timestamp": "2026-06-22T12:34:56+00:00",
  "project": "Smart Academic Forum Core Engine"
}
```

### 2. Get Groups

- Method: `GET`
- URL: `/api/v1/groups`
- Description: Retrieves the list of learning groups or cohorts available in the forum.
- Controller: `App\Http\Controllers\Api\V1\ForumController@getGroups`

#### Response

```json
{
  "status": "success",
  "count": 2,
  "data": [
    {
      "id": 1,
      "name": "Group A",
      "description": "Study group for beginners.",
      "created_at": "2026-06-17T10:25:30.000000Z"
    }
  ]
}
```

### 3. Get Topics by Group

- Method: `GET`
- URL: `/api/v1/groups/{id}/topics`
- Description: Retrieves all discussion topics that belong to a specific group.
- Path parameter:
  - `id` (integer) - group identifier
- Controller: `App\Http\Controllers\Api\V1\ForumController@getTopicsByGroup`

#### Successful Response

```json
{
  "status": "success",
  "group_name": "Group A",
  "data": [
    {
      "id": 10,
      "title": "Welcome to the study forum",
      "group_id": 1,
      "creator": {
        "id": 5,
        "name": "Alice",
        "role": "student"
      }
    }
  ]
}
```

#### Error Response (group not found)

```json
{
  "message": "No query results for model [App\\Models\\Group] 999"
}
```

### 4. Publish Post

- Method: `POST`
- URL: `/api/v1/posts/publish`
- Description: Creates a new forum post attached to a topic.
- Controller: `App\Http\Controllers\Api\V1\ForumController@createPost`

#### Request Body

```json
{
  "topic_id": 12,
  "user_id": 7,
  "content": "This is my reply to the topic.",
  "is_private": false
}
```

#### Validation Rules

- `topic_id`: required, must exist in `topics.id`
- `user_id`: required, must exist in `users.id`
- `content`: required, string, minimum length 3
- `is_private`: required, boolean

#### Success Response

```json
{
  "status": "created",
  "message": "Post successfully recorded in database tracking registers.",
  "data": {
    "id": 42,
    "topic_id": 12,
    "user_id": 7,
    "content": "This is my reply to the topic.",
    "is_private": false,
    "created_at": "2026-06-22T12:34:56.000000Z",
    "updated_at": "2026-06-22T12:34:56.000000Z"
  }
}
```

#### Common Validation Error Response

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "content": [
      "The content field is required."
    ]
  }
}
```

## Notes

- The API is implemented in `backend-api-laravel/routes/api.php` and the custom endpoint logic is contained in `backend-api-laravel/app/Http/Controllers/Api/V1/ForumController.php`.
- The custom forum endpoints currently expose basic read/write operations for groups, topics, and posts.
- Additional API endpoints can be added under the `/v1` prefix to support quiz, user interaction, or notification features later.
