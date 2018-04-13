<?php

require_once dirname(__FILE__) . '/config.php';

$struct = array(
    'status' => 1,
    'result' => '',
    'msg' => '',
);

try {
    $cmd = empty($_REQUEST['cmd']) ? 'release_free_plugin' : $_REQUEST['cmd'];
    $ver = empty($_REQUEST['new_ver']) ? '' : $_REQUEST['new_ver'];
    
    $plugin_dir = empty($_REQUEST['plugin_dir']) ? '' : $_REQUEST['plugin_dir'];
    $plugin_dir = strip_tags($plugin_dir);
    $plugin_dir = trim($plugin_dir);
    $plugin_dir = str_replace('..', '', $plugin_dir);

    if (!is_dir($plugin_dir)) {
        throw new Exception("Plugin directory doesn't exist.");
    }

    switch ($cmd) {
        case 'package_pro_plugin':
            $main_plugin_file = App_Release_Manager_File::findMainPluginFile($plugin_dir);

            $wp_res = App_Release_Manager_WP_Lib::parse( $main_plugin_file );

            if ( ! is_dir( $wp_res[ 'target_release_dir' ] ) ) {
                mkdir( $wp_res[ 'target_release_dir' ], 0750, 1 );
            }

            if ( empty( $wp_res['plugin_id'] ) ) {
                $struct['result'] .= "Something's wrong.";
                break;
            }

            $target_zip_file = $wp_res['target_release_file'];
            $plugin_dir = $wp_res['plugin_dir'];
            $zip_res = App_Release_Manager_File::archive( $target_zip_file, $plugin_dir );

            $update_rec = array(
                "author" => "<a href='http://orbisius.com' target='_blank'>Orbisius.com</a>",
                "author_profile" => "http://profiles.wordpress.org/lordspace/",
                "downloaded" => 'n/a',
                "homepage" => "http://club.orbisius.com/products/wordpress-plugins/{$wp_res['plugin_id']}/",
                "requires" => "3.0",
                "tested" => $wp_res['tested_with_wp_version'],
                "url" => "http://club.orbisius.com/products/wordpress-plugins/{$wp_res['plugin_id']}/"
            );

            file_put_contents( $wp_res['target_release_dir'] . '/update.json', json_encode( $update_rec, JSON_PRETTY_PRINT ) );

            if ( ! empty( $wp_res['change_log'] )) { // 1 level up is the change log
                file_put_contents( dirname( $wp_res['target_release_dir'] ) . '/changelog.txt', $wp_res['change_log'] );
            }

            $struct['result'] .= "<pre>";

	        $rel_dir_linux = $wp_res['target_release_dir'];
	        $struct['result'] .= "\nRelease dir (linux): <input type='text' value='$rel_dir_linux' class='full_width' onclick='this.select();' />\n";
	        $rel_dir_win = $wp_res['target_release_dir_windows'];
	        $struct['result'] .= "\nRelease dir (win): <input type='text' value='$rel_dir_win' class='full_width' onclick='this.select();' />\n";

	        $struct['result'] .= var_export($wp_res, 1);
//            $struct['result'] .= var_export($zip_res, 1);
            $struct['result'] .= "</pre>";

            break;

        case 'release_free_plugin':
            $stored_ver = App_Release_Manager_Release::getRelease($plugin_dir);

            // 1.0.1
            if (!empty($stored_ver) && version_compare($ver, $stored_ver, '<=')) {
                throw new Exception("The plugin has already been tagged with this verison [$ver] or the version is smaller than current one.");
            }

            if (empty($ver)) {
                throw new Exception("Target version not specified.");
            }

            $cur_dir = getcwd();

            chdir($plugin_dir);

            $info = `svn info 2>&1`;
            $data = App_Release_Manager_File::parsePluginMeta('', $info);

            $trunk_url = empty( $data['URL'] ) ? '' : $data['URL'];
            
            if ( empty( $trunk_url ) ) {
                throw new Exception("Cannot detect trunk URL for $plugin_dir. [$info]");
            }
            
            $trunk_url = rtrim($trunk_url, '/') . '/';
            $tags_url = str_replace('trunk', 'tags', $trunk_url);
            $new_tag_url = $tags_url . $ver;

            $struct['result'] .= $plugin_dir;
            $struct['result'] .= "<pre>";
            $struct['result'] .= "trunk_url : $trunk_url\n";
            $struct['result'] .= "new_tag_url : $new_tag_url\n";

            $cmd_tag = "svn cp "
                    . " --non-interactive "
                    . " --username=" . escapeshellarg(APP_SVN_USER)
                    . " --password=" . escapeshellarg(APP_SVN_PASS)
                    . ' ' . escapeshellarg($trunk_url)
                    . ' ' . escapeshellarg($new_tag_url)
                    . ' --message=' . escapeshellarg("Released version $ver") . ' 2>&1';

            set_time_limit(6 * 60);
            //$run_cmd = $cmd_tag;
            $run_cmd = `$cmd_tag`; // svn commit is slow sometimes (or most of the time).

            $struct['result'] .= "cmd: [$cmd_tag]";
            $struct['result'] .= $run_cmd;

            if (!preg_match('#Committed revision\s+\d+#si', $run_cmd)) {
                throw new Exception("Commit failed. Cmd output: " . $run_cmd);
            } else {
                $struct['result'] .= App_Release_Manager_String::msg('Pushed new version successfully.', 1);
            }

//            $rel_dir_linux = $data['target_release_dir'];
//            $struct['result'] .= "Release dir (linux): <input type='text' value='$rel_dir_linux' />";
//
//            $rel_dir_win = $data['target_release_dir_windows'];
//            $struct['result'] .= "Release dir (win): <input type='text' value='$rel_dir_win' />";

            $struct['result'] .= var_export($data, 1);

            App_Release_Manager_Release::setRelease($plugin_dir, $ver);

            chdir($cur_dir);
            $struct['result'] .= "</pre>";

            break;

        default:
            break;
    }


} catch (Exception $e) {
    $struct['status'] = 0;
    $struct['msg'] = $e->getMessage();
    $struct['result'] .= App_Release_Manager_String::msg($e->getMessage(), 0);
}

App_Release_Manager_Ajax::sendJSON($struct);
