<?php
/**
 * Database connection and functions for Neodock Recipes
 */

// Connect to SQL Server
$connectionInfo = array(
    "Database" => DB_NAME,
    "UID" => DB_USER,
    "PWD" => DB_PASSWORD,
    "CharacterSet" => "UTF-8"
);

$conn = sqlsrv_connect(DB_SERVER, $connectionInfo);

// Check connection
if (!$conn) {
    // In production, you might want to handle this more gracefully
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// Make sure tables exist
ensure_tables_exist();

/**
 * Create necessary tables if they don't exist
 */
function ensure_tables_exist() {
    global $conn;

    // Check if recipes table exists
    $recipes_table_sql = "
    IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'recipes')
    BEGIN
        CREATE TABLE recipes (
            id INT IDENTITY(1,1) PRIMARY KEY,
            path NVARCHAR(255) NOT NULL UNIQUE,
            title NVARCHAR(255) NOT NULL,
            category NVARCHAR(100) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    END";

    $ratings_table_sql = "
    IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'ratings')
    BEGIN
        CREATE TABLE ratings (
            id INT IDENTITY(1,1) PRIMARY KEY,
            recipe_id INT NOT NULL,
            rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 10),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (recipe_id) REFERENCES recipes(id)
        );
    END";

    // Execute the queries
    sqlsrv_query($conn, $recipes_table_sql);
    sqlsrv_query($conn, $ratings_table_sql);
}

/**
 * Get a recipe ID from the database or create if it doesn't exist
 * 
 * @param string $path Path to the recipe file
 * @return int Recipe ID
 */
function get_recipe_id($path) {
    global $conn;

    // Check if recipe exists in database
    $sql = "SELECT id FROM recipes WHERE path = ?";
    $stmt = sqlsrv_prepare($conn, $sql, array($path));
    sqlsrv_execute($stmt);

    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        return $row['id'];
    }

    // Recipe doesn't exist, create it
    // Extract category from path
    $path_parts = explode('/', $path);
    $category = count($path_parts) > 2 ? $path_parts[1] : 'Uncategorized';

    // Extract title from filename
    $filename = pathinfo($path, PATHINFO_FILENAME);
    $title = str_replace('-', ' ', $filename);

    // Insert recipe directly without calling get_recipe_info()
    $sql = "INSERT INTO recipes (path, title, category) VALUES (?, ?, ?)";
    $params = array($path, $title, $category);

    $stmt = sqlsrv_prepare($conn, $sql, $params);
    if (sqlsrv_execute($stmt)) {
        // Get the ID of the inserted recipe
        $sql = "SELECT id FROM recipes WHERE path = ?";
        $stmt = sqlsrv_prepare($conn, $sql, array($path));
        sqlsrv_execute($stmt);

        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            return $row['id'];
        }
    }

    // If something went wrong, return 0
    return 0;
}

/**
 * Get recipe ratings information
 * 
 * @param int $recipe_id Recipe ID
 * @return array Average rating and count
 */
function get_recipe_ratings($recipe_id) {
    global $conn;

    $sql = "SELECT AVG(rating) as avg_rating, COUNT(id) as count 
           FROM ratings WHERE recipe_id = ?";

    $stmt = sqlsrv_prepare($conn, $sql, array($recipe_id));
    sqlsrv_execute($stmt);

    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        return array(
            'avg_rating' => $row['avg_rating'] ? $row['avg_rating'] : 0,
            'count' => $row['count']
        );
    }

    return array('avg_rating' => 0, 'count' => 0);
}

/**
 * Save a new rating for a recipe
 * 
 * @param int $recipe_id Recipe ID
 * @param int $rating Rating value (1-10)
 * @return bool Success status
 */
function save_rating($recipe_id, $rating) {
    global $conn;

    // Check if rating functionality is enabled
    if (!defined('RATING_ENABLED') || !RATING_ENABLED) {
        return false;
    }

    // Check if client IP is allowed to rate
    if (!is_client_ip_allowed()) {
        return false;
    }

    $sql = "INSERT INTO ratings (recipe_id, rating) VALUES (?, ?)";
    $params = array($recipe_id, $rating);

    $stmt = sqlsrv_prepare($conn, $sql, $params);
    return sqlsrv_execute($stmt);
}
