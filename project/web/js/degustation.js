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
        $("#listes_operateurs .list-group-item .btn-success").click(function() {
            var ligne = $(this).parent().parent();
            ligne.attr('data-state', 'active');
            $.updateItem(ligne);

            return false;
        });

        $("#listes_operateurs .list-group-item .btn-danger").click(function() {
            var ligne = $(this).parent().parent();
            ligne.attr('data-state', '');
            $.updateItem(ligne);

            return false;
        });

        $("#nav_a_prelever").click(function() {
            $(this).parent().find('li').removeClass('active');
            $(this).addClass('active');
            $("#listes_operateurs .list-group-item[data-state!=active]").addClass('hidden');

            return false;
        });

        $("#nav_tous").click(function() {
            $(this).parent().find('li').removeClass('active');
            $(this).addClass('active');
            $("#listes_operateurs .list-group-item").removeClass('hidden');

            return false;
        });


    });

    $.updateItem = function(ligne)
    {
        if(ligne.attr('data-state') == "active") {
            ligne.find('button.btn-danger, select').removeClass('hidden');
            ligne.find('button.btn-success').addClass('hidden');
        } else {
            ligne.find('button.btn-danger, select').addClass('hidden');
            ligne.find('button.btn-success').removeClass('hidden');
            if($("#nav_a_prelever").hasClass('active')) {
                ligne.addClass('hidden');
            }
        }
    }

})(jQuery);