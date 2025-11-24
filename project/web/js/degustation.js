
  $(document).ready(function(){

    $('#mailPreviewModal').modal('show');

    var originPrelev = document.querySelector('#btn-preleve-all');
    if (originPrelev) {
      originPrelev.addEventListener("click", function (e) {
        if (originPrelev.dataset.status == "prelever") {
          originPrelev.innerHTML = '<i class="glyphicon glyphicon-remove-sign"></i> Tout retirer';
          originPrelev.dataset.status = 'retirer';
        } else {
          originPrelev.innerHTML = '<i class="glyphicon glyphicon-ok-sign"></i> Tout prélever';
          originPrelev.dataset.status = 'prelever';
        }
        document.querySelectorAll('.switch').forEach(function (el) {
          el.checked = !el.checked;
        });
      });
    }

    var originAttabler = document.querySelector('#btn-attabler-all');
    if (originAttabler) {
      originAttabler.addEventListener("click", function (e) {
        if (originAttabler.dataset.status == "attabler") {
          originAttabler.innerHTML = '<i class="glyphicon glyphicon-ok-sign"></i> Tout enlever de la table ' + originAttabler.dataset.table;
          originAttabler.dataset.status = 'retirer';
        } else {
          originAttabler.innerHTML = '<i class="glyphicon glyphicon-remove-sign"></i> Tous sur la table ' + originAttabler.dataset.table;
          originAttabler.dataset.status = 'attabler';
        }
        document.querySelectorAll('.switch').forEach(function (el) {
          el.checked = !el.checked;
        });
      });
    }


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

      if (del = e.target.closest('.lot-anonymat button.close')) {
        const td = del.closest('td')
        const input = td.querySelector('input')
        input.value = ''
      }
    });

    const tableFiltre = document.querySelector('#table_filtre')
    const lines = document.querySelectorAll('.table_filterable tbody tr.searchable');
    const clear = document.querySelector('.table_filterable tbody tr:not(.searchable)')
    const annulerFiltre = document.getElementById('btn_annuler_filtre')

    tableFiltre?.addEventListener('keyup', function() {
        const terms = this.value.split(' ');
        lines.forEach(function(line, index) {
            const words = line.innerText;

            for(keyTerm in terms) {
                var termRegexp = new RegExp(terms[keyTerm], 'i');
                if(words.search(termRegexp) < 0) {
                    line.classList.add("hidden");
                    return;
                }
            }

            line.classList.remove("hidden");
        });

        if(document.querySelectorAll(".table_filterable tbody tr.searchable.hidden").length == document.querySelectorAll(".table_filterable tbody tr.searchable").length) {
            clear?.classList.remove('hidden');
        } else {
            clear?.classList.add('hidden');
        }

        if(this.value) {
            annulerFiltre.classList.remove('hidden');
        } else {
            annulerFiltre.classList.add('hidden');
        }
    });

    document.getElementById('btn_annuler_filtre_table')?.addEventListener('click', function(e) {
        annulerFiltre.click();
        return false;
    });

    document.getElementById('btn_annuler_filtre')?.addEventListener('click', function(e) {
        document.querySelector('#table_filtre').value = "";
        document.querySelector('#table_filtre').dispatchEvent(new Event("keyup"))
        return false;
    });
  });
