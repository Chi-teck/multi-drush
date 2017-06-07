<?php

/**
 * @file
 * Drush launcher.
 */

$drupal_root = FALSE;
$drupal_version = FALSE;
$drush_major_version = FALSE;

// Lookup through directories to find Drupal installation.
$path = getcwd();
while ($path) {
  if ($drupal_version = multi_drush_get_version($path)) {
    $drupal_root = $path;
    break;
  }
  elseif ($path == '/') {
    break;
  }
  $path = dirname($path);
}

$drush_endpoint = '/vendor/drush/drush/drush.php';

if ($drupal_root) {
  // Support local Drush installation.
  if (file_exists($drupal_root . $drush_endpoint)) {
    require $drupal_root . $drush_endpoint;
  }
  // In Drupal composer project vendor directory is outside document root.
  elseif (file_exists($drupal_root . '/..' . $drush_endpoint)) {
    require $drupal_root . '/..' . $drush_endpoint;
  }
  // Run bundled Drush instance.
  else {
    // In 8.4 Drupal updated Symfony components.
    $drush_major_version = version_compare($drupal_version, 8.4, '>') ? 9 : 8;
    require __DIR__ . '/drush_' . $drush_major_version . $drush_endpoint;
  }
}
// Use Drush 8 when no Drupal site was found.
else {
  require __DIR__ . '/drush_8' . $drush_endpoint;
}

/**
 * Returns Drupal version.
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
