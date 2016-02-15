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

    $('#btn-validation-document-parcellaire').click(function() {
            $("input:checkbox[name*=validation]").each(function() {
                    $(this).parent().parent().parent().removeClass("has-error");
            });
            $("#engagements .alert-danger").addClass("hidden");
            if($("input:checkbox[name*=validation]").length != $("input:checkbox[name*=validation]:checked").length) {
                $("#engagements .alert-danger").removeClass("hidden");
                $("input:checkbox[name*=validation]:not(:checked)").each(function() {
                    $(this).parent().parent().parent().addClass("has-error");
                });
                $("input:checkbox[name*=validation]:checked").each(function() {
                    $(this).parent().parent().parent().removeClass("has-error");
                });
                return false;
            }
        });
});