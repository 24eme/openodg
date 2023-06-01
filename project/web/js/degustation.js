
  $(document).ready(function(){

    $('#mailPreviewModal').modal('show');

    $('#btn-preleve-all').on('click', function (event) {
      $('.bsswitch').each(function(index, element) {
        $(element).bootstrapSwitch('state', true)
      })
    })

    $('#popupLeurreForm').each(function(){
      $('#vin_sans_cepage').click(function(){
        if($(this).is(':checked')){
          $('#cepages_choice').hide();
        }else{
          $('#cepages_choice').show();
        }
      });
    });

    $('.degustation li.ajax a').on('click',function(){
      var form = $('form.degustation');
      form.post();
    });

    $('.bsswitch').on('switchChange.bootstrapSwitch', function (event, state) {
      var state = $(this).bootstrapSwitch('state');
      var form = $(this).parents('form');
      if($(this).hasClass('ajax')){
        if(form.hasClass('degustateurs-confirmation')){
          $(this).parents('tr').removeClass("text-muted").removeClass("disabled").removeAttr("disabled").css("text-decoration",'');
        }
        $.formPost(form);
      }
    });

    $('.degustation .bsswitch').on('switchChange.bootstrapSwitch', function (event, state) {
      var state = $(this).bootstrapSwitch('state');
      var form = $(this).parents('form');

      if(form.hasClass('prelevements')){
        updateSynthesePrelevementLots();
      }

      if(form.hasClass('degustateurs')){
        updateSyntheseDegustateurs();
      }

      var hash = $(this).parents('td').attr("data-hash");
      if (hash === undefined) {
         return true;
      }

      if(form.hasClass('table')){
        updateSyntheseTable($(this),state,hash);
      }


    });

    var updateSyntheseTable = function(elt,state,hash){
      if($('tr[data-hash="'+hash+'"] .nblots').length){
      var val = $('tr[data-hash="'+hash+'"] .nblots').html();
      var regex = /[0-9]+$/g;

      if(val.match(regex)){
        var nbToAdd = -1;
        if(state){
          nbToAdd = 1;
        }
        var old = parseInt(val);
        var diff = parseInt(nbToAdd);
        var newVal = old+diff;
        $('tr[data-hash="'+hash+'"] .nblots').html(""+newVal);

        var valTotal = $('.nblots span[data-total="1"]').html();
        var oldTotal = parseInt(valTotal);
        var newTotal = oldTotal+diff;
        $('.nblots span[data-total="1"]').html(""+newTotal);
      }

      }
    }

    var updateSynthesePrelevementLots = function(object){
      var listAdherents = {};

      $("[data-adherent]").each(function(){
        listAdherents[$(this).attr("data-adherent")] = 0;
      });

      $('.degustation.prelevements').each(function(){
        var nbLotsSelectionnes = 0;
        var nbAdherentsLots = 0;

        $(this).find('.bsswitch').each(function () {
           var state = $(this).bootstrapSwitch('state');
           if(state){
              listAdherents[$(this).attr("data-preleve-adherent")]++;
              nbLotsSelectionnes++
           }
      });
      nbAdherentsLots = Object.keys(listAdherents).length;
      for(let i in listAdherents){
        if(listAdherents[i] == 0){
          nbAdherentsLots--;
        }
      }

      $('tr strong#nbLotsSelectionnes').html(""+nbLotsSelectionnes);
      $('tr strong#nbAdherentsAPrelever').html(""+nbAdherentsLots);
       });
    }
    updateSynthesePrelevementLots();

    var updateSyntheseDegustateurs = function(){
      $('.degustation.degustateurs').each(function(){

        var college = 0;

        $(this).find('.bsswitch').each(function () {
           var state = $(this).bootstrapSwitch('state');
           if(state){
             college++;
           }

      });
        $(".collegeCounter li.active span.badge").html(""+college);

       });
    }

    updateSyntheseDegustateurs();
    if(document.getElementById('degustation_creation_time'))
      document.getElementById('degustation_creation_time').style.paddingTop = '0';

    // Anonymisation manuelle
    document.getElementById("table_anonymisation_manuelle")?.addEventListener('click', function (e) {
      const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

      if (button = e.target.closest('button.add-to-table')) {
        const numero_table = button.dataset.table;
        const tr = button.closest('tr')
        const td_table = tr.querySelector('.lot-table')
        const td_anonymat = tr.querySelector('.lot-anonymat')
        const inputdiv = td_anonymat.querySelector('.form-group')
        const input = inputdiv.querySelector('input')

        td_table.textContent = alphabet[numero_table - 1]
        document.querySelector("#liste-tables > a[data-table='"+numero_table+"'] > span.badge").textContent++

        inputdiv.style.display = 'block'
        input.value = document.querySelector("#liste-tables > a[data-table='"+numero_table+"'] > span.badge").textContent
        input.focus()
        input.select()
        button.remove()
      }

      if (del = e.target.closest('.lot-anonymat button.close')) {
        const tr = del.closest('tr')
        const td_table = tr.querySelector('.lot-table')
        const table = td_table.textContent
        const numero_table = alphabet.indexOf(table) + 1
        const td_anonymat = tr.querySelector('.lot-anonymat')
        const inputdiv = td_anonymat.querySelector('.form-group')
        const input = td_anonymat.querySelector('input')

        td_table.textContent = ''
        input.value = ''
        inputdiv.style.display = 'none'
        document.querySelector("#liste-tables > a[data-table='"+numero_table+"'] > span.badge").textContent--

        const b = document.createElement('div')
        b.innerHTML = '<button class="add-to-table" data-table="'+numero_table+'">Ajouter à la table '+alphabet[numero_table - 1]+'</button>'.trim()
        td_anonymat.append(b)
      }
    });
  });
