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
    });

})(jQuery);