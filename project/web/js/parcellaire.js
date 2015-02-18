$(document).ready(function()
{
    $("#parcellaire_infos_modification_btn").click(function() {
        $("#parcellaire_infos_visualisation").hide();
        $("#parcellaire_infos_modification").show();
    });

    $(".deleteButton").click(function() {
        return confirm("Êtes vous sûr de vouloir supprimer cette parcelle?");
    });

    $(".tdAcheteur").click(function(evt) {
        if (evt.target.nodeName == 'TD') {
            var input = $(this).children('input');
            input.prop("checked", !input.prop("checked"));
            return false;
        }
    });

});