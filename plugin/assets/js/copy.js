if (jQuery)(function ($) {
    $(document).on('click', '.compmodule_table tr td input[type="text"]', function (e) {
        e.preventDefault();
        $(this).select();
    });
    $(document).on('click', '.compmodule_drop_cache', function (e) {
        e.preventDefault();
        var data = {
            action: 'compmodule_drop_cache',
            args: {
                token: $(this).attr('data-token')
            }
        };
        $.post(ajaxurl, data, function (response) {
            alert('Cache deleted');
        });
    });
})(jQuery);