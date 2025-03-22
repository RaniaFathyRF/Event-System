# Event System Installation Guide (Using Docker and Supervisor)

## Overview

The **Event System** is a Laravel-based web application designed for efficient event management, including webhook processing, ticket synchronization, user authentication, and event workflows. This project incorporates **Filament Dashboard** for streamlined management and visualization.

## Key Features
- **Admin Dashboard** (Filament Panel): Manage tickets, users, and logs with CRUD functionality.
- **Webhook Handling**: Processes incoming webhooks from Tito for syncing ticket and event data.
- **Queue Processing**: Uses Laravel queues for background job handling.
- **Event-Based Workflows**: Automates key tasks with the Laravel scheduler.

---

## Architecture

The system architecture follows modular design principles, as shown below:

```plaintext
+--------------+       +-------------+       +----------+
| Admin/User   | ----> | Nginx       | ----> | Laravel  |
| Interface    |       | Web Server  |       | Backend  |
+--------------+       +-------------+       +----+-----+
                                                |
                                                |
                                        +-------v-------+
                                        | MySQL Database |
                                        +---------------+
                                                               
                                        +---------------+
                                        | Redis Queue   |
                                        +---------------+
```

1. **Frontend**: Admin dashboard and user interfaces provided by Filament and Laravel Blade and swagger documentation for APIS.
2. **Backend**: Laravel handling ticketing APIs, webhooks, and workflows.
3. **Database**: Stores tickets, users, and event log information.
4. **Queue System**: Redis-backed queues for asynchronous job processing.
5. **External API Integration**: Communicates with Tito for event and ticket data.
---

## Prerequisites

Before proceeding, ensure the following tools and dependencies are installed:

- **System Requirements**
    - Docker & Docker Compose
    - Supervisor
    - Node.js and npm (for building frontend assets)
- **Laravel Prerequisites**
    - PHP ≥ 8.2 via Docker
    - Composer for dependency management
    - MySQL ≥ 8.0 for persistence


## High-Level Integration Flow

Below is a visual representation of how the Tito API integrates with our Laravel-based Event System.

```plaintext
+---------------+                                 +-------------------+
| Event System  |     Requests API Calls         |     Tito API      |
| (Laravel App) +------------------------------->+ (tito.io/v3)      |
+---------------+                                 +-------------------+
         ^                                                     |
         |                                                     v
         |                            Sends Tickets and Event Updates (Webhooks)
         +<----------------------------------------------------+
```

---

By following these steps, your Laravel application will be able to securely integrate with the Tito API for ticket management and webhook updates. If you encounter any issues, refer to the Laravel documentation or Tito’s official API guide.

---

## Installation Steps

### Step 1: Clone the Repository

```bash
git clone <repository-url>
cd event-system
```

---

### Step 2: Configure Environment Variables

Copy and edit the `.env` file:

```bash
cp .env.example .env
```

Update the following configurations in `.env`:

- **Database Configuration**:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=event_system
    DB_USERNAME=root
    DB_PASSWORD=root_password
    ```
- **APP Configuration**
  ```env
    APP_ENV=local
    APP_KEY=base64:fg81E9/m+/VCTY15CeI2rQRyj5zbkqbMhUI6oHxTwKA=
    APP_DEBUG=true
    APP_URL=https://localhost:8000
    APP_LOCALE=en
    APP_FALLBACK_LOCALE=en
    APP_FAKER_LOCALE=en_US
    APP_MAINTENANCE_DRIVER=file
    PHP_CLI_SERVER_WORKERS=4
    BCRYPT_ROUNDS=12
    VITE_APP_NAME="${APP_NAME}"
  ```
- **Logs Configuration**
  ```env
    LOG_CHANNEL=stack
    LOG_STACK=single
    LOG_DEPRECATIONS_CHANNEL=null
    LOG_LEVEL=debug
  ```
- **Queue Configuration**
  ```env
    BROADCAST_CONNECTION=log
    FILESYSTEM_DISK=local
    QUEUE_CONNECTION=database
  ```
- **Mail Configuration**
  ```env
    MAIL_MAILER=smtp
    MAIL_HOST=smtp.gmail.com  # Change this based on your provider
    MAIL_PORT=587
    MAIL_USERNAME=example@gmail.com
    MAIL_PASSWORD={mail password}
    MAIL_ENCRYPTION=tls
    MAIL_FROM_ADDRESS=example@gmail.com
    MAIL_FROM_NAME={app name}
  ```

- **Redis Configuration**
  ```env
    CACHE_STORE=database
    MEMCACHED_HOST=127.0.0.1
    REDIS_CLIENT=phpredis
    REDIS_HOST=127.0.0.1
    REDIS_PASSWORD=null
    REDIS_PORT=6379
  ```
- **Tito API Configuration**:
  - **Tito Configuration**: To integrate Tito services with the Event System, you need to generate and securely store your API keys. Follow the instructions below to configure your Tito API token in the Laravel application.
    - **Obtain Your API Key from Tito**
        - Go to (https://id.tito.io/).
        - Log in with your Tito account credentials.
        - Navigate to **API Keys**.
        - Generate a new **Personal Access Token**.
        - Copy the generated access token (store it securely as it will not be shown again!).

    - **Add API Credentials to Your `.env` File**
        - Edit your application’s `.env` file and include the following variables:
    - **TITO_API_KEY**: Paste the **API key/token** obtained from Tito Identity(https://id.tito.io/api-access-tokens).
    - **TITO_API_ACCOUNT**: This is your Tito account’s slug.  
      You can find it in your Tito dashboard URL. For example:  
      If your dashboard URL is `https://your-account.tito.io`, use `your-account` for `TITO_API_ACCOUNT`.
    - **TITO_API_EVENT**: Paste event slug from Tito dashboard URL.
    - **TITO_WEBHOOK_SECRET**: Create new webhook endpoint from Settings > Webhook Endpoints
      Copy your security token from there and paste it here.

  ```env
    TITO_API_KEY=your_tito_api_token
    TITO_API_BASE=https://api.tito.io/v3
    TITO_API_ACCOUNT=your_tito_account_slug
    TITO_API_EVENT=your_tito_event_slug
    TITO_WEBHOOK_SECRET=your_webhook_secret_here
  ```

---

### Step 3: Start Docker Containers

Build and start the application containers:

```bash
docker-compose up -d
```

Verify your containers are running:

```bash
docker ps
```

You should see services like `app`, `mysql`, `nginx`, `schedule`, and `queues`.

---

### Step 4: Install Dependencies

```bash
docker exec -it <app-container> bash
composer install
php artisan key:generate
exit
```

---

### Step 5: Migrate and Seed the Database

Run the following commands to set up the database schema and seed default application data:

```bash
docker exec -it <app-container> bash
php artisan migrate --seed
exit
```

---

### Step 6: Set Up Queue Workers with Supervisor

Queue processing is essential for handling webhooks and tickets asynchronously. Set up **Supervisor** to manage Laravel's queue workers.

 1.**Verify Worker Status**:

   ```bash
   docker exec -it <app-container> supervisorctl status
   ```
 2.**Verify schedule logs**:

   ```bash
   docker-compose logs scheduler
   docker logs <scheduler-container>
   ```

 3.**Logs for worker activity can be found in**:
   ```bash
   /var/www/html/storage/logs/webhook-worker.log
   /var/www/html/storage/logs/syncTickets-worker.log
   ```
---

### Step 7: Sync Tickets from Tito API TO Laravel Database

Run the following commands to sync tito tickets in laravel Database:

```bash
docker exec -it <app-container> bash
php artisan sync:tickets
exit
```

---
### Step 8: Access the Application

1. Access the application in your browser:

    - **Frontend Swagger APIS Interface**: http://localhost:8000/api/documentation#/
      -** Postman APIS Documentation**: https://documenter.getpostman.com/view/20126221/2sAYkHny68#1c14ac81-f3d5-4c47-b820-fa7dc146ba7d 
    - **Admin Dashboard: `http://localhost:8000/admin/login`**

      - **Log in with the seeded admin credentials**
      
           - **Super Admin User**: Grant access to all Tickets and Users with Cruds
           ```bash
             username: super_admin@example.com
             password: password
           ```
      
          - **Admin User**: Grant access to all Tickets with only Soft Delete
          ```
             username: admin@example.com
             password: password
          ```

---

### Architecture Diagram (UML Style)

```plaintext
+-----------------+               +---------------------+               +----------------------+
|   Admin Panel   |               |     Laravel App     |               |   Tito API/Webhooks  |
| (Filament)      |               |  (PHP Backend)      |               | (3rd Party Service)  |
+-----------------+               +---------------------+               +----------------------+
         |                                |                                    |
         |                                |                                    |
         +--------------------------------v------------------------------------+
                                      +----------------+
                                      |  Nginx/Redis   |
                                      | (Load Balancer)|
                                      +----------------+
                                               |
                                      +----------------+
                                      |  MySQL DB       |
                                      +----------------+
```

### Commands Cheat Sheet

| Command                      | Description                  |
|------------------------------|------------------------------|
| `docker-compose up -d`       | Start the containers         |
| `docker-compose down`        | Stop the containers          |
| `docker exec -it app bash`   | Access Laravel container     |
| `php artisan migrate --seed` | Run migrations and seed data |
| `php artisan sync:tickets`   | Run command and sync Tickets |
---

### Troubleshooting

#### Common Issues:

1. **Database Connection Errors**:
   Ensure `.env` configuration matches the Docker `mysql` container settings.

2. **Queue Workers Not Processing**:
    - Verify Supervisor is running:
      ```bash
      docker exec -it <app-container> supervisorctl status
      ```
    - Check logs:
      ```bash
      docker exec -it <app-container> tail -f /var/log/supervisord.log
      ```

3. **Webhook & Sync Tickets Not Working**:
    - Check webhook log folder from here:
      ```bash
         /storage/logs/webhooks/*
         /storage/logs/syncTickets/*
      ```
   - OR You can access logs from here:
     ```bash
        http://localhost:8000/log-viewer
     ```

4. **Rebuild Containers**:
   To apply changes to configurations:
   ```bash
   docker-compose down
   docker-compose up --build -d
   ```

---

## System Summary

| Component | Technology |
| --- | --- |
| Backend | Laravel (PHP 8.2) |
| Database | MySQL |
| Queue | Redis |
| Scheduler & Worker | Laravel Scheduler + Supervisor |
| Admin Panel | Filament Dashboard |
| Third-Party APIs | Tito Integration |
