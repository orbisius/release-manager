<?php

if (!function_exists('shell_exec')) {
    die('Error: shell_exec() is not enabled');
}

define( 'APP_BASE_DIR', dirname( __FILE__ ) );

define( 'APP_GIT_BIN', @is_file('/usr/local/bin/ogit') ? '/usr/local/bin/ogit' : '/usr/bin/git');

define( 'APP_SVN_BIN', 'svn');

define( 'APP_NL', "<br/>\n" );
define( 'APP_LIVE_ENV', empty($_SERVER['DEV_ENV']) );
define( 'APP_LATEST_WP', rel_mng_get_latest_wp_version() );// load it dyn

if ( file_exists( APP_BASE_DIR . '/conf/config.custom.php' ) ) {
	require_once APP_BASE_DIR . '/conf/config.custom.php';
}

if ( ! defined( 'APP_SVN_USER' ) ) {
    define( 'APP_SVN_USER', "THIS-IS-SUPPOSED-TO-BE-YOUR-WP-USER" );
}

if ( ! defined( 'APP_SVN_PASS' ) ) {
    define( 'APP_SVN_PASS', "THIS-IS-SUPPOSED-TO-BE-YOUR-WP-PASS" );
}

if ( ! defined( 'APP_SCAN_DIRS' ) ) {
    define( 'APP_SCAN_DIRS', dirname(dirname(__DIR__) ) );
}

require_once dirname( __FILE__ ) . '/includes/file.php';
require_once dirname( __FILE__ ) . '/includes/wp_lib.php';
require_once dirname( __FILE__ ) . '/includes/string.php';
require_once dirname( __FILE__ ) . '/includes/ajax.php';
require_once dirname( __FILE__ ) . '/includes/release.php';

/**
 * Parses the WP website to get the latest WP version. Requests are made every 24h
 *
 * @param void
 * @return string e.g. 3.5.1
 */
function rel_mng_get_latest_wp_version() {
    $url = 'http://wordpress.org/download/';
    $ver = '4.2.4';
    $ver_file = APP_BASE_DIR . '/data/latest_wp_ver.txt';

    if ( !file_exists($ver_file) || (time() - filemtime($ver_file) > 4 * 3600)) {
        $body_buff = file_get_contents($url);
        
        // look for a link that points to latest.zip"
        // <a class="button download-button" href="/latest.zip" onClick="recordOutboundLink(this, 'Download', 'latest.zip');return false;">
        // <strong>Download&nbsp;WordPress&nbsp;3.5.1</strong></span></a>
        if (preg_match('#(<a.*?latest\.zip.*?</a>)#si', $body_buff, $matches)) {
            $dl_link = $matches[1];
            $dl_link = strip_tags($dl_link);

            if (preg_match('#(\d+\.\d+(?:\.\d+)?[\w]*)#si', $dl_link, $ver_matches)) { // 1.2.3 or 1.2.3b
                file_put_contents($ver_file, $ver_matches[1], LOCK_EX);
            }
        }
    } else {
        $ver = file_get_contents($ver_file);
    }

    return $ver;
}
