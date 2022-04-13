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
            if(input.attr('disabled') == 'disabled') {
                return false;
            }
            if(input.attr('readonly') == 'readonly') {
                return false;
            }
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
    if ($('input.affecte_superficie').length) {
        console.log('affecte_superficie : compute_superficies');
        function compute_superficies_input() {
            var somme_superficie = 0;
            $('.affecte_superficie').each(function() {somme_superficie += parseFloat($(this).val())});
            $('#total_superficie').html(somme_superficie.toFixed(4));
            somme_superficie = 0;
            $('.affecte_superficie:not([disabled])').each(function() {somme_superficie += parseFloat($(this).val())});
            $('#total_affecte').html(somme_superficie.toFixed(4));
        }
        $('input.affecte_superficie').change(function () { console.log('affecte_superficie changing'); compute_superficies_input()});
        compute_superficies_input();
    }

    if ($('.superficie2compute').length) {
        function compute_superficies() {
            $('.total_superficie').each(function() {
                var somme_superficie = 0;
                $(this).closest('table').find(".superficie2compute").each(function() {
                    if ($(this).parent().parent().find('.bsswitch:checked').length) {
                        somme_superficie += parseFloat($(this).html());
                    }
                });
                $(this).html(somme_superficie);
            });
        }
        $('input.bsswitch').on('update', function () { compute_superficies()});
        compute_superficies();
    }

});