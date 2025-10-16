# API Documentation

**Date:** October 16, 2025  
**Version:** 1.0  
**Status:** ✅ **COMPLETE**

---

## 🎯 **Overview**

This document provides comprehensive API documentation for the {{APP_NAME}} application. The API follows RESTful principles and provides endpoints for post management, user authentication, company management, and Instagram integration.

---

## 📋 **Table of Contents**

1. [Authentication](#authentication)
2. [Post Management API](#post-management-api)
3. [User Management API](#user-management-api)
4. [Company Management API](#company-management-api)
5. [Instagram Integration API](#instagram-integration-api)
6. [Media Management API](#media-management-api)
7. [Error Handling](#error-handling)
8. [Rate Limiting](#rate-limiting)
9. [Response Formats](#response-formats)

---

## 🔐 **Authentication**

### Authentication Methods

The API supports multiple authentication methods:

- **Session-based Authentication** (Web routes)
- **Token-based Authentication** (API routes)
- **Magic Link Authentication** (Email-based)

### Authentication Headers

```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Authentication Endpoints

| Method | Endpoint           | Description            |
| ------ | ------------------ | ---------------------- |
| POST   | `/login`           | User login             |
| POST   | `/register`        | User registration      |
| POST   | `/logout`          | User logout            |
| POST   | `/forgot-password` | Password reset request |
| POST   | `/reset-password`  | Password reset         |
| POST   | `/verify-email`    | Email verification     |

---

## 📝 **Post Management API**

### Base URL

```
/posts
```

### Endpoints

#### Get Posts

```http
GET /posts
```

**Query Parameters:**

- `status` (string): Filter by post status (`draft`, `scheduled`, `published`, `failed`)
- `type` (string): Filter by post type (`feed`, `story`, `reel`, `carousel`)
- `instagram_account_id` (integer): Filter by Instagram account
- `search` (string): Search in title and caption
- `page` (integer): Page number for pagination
- `per_page` (integer): Items per page (default: 15)

**Response:**

```json
{
    "data": [
        {
            "id": 1,
            "title": "Sample Post",
            "caption": "This is a sample post #example",
            "type": "feed",
            "status": "draft",
            "scheduled_at": null,
            "published_at": null,
            "hashtags": ["example"],
            "mentions": [],
            "media": [
                {
                    "id": 1,
                    "type": "image",
                    "filename": "sample.jpg",
                    "url": "/storage/posts/1/sample.jpg",
                    "order": 1
                }
            ],
            "created_at": "2025-10-16T10:00:00Z",
            "updated_at": "2025-10-16T10:00:00Z"
        }
    ],
    "links": {
        "first": "http://localhost/posts?page=1",
        "last": "http://localhost/posts?page=10",
        "prev": null,
        "next": "http://localhost/posts?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 10,
        "per_page": 15,
        "to": 15,
        "total": 150
    }
}
```

#### Create Post

```http
POST /posts
```

**Request Body:**

```json
{
    "title": "New Post",
    "caption": "This is a new post #new #example",
    "type": "feed",
    "instagram_account_id": 1,
    "scheduled_at": "2025-10-17T15:00:00Z",
    "media": [
        {
            "type": "image",
            "file": "base64_encoded_image_data",
            "filename": "image.jpg"
        }
    ]
}
```

**Response:**

```json
{
    "message": "Post created successfully",
    "post": {
        "id": 2,
        "title": "New Post",
        "caption": "This is a new post #new #example",
        "type": "feed",
        "status": "draft",
        "scheduled_at": "2025-10-17T15:00:00Z",
        "hashtags": ["new", "example"],
        "mentions": [],
        "media": [
            {
                "id": 2,
                "type": "image",
                "filename": "image.jpg",
                "url": "/storage/posts/2/image.jpg",
                "order": 1
            }
        ],
        "created_at": "2025-10-16T10:30:00Z",
        "updated_at": "2025-10-16T10:30:00Z"
    }
}
```

#### Get Single Post

```http
GET /posts/{id}
```

**Response:**

```json
{
    "id": 1,
    "title": "Sample Post",
    "caption": "This is a sample post #example",
    "type": "feed",
    "status": "draft",
    "scheduled_at": null,
    "published_at": null,
    "hashtags": ["example"],
    "mentions": [],
    "media": [
        {
            "id": 1,
            "type": "image",
            "filename": "sample.jpg",
            "url": "/storage/posts/1/sample.jpg",
            "order": 1
        }
    ],
    "created_at": "2025-10-16T10:00:00Z",
    "updated_at": "2025-10-16T10:00:00Z"
}
```

#### Update Post

```http
PUT /posts/{id}
PATCH /posts/{id}
```

**Request Body:**

```json
{
    "title": "Updated Post Title",
    "caption": "Updated caption #updated",
    "type": "feed",
    "media": [
        {
            "type": "image",
            "file": "base64_encoded_image_data",
            "filename": "new_image.jpg"
        }
    ],
    "delete_media": [1, 2]
}
```

**Response:**

```json
{
    "message": "Post updated successfully",
    "post": {
        "id": 1,
        "title": "Updated Post Title",
        "caption": "Updated caption #updated",
        "type": "feed",
        "status": "draft",
        "hashtags": ["updated"],
        "mentions": [],
        "media": [
            {
                "id": 3,
                "type": "image",
                "filename": "new_image.jpg",
                "url": "/storage/posts/1/new_image.jpg",
                "order": 1
            }
        ],
        "updated_at": "2025-10-16T11:00:00Z"
    }
}
```

#### Delete Post

```http
DELETE /posts/{id}
```

**Response:**

```json
{
    "message": "Post deleted successfully"
}
```

#### Schedule Post

```http
POST /posts/{id}/schedule
```

**Request Body:**

```json
{
    "scheduled_at": "2025-10-17T15:00:00Z"
}
```

**Response:**

```json
{
    "message": "Post scheduled successfully",
    "post": {
        "id": 1,
        "status": "scheduled",
        "scheduled_at": "2025-10-17T15:00:00Z",
        "updated_at": "2025-10-16T11:30:00Z"
    }
}
```

#### Get Post Statistics

```http
GET /posts/stats
```

**Response:**

```json
{
    "total": 150,
    "drafts": 45,
    "scheduled": 30,
    "publishing": 5,
    "published": 65,
    "failed": 5
}
```

---

## 👤 **User Management API**

### Base URL

```
/users
```

### Endpoints

#### Get User Profile

```http
GET /profile
```

**Response:**

```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": "2025-10-16T10:00:00Z",
    "locale": "en",
    "timezone": "America/New_York",
    "current_company": {
        "id": 1,
        "name": "Example Company",
        "role": "admin"
    },
    "created_at": "2025-10-16T10:00:00Z",
    "updated_at": "2025-10-16T10:00:00Z"
}
```

#### Update User Profile

```http
PUT /profile
PATCH /profile
```

**Request Body:**

```json
{
    "name": "John Smith",
    "locale": "ru",
    "timezone": "Europe/Moscow"
}
```

**Response:**

```json
{
    "message": "Profile updated successfully",
    "user": {
        "id": 1,
        "name": "John Smith",
        "email": "john@example.com",
        "locale": "ru",
        "timezone": "Europe/Moscow",
        "updated_at": "2025-10-16T12:00:00Z"
    }
}
```

#### Update Password

```http
PUT /password
```

**Request Body:**

```json
{
    "current_password": "old_password",
    "password": "new_password",
    "password_confirmation": "new_password"
}
```

**Response:**

```json
{
    "message": "Password updated successfully"
}
```

#### Delete User Account

```http
DELETE /profile
```

**Request Body:**

```json
{
    "password": "current_password"
}
```

**Response:**

```json
{
    "message": "Account deleted successfully"
}
```

---

## 🏢 **Company Management API**

### Base URL

```
/companies
```

### Endpoints

#### Get Company Settings

```http
GET /companies/settings
```

**Response:**

```json
{
    "company": {
        "id": 1,
        "name": "Example Company",
        "description": "A sample company",
        "website": "https://example.com",
        "created_at": "2025-10-16T10:00:00Z",
        "updated_at": "2025-10-16T10:00:00Z"
    },
    "team_members": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "admin",
            "joined_at": "2025-10-16T10:00:00Z"
        }
    ],
    "invitations": [
        {
            "id": 1,
            "email": "invite@example.com",
            "role": "user",
            "status": "pending",
            "created_at": "2025-10-16T11:00:00Z"
        }
    ]
}
```

#### Update Company Settings

```http
PUT /companies/settings
```

**Request Body:**

```json
{
    "name": "Updated Company Name",
    "description": "Updated description",
    "website": "https://updated.com"
}
```

**Response:**

```json
{
    "message": "Company settings updated successfully",
    "company": {
        "id": 1,
        "name": "Updated Company Name",
        "description": "Updated description",
        "website": "https://updated.com",
        "updated_at": "2025-10-16T12:00:00Z"
    }
}
```

#### Invite Team Member

```http
POST /companies/invite
```

**Request Body:**

```json
{
    "email": "newmember@example.com",
    "role": "user"
}
```

**Response:**

```json
{
    "message": "Invitation sent successfully",
    "invitation": {
        "id": 2,
        "email": "newmember@example.com",
        "role": "user",
        "status": "pending",
        "created_at": "2025-10-16T12:30:00Z"
    }
}
```

#### Update Team Member Role

```http
PUT /companies/members/{id}/role
```

**Request Body:**

```json
{
    "role": "admin"
}
```

**Response:**

```json
{
    "message": "Member role updated successfully"
}
```

#### Remove Team Member

```http
DELETE /companies/members/{id}
```

**Response:**

```json
{
    "message": "Team member removed successfully"
}
```

---

## 📸 **Instagram Integration API**

### Base URL

```
/instagram
```

### Endpoints

#### Get Instagram Accounts

```http
GET /instagram/accounts
```

**Response:**

```json
{
    "accounts": [
        {
            "id": 1,
            "username": "example_account",
            "instagram_user_id": "123456789",
            "account_type": "personal",
            "profile_picture_url": "https://example.com/profile.jpg",
            "followers_count": 1000,
            "status": "active",
            "is_active": true,
            "is_token_expired": false,
            "is_token_expiring_soon": false,
            "token_expires_at": "2025-12-16T10:00:00Z",
            "last_synced_at": "2025-10-16T10:00:00Z",
            "created_at": "2025-10-16T10:00:00Z"
        }
    ],
    "has_company": true
}
```

#### Connect Instagram Account

```http
POST /instagram/connect
```

**Request Body:**

```json
{
    "code": "instagram_oauth_code",
    "state": "csrf_state_token"
}
```

**Response:**

```json
{
    "message": "Instagram account connected successfully",
    "account": {
        "id": 2,
        "username": "new_account",
        "instagram_user_id": "987654321",
        "account_type": "business",
        "status": "active",
        "created_at": "2025-10-16T13:00:00Z"
    }
}
```

#### Disconnect Instagram Account

```http
DELETE /instagram/accounts/{id}
```

**Response:**

```json
{
    "message": "Instagram account disconnected successfully"
}
```

#### Sync Instagram Account

```http
POST /instagram/accounts/{id}/sync
```

**Response:**

```json
{
    "message": "Instagram account synced successfully",
    "account": {
        "id": 1,
        "last_synced_at": "2025-10-16T13:30:00Z",
        "followers_count": 1050
    }
}
```

---

## 🎬 **Media Management API**

### Base URL

```
/media
```

### Endpoints

#### Upload Media

```http
POST /media/upload
```

**Request Body:**

```json
{
    "file": "base64_encoded_file_data",
    "filename": "image.jpg",
    "type": "image"
}
```

**Response:**

```json
{
    "message": "Media uploaded successfully",
    "media": {
        "id": 1,
        "filename": "image.jpg",
        "original_filename": "image.jpg",
        "mime_type": "image/jpeg",
        "file_size": 1024000,
        "storage_path": "posts/1/image.jpg",
        "url": "/storage/posts/1/image.jpg",
        "type": "image",
        "metadata": {
            "width": 1920,
            "height": 1080
        },
        "created_at": "2025-10-16T14:00:00Z"
    }
}
```

#### Get Media File

```http
GET /media/{id}
```

**Response:**

```json
{
    "id": 1,
    "filename": "image.jpg",
    "original_filename": "image.jpg",
    "mime_type": "image/jpeg",
    "file_size": 1024000,
    "storage_path": "posts/1/image.jpg",
    "url": "/storage/posts/1/image.jpg",
    "type": "image",
    "metadata": {
        "width": 1920,
        "height": 1080
    },
    "created_at": "2025-10-16T14:00:00Z"
}
```

#### Delete Media

```http
DELETE /media/{id}
```

**Response:**

```json
{
    "message": "Media deleted successfully"
}
```

---

## ⚠️ **Error Handling**

### Error Response Format

All API errors follow a consistent format:

```json
{
    "message": "Error description",
    "errors": {
        "field_name": ["Specific error message"]
    },
    "error_code": "VALIDATION_ERROR"
}
```

### HTTP Status Codes

| Code | Description           |
| ---- | --------------------- |
| 200  | Success               |
| 201  | Created               |
| 400  | Bad Request           |
| 401  | Unauthorized          |
| 403  | Forbidden             |
| 404  | Not Found             |
| 422  | Validation Error      |
| 429  | Too Many Requests     |
| 500  | Internal Server Error |

### Common Error Codes

| Error Code             | Description               |
| ---------------------- | ------------------------- |
| `VALIDATION_ERROR`     | Request validation failed |
| `AUTHENTICATION_ERROR` | Authentication failed     |
| `AUTHORIZATION_ERROR`  | Insufficient permissions  |
| `NOT_FOUND`            | Resource not found        |
| `RATE_LIMIT_EXCEEDED`  | Too many requests         |
| `INSTAGRAM_API_ERROR`  | Instagram API error       |
| `FILE_UPLOAD_ERROR`    | File upload failed        |

### Example Error Responses

#### Validation Error (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "title": ["The title field is required."],
        "caption": [
            "The caption field must not be greater than 2200 characters."
        ]
    },
    "error_code": "VALIDATION_ERROR"
}
```

#### Authentication Error (401)

```json
{
    "message": "Unauthenticated.",
    "error_code": "AUTHENTICATION_ERROR"
}
```

#### Not Found Error (404)

```json
{
    "message": "Post not found.",
    "error_code": "NOT_FOUND"
}
```

---

## 🚦 **Rate Limiting**

### Rate Limits

| Endpoint Type  | Limit        | Window   |
| -------------- | ------------ | -------- |
| Authentication | 5 requests   | 1 minute |
| API Endpoints  | 60 requests  | 1 minute |
| File Upload    | 10 requests  | 1 minute |
| Instagram API  | 200 requests | 1 hour   |

### Rate Limit Headers

```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

### Rate Limit Exceeded Response

```json
{
    "message": "Too many requests. Please try again later.",
    "error_code": "RATE_LIMIT_EXCEEDED",
    "retry_after": 60
}
```

---

## 📊 **Response Formats**

### Pagination

All list endpoints support pagination:

```json
{
  "data": [...],
  "links": {
    "first": "http://localhost/api/posts?page=1",
    "last": "http://localhost/api/posts?page=10",
    "prev": null,
    "next": "http://localhost/api/posts?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 15,
    "to": 15,
    "total": 150
  }
}
```

### Sorting

Most list endpoints support sorting:

```
GET /posts?sort=created_at&direction=desc
GET /posts?sort=title&direction=asc
```

### Filtering

List endpoints support various filters:

```
GET /posts?status=draft&type=feed
GET /posts?instagram_account_id=1
GET /posts?search=example
```

### Field Selection

You can specify which fields to include:

```
GET /posts?fields=id,title,status
```

---

## 🔧 **SDK Examples**

### JavaScript/Node.js

```javascript
const axios = require('axios')

const api = axios.create({
    baseURL: 'https://your-app.com/api',
    headers: {
        Authorization: 'Bearer your-token',
        'Content-Type': 'application/json',
    },
})

// Create a post
const createPost = async (postData) => {
    try {
        const response = await api.post('/posts', postData)
        return response.data
    } catch (error) {
        console.error('Error creating post:', error.response.data)
        throw error
    }
}

// Get posts
const getPosts = async (filters = {}) => {
    try {
        const response = await api.get('/posts', { params: filters })
        return response.data
    } catch (error) {
        console.error('Error fetching posts:', error.response.data)
        throw error
    }
}
```

### PHP

```php
<?php

class AutopostAPI {
    private $baseUrl;
    private $token;

    public function __construct($baseUrl, $token) {
        $this->baseUrl = $baseUrl;
        $this->token = $token;
    }

    public function createPost($data) {
        $response = $this->makeRequest('POST', '/posts', $data);
        return $response;
    }

    public function getPosts($filters = []) {
        $response = $this->makeRequest('GET', '/posts', $filters);
        return $response;
    }

    private function makeRequest($method, $endpoint, $data = []) {
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST' || $method === 'PUT') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
```

### Python

```python
import requests
import json

class AutopostAPI:
    def __init__(self, base_url, token):
        self.base_url = base_url
        self.token = token
        self.headers = {
            'Authorization': f'Bearer {token}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }

    def create_post(self, data):
        response = requests.post(
            f'{self.base_url}/posts',
            headers=self.headers,
            json=data
        )
        response.raise_for_status()
        return response.json()

    def get_posts(self, filters=None):
        response = requests.get(
            f'{self.base_url}/posts',
            headers=self.headers,
            params=filters or {}
        )
        response.raise_for_status()
        return response.json()
```

---

## 📚 **Additional Resources**

### Webhooks

The API supports webhooks for real-time notifications:

- **Post Published**: Triggered when a post is successfully published
- **Post Failed**: Triggered when a post fails to publish
- **Account Connected**: Triggered when an Instagram account is connected
- **Account Disconnected**: Triggered when an Instagram account is disconnected

### Webhook Payload Example

```json
{
    "event": "post.published",
    "timestamp": "2025-10-16T15:00:00Z",
    "data": {
        "post_id": 1,
        "instagram_account_id": 1,
        "published_at": "2025-10-16T15:00:00Z"
    }
}
```

### Testing

Use the provided test endpoints for development:

```
GET /api/test/posts - Get test posts
POST /api/test/posts - Create test post
GET /api/test/instagram - Test Instagram connection
```

---

## 📞 **Support**

For API support and questions:

- **Documentation**: [API Documentation](https://your-app.com/docs/api)
- **Support Email**: api-support@your-app.com
- **Status Page**: [API Status](https://status.your-app.com)

---

**Last Updated:** October 16, 2025  
**Version:** 1.0  
**Status:** ✅ **COMPLETE**
