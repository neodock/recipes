<?php
// Include necessary files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';
require_once '../includes/admin.php';

// Check if user is allowed to access admin
if (!is_admin_allowed()) {
    header('Location: ../index.php');
    exit;
}

// Get current theme preference (default to dark)
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';

// Process cleanup request
$cleanup_result = null;
if (isset($_POST['cleanup']) && $_POST['cleanup'] === 'yes') {
    $cleanup_result = cleanup_orphaned_recipes();
}

// Find orphaned recipes
$orphaned_recipes = find_orphaned_recipes();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orphaned Records - Neodock Recipes</title>
    <!-- Bootstrap CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Neodock Recipes</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
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
                        <a class="nav-link" href="../top-rated.php">Top Rated</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                            Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php">Dashboard</a></li>
                            <li><a class="dropdown-item active" href="orphaned-records.php">Orphaned Records</a></li>
                        </ul>
                    </li>
                </ul>
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
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4"><i class="fas fa-database"></i> Orphaned Records</h1>

                <?php if ($cleanup_result): ?>
                <div class="alert alert-success">
                    <h4>Cleanup Results</h4>
                    <p>Total orphaned records: <?php echo $cleanup_result['total']; ?></p>
                    <p>Successfully deleted: <?php echo $cleanup_result['deleted']; ?></p>
                    <?php if ($cleanup_result['failed'] > 0): ?>
                    <p class="text-danger">Failed to delete: <?php echo $cleanup_result['failed']; ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Orphaned Recipe Records</h4>
                        <p class="text-muted mb-0">These records reference files that no longer exist on disk.</p>
                    </div>
                    <div class="card-body">
                        <?php if (empty($orphaned_recipes)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> No orphaned records found. The database is clean!
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Path</th>
                                        <th>Ratings</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orphaned_recipes as $recipe): ?>
                                    <tr>
                                        <td><?php echo $recipe['id']; ?></td>
                                        <td><?php echo $recipe['title']; ?></td>
                                        <td><?php echo $recipe['category']; ?></td>
                                        <td><span class="text-danger"><?php echo $recipe['path']; ?></span></td>
                                        <td><?php echo $recipe['ratings_count']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <form method="post" onsubmit="return confirm('Are you sure you want to delete all orphaned records? This action cannot be undone.')">
                                <button type="submit" name="cleanup" value="yes" class="btn btn-danger">
                                    <i class="fas fa-trash-alt"></i> Delete All Orphaned Records
                                </button>
                                <p class="text-muted mt-2">This will remove all orphaned recipe records and their associated ratings from the database.</p>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Admin Dashboard
                    </a>
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
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/script.js"></script>
</body>
</html>
