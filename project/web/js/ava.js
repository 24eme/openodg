/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
(function($)
{
    var _doc = $(document);

    $.initTabHistoryDocuments = function()
    {
    	$('#history-categories a').click(function (event) {
    		event.preventDefault();
    		event.stopImmediatePropagation()
    		$(this).tab('show');
    	})

    };

    _doc.ready(function()
    {
        $.initTabHistoryDocuments();
        /*
        * Volume des recoltes des appellation revendiquées
        */
        var inputs_volumes_recolte = document.querySelectorAll('table#table-revendication input.input-recolte-hl');
        inputs_volumes_recolte.forEach(function (input, index) {
            input.addEventListener('change', function (event) {
                var hash = input.getAttribute("data");
                var vci_recolte = $('#vci_'+hash);
                var total_recolte = $('#total_'+hash);
                var vci = 0;
                if(vci_recolte.text()){
                  vci = parseFloat(vci_recolte.text());
                }
                total_recolte.html((parseFloat(input.value.replace(',', '.')) + vci).toFixed(2))
            })
        })
    });
})(jQuery);
