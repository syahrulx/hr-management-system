# Railway Deployment Guide

This guide describes how to deploy your HR Management System to Railway.

## 1. Prerequisites

-   A Railway account.
-   Your project pushed to GitHub.
-   Your Supabase database credentials.

## 2. Setup Steps

1.  **Create New Project in Railway**:

    -   Select "Deploy from GitHub repo".
    -   Choose your `hr-management-system` repository.
    -   Click "Deploy Now".

2.  **Configure Variables**:

    -   Go to the **Settings** or **Variables** tab of your new service.
    -   Add the following variables (copy values from your local `.env` or Supabase dashboard):

    | Variable        | Value / Note                                                                         |
    | :-------------- | :----------------------------------------------------------------------------------- |
    | `APP_NAME`      | `HR System`                                                                          |
    | `APP_ENV`       | `production`                                                                         |
    | `APP_DEBUG`     | `false`                                                                              |
    | `APP_URL`       | `https://<your-railway-url>.up.railway.app` (You get this after generating a domain) |
    | `APP_KEY`       | Copy from local `.env` or generate new one                                           |
    | `LOG_CHANNEL`   | `stderr` (Important for seeing logs in Railway)                                      |
    | `DB_CONNECTION` | `pgsql`                                                                              |
    | `DB_HOST`       | `<your-supabase-host>`                                                               |
    | `DB_PORT`       | `5432`                                                                               |
    | `DB_DATABASE`   | `postgres`                                                                           |
    | `DB_USERNAME`   | `<your-supabase-user>`                                                               |
    | `DB_PASSWORD`   | `<your-supabase-password>`                                                           |

3.  **Generate a Domain**:
    -   Go to **Settings** -> **Networking**.
    -   Click "Generate Domain" to get a public URL.
    -   Update `APP_URL` variable with this domain.

## 3. Deployment Behavior

-   The included `Procfile` will automatically:
    -   Run database migrations (`php artisan migrate --force`).
    -   Cache configurations for speed.
    -   Start the server.
-   **Note**: The build process (`npm run build`) is handled automatically by Railway's build system.
