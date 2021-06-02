/* =================================================================================== */
/* JQUERY CONTEXT */
/* =================================================================================== */
(function($)
{
    var _doc = $(document);

    $.initExploitation = function()
    {
        $('.btn_exploitation_modifier').click(function(e) {
            $('.btn_exploitation_modifier').addClass("hidden")
            $('#btn_exploitation_annuler').removeClass("hidden")
            $('.row_form_exploitation').removeClass("hidden");
            $('.row_info_exploitation').addClass("hidden");
        });
        if($('#drevDenominationAuto').length){
            if($('#drevDenominationAuto').data("auto")){
                $('#drevDenominationAuto').modal('show');
            }
        }
        $('#checkbox_logement_vin').on('change', function() {
            $('#form_logement_vin').find('input').val("");
        });
    }

    $.initPrelevement = function()
    {
        $('.form-chai button').click(function() {
            $('.form-chai button, .form-chai .form-group, .form-chai p').toggleClass('hidden');
        });
    }

    $.initControleExterne = function()
    {
        $('#checkbox_non_conditionneur').on('change', function() {
            if($(this).is(':checked')) {
                $('#bloc-form-control-externe').addClass('opacity-lg');
                $('#bloc-lieu-prelevement').addClass('opacity-lg');
                $('#bloc-form-control-externe input, #bloc-form-control-externe select, #bloc-lieu-prelevement button, #bloc-lieu-prelevement a, #bloc-form-control-externe button, #bloc-form-control-externe a').attr('disabled', 'disabled');
            } else {
                $('#bloc-form-control-externe').removeClass('opacity-lg');
                $('#bloc-lieu-prelevement').removeClass('opacity-lg');
                $('#bloc-form-control-externe input, #bloc-form-control-externe select, #bloc-lieu-prelevement button, #bloc-lieu-prelevement a, #bloc-form-control-externe button, #bloc-form-control-externe a').removeAttr('disabled');
            }
        });
        if($('#checkbox_non_conditionneur').is(':checked')) {
            $('#checkbox_non_conditionneur').change();
        }
    }

    $.initBtnValidation = function()
    {
        $('#btn-validation').click(function() {
            $(this).parents('form').attr('action', $(this).parents('form').attr('action') + '?redirect=validation');
            $(this).parents('form').submit();
            return false;
        });
    }

    $.initFocusAndErrorToRevendicationField = function()
    {
        var field = $('.error_field_to_focused');
        field.focus();
    }

    $.initRevendicationFadeRow = function()
    {

        $('tr.with_superficie').each(function() {
            var children = $(this).find(" td input");
            if (children.length == 2) {
                var fade = true;
                children.each(function() {
                    if ($(this).val() != "") {
                        fade = false;
                    }
                });
                if (fade) {
                    $(this).addClass('with_superficie_fade');
                }
            }

        });
    }

    $.initRevendicationEventsFadeInOut = function() {
        $('tr.with_superficie_fade').each(function() {
            var tr = $(this);
            var children = $(this).find(" td input");
            children.each(function() {
                $(this).focus(function() {
                    tr.removeClass('with_superficie_fade');
                });
                $(this).blur(function() {
                    $.initRevendicationFadeRow();
                });
            });
        });
    }

    $.initEventErrorRevendicationField = function() {
        var field = $('.error_field_to_focused');
        var divError = field.parent().parent();
        field.each(function() {
            $(this).blur(function() {
                if ($(this).val() != "") {
                    divError.removeClass('has-error');
                } else {
                    divError.addClass('has-error');
                }
            });
        });
    }

    $.initRecapEventsAccordion = function() {
        $('#revendication_accordion tr.trAccordion').click(function() {
            var eventId = $(this).attr('data-target');
            var span = $(this).find('small span');

            var ouverts = $('tr td.hiddenRow div.in');
            if (ouverts.length && (eventId == '#' + ouverts.attr('id'))) {
                span.removeClass('glyphicon-chevron-right');
                span.addClass('glyphicon-chevron-down');
                $(eventId).collapse('hide');
            } else {
                $('tr td.hiddenRow div').each(function() {
                    $('#revendication_accordion tr.trAccordion').each(function() {
                        var span_all = $(this).find('small span');
                        span_all.removeClass('glyphicon-chevron-right');
                        span_all.addClass('glyphicon-chevron-down');
                    });
                    $(this).removeClass('in');
                });
                span.removeClass('glyphicon-chevron-down');
                span.addClass('glyphicon-chevron-right');
                $(eventId).collapse('show');

            }
        });
    }

    $.initCalculAuto = function(){

      $(".edit_vci tr.produits").each(function(){
        var produits = $(this);
        produits.find("input.sum_stock_final").change(function(){
          var sum = 0.0;

          produits.find('input.sum_stock_final').each(function(){
            local = $(this).val();
            if(!local){ local=0.0;}else{ local=parseFloat(local); }
            sum=sum+local;
          });
          produits.find("input.stock_final").val(sum.toFixed(2));
        });

      });

        var sommeRevendication = function() {
            $('#table-revendication tbody tr').each(function() {
                var somme = 0;
                $(this).find('.input_sum_value').each(function() {
                    if($(this).val()) {
                        somme += parseFloat($(this).val());
                    } else {
                    	if (parseFloat($(this).text())) {
                        somme += parseFloat($(this).text());
                    	}
                    }
                })
                if (!isNaN(somme)) {
	                if ($(this).find('.input_sum_total').is( "input" )) {
	                	$(this).find('.input_sum_total').val(somme.toFixed(2));
	                } else {
	                    $(this).find('.input_sum_total').text(somme.toFixed(2));
	                }
                }
            });

        }

        $('#table-revendication .input_sum_value').on('change', function() {
            sommeRevendication();
        });
        sommeRevendication();
    }

    $.initSocieteChoixEtablissement = function() {
        $('.societe_choix_etablissement').on('change', function (e) {
          if($(this).val() != "0"){
            $("#choix_etablissement").submit();
          }
        });
    }

    $.initLots = function(type) {
        if ($('#form_'+type+'_lots').length == 0)
        {
            return;
        }

        $('div.checkboxlots input[type="checkbox"]').click(function(e){
          e.preventDefault();
        });

        var checkBlocsLotCepages = function() {
            $('#form_'+type+'_lots .ligne_lot_cepage').each(function() {
                var saisi = true;
                $(this).find('input, select').each(function() {
                    if(!$(this).val()) {
                        saisi = false;
                    }
                });
                $(this).find('input, select').each(function() {
                    if($(this).is(":focus")) {
                        saisi = true;
                    }
                });
                if(!saisi) {
                    $(this).addClass('transparence-sm');
                } else {
                    $(this).removeClass('transparence-sm');
                }
            });

            $('#form_'+type+'_lots .modal_lot_cepages').each(function() {

                var libelle = "";
                var volume = 0.0;
                var total = 0.0;
                $(this).find('.ligne_lot_cepage').each(function() {
                  if ($(this).find('select.selectCepage').val()) {
                    total += ($(this).find('.input-hl').val())? parseFloat($(this).find('.input-hl').val()) : 0;
                  }
                });
                $(this).find('.ligne_lot_cepage').each(function() {
                    var ligne = $(this);
                    var cepage = $(this).find('.select2 option:selected').text();
                    var volume = parseFloat($(this).find('.input-hl').val());
                    if(cepage) {
                      $(this).removeClass('transparence-sm');
                  } else {
                      $(this).addClass('transparence-sm');
                    }
                    if(cepage && volume > 0) {
                        if(libelle) {
                            libelle = libelle + ", ";
                        }else{
                            libelle = "Mention : ";
                        }
                        var p = (total)? Math.round((volume/total) * 100) : 0;
                        libelle = libelle + cepage + "&nbsp;("+p+"%)";
                    }

                    $(this).find('input, select').each(function() {
                        if($(this).is(":focus")) {
                            ligne.removeClass('transparence-sm');
                        }
                    });
                });
                if(!libelle) {
                    libelle = "Sans mention de cépage";
                    $('#lien_'+$(this).attr('id')).removeAttr("checked");
                }else{
                  $('#lien_'+$(this).attr('id')).prop("checked","checked");
                }
                $('span.checkboxtext_'+$(this).attr('id')).html(libelle + " <a>(Changer)</a>");
            });

            document.querySelectorAll('#form_'+type+'_lots .modal_lot_cepages').forEach(function(modal) {
              var total = 0.00

              while (! modal.classList.contains('modal')) {
                  modal = modal.parentElement
              }
              var input_volume_id = modal.dataset.inputvolumeid;

              inputs = modal.querySelectorAll('input.input-hl')
              var nbRempli = 0;
              inputs.forEach(function (input) {
                  if (! isNaN(parseFloat(input.value)) && $('#'+input.id).parents('.ligne_lot_cepage').find('select.selectCepage').val() ) {
                      total += parseFloat(input.value)
                      nbRempli++;
                  }
              })

              var vol_total = document.getElementById(input_volume_id)
              if(!nbRempli) {
                vol_total.readOnly = false;
                return;
              }

              vol_total.value = total;

              $('#'+modal.id).find('.input-total').val(total.toFixed(2));

              $('#'+input_volume_id).blur()

              vol_total.readOnly = (parseFloat(vol_total.value) > 0) ? true : false
              if(vol_total.readOnly){
                let target_link = $('div.checkboxlots input[type="checkbox"]').attr('data-target');
                $('#'+input_volume_id).attr('data-target', target_link);
                $('#'+input_volume_id).attr('data-toggle', "modal");
              }
            })
        }

        function precision(f) {
            if (!isFinite(f)) { return 2 }
            var e = 1, p = 0
            while (Math.round(f * e) / e !== f) { e *= 10; p++; }
            if (p > 4) { p = 4 }
            return p
        }

        checkBlocsLotCepages();
        $('#form_'+type+'_lots .ligne_lot_cepage input').on('change', function() { checkBlocsLotCepages(); });
        $('#form_'+type+'_lots .ligne_lot_cepage select').on('change', function() { checkBlocsLotCepages(); });
        $('#form_'+type+'_lots .ligne_lot_cepage input').on('focus', function() { checkBlocsLotCepages(); });
        $('#form_'+type+'_lots .ligne_lot_cepage select').on('focus', function() { checkBlocsLotCepages(); });
        $('#form_'+type+'_lots .ligne_lot_cepage input').on('blur', function() { checkBlocsLotCepages(); });
        $('#form_'+type+'_lots .ligne_lot_cepage select').on('blur', function() { checkBlocsLotCepages(); });


        //Vérification de la cohérence des saisies dans la popup modal_lot_cepages
        $('.modal_lot_cepages a.btn-success').on('click',function(e){
          if(!$(this).parents('.modal_lot_cepages').find('.input-total').val()){  //recupere le volume total.
            $(this).parents('.modal_lot_cepages').find('.input-total').parents('.form-group').addClass('has-error');
            return false;
          }
          $(this).parents('.modal_lot_cepages').find('.input-total').parents('.form-group').removeClass('has-error');
          //RaZ des lignes sans cepages
          var nbinputavecvaleurs = 0;
          $(this).parents('.modal-dialog').find('.input-hl').each(function(){
            if($(this).val()){
              nbinputavecvaleurs += 1;
            }
          });
          $(this).parents('.modal-dialog').find('.input-pc').each(function(){
            if($(this).val()){
              nbinputavecvaleurs += 1;
            }
          });

          $(this).parents('.modal-dialog').find('.input-hl').each(function(){
            if (!$(this).val() && !$(this).parents('.ligne_lot_cepage').find('.input-pc').val() && nbinputavecvaleurs) {
              $(this).parents('.ligne_lot_cepage').find('select.selectCepage').val('');
              $(this).parents('.ligne_lot_cepage').find('select.selectCepage').trigger('change');
            }
          });

          //si pas de hl et pas de %, on set les %
          var nbligneaveccepage = 0;
          var nbligneaveccepageetpcouhl = 0;
          $(this).parents('.modal-dialog').find('select.selectCepage').each(function(){
            if($(this).val()) {
              nbligneaveccepage ++;
            }
            line = $(this).parents('.ligne_lot_cepage')
            if (line.find('.input-pc').val() || line.find('.input-hl').val()) {
              nbligneaveccepageetpcouhl ++;
            }
          });
          var i = 0;
          var sumpc = 0;
          console.log("nbligneaveccepageetpcouhl "+nbligneaveccepageetpcouhl);
          if (!nbligneaveccepageetpcouhl) {
            $(this).parents('.modal-dialog').find('.input-pc').each(function(){
              if (!$(this).parents('.ligne_lot_cepage').find('select.selectCepage').val()) {
                return ;
              }
              i ++;
              if (i < nbligneaveccepage) {
                $(this).val((100 / nbligneaveccepage).toFixed(2));
                sumpc += parseFloat($(this).val());
                return;
              }
              $(this).val((100 - sumpc).toFixed(2));
              $(this).parents('.modal_lot_cepages').find('.switch_hl_to_pc').prop("checked", true);
              $(this).parents('.modal_lot_cepages').find('.switch_hl_to_pc').trigger("change");

            });
          }

          //si % sélectionné, on rempli les hl
          if ($('.switch_hl_to_pc').is(':checked')) {
            var total = $(this).parents('.modal_lot_cepages').find('.input-total').val();
            var i = 0;
            var sumhl = 0;
            $(this).parents('.modal-dialog').find('.input-hl').each(function(){

              if (!$(this).parents('.ligne_lot_cepage').find('select.selectCepage').val()) {
                return ;
              }
              i ++;
              if (i < nbligneaveccepage) {
                $(this).val(( $(this).parents('.ligne_lot_cepage').find('.input-pc').val() * total / 100).toFixed(2));
                sumhl += parseFloat($(this).val());
                return;
              }
              $(this).val((total-sumhl).toFixed(2));
              $(this).trigger('change')
            });
          }
          checkBlocsLotCepages();

        });
        //Au switch, on remet à Zero les inputs non visibles et on affiche la bonne colonne
        var set_switch = function(){
          var is_pc = $('.switch_hl_to_pc').is(':checked');

          if(is_pc){
            $(this).parents('.modal_lot_cepages').find('.input-group-pc').show();
            $(this).parents('.modal_lot_cepages').find('.input-group-hl').hide();
            $(this).parents('.modal_lot_cepages').find('.input-hl').each(function(){ $(this).val(''); });
          } else {
            $(this).parents('.modal_lot_cepages').find('.input-group-hl').show();
            $(this).parents('.modal_lot_cepages').find('.input-group-pc').hide();
            $(this).parents('.modal_lot_cepages').find('.input-pc').each(function(){ $(this).val(''); });
          }

        };

        $('.switch_hl_to_pc').on('change', set_switch);
        $('.input-total').on('change', set_switch);

        //on recupere le vol-total dans la popup
        $('input.input-float').on('change',function(){
          $('.input-total').val($(this).val());
        });

        $('#form_'+type+'_lots input.input-float').on('click', function(e) {
            if (! e.target.readOnly) {
                return false
            }

            id = parseInt(e.target.id.replace(/[^0-9]/g, ''))
            $('#'+type+'_lots_lots_'+id+'_cepages').modal('toggle')
        })

        if(window.location.hash == "#dernier") {
            $('#form_'+type+'_lots .bloc-lot:last input:first').focus();
        } else {
            $('#form_'+type+'_lots .bloc-lot:first input:first').focus();
        }

        $('#form_'+type+'_lots .lot-delete').on('click', function() {
            if(!confirm("Étes vous sûr de vouloir supprimer ce lot ?")) {

                return;
            }

            $(this).parents('.bloc-lot').find('input, select').each(function() {
                $(this).val("");
            });
            $(this).parents('.bloc-lot').find('.select2autocomplete').select2('val', "");
            $(this).parents('.bloc-lot').hide();
        })
    }

    $.calculTotal = function() {
      var total = 0.0;
      $("tr.hamzastyle-item:visible").each(function(){
        total+=parseFloat($(this).find(".lot_volume").html());
      });

      $("tr .total_lots").html(total.toFixed(2));
      $(document).scrollTo("#table_igp_title");

    }

    $.btn_bsswitch = function() {
      var switchSelector = '#btn-degustable-all';
      $(switchSelector).bootstrapSwitch();

      $(switchSelector).on('switchChange.bootstrapSwitch', function(event, state) {

        $('.bsswitch:not("'+switchSelector+'")').each(function(index, element) {

          if(state){
            $(element).bootstrapSwitch('state', true)
          }else{
            $(element).bootstrapSwitch('state', false)
          }
        })
      })
    }

    /* =================================================================================== */
    /* FUNCTIONS CALL */
    /* =================================================================================== */
    _doc.ready(function()
    {
        $.initExploitation();
        $.initPrelevement();
        $.initBtnValidation();
        $.initFocusAndErrorToRevendicationField();
        $.initEventErrorRevendicationField();
        $.initCalculAuto();
        $.initRevendicationFadeRow();
        $.initRevendicationEventsFadeInOut();
        $.initControleExterne();

        var doc_type = document.querySelector('form[id^="form_"]')
        if (doc_type !== null) {
          doc_type = doc_type.id.split('_')[1]
          $.initLots(doc_type);
        }

        $.initRecapEventsAccordion();
        $.initValidationDeclaration();
        $.initSocieteChoixEtablissement();
        $.btn_bsswitch();

    });

})(jQuery);
