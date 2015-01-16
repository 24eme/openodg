/* =================================================================================== */
/* JQUERY CONTEXT */
/* =================================================================================== */
(function($)
{
    var _doc = $(document);

    /* =================================================================================== */
    /* FUNCTIONS CALL */
    /* =================================================================================== */
    _doc.ready(function()
    {
        $("#a_choisir a.list-group-item").click(function() {
            var element = $("#"+$(this).attr('id').replace("a_choisir", "choisi"));
            element.removeClass("hidden");
            $(this).addClass("hidden");
            //$(document).scrollTo(element);
            return false;
        });
        $("#choisi div.list-group-item button.trash").click(function() {
            console.log('test');
            var ligne = $(this).parent().parent();
            var element = $('#' + ligne.attr('id').replace("choisi", "a_choisir"));
            element.removeClass("hidden");
            ligne.addClass("hidden");
            //$(document).scrollTo(element);
            return false;
        });
    });

})(jQuery);