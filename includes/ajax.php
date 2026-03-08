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
	        $content_type = $callback ? 'application/javascript' : 'application/json';
	        header("Content-Type: $content_type;charset=UTF-8");
        }

        $json_buff = json_encode($struct, JSON_PRETTY_PRINT);
        $prefix = $callback ? $callback . '(' : '';
        $suffix = $callback ? ')' : '';

        echo "$prefix$json_buff$suffix";

        if ($exit) {
            exit;
        }
    }

}
