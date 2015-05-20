/* =================================================================================== */
/* JQUERY CONTEXT */
/* =================================================================================== */
(function($)
{
    var _doc = $(document);

    _doc.ready(function()
    {
        $('.data-sum-element').change(function() {
            var sumElement = $($(this).attr('data-sum-element'));

            var sum = 0;
            try {
                sum = eval(sumElement.attr('data-sum').replace(/(#[0-9a-zA-Z_-]+)/g, 'parseFloat(($("$1").val()) ? $("$1").val() : 0)'));
            } catch (e) {

            }

            sumElement.val(sum);

            sumElement.change();
        });

    });
})(jQuery);