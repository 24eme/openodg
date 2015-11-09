/* =================================================================================== */
/* JQUERY CONTEXT */
/* =================================================================================== */
(function($)
{
    var _doc = $(document);

    $.initExploitation = function()
    {
        $('#btn_exploitation_modifier').click(function(e) {
            $('#btn_exploitation_modifier').addClass("hidden")
            $('#btn_exploitation_annuler').removeClass("hidden")
            $('#row_form_exploitation').removeClass("hidden");
            $('#row_info_exploitation').addClass("hidden");
        });
    }

    $.initPrelevement = function()
    {
        $('.form-chai button').click(function() {
            $('.form-chai button, .form-chai .form-group, .form-chai p').toggleClass('hidden');
        });
    }

    $.initControleExterne = function()
    {
        $('#checkbox_non_conditionneur').on('change', function() {
            if($(this).is(':checked')) {
                $('#bloc-form-control-externe').addClass('opacity-lg');
                $('#bloc-lieu-prelevement').addClass('opacity-lg');
                $('#bloc-form-control-externe input, #bloc-form-control-externe select, #bloc-lieu-prelevement button, #bloc-lieu-prelevement a, #bloc-form-control-externe button, #bloc-form-control-externe a').attr('disabled', 'disabled');
            } else {
                $('#bloc-form-control-externe').removeClass('opacity-lg');
                $('#bloc-lieu-prelevement').removeClass('opacity-lg');
                $('#bloc-form-control-externe input, #bloc-form-control-externe select, #bloc-lieu-prelevement button, #bloc-lieu-prelevement a, #bloc-form-control-externe button, #bloc-form-control-externe a').removeAttr('disabled');
            }
        });
        if($('#checkbox_non_conditionneur').is(':checked')) {
            $('#checkbox_non_conditionneur').change();
        }
    }

    $.initBtnValidation = function()
    {
        $('#btn-validation').click(function() {
            $(this).parents('form').attr('action', $(this).parents('form').attr('action') + '?redirect=validation');
            $(this).parents('form').submit();
            return false;
        });
    }

    $.initFocusAndErrorToRevendicationField = function()
    {
        var field = $('.error_field_to_focused');
        field.focus();
    }

    $.initRevendicationFadeRow = function()
    {

        $('tr.with_superficie').each(function() {
            var children = $(this).find(" td input");
            if (children.length == 2) {
                var fade = true;
                children.each(function() {
                    if ($(this).val() != "") {
                        fade = false;
                    }
                });
                if (fade) {
                    $(this).addClass('with_superficie_fade');
                }
            }

        });
    }

    $.initRevendicationEventsFadeInOut = function() {
        $('tr.with_superficie_fade').each(function() {
            var tr = $(this);
            var children = $(this).find(" td input");
            children.each(function() {
                $(this).focus(function() {
                    tr.removeClass('with_superficie_fade');
                });
                $(this).blur(function() {
                    $.initRevendicationFadeRow();
                });
            });
        });
    }

    $.initEventErrorRevendicationField = function() {
        var field = $('.error_field_to_focused');
        var divError = field.parent().parent();
        field.each(function() {
            $(this).blur(function() {
                if ($(this).val() != "") {
                    divError.removeClass('has-error');
                } else {
                    divError.addClass('has-error');
                }
            });
        });
    }

    $.initRecapEventsAccordion = function() {
        $('#revendication_accordion tr.trAccordion').click(function() {
            var eventId = $(this).attr('data-target');
            var span = $(this).find('small span');

            var ouverts = $('tr td.hiddenRow div.in');
            if (ouverts.length && (eventId == '#' + ouverts.attr('id'))) {
                span.removeClass('glyphicon-chevron-right');
                span.addClass('glyphicon-chevron-down');
                $(eventId).collapse('hide');
            } else {
                $('tr td.hiddenRow div').each(function() {
                    $('#revendication_accordion tr.trAccordion').each(function() {
                        var span_all = $(this).find('small span');
                        span_all.removeClass('glyphicon-chevron-right');
                        span_all.addClass('glyphicon-chevron-down');
                    });
                    $(this).removeClass('in');
                });
                span.removeClass('glyphicon-chevron-down');
                span.addClass('glyphicon-chevron-right');
                $(eventId).collapse('show');

            }
        });
    }

    $.initValidationDeclaration = function() {
    	$('#submit-confirmation-validation').click(function() {
    	    $('#validation-form').submit();
    	});

        $('#btn-validation-document').click(function() {
            $("input:checkbox[name*=validation]").each(function() {
                    $(this).parent().parent().parent().removeClass("has-error");
            });
            $("#engagements .alert-danger").addClass("hidden");
            if($("input:checkbox[name*=validation]").length != $("input:checkbox[name*=validation]:checked").length) {
                $("#engagements .alert-danger").removeClass("hidden");
                $("input:checkbox[name*=validation]:not(:checked)").each(function() {
                    $(this).parent().parent().parent().addClass("has-error");
                });
                $("input:checkbox[name*=validation]:checked").each(function() {
                    $(this).parent().parent().parent().removeClass("has-error");
                });
                return false;
            }
        });
    }

    /* =================================================================================== */
    /* FUNCTIONS CALL */
    /* =================================================================================== */
    _doc.ready(function()
    {
        $.initExploitation();
        $.initPrelevement();
        $.initBtnValidation();
        $.initFocusAndErrorToRevendicationField();
        $.initEventErrorRevendicationField();
        $.initRevendicationFadeRow();
        $.initRevendicationEventsFadeInOut();
        $.initControleExterne();

        $.initRecapEventsAccordion();
        $.initValidationDeclaration();

    });

})(jQuery);