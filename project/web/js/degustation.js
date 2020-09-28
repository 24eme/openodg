
  $(document).ready(function(){
    $('.degustation li.ajax a').on('click',function(){
      var form = $('form.degustation');
      form.post();
    });

    $('.degustation .bsswitch.ajax').on('switchChange.bootstrapSwitch', function (event, state) {
      var state = $(this).bootstrapSwitch('state');
      var form = $(this).parents('form');
      $.formPost(form);
      var hash = $(this).parents('td').attr("data-hash");
      if (hash === undefined) {
         return true;
      }
      var libelleProduit = $(this).parents('td').attr("data-libelle-produit");
      if(!$('tr[data-hash="'+hash+'"] .nblots').length){
        var newContent = '<tr class="vertical-center cursor-pointer" data-hash="'+hash+'"><td>'+libelleProduit+'</td><td class="nblots">1</td></tr>';
        $('tbody#synthese').append(newContent);
      }else{
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
      }

      }
    });
  });
