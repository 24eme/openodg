(function($)
{
    var _doc = $(document);

    $.initLots = function() {
        if ($('#form_drev_lots').length == 0)
        {
            return;
        }

        $('div.checkboxlots input[type="checkbox"]').click(function(e){
          e.preventDefault();
        });

        var checkBlocsLot = function() {
            $('#form_drev_lots .bloc-lot').each(function() {
                var saisi = false;
                $(this).find('input, select').each(function() {
                    if(($(this).val() && $(this).attr('data-default-value') != $(this).val()) || $(this).is(":focus")) {
                        saisi = true;
                    }
                });
                if(!saisi) {
                    $(this).addClass('transparence-sm');
                } else {
                    $(this).removeClass('transparence-sm');
                }
            });
        }

        var checkBlocsLotCepages = function() {
            $('#form_drev_lots .ligne_lot_cepage').each(function() {
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

            $('#form_drev_lots .modal_lot_cepages').each(function() {

                var libelle = "";
                var volume = 0.0;
                var total = 0.0;
                $(this).find('.ligne_lot_cepage').each(function() {
                    total += ($(this).find('.input-float').val())? parseFloat($(this).find('.input-float').val()) : 0;
                });
                $(this).find('.ligne_lot_cepage').each(function() {
                    var ligne = $(this);
                    var cepage = $(this).find('.select2 option:selected').text();
                    var volume = parseFloat($(this).find('.input-float').val());
                    if(cepage && volume > 0) {
                        if(libelle) {
                            libelle = libelle + ", ";
                        }else{
                            libelle = "Mention : ";
                        }
                        var p = (total)? Math.round((volume/total) * 100) : 0;
                        libelle = libelle + cepage + "&nbsp;("+p+"%)";
                        $(this).removeClass('transparence-sm');
                    } else {
                        $(this).addClass('transparence-sm');
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
        }

        var inputs_hl = document.querySelectorAll('.modal input.input-hl')

        inputs_hl.forEach(function (input, index) {
            input.addEventListener('change', function (event) {
                var total = 0.0

                var modal = event.target.parentElement
                while (! modal.classList.contains('modal')) {
                    modal = modal.parentElement
                }

                var lot = modal.dataset.lot

                inputs = modal.querySelectorAll('input.input-hl')
                inputs.forEach(function (input) {
                    if (! isNaN(parseFloat(input.value))) {
                        total += parseFloat(input.value)
                    }
                })

                var vol_total = document.getElementById('chgt_denom_changement_volume')
                vol_total.value = parseFloat(total)

                $('#chgt_denom_changement_volume').blur()

                vol_total.readOnly = (parseFloat(vol_total.value) > 0) ? true : false
            })
        })

        function precision(f) {
            if (!isFinite(f)) { return 2 }
            var e = 1, p = 0
            while (Math.round(f * e) / e !== f) { e *= 10; p++; }
            if (p > 4) { p = 4 }
            return p
        }

        checkBlocsLotCepages();
        $('#form_drev_lots input').on('keyup', function() { checkBlocsLot(); checkBlocsLotCepages(); });
        $('#form_drev_lots select').on('change', function() { checkBlocsLot(); checkBlocsLotCepages(); });
        $('#form_drev_lots input').on('focus', function() { checkBlocsLot(); checkBlocsLotCepages(); });
        $('#form_drev_lots select').on('focus', function() { checkBlocsLot(); checkBlocsLotCepages(); });
        $('#form_drev_lots input').on('blur', function() { checkBlocsLot(); checkBlocsLotCepages(); });
        $('#form_drev_lots select').on('blur', function() { checkBlocsLot(); checkBlocsLotCepages(); });

          $('#form_drev_lots input.input-float').on('click', function(e) {
            if (! e.target.readOnly) {
                return false
            }

            id = parseInt(e.target.id.replace(/[^0-9]/g, ''))
            $('#conditionnement_lots_lots_'+id+'_cepages').modal('toggle')
        })


    }

    _doc.ready(function()
    {
        $.initLots();

    });

})(jQuery);
