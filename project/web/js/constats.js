/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(function ($)
{
    var _doc = $(document);


    $.initRendezvousDeclarantForm = function () {
        if ($('form#rendezvousDeclarantForm').length) {

            $('.select2').on("change", function (e) {
                $('form#rendezvousDeclarantForm').submit();
            });
        }
    }

    $.initFormOperateurRendezvous = function () {
        $('form.form_operateur_rendezvous input').focus(function () {
            var id = $(this).parents('form').attr('id');
            $('form.form_operateur_rendezvous').each(function () {
                if ($(this).attr('id') != id) {
                    $(this).find('input').each(function () {
                        $(this).attr('disabled', true);
                    });
                    $(this).attr('style','opacity:0.6');
                }
            });

        });
        $('form.form_operateur_rendezvous input').blur(function () {
            var id = $(this).parents('form').attr('id');
            $('form.form_operateur_rendezvous').each(function () {
                if ($(this).attr('id') != id) {
                    $(this).find('input').each(function () {
                        $(this).attr('disabled', false);
                    });
                    $(this).attr('style','opacity:1');
                }
            });

        });
    }



    /* =================================================================================== */
    /* FUNCTIONS CALL */
    /* =================================================================================== */
    _doc.ready(function ()
    {
        $.initRendezvousDeclarantForm();
        $.initFormOperateurRendezvous();

    });

})(jQuery);

