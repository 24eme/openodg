/* =================================================================================== */
/* JQUERY CONTEXT */
/* =================================================================================== */
(function($)
{   
    /* =================================================================================== */
    /* GLOBAL VARS */
    /* =================================================================================== */
    
    // Main elements
    var _doc = $(document);
    
    $.initNavTab = function()
    {
        var hash = window.location.hash;
        hash && $('ul.nav a[href="' + hash + '"]').tab('show');
        $('.nav-tabs a').click(function (e) {
            $(this).tab('show');
            var scrollmem = $('body').scrollTop();
            window.location.hash = this.hash;
            $('html,body').scrollTop(scrollmem);
        });
    }

    /* =================================================================================== */
    /* FUNCTIONS CALL */
    /* =================================================================================== */
    _doc.ready(function()
    {
        $.initNavTab();
    });
    
})(jQuery);