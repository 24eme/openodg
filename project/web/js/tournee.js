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

            return false;
        });
    });

})(jQuery);