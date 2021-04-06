
  $(document).ready(function(){

    $('#mailPreviewModal').modal('show');
    $('.link-mail-auto').click(function(){
      var retour = $(this).attr("data-retour");
      if(retour){
         window.location = retour;
      }
    });

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

  });
