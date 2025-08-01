# Neodock Recipes

A PHP-based recipe management system that allows users to browse, search, and rate cooking recipes stored as PDF files.

## Features

- Browse recipes by category
- Search recipes by name
- View PDF recipes directly in the browser
- Rate recipes on a 10-star scale
- View top-rated recipes
- Light and dark mode themes
- Responsive design with Bootstrap 5.3

## Requirements

- PHP 5.6.0 or higher
- Microsoft SQL Server
- SQL Server Driver for PHP
- Web server (Apache, Nginx, etc.)

## Installation

1. Clone this repository to your web server's document root or a subdirectory

2. Run the script to download Bootstrap 5.3:
   ```
   php download-bootstrap.php
   ```

3. Update database configuration in `includes/config.php`:
   ```php
   define('DB_SERVER', 'your_server_name');
   define('DB_USER', 'your_username');
   define('DB_PASSWORD', 'your_password');
   define('DB_NAME', 'NeodockRecipes');

   // Rating configuration
   define('RATING_ENABLED', true); // Master switch to enable/disable rating
   define('USE_IP_ALLOWLIST', true); // Enable IP address filtering
   $RATING_IP_ALLOWLIST = [
       '127.0.0.1',           // localhost
       '192.168.1.0/24',      // IP range in CIDR notation
       'example.domain.com'   // Hostname
   ];
   ```

4. Run the setup script to create the database and tables:
   ```
   php setup.php
   ```

   Alternatively, you can run the SQL script directly against your SQL Server:
   ```
   sqlcmd -S your_server_name -i db-setup.sql
   ```

5. Make sure your web server has permission to read the `repo/` directory and its contents

6. Access the application through your web browser

## Recipe Repository Structure

Recipes are stored as PDF files in the `repo/` directory. The directory structure is as follows:

```
repo/
├── Beef/
├── Chicken-Turkey/
├── Cookies/
├── Desserts/
├── Dressings/
├── Fish/
├── Meat (mixed)/
├── Meatless/
├── Pork/
├── Sides/
├── Soups/
└── [other recipe folders]
```

Each subdirectory represents a recipe category.

## Usage

- **Browse Recipes**: Navigate through the categories to find recipes
- **Search**: Use the search box to find recipes by name
- **View Recipe**: Click on a recipe card to view the PDF and rating options
- **Rate Recipe**: When viewing a recipe, use the star rating system to rate it
- **Theme Toggle**: Use the moon icon in the navbar to switch between dark (default) and light modes
