if (jQuery)(function ($) {
    $(document).on('click', '.compmodule_table tr td input[type="text"]', function (e) {
        e.preventDefault();
        $(this).select();
    });
})(jQuery);