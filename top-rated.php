<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';
require_once 'includes/admin.php';

// Get current theme preference (default to dark)
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';

// Get top rated recipes (default is now 20)
$recipes = get_top_rated_recipes(50);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Rated Recipes - Neodock Recipes</title>
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
                        <a class="nav-link" href="index.php">Home</a>
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
                        <a class="nav-link active" href="top-rated.php">Top Rated</a>
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
                    <input class="form-control me-2" type="search" name="search" placeholder="Search recipes">
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
        <h1 class="mb-4">Top Rated Recipes</h1>

        <?php if (empty($recipes)): ?>
            <div class="alert alert-info">No rated recipes found yet. Be the first to rate a recipe!</div>
        <?php else: ?>
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
