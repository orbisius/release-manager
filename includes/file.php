<?php

class App_Release_Manager_File {
    /*echo "Changing back to [$cur_dir]...\n";
    chdir($cur_dir);*/

    /**
     * Creates the zip file which contains the fresh site folder (without htdocs or wordpress folder).
     * The default latest.zip file contains wordpress in it.
     * @todo read the release_manager_ignore file and skip files and dirs.
     * App_Release_Manager_File::archive();
     * @param str $target_archive_file
     * @param str $path
     * @return string
     * @see http://askubuntu.com/questions/28476/how-do-i-zip-up-a-folder-but-exclude-the-git-subfolder
     */
    public static function archive($target_archive_file, $path, $extra_params = []) {
        $current_dir = getcwd();
        chdir(dirname($path));
        $folder2zip = basename($path);

        $binary = 'zip'; // /bin/zip ? or zip.exe; the exclusion for the directory doesn't work on Windows

        // exclude some files and especially our mu plugin so people don't see how to get access to the system.
        $options_extra = array(
            '-x ' . escapeshellarg('*.git*'),
            '-x ' . escapeshellarg('*.svn*'),
            '-x ' . escapeshellarg('*.log*'),
            '-x ' . escapeshellarg('*.bak*'),
            '-x ' . escapeshellarg('*.zip*'),
            '-x ' . escapeshellarg('*screenshot-*'),
            '-x ' . escapeshellarg('*.gitignore*'),
            '-x ' . escapeshellarg('*.release_manager_ignore*'),
            '-x ' . escapeshellarg('*nbproject*'),
            '-x ' . escapeshellarg('*project*'),
            '-x ' . escapeshellarg('*.idea/*'),
            '-x ' . escapeshellarg('*/.ht_sandbox_data/*'),
            '-x ' . escapeshellarg('*/mu-plugins/*'),
        );

		if (!empty($extra_params['exclude'])) {
			$exclude = (array) $extra_params['exclude'];
			foreach ($exclude as $exc) {
				$options_extra[] = '-x ' . escapeshellarg($exc);
			}
		}

        $options_extra_str = join(' ', $options_extra);

        // -q -> quiet
        // -r -> recursive
        // -9 -> maximum compression
        $cmd = "$binary -r -9 $target_archive_file $folder2zip/*.* $options_extra_str 2>&1";

        //$result = `$cmd`; // it is faster to call OS funcs
        $output_arr = array();
        $return_var = '';

        exec( $cmd, $output_arr, $result );

        chdir($current_dir);

        return $result;
    }

    /**
     * Reads a file partially e.g. the first NN bytes.
     *
     * @param string $file
     * @param int $len_bytes how much bytes to read
     * @param int $seek_bytes should we start from the start?
     * @return string
     */
    static function readFilePartially($file, $len_bytes = 512, $seek_bytes = 0) {
        $buff = '';
        
		if (!file_exists($file)) {
            return false;
        }
		
        $file_handle = fopen($file, 'rb');

        if (!empty($file_handle)) {
            if ($seek_bytes > 0) {
                fseek($file_handle, $seek_bytes);
            }

            $buff = fread($file_handle, $len_bytes);
            fclose($file_handle);
        }

        return $buff;
    }

    /**
     * This plugin scans the files in a folder and tries to get plugin data.
     * The real plugin file will have Name, Description variables set.
     * If the file doesn't have that info WP will prefill the data with empty values.
     *
     * @param string $folder - plugin's folder e.g. wp-content/plugins/like-gate/
     * @return string wp-content/plugins/like-gate/like-gate.php or false if not found.
     */
    static public function findMainPluginFile($folder = '') {
        $folder = trim($folder, '/') . '/';
        $files_arr = glob($folder . '*.php'); // list only php files.

        foreach ($files_arr	as $file) {
            $buff = self::readFilePartially($file);

            // Did we find the plugin? If yes, it'll have Name filled in.
            if (stripos($buff, 'Plugin Name') !== false) {
                return $file;
            }
        }

        return false;
    }

    /**
     * Parses the meta info from a plugin. Values are separated by a colon (:).
     * If buff is passed it'll be used instead of reading the file's stuff.
     * App_Release_Manager_File::parsePluginMeta();
     * @param str $main_plugin_file
     * @param str $buff (optional)
     * @return array
     */
    static public function parsePluginMeta($main_plugin_file, $buff = '') {
        $data = array();

        if (empty($buff)) {
            $buff = self::readFilePartially($main_plugin_file, 1024);
        }
        
        $lines = preg_split('#[\r\n]+#si', $buff);

        if (!empty($main_plugin_file)) {
            $readme_file = dirname($main_plugin_file) . '/readme.txt';

            if (is_file($readme_file)) {
                $readme_buff = self::readFilePartially($readme_file, 1024);
                $readme_lines = preg_split('#[\r\n]+#si', $readme_buff);
                array_unshift($readme_lines, "Readme Content");
                $lines = array_merge($lines, $readme_lines);
            }
        }

        $data['raw_lines'] = $lines;

        foreach ($lines as $line) {
            $line = trim($line);
            
            if ((strpos($line, ':') === false)
                    || !preg_match('#^[\w+\s]{2,20}:\s*\w+#si', $line) // colon must be within the first 2 to 20 chars
                    || preg_match('#^(//|/\*|\*)#si', $line)
                    || !preg_match('#\w+\s*:\s*\w+#si', $line) // colon surrounded by something non numeric
                    || preg_match('#^https?://[\w-?&%\#+.=\s/]+$#si', $line)) {
                continue;
            }

            // we only care about the meta info key : val
            list ($key, $val) = explode(':', $line, 2); // slit on first occurrence

            $key = trim($key);
            $val = trim($val);

            $data[$key] = $val;
        }

        $defaults = array(
            'Plugin Name' => 'n/a',
            'Version' => 'n/a',
            'Tested up to' => 'n/a',
            'Stable tag' => 'n/a',
        );

        $data = array_merge($defaults, $data);

        return $data;
    }
}
