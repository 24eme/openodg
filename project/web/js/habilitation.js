(function($)
{

    $.initHabilitationDemande = function()
    {
        $( ".modal" ).on('shown.bs.modal', function(){
            $(".select2").on("select2-open", function (e) {
                $(".select2-result-selectable").each(function(){
                    if($(this).text() == 'Enregistrement'){
                        $(this).css('color','#9f0038');
                        $(this).css('background-color','#ffe9ef');
                    }
                });
            });
        });

          $(".select2-result-selectable").each(function(){
            console.log($(this).html());
        });
    }

    $(document).ready(function()
    {
        $.initHabilitationDemande();

    });

})(jQuery);
