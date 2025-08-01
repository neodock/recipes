<?php
/**
 * Script to download Bootstrap 5.3
 */

// Define the URLs for Bootstrap files
$bootstrap_css_url = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css';
$bootstrap_js_url = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js';

// Create assets directories if they don't exist
if (!is_dir('assets')) {
    mkdir('assets', 0755);
}

if (!is_dir('assets/css')) {
    mkdir('assets/css', 0755);
}

if (!is_dir('assets/js')) {
    mkdir('assets/js', 0755);
}

// Download Bootstrap CSS
echo "Downloading Bootstrap CSS...\n";
$css_content = file_get_contents($bootstrap_css_url);
if ($css_content === false) {
    die("Failed to download Bootstrap CSS.\n");
}

// Save Bootstrap CSS
$css_result = file_put_contents('assets/css/bootstrap.min.css', $css_content);
if ($css_result === false) {
    die("Failed to save Bootstrap CSS.\n");
}
echo "Bootstrap CSS downloaded successfully.\n";

// Download Bootstrap JS
echo "Downloading Bootstrap JS...\n";
$js_content = file_get_contents($bootstrap_js_url);
if ($js_content === false) {
    die("Failed to download Bootstrap JS.\n");
}

// Save Bootstrap JS
$js_result = file_put_contents('assets/js/bootstrap.bundle.min.js', $js_content);
if ($js_result === false) {
    die("Failed to save Bootstrap JS.\n");
}
echo "Bootstrap JS downloaded successfully.\n";

echo "\nBootstrap 5.3 has been downloaded and saved to the assets directory.\n";
echo "You can now run setup.php to set up the database.\n";
