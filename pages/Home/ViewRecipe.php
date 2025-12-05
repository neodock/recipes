<?php
    // Check if recipe path is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header('Location: index.php?controller=Home&page=Index');
        exit;
    }

    $recipe_info = \Neodock\Recipes\RecipeUtilities::GetRecipeInfo($_GET['id']);

    // Handle rating submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating']) && \Neodock\Recipes\AdminUtilities::canRateRecipe()) {
        $rating = intval($_POST['rating']);
        $recipe_id = $_POST['recipe_id'];

        // Validate rating (1-10)
        if ($rating >= 1 && $rating <= 10) {
            \Neodock\Recipes\RecipeUtilities::RateRecipe($recipe_id, $rating);

            // Refresh the page to show updated rating
            header('Location: index.php?controller=Home&page=ViewRecipe&id=' . $_GET['id'] . '&rated=1');
            exit;
        }
    }

    // Get current rating information
    $rating_info = \Neodock\Recipes\RecipeUtilities::GetRecipeRatings($recipe_info['recipe_id']);
?>
<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?controller=Home&page=Index">Home</a></li>
            <li class="breadcrumb-item"><a href="index.php?controller=Home&page=Index&category=<?=$recipe_info['category_id']?>"><?php echo $recipe_info['category']; ?></a></li>
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
                <?php echo \Neodock\Recipes\RecipeUtilities::DisplayRating($rating_info['avg_rating']); ?>
                <small class="text-muted">(<?php echo $rating_info['count']; ?> ratings)</small>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <h5>Category: <?php echo $recipe_info['category']; ?></h5>
            </div>

            <!-- PDF Viewer -->
            <div class="ratio ratio-16x9 mb-4" style="height: 800px;">
                <object data="<?=$recipe_info['url']?>" type="application/pdf" width="100%" height="100%">
                    <p>Your browser does not support PDFs.
                        <a href="<?=$recipe_info['url']?>" target="_blank">Download the PDF</a>.</p>
                </object>
            </div>

            <!-- Rating Form -->
            <?php if (\Neodock\Recipes\AdminUtilities::canRateRecipe()): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Rate this recipe</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="index.php?controller=Home&page=ViewRecipe&id=<?=$_GET['id']?>">
                            <input type="hidden" name="recipe_id" value="<?=$_GET['id']?>">
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
                        <p class="mb-0"><i class="fas fa-info-circle"></i> Rating functionality is only available from allowed client networks/IPs.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-4">
                <a href="index.php?controller=Home&page=Index" class="btn btn-secondary">Back to Recipes</a>
                <a href="<?=$recipe_info['url']?>" target="_blank" class="btn btn-info"><i class="fas fa-external-link-alt"></i> Open in New Tab</a>
                <a href="<?=$recipe_info['url']?>" download class="btn btn-success">Download Recipe</a>
            </div>
        </div>
    </div>
</div>
