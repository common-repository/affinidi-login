(function ($) {
    $(document).ready(function(){
        var hash = window.location.hash;
        var anchor = $('#' + hash);
        if (anchor.length > 0) {
            anchor.click();
        }
    });
})(jQuery);