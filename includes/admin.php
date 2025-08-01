<?php
/**
 * Admin functionality for Neodock Recipes
 */

/**
 * Check if current user is allowed to access admin features
 * 
 * @return bool True if user is allowed, false otherwise
 */
function is_admin_allowed() {
    global $ADMIN_IP_ALLOWLIST;

    // If admin functionality is disabled, nobody is allowed
    if (!defined('ADMIN_ENABLED') || !ADMIN_ENABLED) {
        return false;
    }

    // Get client IP address
    $client_ip = $_SERVER['REMOTE_ADDR'];
    $client_hostname = gethostbyaddr($client_ip);

    // Check if client IP is in the allowlist
    foreach ($ADMIN_IP_ALLOWLIST as $allowed) {
        // Check if it's a direct IP match
        if ($allowed === $client_ip) {
            return true;
        }

        // Check if it's a hostname match
        if ($allowed === $client_hostname) {
            return true;
        }

        // Check if it's a CIDR notation
        if (strpos($allowed, '/') !== false) {
            list($subnet, $mask) = explode('/', $allowed);

            // Convert IP to binary string
            $ip_binary = ip2long($client_ip);
            $subnet_binary = ip2long($subnet);

            // Create mask
            $mask_binary = -1 << (32 - $mask);

            // Check if IP is in subnet
            if (($ip_binary & $mask_binary) === ($subnet_binary & $mask_binary)) {
                return true;
            }
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
