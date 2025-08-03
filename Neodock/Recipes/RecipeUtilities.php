<?php
namespace Neodock\Recipes;

class RecipeUtilities
{
    public static function GetCategories() : array {
        $config = \Neodock\Framework\Configuration::getInstance();

        $categories = [];
        $repo_dir = $config->get('recipe_repo');

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

     public static function GetCategoryLinks() : string {
        $categories = self::GetCategories();
        $output = '';

        foreach ($categories as $category) {
            $output .= '<li><a class="dropdown-item" href="index.php?module=Home&page=index&category=' . urlencode($category) . '">' . htmlspecialchars($category) . '</a></li>';
        }

        return $output;
    }

    public static function GetRecipes($search_query = '', $category_filter = '') {
        $config = \Neodock\Framework\Configuration::getInstance();
        $recipes = [];
        $repo_dir = $config->get('recipe_repo');

        // Get all categories or just the filtered one
        $categories = [];
        if (!empty($category_filter)) {
            $categories[] = $category_filter;
        } else {
            $categories = self::GetCategories();
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
                        $recipe_id = self::GetRecipeId($recipe_path);
                        $rating_info = self::GetRecipeRatings($recipe_id);

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
                $recipe_id = self::GetRecipeId($recipe_path);
                $rating_info = self::GetRecipeRatings($recipe_id);

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

    public static function GetRecipeInfo($recipe_path) {
        // Extract category from path
        $path_parts = explode('/', $recipe_path);
        $category = count($path_parts) > 2 ? $path_parts[1] : 'Uncategorized';

        // Extract title from filename
        $filename = pathinfo($recipe_path, PATHINFO_FILENAME);
        $title = str_replace('-', ' ', $filename);

        // Get recipe ID
        $recipe_id = self::GetRecipeId($recipe_path);

        return [
            'id' => $recipe_id,
            'title' => $title,
            'path' => $recipe_path,
            'category' => $category
        ];
    }

    public static function DisplayRating($rating) {
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
    public static function GetTopRatedRecipes($limit = 20) {
        return [];

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

    public static function GetRecipeId($path) : int {
        return 0;
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

    public static function GetRecipeRatings($recipe_id) : array {
        return array('avg_rating' => 0, 'count' => 0);

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
}