<?php

/**
 * This file helps me release WordPress plugins easily.
 */
require_once dirname( __FILE__ ) . '/header.php';

$plugin_dirs = array();

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
    $plugin_dir = trim($plugin_dir, '/') . '/';

    if (empty($plugin_dir)) {
        continue;
    }
    
    echo "<h4>Processing: [$plugin_dir]</h4>";
    $plugins = glob($plugin_dir . '*', GLOB_ONLYDIR);

    foreach ($plugins as $plugin_root_dir) {
        // skipping plugins that are not tracked by a version control.
        if (!is_dir($plugin_root_dir . '/.svn')
                && !is_dir($plugin_root_dir . '/.git')
                ) {
            continue;
        }

        $ok = 0;
        $plugin_root_dir = rtrim($plugin_root_dir, '/') . '/';
        
        $manage_link = $plugin_root_dir;
        $manage_link = str_replace('\\', '/', $manage_link);
        $manage_link = preg_replace('#.*?/(www|public_html|htdocs)/#si', '', $manage_link);
        $manage_link = preg_replace('#(wp-content)/.*#si', '', $manage_link);

        $manage_site_pub = 'http://localhost/' . $manage_link;
        $manage_site_adm = 'http://localhost/' . trim($manage_link, '/') . '/wp-admin/';

        echo "<div class='plugin_container'>\n";

        $main_plugin_file = App_Release_Manager_File::findMainPluginFile($plugin_root_dir);
        $data = App_Release_Manager_File::parsePluginMeta($main_plugin_file);

        if (!empty($data['Plugin Name'])) {
            $ver = $data['Version'];
            
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    //var_dump($v);
                    continue;
                }
                
                if (stripos($k, 'name') !== false) {
                    $k = "<strong>$k</strong>";
                    $v = "<strong>$v</strong>";
                    
                    $v .= " Manage: <a href='$manage_site_pub' target='_blank'>Site</a>\n";
                    $v .= "| <a href='$manage_site_adm' target='_blank'>Admin</a>\n";
                    $v .= '<br/><input class="full_width" type="text" value="' . $plugin_root_dir .'" onclick="this.select();" />';
                }

                if ($v == 'n/a') {
                    $v = App_Release_Manager_String::msg($v, 0, 0);
                }

                echo "$k: $v" . APP_NL;
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

            $tested_ver = empty( $data['Tested up to'] ) ? '0.0.0' : $data['Tested up to'];

            if ( strlen( $tested_ver ) != strlen( APP_LATEST_WP ) ) { // pad
                $tested_ver .= '.9';
            }

            if (version_compare($tested_ver, APP_LATEST_WP, '>=')) {
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
                echo App_Release_Manager_String::msg("Readme file [$readme_file] doesn't exist!" . APP_NL, 0);
            }

            $svn_info = `svn status $plugin_root_dir`;

            // Let's check if we have commited all of the changes.
            // this regex is a multi-line check see 'm' after the #
            if (preg_match('#^M\s+#mi', $svn_info)) {
                echo App_Release_Manager_String::msg("There are still uncommitted modified files." . APP_NL, 0);
                echo App_Release_Manager_String::msg("<pre>$svn_info</pre>". APP_NL, 0);
            } else {
                $ok++;
            }
        } else {
            echo "Plugin info couldn't be parsed." . APP_NL;
        }

        $stored_ver = App_Release_Manager_Release::getRelease($plugin_root_dir);

        // 1.0.1
        if (!empty($stored_ver) && version_compare($ver, $stored_ver, '<=')) {
            echo App_Release_Manager_String::msg("The plugin has already been tagged with this verison [$ver] "
                    . "or the version is smaller than current one." . APP_NL, 1);
            $ok -= 2;
        }

        $plugin_full_dir_enc = urlencode($plugin_root_dir);
        $id = 'product_' . sha1($plugin_full_dir_enc);

        echo "<div class='release_container'>\n";
        
        if ( is_dir( $plugin_root_dir . '/.git' ) && ! is_dir( $plugin_root_dir . '/.svn' ) ) {
            $wp_res = App_Release_Manager_WP_Lib::parse( $main_plugin_file );
            $warn = '';
            
            if ( ! empty( $wp_res['target_release_file'] ) && file_exists( $wp_res['target_release_file'] ) ) {
                $warn = App_Release_Manager_String::msg( "(release v{$wp_res['version']} exists)", 0 );
            }

            echo "<button class='push_pro_release' data-id='$id' data-new_ver='{$ver}'"
                . " data-plugin_full_dir='$plugin_full_dir_enc'>Package Pro Release</button> $warn" . APP_NL;
        } elseif ($ok >= 4) {
            echo "<button class='push_release' data-id='$id' data-new_ver='{$ver}'"
                . " data-plugin_full_dir='$plugin_full_dir_enc'>Push Release</button>" . APP_NL;
        }

        echo "</div>\n";

        echo "<div class='result_$id'></div>\n";
        echo APP_NL;
        echo "</div>\n";

        // @todo check for alreay zzz_release file
        // check live version
        // add header + footer for this file -> move js & css in oun files.
        // commit changes from web?
        // inline edit for versions ???
        // see diff
    }

    echo "<hr />" . APP_NL;
}

?>
<?php require_once dirname(__FILE__) . '/footer.php'; ?>
