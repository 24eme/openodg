$(document).ready(function()
{
    $("#parcellaire_infos_modification_btn").click(function() {
        $("#parcellaire_infos_visualisation").hide();
        $("#parcellaire_infos_modification").show();
    });

    $(".deleteButton").click(function(e) {
        if(confirm("Êtes vous sûr de vouloir supprimer cette parcelle?")) {
            $(this).next('.fakeDeleteButton').click();
        }

        return false;
    });

    $(".tdAcheteur").click(function(evt) {
        if (evt.target.nodeName == 'TD') {
            var input = $(this).children('input');
            input.prop("checked", !input.prop("checked"));
            return false;
        }
    });   
    
    $("form.parcellaireForm").each(function(){
        $(this).find("td input").click(function(){
            $(this).select();
        });
    });
});