<?php

$orb_rel_dir = dirname(dirname(dirname(__DIR__))) . '/wpu/';

if (is_dir($orb_rel_dir)) {
    define('APP_ORBISIUS_PRO_BASE_DIR', $orb_rel_dir);
}

// Define your WordPress.org credentials.
define( 'APP_SVN_USER', "your-wp-user" );
define( 'APP_SVN_PASS', "your-wp-pass" );

// define one or more folders to be scanned. Separate either by new lines or by a pipe |
// Leading and trailing whitespaces will be removed so indent the folders for prettiness if you want.
define('APP_SCAN_DIRS', "
    /path/to/folder/1
    /path/to/folder/2
");
