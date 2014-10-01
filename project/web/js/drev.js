/* =================================================================================== */
/* JQUERY CONTEXT */
/* =================================================================================== */
(function($)
{
    var _doc = $(document);

    $.initExploitation = function()
    {
        $('#btn_exploitation_modifier').click(function(e) {
            $('#btn_exploitation_modifier').addClass("hidden")
            $('#btn_exploitation_annuler').removeClass("hidden")
            $('#row_form_exploitation').removeClass("hidden");
            $('#row_info_exploitation').addClass("hidden");
        });
    }

    /* =================================================================================== */
    /* FUNCTIONS CALL */
    /* =================================================================================== */
    _doc.ready(function()
    {
        $.initExploitation();
    });

})(jQuery);