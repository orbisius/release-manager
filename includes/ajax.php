<?php

class App_Release_Manager_Ajax {
    /**
     * Usage: App_Release_Manager_Ajax::isAjax();
     * @return bool
     */
    public static function isAjax() {
        $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        return $is_ajax;
    }

    /**
     * Usage: App_Release_Manager_Ajax::sendJSON();
     * @return bool
     */
    public static function sendJSON($struct, $send_header = 1, $exit = 1) {
        // Different header is required for ajax and jsonp
        // see https://gist.github.com/cowboy/1200708
        $callback = isset($_REQUEST['callback']) ? preg_replace('/[^a-z0-9$_]/si', '', $_REQUEST['callback']) : false;

        if ($send_header && !headers_sent()) {
            header('Access-Control-Allow-Origin: *');
            header('Content-Type: ' . ($callback ? 'application/javascript' : 'application/json') . ';charset=UTF-8');
        }

        $json_buff = version_compare(phpversion(), '5.4.0', '>=') ? json_encode($struct, JSON_PRETTY_PRINT) : json_encode($struct);

        echo ($callback ? $callback . '(' : '') . $json_buff . ($callback ? ')' : '');

        if ($exit) {
            exit;
        }
    }

}
