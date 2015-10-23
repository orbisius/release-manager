jQuery(document).ready(function ($) {
    $('.push_release').on('click', function () {
        var plugin_id = $(this).data('id');
        var new_ver = $(this).data('new_ver');
        var plugin_dir = $(this).data('plugin_full_dir');
        var container_id = '.result_' + plugin_id;

        plugin_dir = unescape(plugin_dir);

        var params = {
            plugin_dir: plugin_dir,
            new_ver: new_ver
        };

        $(container_id).empty().html('Loading ...');

        $.post("ajax.php", params, function (json) {
            $(container_id).html(json.result);
        });
    });

    $('.push_pro_release').on('click', function () {
        var plugin_id = $(this).data('id');
        var new_ver = $(this).data('new_ver');
        var plugin_dir = $(this).data('plugin_full_dir');
        var container_id = '.result_' + plugin_id;

        plugin_dir = unescape(plugin_dir);

        var params = {
            cmd : 'package_pro_plugin',
            plugin_dir: plugin_dir,
            new_ver: new_ver
        };

        $(container_id).empty().html('Loading ...');

        $.post("ajax.php", params, function (json) {
            $(container_id).html(json.result);
        });
    });
});