(function($)
{

    $.initHabilitationDemande = function()
    {
        $(".modal.modal-demande").on('shown.bs.modal', function() {
            $(".select2-statut").on("select2-open", function (e) {
                $(".select2-result-selectable").each(function() {
                    $(this).html($(this).html().replace(/\((.+)\)/, '<br /><small>↳ $1</small>'));
                });
            });
        });
    }

    $(document).ready(function()
    {
        $.initHabilitationDemande();
        $('#habilitation_voirtout').on('change', function() {
            document.location.href=$(this).attr('data-href');
        });
    });

})(jQuery);
