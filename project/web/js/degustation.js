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

        $("#listes_operateurs .list-group-item[data-state!=active]").click(function() {
            var ligne = $(this);
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
            $("#listes_operateurs .list-group-item[data-state=active]").removeClass('list-group-item-success');
            return false;
        });

        $("#nav_tous").click(function() {
            $(this).parent().find('li').removeClass('active');
            $(this).addClass('active');
            $("#listes_operateurs .list-group-item").removeClass('hidden');
            $("#listes_operateurs .list-group-item[data-state=active]").addClass('list-group-item-success');

            return false;
        });


    });

    $.updateItem = function(ligne)
    {
        if(ligne.attr('data-state') == "active") {
            ligne.find('button.btn-danger, select').removeClass('hidden');
            ligne.find('button.btn-success').addClass('hidden');
            if(ligne.hasClass('clickable')) {
                ligne.addClass('list-group-item-success');
            }
            ligne.removeClass('clickable');
            if(ligne.find('select option[selected=selected]').length == 0) {
                $.tireAuSortCepage(ligne.find('select'));
            }
        } else {
            ligne.find('button.btn-danger, select').addClass('hidden');
            ligne.find('button.btn-success').removeClass('hidden');
            ligne.removeClass('list-group-item-success');
            ligne.addClass('clickable');
            if($("#nav_a_prelever").hasClass('active')) {
                ligne.addClass('hidden');
            }
            ligne.find('select option[selected=selected]').removeAttr('selected');   
        }

        $.updateNbPrelever();
        $.updateRecapCepages();
    }

    $.updateNbPrelever = function()
    {
        $("#nav_a_prelever .badge").html($("#listes_operateurs .list-group-item[data-state=active]").length);
    }

    $.tireAuSortCepage = function(select)
    {
        var nb_options = (select.find('option').length - 1);
        select.find('option').eq(Math.floor((Math.random() * nb_options) + 1)).attr('selected', 'selected');
    }

    $.updateRecapCepages = function()
    {
        $('#recap_cepages span.badge').text("0");

        $("#listes_operateurs .list-group-item select option:selected").each(function(index, value) {
            var item = $('#recap_cepages li[data-cepage="'+$(value).html()+'"] .badge');
            item.html(parseInt(item.html()) + 1);
        });

    }

})(jQuery);