<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';
require_once 'includes/ip_utils.php';

// Get current theme preference (default to dark)
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';

// Check if recipe path is provided
if (!isset($_GET['path']) || empty($_GET['path'])) {
    header('Location: index.php');
    exit;
}

$recipe_path = $_GET['path'];
$recipe_info = get_recipe_info($recipe_path);

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    $rating = intval($_POST['rating']);
    $recipe_id = $_POST['recipe_id'];

    // Validate rating (1-10)
    if ($rating >= 1 && $rating <= 10) {
        save_rating($recipe_id, $rating);
        // Refresh the page to show updated rating
        header('Location: view-recipe.php?path=' . urlencode($recipe_path) . '&rated=1');
        exit;
    }
}

// Get current rating information
$rating_info = get_recipe_ratings($recipe_info['id']);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $recipe_info['title']; ?> - Neodock Recipes</title>
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
                        <a class="nav-link" href="top-rated.php">Top Rated</a>
                    </li>
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
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php?category=<?php echo urlencode($recipe_info['category']); ?>"><?php echo $recipe_info['category']; ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $recipe_info['title']; ?></li>
            </ol>
        </nav>

        <?php if (isset($_GET['rated'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Thank you for rating this recipe!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><?php echo $recipe_info['title']; ?></h2>
                <div>
                    <?php echo display_rating($rating_info['avg_rating']); ?>
                    <small class="text-muted">(<?php echo $rating_info['count']; ?> ratings)</small>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h5>Category: <?php echo $recipe_info['category']; ?></h5>
                </div>

                <!-- PDF Viewer -->
                <div class="ratio ratio-16x9 mb-4" style="height: 800px;">
                    <object data="<?php echo $recipe_path; ?>" type="application/pdf" width="100%" height="100%">
                        <p>Your browser does not support PDFs. 
                           <a href="<?php echo $recipe_path; ?>" target="_blank">Download the PDF</a>.</p>
                    </object>
                </div>

                <!-- Rating Form -->
                <?php if (defined('RATING_ENABLED') && RATING_ENABLED && is_client_ip_allowed()): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Rate this recipe</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="view-recipe.php?path=<?php echo urlencode($recipe_path); ?>">
                            <input type="hidden" name="recipe_id" value="<?php echo $recipe_info['id']; ?>">
                            <div class="rating-container mb-3">
                                <div class="rating">
                                    <?php for ($i = 10; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required />
                                        <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Rating</button>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="card mt-4 bg-light">
                    <div class="card-body text-center">
                        <p class="mb-0"><i class="fas fa-info-circle"></i> Rating functionality is only available from allowed networks.</p>
                    </div>
                </div>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="index.php" class="btn btn-secondary">Back to Recipes</a>
                    <a href="<?php echo $recipe_path; ?>" target="_blank" class="btn btn-info"><i class="fas fa-external-link-alt"></i> Open in New Tab</a>
                    <a href="<?php echo $recipe_path; ?>" download class="btn btn-success">Download Recipe</a>
                </div>
            </div>
        </div>
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
