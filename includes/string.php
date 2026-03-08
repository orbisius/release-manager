<?php

class App_Release_Manager_String {
    /**
     * App_Release_Manager_String::msg();
     */
    static public function msg($msg = '', $status = 0, $div = 1) {
        $prefix = '';
        
        switch ($status) {
            case 0:
                $cls = 'warn';
                $prefix = '<i class="bi bi-x-circle-fill"></i>';
                break;

            case 1:
                $cls = 'ok';
                $prefix = '<i class="bi bi-check-circle-fill"></i>';
                break;

            default:
                $cls = 'notice';
                $prefix = '<i class="bi bi-exclamation-circle-fill"></i>';
                break;
        }

        $container_tag = $div ? 'div' : 'span';

        $msg = "<$container_tag class='$cls'>$prefix $msg</$container_tag>";
        return $msg;
    }
}
