/* =================================================================================== */
/* JQUERY CONTEXT */
/* =================================================================================== */
(function($)
{
    var _doc = $(document);

    $.initExploitation = function()
    {
        $('#btn_exploitation_modifier').click(function(e) {
            $('#row_form_exploitation').removeClass("hidden");
            $('#row_info_exploitation').addClass("hidden");
        });
    }

    var ajax_post_url = null;

    $.fn.ajaxPostForm = function() {
        var form = $(this);
        var form_id = $(this).attr('id');
        $('.ajax').each(function() {
            $(this).click(function(e) {
                ajax_post_url = $(this).attr('href');
                formPost(form);
                e.preventDefault()
            });
        });

    };
    
    var formPost = function(form)
    {
        $.ajax({
            url: $(form).attr('action'),
            type: "POST",
            data: $(form).serializeArray(),
            dataType: "json",
            async: true,
            success: function(msg) {
                if (ajax_post_url) {
                    document.location.href = ajax_post_url;
                }
                var drev_rev = $('.drev_rev')
                if(msg.drev_rev && drev_rev.length > 0){
                    drev_rev.each(function(){
                        $(this).val(msg.drev_rev)
                    });
                }
            },
            error: function(textStatus) {
                form.submit();
            }
        });
    };

    /* =================================================================================== */
    /* FUNCTIONS CALL */
    /* =================================================================================== */
    _doc.ready(function()
    {
        $.initExploitation();
        var ajaxForm = $('form.ajaxForm');
        if (ajaxForm.length > 0) {
            ajaxForm.ajaxPostForm();
        }
    });

})(jQuery);