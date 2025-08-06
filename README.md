# AI-Powered CMS API (Laravel 11)

![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php)
![Laravel Version](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel)
![Database](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)

A robust and secure REST API for a simple Content Management System (CMS), built with Laravel 11 and MySQL. This project demonstrates modern API development practices, including token-based authentication, role-based access control, and asynchronous job processing for integrating third-party AI services.

The key feature is its AI-powered content enrichment: new articles have their URL slug and a brief summary generated automatically in the background by the **Google Gemini API**, ensuring the user receives an instant response while the heavy lifting is handled asynchronously.

---

## Key Features

-   **Secure Authentication:** Token-based authentication using Laravel Sanctum.
-   **Role-Based Access Control (RBAC):**
    -   **Admin:** Full administrative privileges over all articles and categories.
    -   **Author:** Restricted to managing only their own articles.
-   **Category Management:** Full CRUD (Create, Read, Update, Delete) functionality for categories, restricted to Admins.
-   **Article Management:** Full CRUD functionality for articles with ownership policies.
-   **Asynchronous AI Integration:**
    -   **Slug Generation:** A background job calls the Google Gemini API to generate a unique, SEO-friendly slug based on the article's title and content.
    -   **Summary Generation:** A background job calls the Google Gemini API to generate a 2-3 sentence summary from the article's content.
-   **Queue System:** Utilizes Laravel's queue system with the database driver to process AI jobs in the background without blocking API responses.
-   **Advanced API Filtering:**
    -   Filter articles by `status`, `category` (name or slug), `author` (name), and `published_at` date range.
-   **Database Seeding:** Comes with default Admin and Author users for immediate testing.
-   **Professional Tooling:** Includes a complete Postman collection for easy and comprehensive API testing.

## Technical Stack

-   **Backend:** Laravel 11, PHP 8.2
-   **Database:** MySQL
-   **API Authentication:** Laravel Sanctum
-   **Queue Management:** Laravel Queues (Database Driver)
-   **AI Service:** Google Gemini API
-   **API Client:** Postman

## Setup and Installation

Follow these steps to get the project up and running on your local machine.

### Prerequisites

-   PHP >= 8.2
-   Composer
-   MySQL
-   A **Google Gemini API Key** from [Google AI Studio](https://aistudio.google.com/)

### Installation Steps

1.  **Clone the repository:**
    ```bash
    git clone [https://github.com/](https://github.com/)[your-github-username]/[your-repo-name].git
    cd [your-repo-name]
    ```

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

3.  **Create and configure your environment file:**
    -   Copy the example environment file:
        ```bash
        cp .env.example .env
        ```
    -   Open the `.env` file and configure your database connection (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
    -   Add your Google Gemini API key at the bottom:
        ```ini
        GEMINI_API_KEY="your-gemini-api-key-goes-here"
        ```

4.  **Generate a new application key:**
    ```bash
    php artisan key:generate
    ```

5.  **Run the database migrations and seed the database:**
    -   This command will create all necessary tables and populate the `users` table with default Admin and Author accounts.
    ```bash
    php artisan migrate --seed
    ```

6.  **Start the local development server:**
    ```bash
    php artisan serve
    ```

7.  **Start the queue worker:**
    -   **This step is essential for the AI features to work.** The queue worker processes the background jobs. Open a **new, separate terminal window** and run:
    ```bash
    php artisan queue:work
    ```

The API is now running and accessible at `http://127.0.0.1:8000`.

---

## Testing with Postman

A complete Postman collection is included in this repository to test all API endpoints.

1.  **Import the Collection:** Import the `CMS_API_Collection.postman_collection.json` file into your Postman client.
2.  **Configure the Base URL:** The collection uses a `{{base_url}}` variable. To set it, click on the collection, go to the "Variables" tab, and set the `CURRENT VALUE` of `base_url` to `http://127.0.0.1:8000`.
3.  **Authentication:**
    -   Run the **`[POST] Login as Admin`** or **`[POST] Login as Author`** request first.
    -   The API token is **automatically saved** to a collection variable `{{api_token}}`.
    -   All other protected requests will use this token automatically.

### Default User Credentials

-   **Admin:**
    -   **Email:** `admin@example.com`
    -   **Password:** `password123`
-   **Author:**
    -   **Email:** `author1@example.com`
    -   **Password:** `password123`

---
## API Endpoint Overview

| Method      | URI                     | Action                          | Protected By              |
| :---------- | :---------------------- | :------------------------------ | :------------------------ |
| **POST** | `/api/login`            | Log in a user                   | Public                    |
| **POST** | `/api/logout`           | Log out the current user        | Sanctum                   |
|             |                         |                                 |                           |
| **GET** | `/api/categories`       | List all categories             | Sanctum + Admin Middleware|
| **POST** | `/api/categories`       | Create a new category           | Sanctum + Admin Middleware|
| **GET** | `/api/categories/{id}`  | Get a single category           | Sanctum + Admin Middleware|
| **PUT** | `/api/categories/{id}`  | Update a category               | Sanctum + Admin Middleware|
| **DELETE** | `/api/categories/{id}`  | Delete a category               | Sanctum + Admin Middleware|
|             |                         |                                 |                           |
| **GET** | `/api/articles`         | List & filter articles          | Sanctum                   |
| **POST** | `/api/articles`         | Create a new article            | Sanctum                   |
| **GET** | `/api/articles/{id}`    | Get a single article            | Sanctum                   |
| **PUT** | `/api/articles/{id}`    | Update an article               | Sanctum + Article Policy  |
| **DELETE** | `/api/articles/{id}`    | Delete an article               | Sanctum + Article Policy  |