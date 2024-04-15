jQuery(function () {
    jQuery(document).on('click', '.wpaudit_check-plugin-btn', function (e) {
        var $this = jQuery(this)
        var row = $this.parent();
        var container = $this.closest('.wpaudit_container');
        var processing = row.find('.processing');
        var plugin = $this.data('plugin');
        var version = $this.data('version');
        jQuery.ajax({
            url: wpaudit.ajax_url,
            type: 'POST',
            data: {
                action: 'wpaudit_verify_plugin',
                plugin: plugin,
                version: version,
                nonce: container.data('nonce')
            },
            dataType: 'json',
            beforeSend: function () {
                processing.removeClass('hide');
                $this.hide();
            },
            success: function (response) {
                if (response.html) {
                    row.html(jQuery(response.html));
                } else {
                    processing.addClass('hide');
                    $this.show();
                }
            },
            error: function (xhr, status, error) {
                processing.addClass('hide');
                $this.show();
            }
        });

        return false;
    })
})