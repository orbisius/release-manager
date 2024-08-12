<?php

class App_Release_Manager_Release {
    private static $file = 'zzz_release.txt';

	/**
	 * App_Release_Manager_Release::initEnv();
	 * @return int
	 */
	public static function initEnv() {
		$host = empty($_SERVER['HTTP_HOST']) ? trim(`hostname`) : $_SERVER['HTTP_HOST'];
		$host = preg_replace('#^www\.#si', '', $host);
		$host = strtolower($host);
		$host = empty($host) ? 'localhost' : $host;

		// git won't sync or pull without those variables.
		// https://git-scm.com/book/en/v2/Git-Internals-Environment-Variables
		putenv('GIT_MERGE_AUTOEDIT=no'); // or pass this --no-edit to pull or this will prompt for msg on merge
		putenv("GIT_AUTHOR_NAME='$host'");
		putenv("GIT_AUTHOR_EMAIL='admin@$host'");
		putenv("GIT_COMMITTER_NAME='$host'");
		putenv("GIT_COMMITTER_EMAIL='admin@$host'");
//		putenv("GIT_TRACE=1");
//		putenv("GCM_TRACE=000_git.log");
//		putenv("GCM_TRACE=1");
//		putenv("GIT_CURL_VERBOSE=1");

		// set some timeout
		// https://resources.collab.net/blogs/tips-on-git
		putenv("GIT_HTTP_LOW_SPEED_TIME=60");
		putenv("GIT_HTTP_LOW_SPEED_LIMIT=32768"); //  download speed in bytes per second.

		return 1;
	}

    /**
     * Reads a file partially e.g. the first NN bytes.
     *
     * @param string $file
     * @param int $len_bytes how much bytes to read
     * @param int $seek_bytes should we start from the start?
     * @return string
     */
    static function getRelease($plugin_dir) {
        $buff = '';
        $release_file = $plugin_dir . '/' . self::$file;

        if (is_file($release_file)) {
            $buff = file_get_contents($release_file);
            $buff = trim($buff);
        }

        return $buff;
    }

    static function setRelease($plugin_dir, $ver) {
        $release_file = $plugin_dir . '/' . self::$file;
        
        // let's mark the plugin as updated to that version.
        return file_put_contents($release_file, $ver);
    }
}
