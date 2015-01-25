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
        $('a.link-to-section').click(function() {
            $($(this).attr('href')).removeClass('hidden');
            $(document).scrollTo($(this).attr('href'));
            $(this).closest('section').addClass('hidden');
            console.log(L.getMap('carte_1'));
            return false;
        });

    });

})(jQuery);