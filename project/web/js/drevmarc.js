/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
(function($)
{
    var _doc = $(document);

    
     $.initFocusAndErrorToRevendicationFieldDrevMarc = function() {
        var field = $('.error_field_to_focused');
        var divError = field.parent();
        field.each(function() {
            $(this).blur(function() {
                if ($(this).val() != "") {
                    divError.removeClass('has-error');
                } else {
                    divError.addClass('has-error');
                }
            });
        });
    }

    /* =================================================================================== */
    /* FUNCTIONS CALL */
    /* =================================================================================== */
    _doc.ready(function()
    {
        $.initFocusAndErrorToRevendicationFieldDrevMarc();

    });

})(jQuery);