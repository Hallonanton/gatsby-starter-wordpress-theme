<?php
/**
 * Library includes
 *
 * The $lib_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed.
 *
 * Please note that missing files will produce a fatal error.
 */
$lib_includes = [
  'lib/acf-fields.php',
  'lib/register-posts.php',
  'lib/theme-functions.php'
];

foreach ($lib_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'sage'), $file), E_USER_ERROR);
  }

  require_once $filepath;
}
unset($file, $filepath);