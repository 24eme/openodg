
  $(document).ready(function(){
    $('.degustation li.ajax a').on('click',function(){
      var form = $('form.degustation');
      form.post();
    });

    $('.bsswitch').on('switchChange.bootstrapSwitch', function (event, state) {
      var state = $(this).bootstrapSwitch('state');
      var form = $(this).parents('form');
      if($(this).hasClass('ajax')){
        $.formPost(form);
      }
    });

    $('.degustation .bsswitch').on('switchChange.bootstrapSwitch', function (event, state) {
      var state = $(this).bootstrapSwitch('state');
      var form = $(this).parents('form');

      var hash = $(this).parents('td').attr("data-hash");
      if (hash === undefined) {
         return true;
      }

      if(form.hasClass('table')){
        updateSyntheseTable($(this),state,hash);
      }

      if(form.hasClass('prelevements')){
        updateSynthesePrelevementLots();
      }

      if(form.hasClass('degustateurs')){
        updateSyntheseDegustateurs($(this),state,hash);
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

    var updateSynthesePrelevementLots = function(){

      $('.degustation.prelevements').each(function(){
        var nbLotsSelectionnes = 0;
        var adherents = {};

        $(this).find('.bsswitch').each(function () {
           var state = $(this).bootstrapSwitch('state');
           if(state){
             nbLotsSelectionnes++;
             adherents[$(this).parents('td').attr("data-hash")]=1;
           }

      });
      $('tr strong.nbLotsSelectionnes').html(""+nbLotsSelectionnes);
      $('tr strong.nbAdherents').html(""+Object.size(adherents));
       });
    }

    updateSynthesePrelevementLots();

    var updateSyntheseDegustateurs = function(elt,state,hash){
      var collegeKey =elt.parents('td').attr('data-hash');
      var valDegustateurSelectionne = $('tr td strong.'+collegeKey).html();
      var regex = /[0-9]+$/g;

      if(valDegustateurSelectionne.match(regex)){
        var nbLotToAdd = -1;
        if(state){
          nbLotToAdd = 1;
        }
        var old = parseInt(valDegustateurSelectionne);
        var diff = parseInt(nbLotToAdd);
        var newvalDegustateurSelectionne = old+diff;
        $('tr td strong.'+collegeKey).html(""+newvalDegustateurSelectionne);
      }

    }

    $('#time').on('click',function(){
      $(this).clockpicker({placement: 'bottom',align: 'left',autoclose: true});
    });

  });
