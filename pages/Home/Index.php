<?php
    global $theme, $search_query, $category_filter, $recipes, $top_rated_recipes;
    $this->setTitle('Home');
?>
<!-- Main Content -->
<div class="container mt-4">
    <?php if (!empty($search_query) || !empty($category_filter)): ?>
        <div class="alert alert-info">
            <?php if (!empty($search_query)): ?>
                <h4>Search results for: "<?php echo $search_query; ?>"</h4>
            <?php endif; ?>
            <?php if (!empty($category_filter)): ?>
                <h4>Category: <?=\Neodock\Recipes\RecipeUtilities::GetCategoryName($category_filter)?></h4>
            <?php endif; ?>
            <a href="index.php" class="btn btn-sm btn-secondary">Clear filters</a>

            <span class="float-end">
                <a href="<?=\Neodock\Framework\StringUtils::CurrentUrlWithoutSort()?>&sort=alpha" class="btn btn-sm <?php if ($_SESSION['sort'] == 'alpha') { echo 'btn-secondary'; } else { echo 'btn-primary'; } ?>">Sort by Title</a>
                <a href="<?=\Neodock\Framework\StringUtils::CurrentUrlWithoutSort()?>&sort=rating" class="btn btn-sm <?php if ($_SESSION['sort'] == 'rating') { echo 'btn-secondary'; } else { echo 'btn-primary'; } ?>">Sort by Rating</a>
                <a href="<?=\Neodock\Framework\StringUtils::CurrentUrlWithoutSort()?>&sort=dateadded" class="btn btn-sm <?php if ($_SESSION['sort'] == 'dateadded') { echo 'btn-secondary'; } else { echo 'btn-primary'; } ?>">Sort by Date Added</a>
            </span>

        </div>
    <?php else: ?>
        <div class="jumbotron p-5 mb-4 rounded">
            <h1 class="display-4">Welcome to <?=\Neodock\Framework\Configuration::get('sitetitle')?></h1>
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
                            $categories = \Neodock\Recipes\RecipeUtilities::GetCategories();
                            foreach ($categories as $category):
                                ?>
                                <div class="col-md-3">
                                    <a href="index.php?controller=Home&page=Index&category=<?=$category['id']; ?>" class="text-decoration-none">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-folder me-2 text-warning"></i>
                                            <span><?php echo \Neodock\Framework\StringUtils::TitleCase($category['name']); ?></span>
                                            <span class="badge bg-secondary ms-2"><?=$category['recipecount']?></span>
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
                            <a href="index.php?controller=Home&page=TopRated" class="btn btn-sm btn-outline-primary">View Details</a>
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
                                                    <?php echo \Neodock\Recipes\RecipeUtilities::DisplayRating($recipe['avg_rating']); ?>
                                                    <small class="text-muted">(<?php echo $recipe['ratings_count']; ?> ratings)</small>
                                                </div>
                                                <a href="index.php?controller=Home&page=ViewRecipe&id=<?php echo $recipe['id']; ?>" class="btn btn-primary">View Recipe</a>
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
            <div class="row row-cols-1 g-4">
                <?php foreach ($recipes as $recipe): ?>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo \Neodock\Framework\StringUtils::TitleCase($recipe['recipe_title']); ?></h5>
                                <p class="card-text small"><?php echo 'Added: ' . date('F j, Y', strtotime($recipe['dateadded'])); ?></p>
                                <div class="mb-2">
                                    <?php echo \Neodock\Recipes\RecipeUtilities::DisplayRating($recipe['ratings_average']); ?>
                                    <small class="text-muted">(<?php echo $recipe['ratings_count']; ?> ratings)</small>&nbsp;&nbsp;
                                    <a href="index.php?controller=Home&page=ViewRecipe&id=<?=$recipe['recipe_id']?>" class="btn btn-primary">View Recipe</a>&nbsp;&nbsp;
                                    <a href="index.php?controller=Home&page=ViewRecipe&id=<?=$recipe['recipe_id']?>" target="_blank" class="btn btn-info"><i class="fas fa-external-link-alt"></i> Open in New Tab</a>&nbsp;&nbsp;
                                    <a href="<?=str_replace('\\', '/', str_replace(str_replace('/', '\\', \Neodock\Framework\Configuration::getInstance()::get('basedir')) . '\\public', Neodock\Framework\Configuration::get('baseurl'), $recipe['recipe_filepath']))?>" class="btn btn-info">Download Recipe PDF</a>
                                </div>

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
