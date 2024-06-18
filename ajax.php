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

            if (empty($main_plugin_file)) {
                throw new Exception("Cannot find main plugin file.");
            }

            $wp_res = App_Release_Manager_WP_Lib::parse( $main_plugin_file );

            if ( ! is_dir( $wp_res[ 'target_release_dir' ] ) ) {
                mkdir( $wp_res[ 'target_release_dir' ], 0750, true );
            }

            if ( empty( $wp_res['plugin_id'] ) ) {
                $struct['result'] .= "Something's wrong.";
                break;
            }

	        $extra_cool_params = [
				'exclude' => [],
	        ];

	        $buff = '';
	        $git_ignore = "$plugin_dir/.gitignore";

			// We'll exclude some files and directories from the pkg for various reasons
            // not necessary or for production.
	        // @todo skip non-minified versions of the assets.
			// check what's in git ignore
			if (file_exists($git_ignore)) {
				$buff .= file_get_contents($git_ignore);
				$buff .= "\n";
			}

	        $rl_ignore = "$plugin_dir/.release_manager_ignore";

	        if (file_exists($rl_ignore)) {
				$buff .= file_get_contents($rl_ignore);
				$buff .= "\n";
			}

	        $buff = trim($buff);

	        if (!empty($buff)) {
				$lines = preg_split('#[\r\n]+#si', $buff);
				$lines = array_map('trim', $lines);
				$lines = array_filter($lines);
				$lines = array_unique($lines);

				// Let's see what to ignore;
				// files: */some-file.txt
				// dirs: */mu-plugins/*
				// '-x ' . escapeshellarg('*.idea/*'),
				foreach ($lines as $item) {
					if (preg_match('/^\h*[#;]/si', $item)) { // comments?
                        continue;
                    }

                    $exclude = '';
					$item_fmt = $item;
					$item_fmt = rtrim($item_fmt, '/');
					$period_pos = strpos(basename($item_fmt), '.'); // must be a file

					if ($period_pos !== false) {
						$exclude = '*/' . $item_fmt;
					} else {
						$exclude = '*/' . $item_fmt . '/*';
					}

					$extra_cool_params['exclude'][] = $exclude;
				}
			}

            $target_zip_file = $wp_res['target_release_file'];
            $plugin_dir = $wp_res['plugin_dir'];
            $zip_res = App_Release_Manager_File::archive( $target_zip_file, $plugin_dir, $extra_cool_params );

            $update_rec = array(
                "author" => "<a href='https://orbisius.com' target='_blank'>Orbisius.com</a>",
                "author_profile" => "https://profiles.wordpress.org/lordspace/",
                "downloaded" => 'n/a',
                "homepage" => "https://orbisius.com/products/wordpress-plugins/{$wp_res['plugin_id']}/",
                "requires" => "3.0",
                "tested" => $wp_res['tested_with_wp_version'],
                "url" => "https://orbisius.com/products/wordpress-plugins/{$wp_res['plugin_id']}/"
            );

	        $cur_dir = getcwd(); // get it so we can go back jic
	        $exit_code = 0;

	        App_Release_Manager_Release::initEnv();

	        $files = []; // to be committed

            $upd_file = $wp_res['target_release_dir'] . '/update.json';
            file_put_contents( $upd_file, json_encode( $update_rec, JSON_PRETTY_PRINT ), LOCK_EX );

            if ( ! empty( $wp_res['change_log'] )) { // 1 level up is the change log
				$change_log_file = dirname( $wp_res['target_release_dir'] ) . '/changelog.txt';
                file_put_contents( $change_log_file, $wp_res['change_log'] );
	            $files[] = $change_log_file;
            }

            $struct['result'] .= "<pre>";

	        $rel_dir_linux = $wp_res['target_release_dir'];
	        $struct['result'] .= "\nRelease dir (linux): <input type='text' value='$rel_dir_linux' class='full_width' readonly='readonly' onclick='this.select();' />\n";
	        $rel_dir_win = $wp_res['target_release_dir_windows'];
	        $struct['result'] .= "\nRelease dir (win): <input type='text' value='$rel_dir_win' class='full_width' readonly='readonly' onclick='this.select();' />\n";

	        $struct['result'] .= var_export($wp_res, 1);
//            $struct['result'] .= var_export($zip_res, 1);
            $struct['result'] .= "</pre>";

            // Let's add to git the new files to git
            $files[] = $upd_file;
            $files[] = $target_zip_file;
            $git_cli = APP_GIT_BIN;

	        foreach ($files as $file) {
		        chdir(dirname($file));
		        $file_esc = escapeshellarg(basename($file));

				// Let's add the file first
	        	$git_cmd = "$git_cli add $file_esc";
	        	$last_line = exec($git_cmd, $output_arr, $exit_code);

	        	if (!empty($exit_code)) {
			        $struct['result'] .= "<pre>Error: couldn't git add: [$file_esc]." . htmlentities(join('', $output_arr) . "</pre>");
                    continue;
		        }

		        // Let's commit the file. It seems for windows it's better to have the file first
		        // https://stackoverflow.com/questions/8795097/how-to-git-commit-a-single-file-directory
		        $git_cmd = "$git_cli commit"
		                   . " -o $file_esc " // -o, --only commit only specified files
		                   . " -m " . escapeshellarg("Committing file [$file] for " . $wp_res['plugin_id']);

		        $git_cmd .= ' 2>&1';
		        $last_line = exec($git_cmd, $output_arr, $exit_code);

		        if (!empty($exit_code)) {
			        $struct['result'] .= "<pre>Error: couldn't git commit: [$file_esc]." . htmlentities(join('', $output_arr) . "</pre>");
                    continue;
		        }
	        } // loop

            // try to push it too
            if (strpos($git_cli, '/ogit') !== false) {
                $git_cmd = "$git_cli push origin master";
                $git_cmd .= ' 2>&1';
                $last_line = exec($git_cmd, $output_arr, $exit_code);

                if (empty($exit_code)) {
                    $struct['result'] .= ' pushed';
                } else {
                    $struct['result'] .= "Error: couldn't git do git push: [$file_esc]." . htmlentities(join('', $output_arr));
                }
            }

			// git push
	        if (0&& empty($exit_code)) {
		        $git_cmd = "git push origin master";
		        $last_line = exec($git_cmd, $output_arr, $exit_code);

		        if (!empty($exit_code)) {
			        $struct['result'] .= "Error: couldn't git push {$wp_res['plugin_id']}" . htmlentities(join('', $output_arr));
		        } else {
			        $struct['result'] .= ' pushed';
		        }
	        }

	        chdir($cur_dir);

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
} finally {
    App_Release_Manager_Ajax::sendJSON($struct);
}
