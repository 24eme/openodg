/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
(function($)
{
    var _doc = $(document);

    $.formModificationCompte = function()
    {
        var bloc = $('#modification_compte');

        var presentation_infos = bloc.find('.presentation');
        var modification_infos = bloc.find('.modification');
        var btn_modifier = bloc.find('a.modifier');
        var btn_annuler = bloc.find('a.annuler');

        // modification_infos.hide();

        btn_modifier.click(function()
        {
            presentation_infos.hide();
            modification_infos.show();
            $("a.modifier").hide();
            bloc.addClass('edition');
            return false;
        });

        btn_annuler.click(function()
        {
            presentation_infos.show();
            modification_infos.hide();
            $("a.modifier").show();
            bloc.removeClass('edition');
            return false;
        });

    };

    _doc.ready(function()
    {
        $.formModificationCompte();
    });

})(jQuery);