<?php

/**
 * @file
 * Drush launcher.
 */

/**
 * Returns Drupal version for a given path.
 *
 * @param string $path
 *   Path start search.
 *
 * @return string|bool
 *   Drupal version or FALSE if no Drupal installation was found.
 */
function multi_drush_get_version($path) {

  // Drupal 8.
  if (file_exists($path . '/core/lib/Drupal.php')) {
    $content = file_get_contents($path . '/core/lib/Drupal.php');
    preg_match("#const VERSION = '(.*)';#", $content, $matches);
  }
  elseif (file_exists($path . '/includes/bootstrap.inc')) {
    // Drupal 6.
    if (file_exists($path . '/includes/database.inc')) {
      $content = file_get_contents($path . '/modules/system/system.module');
      preg_match("#define\('VERSION', '(.*)'\);#", $content, $matches);
    }
    // Drupal 7.
    else {
      $content = file_get_contents($path . '/includes/bootstrap.inc');
      preg_match("#define\('VERSION', '(.*)'\);#", $content, $matches);
    }
  }

  return isset($matches[1]) ? $matches[1] : FALSE;
}

$drush_major_version = 8;
$path = getcwd();
while ($path) {
  if ($drupal_version = multi_drush_get_version($path)) {
    if (version_compare($drupal_version, 8.4, '>')) {
      $drush_major_version = 9;
    }
    break;
  }
  $parent = dirname($path);
  $path = in_array($parent, ['.', $path]) ? FALSE : $parent;
}

require "drush_$drush_major_version/vendor/drush/drush/drush.php";
