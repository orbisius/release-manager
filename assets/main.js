var orbisius_release_manager_app = {
    loading_text: '---- &#9200; -----',
    util: {
        // the api result from API on dev machine may not return an obj due to xdebug headers.
        ensure_json: function (json) {
            if (typeof json == 'string') {
                try {
                    json_obj = JSON.parse(json);
                    json = json_obj;
                } catch (e) {
                    console.log('ensure_json. error: ' + e);
                }
            }

            return json;
        },
    }
};

jQuery(document).ready(function ($) {
    // should work for ajax
    $(document).on("click", ".push_release", function() {
    //$('.push_release').on('click', function () {
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
            json = orbisius_release_manager_app.util.ensure_json(json);
            $(container_id).html(json.result);
        });
    });

    // should work for ajax
    $(document).on("click", ".push_pro_release", function() {
    //$('.push_pro_release').on('click', function () {
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
            json = orbisius_release_manager_app.util.ensure_json(json);
            $(container_id).html(json.result);
        });
    });
});