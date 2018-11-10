(function ($) {
    $(document).ready(function () {
        $(".toggle").on("click", function () {
            $($(this).data("target")).toggle();
        });
        $("pre").each(function (i, e) {
            hljs.registerLanguage("markup", function () {
                return hljs.getLanguage("php");
            });
            hljs.highlightBlock(e);
        });
    });
})(jQuery);