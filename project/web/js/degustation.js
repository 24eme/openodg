
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
      var libelleProduit = $(this).parents('td').attr("data-libelle-produit");
      if(!$('tr[data-hash="'+hash+'"] .nblots').length){
        var newContent = '<tr class="vertical-center cursor-pointer" data-hash="'+hash+'"><td>'+libelleProduit+'</td><td class="nblots">1</td></tr>';
        $('tbody#synthese').append(newContent);
      }else{
      var val = $('tr[data-hash="'+hash+'"] .nblots').html();
      var regex = /[0-9]+$/g;

      if(val.match(regex)){
        var textAdded = " (-1)";
        if(state){
          textAdded = " (+1)";
        }
        $('tr[data-hash="'+hash+'"] .nblots').html(val+textAdded);
      }else{
        var regex = /[0-9]+ (\([0-9\-\+]+)\)$/g;
        if(val.match(regex)){
          var nb = parseInt(val.replace(/^.+\(/,'').replace(/\)/,''));
          var newNb = 0;
          if(state){
            newNb = nb+1;
          }else{
            newNb = nb-1;
          }
          var textNb = (newNb >= 0)? "+"+newNb : newNb;
          textAdded = " ("+textNb+")";
          $('tr[data-hash="'+hash+'"] .nblots').html(val.replace(/ \(.+$/,'')+textAdded);
        }
      }

      }
    });
  });
