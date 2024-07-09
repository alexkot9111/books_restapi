# books_restapi

## Overview

This project provides a REST API for managing information about books and their authors using Symfony.

## Features

 - CRUD Operations: Perform Create, Read, Update operations for books and authors via API endpoints.
 - Data Validation: Validate data inputs to ensure consistency and integrity.
 - Database Integration: Utilize PostgreSQL database for storing book and author information.
 - Symfony Components: Leveraging Symfony components for routing, controllers, ORM (Doctrine), and API response handling.

## Prerequisites

  - Docker Engine
  - Docker Compose

## Installation

1. **Clone the Repository**

      ```bash
      git clone https://github.com/alexkot9111/books_restapi.git
      cd books_restapi
      ```

2. **Build and Start Docker Containers**

  This command starts the Symfony application, PostgreSQL database, and other configured services in detached mode:
      ```bash
      docker-compose up -d --build
      ```
      
3. **Install Composer Dependencies**

  Access the Symfony container and install PHP dependencies using Composer:
      ```bash
      docker-compose exec php-fpm composer install
      ```
            
4. **Run Database Migrations**

  Apply database migrations to set up the database schema:
      ```bash
      docker-compose exec php-fpm bin/console doctrine:migrations:migrate --no-interaction
      ```
                  
5. **Accessing the API:**

  The API endpoints can be accessed via http://localhost:32000
  1) Books:
   - /api/books (GET) - Retrieve all books
   - /api/books/search (POST) - Search books by params
   - /api/books/create (POST) - Create a new book
   - /api/books/{id} (PUT) - Update a new book
   - /api/books/{id} (GET) - Show concrete book
     
  2) Authors:
   - /api/authors (GET) - Retrieve all authors
   - /api/authors/create (POST) - Create a new author

## Running Tests

  To run the PHPUnit tests, use the following command:
      ```bash
      docker-compose exec php-fpm php bin/phpunit
      ```
      
## Stopping the Containers

  To stop the Docker containers:
      ```bash
      docker-compose down
      ```
      
