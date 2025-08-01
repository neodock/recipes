<?php
/**
 * Helper functions for Neodock Recipes
 */

/**
 * Get all recipe categories from the repository
 * 
 * @return array Categories found in the repository
 */
function get_categories() {
    $categories = [];
    $repo_dir = RECIPES_DIR;

    if (is_dir($repo_dir)) {
        $dirs = scandir($repo_dir);

        foreach ($dirs as $dir) {
            if ($dir !== '.' && $dir !== '..' && is_dir($repo_dir . '/' . $dir)) {
                $categories[] = $dir;
            }
        }

        // Sort categories alphabetically
        sort($categories, SORT_STRING | SORT_FLAG_CASE);
    }

    return $categories;
}

/**
 * Generate HTML for category dropdown links
 * 
 * @return string HTML for category links
 */
function get_category_links() {
    $categories = get_categories();
    $output = '';

    foreach ($categories as $category) {
        $output .= '<li><a class="dropdown-item" href="index.php?category=' . urlencode($category) . '">' . htmlspecialchars($category) . '</a></li>';
    }

    return $output;
}

/**
 * Get recipes based on search query and category filter
 * 
 * @param string $search_query Optional search query
 * @param string $category_filter Optional category filter
 * @return array List of recipes matching criteria
 */
function get_recipes($search_query = '', $category_filter = '') {
    $recipes = [];
    $repo_dir = RECIPES_DIR;

    // Get all categories or just the filtered one
    $categories = [];
    if (!empty($category_filter)) {
        $categories[] = $category_filter;
    } else {
        $categories = get_categories();
    }

    // For each category directory
    foreach ($categories as $category) {
        $category_path = $repo_dir . '/' . $category;

        if (is_dir($category_path)) {
            $files = scandir($category_path);

            foreach ($files as $file) {
                // Check if it's a PDF file
                if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
                    $recipe_path = $category_path . '/' . $file;
                    $recipe_title = str_replace('-', ' ', pathinfo($file, PATHINFO_FILENAME));

                    // Skip if doesn't match search query
                    if (!empty($search_query) && stripos($recipe_title, $search_query) === false) {
                        continue;
                    }

                    // Get recipe ID and rating info from database
                    $recipe_id = get_recipe_id($recipe_path);
                    $rating_info = get_recipe_ratings($recipe_id);

                    $recipes[] = [
                        'id' => $recipe_id,
                        'title' => $recipe_title,
                        'path' => $recipe_path,
                        'category' => $category,
                        'avg_rating' => $rating_info['avg_rating'],
                        'ratings_count' => $rating_info['count']
                    ];
                }
            }
        }
    }

    // Also look for PDF files in the root of the repo directory
    $files = scandir($repo_dir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
            $recipe_path = $repo_dir . '/' . $file;
            $recipe_title = str_replace('-', ' ', pathinfo($file, PATHINFO_FILENAME));

            // Skip if doesn't match search query or category filter
            if (!empty($search_query) && stripos($recipe_title, $search_query) === false) {
                continue;
            }
            if (!empty($category_filter)) {
                continue; // Skip files in root if category filter is active
            }

            // Get recipe ID and rating info from database
            $recipe_id = get_recipe_id($recipe_path);
            $rating_info = get_recipe_ratings($recipe_id);

            $recipes[] = [
                'id' => $recipe_id,
                'title' => $recipe_title,
                'path' => $recipe_path,
                'category' => 'Uncategorized',
                'avg_rating' => $rating_info['avg_rating'],
                'ratings_count' => $rating_info['count']
            ];
        }
    }

    return $recipes;
}

/**
 * Get information about a specific recipe
 * 
 * @param string $recipe_path Path to the recipe file
 * @return array Recipe information
 */
function get_recipe_info($recipe_path) {
    // Extract category from path
    $path_parts = explode('/', $recipe_path);
    $category = count($path_parts) > 2 ? $path_parts[1] : 'Uncategorized';

    // Extract title from filename
    $filename = pathinfo($recipe_path, PATHINFO_FILENAME);
    $title = str_replace('-', ' ', $filename);

    // Get recipe ID
    $recipe_id = get_recipe_id($recipe_path);

    return [
        'id' => $recipe_id,
        'title' => $title,
        'path' => $recipe_path,
        'category' => $category
    ];
}

/**
 * Display star rating
 * 
 * @param float $rating Rating value (0-5)
 * @return string HTML for star rating display
 */
function display_rating($rating) {
    $rating = round($rating * 2) / 2; // Round to nearest 0.5
    $output = '<div class="stars">';

    // Full stars
    for ($i = 1; $i <= floor($rating); $i++) {
        $output .= '<i class="fas fa-star text-warning"></i>';
    }

    // Half star if needed
    if ($rating - floor($rating) >= 0.5) {
        $output .= '<i class="fas fa-star-half-alt text-warning"></i>';
        $i++;
    }

    // Empty stars
    for (; $i <= 10; $i++) {
        $output .= '<i class="far fa-star text-warning"></i>';
    }

    $output .= '</div>';
    return $output;
}

/**
 * Get top rated recipes
 * 
 * @param int $limit Number of recipes to retrieve
 * @return array List of top rated recipes
 */
    function get_top_rated_recipes($limit = 20) {
    global $conn;

    $recipes = [];

    $sql = "SELECT r.id, r.path, r.title, r.category, 
                 AVG(rt.rating) as avg_rating, 
                 COUNT(rt.id) as ratings_count 
           FROM recipes r 
           INNER JOIN ratings rt ON r.id = rt.recipe_id 
           GROUP BY r.id, r.path, r.title, r.category 
           HAVING COUNT(rt.id) >= 1 
           ORDER BY avg_rating DESC, ratings_count DESC 
           OFFSET 0 ROWS FETCH NEXT ? ROWS ONLY";

    $stmt = sqlsrv_prepare($conn, $sql, array($limit));

    if (sqlsrv_execute($stmt)) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $recipes[] = $row;
        }
    }

    return $recipes;
}
