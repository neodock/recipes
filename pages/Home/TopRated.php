<?php
    $recipes = \Neodock\Recipes\RecipeUtilities::GetTopRatedRecipes(\Neodock\Framework\Configuration::getInstance()->get('numberoftopratedrecipes'));

    $this->setTitle('%%%SITETITLE%%% - Top Rated Recipes');
?>
<!-- Main Content -->
<div class="container mt-4">
    <h1 class="mb-4">Top <?=\Neodock\Framework\Configuration::getInstance()->get('numberoftopratedrecipes')?> Rated Recipes</h1>
    <?php if (empty($recipes)): ?>
        <div class="alert alert-info">No rated recipes found yet. Be the first to rate a recipe!</div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-4 g-4">
            <?php foreach ($recipes as $recipe): ?>
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
    <?php endif; ?>
</div>
