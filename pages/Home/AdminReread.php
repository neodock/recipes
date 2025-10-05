<?php
    $db = new \Neodock\Framework\Database();
    $db->query('SELECT * FROM dbo.categories WHERE datedeleted IS NULL');
    $db->execute();
    $categoryresults = $db->resultset();

    $db->query('SELECT * FROM dbo.recipes WHERE datedeleted IS NULL');
    $db->execute();
    $reciperesults = $db->resultset();

    $categories = \Neodock\Recipes\RecipeUtilities::ReadCategoriesFromDisk();

    $categoriestoadd = [];
    foreach ($categories as $category) {
        foreach ($categoryresults as $categoryresult) {
            if ($categoryresult['name'] == $category) {
               continue 2;
            }
        }
        $categoriestoadd[] = $category;
    }

    if (count($categoriestoadd) > 0) {
        $db->query('INSERT INTO dbo.categories (name) VALUES (:name)');
        foreach ($categoriestoadd as $category) {
            $db->bind(':name', $category);
            $db->execute();
        }
    }

    $recipes = \Neodock\Recipes\RecipeUtilities::ReadRecipesFromDisk();
    $recipestoadd = [];
    foreach ($recipes as $recipe) {
        foreach ($reciperesults as $reciperesult) {
            if ($reciperesult['filepath'] == $recipe['filepath']) {
               continue 2;
            }
        }
        $recipestoadd[] = $recipe;
    }

    if (count($recipestoadd) > 0) {
        $db->query('INSERT INTO dbo.recipes (category_id, title, filepath) VALUES (:category, :title, :filepath)');
        foreach ($recipestoadd as $recipe) {
            $db->bind(':category', $recipe['category_id']);
            $db->bind(':title', $recipe['title']);
            $db->bind(':filepath', $recipe['filepath']);;
            $db->execute();
        }
    }
?>
<!-- Main Content -->
<div class="container mt-4">
    <p>Categories added: <?=count($categoriestoadd)?></p>
    <p>Recipes added: <?=count($recipestoadd)?></p>
</div>