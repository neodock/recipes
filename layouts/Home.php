<?php
    global $theme, $search_query, $category_filter, $recipes, $top_rated_recipes;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?=$theme?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>%%%PAGETITLE%%%</title>
    <!-- Bootstrap CSS -->
    <link href="%%%BASEURL%%%/static/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="%%%BASEURL%%%/static/css/style.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php if (\Neodock\Framework\Configuration::getInstance()->get('matomoenabled')) { ?>
        <!-- Matomo -->
        <script>
            var _paq = window._paq = window._paq || [];
            /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function() {
                var u="<?=\Neodock\Framework\Configuration::getInstance()->get('matomourl')?>";
                _paq.push(['setTrackerUrl', u+'z.php']);
                _paq.push(['setSiteId', '<?=\Neodock\Framework\Configuration::getInstance()->get('matomositeid')?>']);
                var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                g.async=true; g.src=u+'z.js'; s.parentNode.insertBefore(g,s);
            })();
        </script>
        <!-- End Matomo Code -->
    <?php } ?>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php?controller=Home&page=Index"><?=\Neodock\Framework\Configuration::get('sitetitle')?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?controller=Home&page=Index">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown">
                        Categories
                    </a>
                    <ul class="dropdown-menu">
                        <?php echo \Neodock\Recipes\RecipeUtilities::GetCategoryLinks(); ?>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?controller=Home&page=TopRated">Top Rated</a>
                </li>
                <?php if (\Neodock\Recipes\AdminUtilities::IsAdmin()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-shield-alt"></i> Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?controller=Home&page=AdminIndex">Dashboard</a></li>
                            <li><a class="dropdown-item" href="index.php?controller=Home&page=AdminReread">Reread Files</a></li>
                            <li><a class="dropdown-item" href="index.php?controller=Home&page=AdminOrphaned">Orphaned Records</a></li>
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

%%%PAGECONTENT%%%

<!-- Footer -->
<footer class="bg-dark text-white mt-5 py-4">
    <div class="container text-center">
        <p>An open source project by <a href="https://neodock.net">Neodock</a><br/>Code available at <a href="https://github.com/neodock/recipes">github.com/neodock/recipes</a></p>
    </div>
</footer>

<!-- Bootstrap JS Bundle with Popper -->
<script src="%%%BASEURL%%%/static/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="%%%BASEURL%%%/static/js/script.js"></script>
</body>
</html>