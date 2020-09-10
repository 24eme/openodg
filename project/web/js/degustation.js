
  $(document).ready(function(){
    $('.degustation li.ajax a').on('click',function(){
      var form = $('form.degustation');
      form.post();
    });

    $('.degustation .bsswitch.ajax').on('switchChange.bootstrapSwitch', function (event, state) {
      var form = $(this).parents('form');
      $.formPost(form);
    });
  });
