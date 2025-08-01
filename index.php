<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';
require_once 'includes/admin.php';

// Get current theme preference (default to dark)
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';

// Get search query if exists
$search_query = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

// Get category filter if exists
$category_filter = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '';

// Get top rated recipes for homepage (up to 20)
$top_rated_recipes = get_top_rated_recipes(); // Using default limit of 20

// Get recipe directories and recipes based on search/filter
$recipes = get_recipes($search_query, $category_filter);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neodock Recipes</title>
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Neodock Recipes</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown">
                            Categories
                        </a>
                        <ul class="dropdown-menu">
                            <?php echo get_category_links(); ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="top-rated.php">Top Rated</a>
                    </li>
                    <?php if (is_admin_allowed()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-shield-alt"></i> Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="admin/index.php">Dashboard</a></li>
                            <li><a class="dropdown-item" href="admin/orphaned-records.php">Orphaned Records</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                <form class="d-flex" action="index.php" method="get">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search recipes" value="<?php echo $search_query; ?>">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                </form>
                <div class="ms-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="themeSwitch" <?php echo $theme === 'dark' ? 'checked' : ''; ?>>
                    <label class="form-check-label text-light" for="themeSwitch">
                        <i class="fas fa-moon"></i>
                    </label>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <?php if (!empty($search_query) || !empty($category_filter)): ?>
            <div class="alert alert-info">
                <?php if (!empty($search_query)): ?>
                    <h4>Search results for: "<?php echo $search_query; ?>"</h4>
                <?php endif; ?>
                <?php if (!empty($category_filter)): ?>
                    <h4>Category: <?php echo ucfirst($category_filter); ?></h4>
                <?php endif; ?>
                <a href="index.php" class="btn btn-sm btn-secondary">Clear filters</a>
            </div>
        <?php else: ?>
            <div class="jumbotron p-5 mb-4 rounded">
                <h1 class="display-4">Welcome to Neodock Recipes</h1>
                <p class="lead">Browse categories below, check out our top-rated recipes, or use the search box to find specific recipes.</p>
            </div>
        <?php endif; ?>

        <?php if (empty($search_query) && empty($category_filter)): ?>
            <!-- Display alphabetized list of categories when no search/filter is active -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>Browse by Category</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php 
                                $categories = get_categories();
                                foreach ($categories as $category): 
                                ?>
                                <div class="col-md-4 mb-3">
                                    <a href="index.php?category=<?php echo urlencode($category); ?>" class="text-decoration-none">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-folder me-2 text-warning"></i>
                                            <span><?php echo $category; ?></span>
                                            <?php 
                                            // Count recipes in this category
                                            $count = count(get_recipes('', $category));
                                            ?>
                                            <span class="badge bg-secondary ms-2"><?php echo $count; ?></span>
                                        </div>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Display top rated recipes section if we have any rated recipes -->
            <?php if (!empty($top_rated_recipes)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Top 20 Recipes</h4>
                            <a href="top-rated.php" class="btn btn-sm btn-outline-primary">View Details</a>
                        </div>
                        <div class="card-body">
                            <div class="row row-cols-1 row-cols-md-4 g-4">
                                <?php foreach ($top_rated_recipes as $recipe): ?>
                                <div class="col">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $recipe['title']; ?></h5>
                                            <p class="card-text">Category: <?php echo $recipe['category']; ?></p>
                                            <div class="mb-2">
                                                <?php echo display_rating($recipe['avg_rating']); ?>
                                                <small class="text-muted">(<?php echo $recipe['ratings_count']; ?> ratings)</small>
                                            </div>
                                            <a href="view-recipe.php?path=<?php echo urlencode($recipe['path']); ?>" class="btn btn-primary">View Recipe</a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Only show recipes when a category is selected or search is performed -->
            <?php if (!empty($recipes)): ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($recipes as $recipe): ?>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $recipe['title']; ?></h5>
                                <p class="card-text">Category: <?php echo $recipe['category']; ?></p>
                                <div class="mb-2">
                                    <?php echo display_rating($recipe['avg_rating']); ?>
                                    <small class="text-muted">(<?php echo $recipe['ratings_count']; ?> ratings)</small>
                                </div>
                                <a href="view-recipe.php?path=<?php echo urlencode($recipe['path']); ?>" class="btn btn-primary">View Recipe</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-warning">No recipes found matching your criteria.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> Neodock Recipes. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>
