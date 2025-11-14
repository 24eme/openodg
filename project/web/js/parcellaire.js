document.querySelectorAll(".table-apporteursCoop tr").forEach(function (tr) {
  if (tr.querySelector('.switch')) {
    tr.querySelector('.switch').addEventListener('change', function (e) {
      tr.querySelector('.texteStatut').textContent = e.currentTarget.checked ? "Apporteur" : "Démissionaire";
    });
  }
});

document.querySelectorAll(".tableIntentionAffectation tr").forEach(function (tr) {
  if (tr.querySelector('input')) {
    tr.querySelector('.switch').addEventListener('change', function (e) {
      tr.querySelector('.affecte_superficie').disabled = !e.currentTarget.checked;
      tr.querySelector('.affecte_superficie').dispatchEvent(new Event('change'));
    });
  }
});

function changeButtonActiveAll(mention, span, check, btnActiveAll = false, onlyText = false) {
  if (!btnActiveAll) {
    var btnActiveAll = document.querySelector('#btn-switchactive-all');
  }
  btnActiveAll.dataset.status = mention;
  btnActiveAll.innerHTML = span;
  var target = document.querySelector(btnActiveAll.dataset.target);
  if (! onlyText) {
    target.querySelectorAll('.switch:not(.inputDisabled)').forEach(function (el) {
      el.checked = check;
      el.checked ? el.parentElement.parentElement.parentElement.classList.add("success") : el.parentElement.parentElement.parentElement.classList.remove("success");
    });
  }
}

var btnActiveAll = document.querySelector('#btn-switchactive-all');
if (btnActiveAll) {
  btnActiveAll.addEventListener('click', function (el) {
    if (btnActiveAll.dataset.status == 'affecter') {
        changeButtonActiveAll('retirer', btnActiveAll.dataset.remove, true, btnActiveAll);
    } else {
        changeButtonActiveAll('affecter', btnActiveAll.dataset.check, false, btnActiveAll);
    }
  });
}

document.querySelectorAll(".tableParcellaire tr td").forEach(function (td) {
  if (td.parentElement.querySelector('.switch')) {
    td.addEventListener('click', function (e) {
      elem = e.currentTarget.parentElement.querySelector('.switch');
      elem.checked = !elem.checked;
      elem.checked ? elem.parentElement.parentElement.parentElement.classList.add('success') : elem.parentElement.parentElement.parentElement.classList.remove('success');
      elem.dispatchEvent(new Event('change'));
      elem.dispatchEvent(new CustomEvent('change-native'));
    });
  }
});

document.querySelectorAll(".tableParcellaire input").forEach(function (el) {
  el.addEventListener('change', function (e) {
    var btnActiveAll = document.querySelector('#btn-switchactive-all');
    if (document.querySelectorAll('.tableParcellaire input:checked').length == document.querySelectorAll('.tableParcellaire input').length) {
      changeButtonActiveAll('retirer', btnActiveAll.dataset.remove, true, btnActiveAll, true);
    } else {
      changeButtonActiveAll('affecter', btnActiveAll.dataset.check, false, btnActiveAll, true);
    }
  });
});

if (document.querySelectorAll(".avaParcellAffec")) {
  document.querySelectorAll(".switch-xl").forEach(function (label) {
    let inputSwitch = label.querySelector('[id^=parcellaire_parcelles_produits_]');
    if (! inputSwitch.dataset.disabled) {
      return ;
    }
    label.querySelector('.slider-xl').style['opacity'] = '0.5';
    inputSwitch.classList.add('inputDisabled');
  });
}


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
        function compute_superficies_input() {
          $('.total_superficie').each(function() {
              var somme_superficie = 0;
              $(this).parents('table').find('.affecte_superficie').each(function() {somme_superficie += parseFloat($(this).val())});
              $(this).parents('table').find('.total_superficie').eq(0).html(somme_superficie.toFixed(4));
              somme_superficie = 0;
              $(this).parents('table').find('.affecte_superficie:not(:disabled)').each(function() {somme_superficie += parseFloat($(this).val())});
              $(this).parents('table').find('.total_affecte').eq(0).html(somme_superficie.toFixed(4));
          });
        }
        $('input.affecte_superficie').change(function () { console.log('affecte_superficie changing'); compute_superficies_input()});
        compute_superficies_input();
    }

    if ($('.superficie2compute').length) {
        function compute_superficies() {
            $('.total_superficie').each(function() {
                var somme_superficie = 0;
                $(this).closest('table').find(".superficie2compute").each(function() {
                    if ($(this).parent().parent().find('.switch:checked').length) {
                        somme_superficie += parseFloat($(this).html().replace(',', '.'));
                    }
                });
                $(this).html(somme_superficie.toFixed(4).replace('.', ','));
            });
        }
        $('input.switch').on('update', function () { compute_superficies()});
        compute_superficies();
    }


    document.querySelectorAll(".switch").forEach( function (el) {
      var event = new Event('change');
      el.dispatchEvent(event);
    });
});
