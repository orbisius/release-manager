<?php

class App_Release_Manager_WP_Lib {
    /**
     *
     * @param type $file
     */
    static public function create_update( $main_plugin_file ) {

    }
    
    /**
     *
     * @param type $file
     */
    static public function parse( $main_plugin_file ) {
        $rec = array();

        $pro_target_release_root_dir = '/Copy/Dropbox/cloud/projects/clients/orbclub.com/htdocs/wpu/app/data/plugins/rel';

        $plugin_id = $main_plugin_file;
        $plugin_id = basename($plugin_id);
        $plugin_id = str_replace('.php', '', $plugin_id);

        $ver = '0.0.0';

        if ( empty( $main_plugin_file ) || ! file_exists( $main_plugin_file ) ) {
            return array();
        }
        
        $plugin_buff = file_get_contents($main_plugin_file);

        if ( preg_match( '#Version:\s*([\d\.]+)#si', $plugin_buff, $matches ) ) {
            $ver = $matches[1];
        }

        $target_release_dir = "$pro_target_release_root_dir/$plugin_id/$ver";
        $target_release_file = "$target_release_dir/$plugin_id.zip";

        $change_log = '';
        $stable_version = $tested_with_wp_version = '';

        $plugin_dir = dirname( $main_plugin_file );
        $readme_file = $plugin_dir . '/readme.txt';

        if (is_file($readme_file)) {
            $readme_buff = file_get_contents($readme_file);
            $ver_quoted = preg_quote($ver);

            if ( preg_match( '#Stable tag\s*:\s*([\d\.]+)#si', $readme_buff, $matches ) ) {
                $stable_version = $matches[1];
            }

            if ( preg_match( '#Tested up to\s*:\s*([\d\.]+)#si', $readme_buff, $matches ) ) {
                $tested_with_wp_version = $matches[1];
            }

            if ( preg_match( '#(\=+\s*Changelog.*)#si', $readme_buff, $matches ) ) {
                $change_log = $matches[1];
            }
        }

        $rec['version'] = $ver;
        $rec['stable_version'] = $stable_version;
        $rec['tested_with_wp_version'] = $tested_with_wp_version;
        $rec['plugin_id'] = $plugin_id;
        $rec['plugin_file'] = $main_plugin_file;
        $rec['plugin_dir'] = $plugin_dir;
        $rec['change_log'] = $change_log;
        $rec['target_release_dir'] = $target_release_dir;
        $rec['target_release_file'] = $target_release_file;

	    $rec['target_release_dir_windows'] = $target_release_dir;
	    $rec['target_release_dir_windows'] = str_replace('/', '\\', $rec['target_release_dir_windows']);
	    $rec['target_release_dir_windows'] = str_replace('\\', "\\", $rec['target_release_dir_windows']);

        return $rec;
    }
}