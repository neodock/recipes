<?php
namespace Neodock\Recipes;

class RecipeUtilities
{
    public static function GetCategories() : array {
        $db = new \Neodock\Framework\Database();
        $db->query('SELECT count(1) AS recipecount, categories.id, categories.name FROM dbo.recipes INNER JOIN dbo.categories ON recipes.category_id = categories.id WHERE recipes.datedeleted IS NULL AND categories.datedeleted IS NULL GROUP BY categories.id, categories.name ORDER BY name');
        $db->execute();
        return $db->resultset();
    }

     public static function GetCategoryLinks() : string {
        $categories = self::GetCategories();
        $output = '';

        foreach ($categories as $category) {
            $output .= '<li><a class="dropdown-item" href="index.php?controller=Home&page=Index&category=' . urlencode($category['id']) . '">' . $category['name'] . '</a></li>';
        }

        return $output;
    }

    public static function ReadCategoriesFromDisk() : array {
        $config = \Neodock\Framework\Configuration::getInstance();
        $repo_dir = $config->get('recipedir');
        return array_diff(scandir($repo_dir), ['.', '..']);
    }

    public static function ReadRecipesFromDisk() : array {
        $config = \Neodock\Framework\Configuration::getInstance();
        $recipes = [];
        $repo_dir = $config->get('recipedir');

        $categories = self::GetCategories();

        foreach ($categories as $category) {
            $category_path = $repo_dir . DIRECTORY_SEPARATOR . $category['name'];

            if (is_dir($category_path)) {
                $files = scandir($category_path);

                foreach ($files as $file) {
                    // Check if it's a PDF file
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
                        $recipe_path = $category_path . DIRECTORY_SEPARATOR . $file;
                        $recipe_title = \Neodock\Framework\StringUtils::TitleCase(str_replace('-', ' ', pathinfo($file, PATHINFO_FILENAME)));

                        $recipes[] = [
                            'title' => $recipe_title,
                            'filepath' => $recipe_path,
                            'category_id' => $category['id'],
                        ];
                    }
                }
            }
        }
        return $recipes;
    }

    /**
     * @throws \Exception
     */
    public static function GetCategoryName($category_id) {
        $db = new \Neodock\Framework\Database();
        $db->query('SELECT name FROM dbo.categories WHERE id = :id');
        $db->bind(':id', $category_id);
        $db->execute();
        $results = $db->resultset();
        if (count($results) > 0) {
            return $results[0]['name'];
        } else {
            throw new \Exception('Category not found');
        }
    }

    public static function GetCategoryId(string $name) {
        $db = new \Neodock\Framework\Database();
        $db->query('SELECT id FROM dbo.categories WHERE name = :name');
        $db->bind(':name', $name);
        $db->execute();
        $results = $db->resultset();
        if (count($results) > 0) {
            return $results[0]['id'];
        } else {
            throw new \Exception('Category not found');
        }
    }

    public static function GetRecipes($search_query = '', $category_filter = ''): array {
    $results = [];

    $db = new \Neodock\Framework\Database();
    $params = [];

    $query = '
        SELECT
        r.id AS recipe_id,
        r.title AS recipe_title,
        r.filepath AS recipe_filepath,
        c.id AS category_id,
        c.name AS category_name,
        COALESCE((SELECT AVG(rating) FROM dbo.ratings WHERE datedeleted IS NULL AND recipe_id = r.id), 0) AS ratings_average,
        (SELECT COUNT(1) FROM dbo.ratings WHERE datedeleted IS NULL AND recipe_id = r.id) AS ratings_count
    FROM	
        dbo.recipes r
        INNER JOIN dbo.categories c ON r.category_id = c.id
    WHERE
        r.datedeleted IS NULL
        AND c.datedeleted IS NULL';

    if ($category_filter != '') {
        $query .= ' AND c.id = :category_id';
        $params[':category_id'] = $category_filter;
    }

    if ($search_query != '') {
        $query .= ' AND r.title LIKE :search_query';
        $params[':search_query'] = '%' . $search_query . '%';
    }

    $db->query($query);
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    $db->execute();
    $results = $db->resultset();

    return $results;
    }

    /**
     * @throws \Exception
     */
    public static function GetRecipeInfo($id): array
    {
        $config = \Neodock\Framework\Configuration::getInstance();
        $db = new \Neodock\Framework\Database();
        $db->query('SELECT r.id AS recipe_id, r.title AS title, r.description AS description, r.filepath AS path, c.id AS category_id, c.name AS category FROM dbo.recipes r INNER JOIN dbo.categories c ON r.category_id = c.id WHERE r.id = :id AND r.datedeleted IS NULL AND c.datedeleted IS NULL');
        $db->bind(':id', $id);
        $db->execute();
        $results = $db->resultset();

        if (count($results) > 0) {
            $results[0]['url'] = $config->get('baseurl') . '/repo' . str_replace('\\', '/', str_replace($config->get('recipedir'), '', $results[0]['path']));
            return $results[0];
        } else {
            throw new \Exception('Recipe not found');
        }
    }

    public static function DisplayRating($rating): string
    {
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
    public static function GetTopRatedRecipes(int $limit = 20): array {
        $db = new \Neodock\Framework\Database();
        $db->query(
            'SELECT r.id, r.filepath AS path, r.title, c.name AS category, 
                 AVG(rt.rating) as avg_rating, 
                 COUNT(rt.id) as ratings_count 
                 FROM recipes r 
                 INNER JOIN ratings rt ON r.id = rt.recipe_id 
                 INNER JOIN categories c ON r.category_id = c.id
                 GROUP BY r.id, r.filepath, r.title, c.name
                 HAVING COUNT(rt.id) >= 1 
                 ORDER BY avg_rating DESC, ratings_count DESC 
                 OFFSET 0 ROWS FETCH NEXT :limit ROWS ONLY');
        $db->bind(':limit', $limit);
        $db->execute();
        return $db->resultset() ?? [];
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
        //return array('avg_rating' => 0, 'count' => 0);

        $db = new \Neodock\Framework\Database();
        $db->query('SELECT AVG(rating) as avg_rating, COUNT(id) as count FROM dbo.ratings WHERE recipe_id = :id');
        $db->bind(':id', $recipe_id);
        $db->execute();
        return $db->resultset()[0];
    }

    public static function RateRecipe($recipe_id, $rating): void
    {
        $db = new \Neodock\Framework\Database();
        $db->query('INSERT INTO dbo.ratings (recipe_id, rating, dateadded) VALUES (:id, :rating, CURRENT_TIMESTAMP)');
        $db->bind(':id', $recipe_id);
        $db->bind(':rating', $rating);
        $db->execute();
    }
}