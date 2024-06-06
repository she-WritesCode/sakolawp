<?php
require plugin_dir_path(__FILE__) . 'plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/she-WritesCode/sakolawp/',
    __FILE__,
    'sakolawp'
);


// Optional: If you're using a private repository, specify the access token like this:
// $myUpdateChecker->setAuthentication('your-token-here');

// Optional: Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');
