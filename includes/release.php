<?php

class App_Release_Manager_Release {
    private static $file = 'zzz_release.txt';

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
