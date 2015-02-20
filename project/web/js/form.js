/* =================================================================================== */
/* JQUERY CONTEXT */
/* =================================================================================== */
(function($)
{
    var _doc = $(document);

    var ajax_post_url = null;
    var in_post = false;

    $.initAjaxPost = function() 
    {

        var notificationError = $('#ajax_form_error_notification');
        var notificationProgress = $('#ajax_form_progress_notification');

        $(document).ajaxError(
            function(event, xhr, settings) {
                if (settings.type === "POST") {
                    notificationError.show();
                }
            }
        );

        $(document).ajaxSuccess(
            function(event, xhr, settings) {
                if (settings.type === "POST") {
                    notificationError.hide();
                }
            }
        );

        $(document).ajaxSend(
            function(event, xhr, settings) {
                if (settings.type === "POST") {
                    notificationError.hide();
                    notificationProgress.show();
                }
            }
        );

        $(document).ajaxComplete(
            function(event, xhr, settings) {
                if (settings.type === "POST") {
                    notificationProgress.hide();
                }
            }
        );
    };

    $.initAjaxCouchdbForm = function() 
    {
        $(document).ajaxComplete(
            function(event, xhr, settings) {
                if (settings.type === "POST") {

                    var data = null;

                    try {
                        var data = jQuery.parseJSON(xhr.responseText);
                    } catch (err) {

                    }

                    if (!(data && data.document && data.document.id && data.document.revision)) {
                        
                        return ;
                    }

                    $("input[data-id="+ data.document.id + "]").val(data.document.revision);                    
                    if ($.fn.RevisionajaxSuccessCallBack) {
                    $.fn.RevisionajaxSuccessCallBack();
                    $.fn.RevisionajaxSuccessCallBack = null;
                    }
                }
            }
        );  

    };

    $.fn.ajaxPostForm = function() {
        var form = $(this);
        var form_id = $(this).attr('id');
        $('.ajax').each(function() {
            $(this).click(function(e) {
                if(in_post) {
                    return false;
                }
                ajax_post_url = $(this).attr('href');
                formPost(form);
                e.preventDefault()
            });
        });

    };
    
    var formPost = function(form)
    {
        in_post = true;
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
                in_post = false;
            },
            error: function(textStatus) {
                form.submit();
                in_post = false;
            }
        });
    };

    /* =================================================================================== */
    /* FUNCTIONS CALL */
    /* =================================================================================== */
    _doc.ready(function()
    {
        $.initAjaxPost();
        $.initAjaxCouchdbForm();
        var ajaxForm = $('form.ajaxForm');
        if (ajaxForm.length > 0) {
            ajaxForm.ajaxPostForm();
        }
    });

})(jQuery);