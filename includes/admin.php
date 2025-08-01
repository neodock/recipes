<?php
require_once 'ip_utils.php';

/**
 * Admin functionality for Neodock Recipes
 */

/**
 * Check if current user is allowed to access admin features
 * 
 * @return bool True if user is allowed, false otherwise
 */
function is_admin_allowed(): bool
{
    global $ADMIN_IP_ALLOWLIST;

    // If USE_IP_ALLOWLIST is disabled, allow all IPs
    if (!defined('ADMIN_ENABLED') || !ADMIN_ENABLED) {
        return false;
    }

    // If allowlist is empty, deny all
    if (empty($ADMIN_IP_ALLOWLIST)) {
        return false;
    }

    $client_ip = get_client_ip();

    // Check if IP is in allowlist
    foreach ($ADMIN_IP_ALLOWLIST as $allowed) {
        // Check if it's a hostname
        if (!filter_var($allowed, FILTER_VALIDATE_IP) && strpos($allowed, '/') === false) {
            // Try to resolve hostname to IP
            $resolved_ips = gethostbynamel($allowed);
            if ($resolved_ips && in_array($client_ip, $resolved_ips)) {
                return true;
            }
            continue;
        }

        // Check if IP is in range
        if (ip_in_range($client_ip, $allowed)) {
            return true;
        }
    }

    return false;
}

/**
 * Find orphaned recipes in the database (recipes that no longer exist on disk)
 * 
 * @return array Array of orphaned recipe records
 */
function find_orphaned_recipes() {
    global $conn;

    $orphaned_recipes = [];

    // Get all recipes from database
    $sql = "SELECT id, path, title, category FROM recipes";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Check if file exists on disk
            if (!file_exists(RECIPE_BASE_DIR . $row['path'])) {
                // Get rating count for this recipe
                $rating_info = get_recipe_ratings($row['id']);
                $row['ratings_count'] = $rating_info['count'];
                $orphaned_recipes[] = $row;
            }
        }
    }

    return $orphaned_recipes;
}

/**
 * Delete orphaned recipe and its ratings from the database
 * 
 * @param int $recipe_id Recipe ID to delete
 * @return bool True on success, false on failure
 */
function delete_orphaned_recipe($recipe_id) {
    global $conn;

    // Start a transaction
    sqlsrv_begin_transaction($conn);

    // First delete ratings for this recipe
    $sql = "DELETE FROM ratings WHERE recipe_id = ?";
    $stmt = sqlsrv_prepare($conn, $sql, array($recipe_id));
    $success = sqlsrv_execute($stmt);

    if (!$success) {
        sqlsrv_rollback($conn);
        return false;
    }

    // Then delete the recipe
    $sql = "DELETE FROM recipes WHERE id = ?";
    $stmt = sqlsrv_prepare($conn, $sql, array($recipe_id));
    $success = sqlsrv_execute($stmt);

    if (!$success) {
        sqlsrv_rollback($conn);
        return false;
    }

    // Commit the transaction
    sqlsrv_commit($conn);
    return true;
}

/**
 * Clean up all orphaned recipes
 * 
 * @return array Result with counts and status
 */
function cleanup_orphaned_recipes() {
    $orphaned_recipes = find_orphaned_recipes();
    $result = array(
        'total' => count($orphaned_recipes),
        'deleted' => 0,
        'failed' => 0,
        'details' => array()
    );

    foreach ($orphaned_recipes as $recipe) {
        $success = delete_orphaned_recipe($recipe['id']);
        if ($success) {
            $result['deleted']++;
            $result['details'][] = array(
                'id' => $recipe['id'],
                'title' => $recipe['title'],
                'status' => 'deleted'
            );
        } else {
            $result['failed']++;
            $result['details'][] = array(
                'id' => $recipe['id'],
                'title' => $recipe['title'],
                'status' => 'failed'
            );
        }
    }

    return $result;
}
