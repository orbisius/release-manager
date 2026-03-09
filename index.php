<?php

/**
 * This file helps me release WordPress plugins easily.
 *
// @todo check for already zzz_release file
// check live version
// add header + footer for this file -> move js & css in oun files.
// commit changes from web?
// inline edit for versions ???
// see diff
 * make page rendering faster
 */
require_once dirname( __FILE__ ) . '/header.php';

$plugin_dirs = [];

if ( defined( 'APP_SCAN_DIRS' ) ) {
    $plugin_dirs = preg_split( '#[\|\r\n]+#si', trim( APP_SCAN_DIRS ) );
    $plugin_dirs = array_map( 'trim', $plugin_dirs );
    $plugin_dirs = array_unique( $plugin_dirs );
    $plugin_dirs = array_filter( $plugin_dirs );
    sort( $plugin_dirs );
}

foreach ($plugin_dirs as $plugin_dir) {
    $plugin_dir = trim($plugin_dir);
    $plugin_dir = str_replace('\\', '/', $plugin_dir); // win dir sep
    $plugin_dir = rtrim($plugin_dir, '/') . '/';

    if (empty($plugin_dir)) {
        continue;
    }

    $plugins = glob($plugin_dir . '*', GLOB_ONLYDIR);
    $plugin_info_rec = [];
    //echo sprintf("<div>Found: %d item(s)</div>", count($plugins));

    // Preprocess: filter and cache plugin data to avoid double parsing
    $plugins_cache = [];

    foreach ($plugins as $idx => $plugin_root_dir) {
        $base_name = basename($plugin_root_dir);
        $plugin_root_dir = rtrim($plugin_root_dir, '/') . '/';

        // skip based on the name
        if ((strpos($base_name, 'skip_') !== false) || (strpos($base_name, '_skip') !== false)) {
            continue;
        }

        // skipping plugins that are not tracked by a version control.
        $has_svn = is_dir($plugin_root_dir . '/.svn');

        if ($has_svn) {
            $has_git = is_dir($plugin_root_dir . '/.git');
        } elseif (is_dir($plugin_root_dir . '/.git')) {
            $has_git = true;
        } else {
            continue;
        }

        $main_plugin_file = App_Release_Manager_File::findMainPluginFile($plugin_root_dir);

        if (empty($main_plugin_file)) {
            continue;
        }

        $data = App_Release_Manager_File::parsePluginMeta($main_plugin_file);

        if (empty($data['Plugin Name'])) {
            continue;
        }

        $plugins_cache[$idx] = [
            'root_dir' => $plugin_root_dir,
            'main_file' => $main_plugin_file,
            'data' => $data,
            'has_svn' => $has_svn,
            'has_git' => $has_git,
        ];
    }

    $plugin_dir_esc = htmlentities($plugin_dir);
    echo "<div class='orbisius-release-manager-scan-dir-heading'>Processing: [$plugin_dir_esc]</div>";

    foreach ($plugins_cache as $plugin_cached) {
        $ok = 0;
        $plugin_root_dir = $plugin_cached['root_dir'];
        $main_plugin_file = $plugin_cached['main_file'];
        $data = $plugin_cached['data'];
        $has_svn = $plugin_cached['has_svn'];
        $has_git = $plugin_cached['has_git'];
        $base_name = basename($plugin_root_dir);

        $manage_link = str_replace('\\', '/', $plugin_root_dir);
        $manage_link = preg_replace('#.*?/(www|public_html|htdocs)/#si', '', $manage_link);
        $manage_link = preg_replace('#(wp-content)/.*#si', '', $manage_link);

        $manage_site_pub = 'http://localhost/' . $manage_link;
        $manage_site_pub_esc = htmlentities($manage_site_pub);
        $manage_link_trimmed = trim($manage_link, '/');
        $manage_site_adm = "http://localhost/$manage_link_trimmed/wp-admin/";
        $manage_site_adm_esc = htmlentities($manage_site_adm);

        $plugin_name = $data['Plugin Name'];
        $plugin_name_esc = htmlentities($plugin_name);
        $base_name_esc = htmlentities($base_name);
        $plugin_uri = empty($data['Plugin URI']) ? '' : $data['Plugin URI'];
        $plugin_uri_esc = htmlentities($plugin_uri);
        $plugin_tags = empty($data['Tags']) ? '' : $data['Tags'];
        $search_text = "$plugin_name $base_name $plugin_uri $plugin_tags";
        $search_text_esc = htmlentities(strtolower($search_text));

        echo "<div class='plugin_container' data-search='$search_text_esc' data-plugin-name='$plugin_name_esc' data-plugin-slug='$base_name_esc' data-plugin-uri='$plugin_uri_esc'>\n";

        $ver = $data['Version'];
        $ver_esc = htmlentities($ver);

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                continue;
            }

            $k_esc = htmlentities($k);
            $v_esc = htmlentities($v);

            if (stripos($k, 'name') !== false) {
                $k_esc = "<strong>$k_esc</strong>";
                $plugin_root_dir_esc = htmlentities($plugin_root_dir);
                $v_esc = "<strong>$v_esc</strong>";
                $v_esc .= " Manage: <a href='$manage_site_pub_esc' target='_blank'>Site</a>\n";
                $v_esc .= "| <a href='$manage_site_adm_esc' target='_blank'>Admin</a>\n";
                $v_esc .= "<br/><input class='full_width' type='text' value='$plugin_root_dir_esc' onclick='this.select();' />";
            }

            if ($v == 'n/a') {
                $v_esc = App_Release_Manager_String::msg($v_esc, 0, 0);
            }

            echo "$k_esc: $v_esc" . APP_NL;
        }

        $stable_tag_match = 0;
        $tested_with_latest_wp = 0;

        echo APP_NL . "<strong>Status/Checklist</strong>:" . APP_NL;

        if ($ver == $data['Stable tag']) {
            echo App_Release_Manager_String::msg("Plugin's stable tag matches its version." . APP_NL, 1);
            $ok++;
        } else {
            echo App_Release_Manager_String::msg("Plugin's version doesn't match stable tag." . APP_NL, 0);
        }

        $req_php = empty( $data['Requires PHP'] ) ? '' : $data['Requires PHP'];

        if (!empty($req_php)) {
            echo App_Release_Manager_String::msg("Has Requires PHP: $req_php" . APP_NL, 1);
            $ok++;
        } else {
            echo App_Release_Manager_String::msg("Missing Requires PHP: " . APP_NL, 0);
            $ok--;
        }

        $req_wp = empty( $data['Requires at least'] ) ? '' : $data['Requires at least'];

        if (!empty($req_wp)) {
            echo App_Release_Manager_String::msg("Has Requires at least: $req_wp" . APP_NL, 1);
            $ok++;
        } else {
            echo App_Release_Manager_String::msg("Missing Requires at least: " . APP_NL, 0);
            $ok--;
        }

        $license = empty( $data['License'] ) ? '' : $data['License'];
        $license_uri = empty( $data['License URI'] ) ? '' : $data['License URI'];

        if (!empty($license) || !empty($license_uri)) {
            $license_info = !empty($license) ? $license : $license_uri;
            echo App_Release_Manager_String::msg("Has License: $license_info" . APP_NL, 1);
            $ok++;
        } else {
            echo App_Release_Manager_String::msg("Missing License: " . APP_NL, 0);
            $ok--;
        }

        // Do we need to check WC tags?
        $wc_regex = '#-(woocommerce|wc-ext)-#si';

        if (preg_match($wc_regex, $plugin_root_dir)
            || preg_match($wc_regex, $main_plugin_file)
        ) {
            // check WC requires if woocommerce plugin
            $min_wc_ver = empty( $data['WC requires at least'] ) ? '' : $data['WC requires at least'];

            // WC requires at least: 3.1
            if ( ! empty( $min_wc_ver ) ) {
                echo App_Release_Manager_String::msg( "WC requires at least: $min_wc_ver" . APP_NL, 1 );
                $ok ++;
            } else {
                echo App_Release_Manager_String::msg( "Missing: WC requires at least: " . APP_NL, 0 );
                $ok --;
            }

            // WC tested up to: 6.5
            $wc_tested_up_to_ver = empty( $data['WC tested up to'] ) ? '' : $data['WC tested up to'];

            if ( ! empty( $wc_tested_up_to_ver ) ) {
                echo App_Release_Manager_String::msg( "WC tested up to: $wc_tested_up_to_ver" . APP_NL, 1 );
                $ok ++;
            } else {
                echo App_Release_Manager_String::msg( "Missing: WC tested up to: " . APP_NL, 0 );
                $ok --;
            }
        }

        $tested_ver = empty( $data['Tested up to'] ) ? '0.0.0' : $data['Tested up to'];

        // Pad 2-part version with .99 so "6.9" means "tested with all 6.9.x"
        $tested_ver_parts = explode('.', $tested_ver);
        $tested_ver_cmp = count($tested_ver_parts) == 2
            ? $tested_ver_parts[0] . '.' . $tested_ver_parts[1] . '.99'
            : $tested_ver;

        if (version_compare($tested_ver_cmp, APP_LATEST_WP, '>=')) {
            echo App_Release_Manager_String::msg("The plugin is tested with the latest WP version: " . APP_LATEST_WP . APP_NL, 1);
            $ok++;
        } else {
            $ok++;
            echo App_Release_Manager_String::msg("The $tested_ver plugin needs to be tested with the latest WP version: " . APP_LATEST_WP . APP_NL, 0);
        }

        // let's check if there is an entry for the latest version
        $readme_file = $plugin_root_dir . 'readme.txt';

        if (is_file($readme_file)) {
            $readme_buff = file_get_contents($readme_file);
            $ver_quoted = preg_quote($ver);

            // the version is = 1.0.1 =
            if (preg_match('#[\s=]+' . $ver_quoted . '[\s=]+#si', $readme_buff)) {
                echo App_Release_Manager_String::msg("Readme file has a change log entry for the current version." . APP_NL, 1);
                $ok++;
            } else {
                echo App_Release_Manager_String::msg("Readme file doesn't have a change log entry for the current version." . APP_NL, 0);
            }
        } else {
            $readme_file_esc = htmlentities($readme_file);
            echo App_Release_Manager_String::msg("Readme file [$readme_file_esc] doesn't exist!" . APP_NL, 0);
        }

        // Only check SVN status for plugins tracked by SVN
        if ($has_svn) {
            $plugin_root_dir_esc = escapeshellarg($plugin_root_dir);
            $svn_info = shell_exec("svn status $plugin_root_dir_esc 2>/dev/null");

            // Let's check if we have committed all the changes.
            // this regex is a multi-line check see 'm' after the #
            if (preg_match('#^\h*M\s+#mi', $svn_info)) {
                echo App_Release_Manager_String::msg("There are still uncommitted modified files." . APP_NL, 0);
                $svn_info_esc = htmlentities($svn_info);
                echo App_Release_Manager_String::msg("<pre>$svn_info_esc</pre>" . APP_NL, 0);
            } else {
                $ok++;
            }
        }

        $plugin_info_rec[$data['Plugin Name']] = $ver . ' tested with WP ' . $tested_ver;

        $stored_ver = App_Release_Manager_Release::getRelease($plugin_root_dir);

        // 1.0.1
        if (!empty($stored_ver) && version_compare($ver, $stored_ver, '<=')) {
            echo App_Release_Manager_String::msg("The plugin has already been tagged with this version [$ver] "
                    . "or the version is smaller than current one." . APP_NL, 1);
            $ok -= 2;
        }

        $plugin_full_dir_enc = urlencode($plugin_root_dir);
        $id = 'product_' . sha1($plugin_full_dir_enc);

        echo "<div class='release_container'>\n";

        if ($has_git && !$has_svn) {
            $wp_res = App_Release_Manager_WP_Lib::parse( $main_plugin_file );
            $warn = '';

            if ( ! empty( $wp_res['target_release_file'] ) && file_exists( $wp_res['target_release_file'] ) ) {
                $wp_res_ver_esc = htmlentities($wp_res['version']);
                $warn = App_Release_Manager_String::msg( "(release v$wp_res_ver_esc exists)", 0 );
            }

            echo "<button class='push_pro_release' data-id='$id' data-new_ver='$ver_esc'"
                . " data-plugin_full_dir='$plugin_full_dir_enc'><i class='bi bi-box-seam'></i> Package Pro Release</button> $warn" . APP_NL;
        } elseif ($ok >= 4) {
            echo "<button class='push_release' data-id='$id' data-new_ver='$ver_esc'"
                . " data-plugin_full_dir='$plugin_full_dir_enc'><i class='bi bi-rocket-takeoff'></i> Push Release</button>" . APP_NL;
        }

        echo "</div>\n";

        echo "<div class='result_$id'></div>\n";
        echo APP_NL;
        echo "</div>\n";
    }

    echo "<hr class='orbisius-release-manager-scan-dir-separator' />" . APP_NL;
}

?>
<?php require_once dirname(__FILE__) . '/footer.php'; ?>
