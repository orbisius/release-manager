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
                $prefix = '<span class="glyphicon glyphicon-remove"></span>';
                break;

            case 1:
                $cls = 'ok';
                $prefix = '<span class="glyphicon glyphicon-ok"></span>';
                break;

            default:
                $cls = 'notice';
                $prefix = '<span class="glyphicon glyphicon-exclamation-sign"></span>';
                break;
        }

        $container_tag = $div ? 'div' : 'span';

        $msg = "<$container_tag class='$cls'>$prefix $msg</$container_tag>";
        return $msg;
    }
}
