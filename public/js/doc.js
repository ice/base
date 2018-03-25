(function ($) {
    // Highlight
    $(document).ready(function () {
        $("pre").each(function (i, e) {
            hljs.registerLanguage("markup", function () {
                return hljs.getLanguage("html");
            });
            hljs.highlightBlock(e);
        });
    });
})(jQuery);
