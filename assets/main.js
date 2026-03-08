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
    // Clear button for filter
    let filter_input = $('#orbisius-release-manager-filter');
    let clear_btn = $('#orbisius-release-manager-filter-clear');

    clear_btn.on('click', function () {
        filter_input.val('').trigger('input').focus();
    });

    // Live filter for plugin cards
    filter_input.on('input search keyup change', function () {
        let query = $(this).val().toLowerCase().trim();
        clear_btn.toggle(query.length > 0);

        if (!query) {
            $('.plugin_container').show();
            $('.orbisius-release-manager-scan-dir-heading').show();
            $('.orbisius-release-manager-scan-dir-separator').show();
            return;
        }

        $('.plugin_container').each(function () {
            let search_data = $(this).data('search') || '';
            let is_match = search_data.indexOf(query) !== -1;
            $(this).toggle(is_match);
        });

        // Show/hide scan dir headings and separators based on whether they have visible plugins
        $('.orbisius-release-manager-scan-dir-heading').each(function () {
            let siblings = $(this).nextUntil('.orbisius-release-manager-scan-dir-heading');
            let has_visible = siblings.filter('.plugin_container:visible').length > 0;
            $(this).toggle(has_visible);
            siblings.filter('.orbisius-release-manager-scan-dir-separator').toggle(has_visible);
        });
    });

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