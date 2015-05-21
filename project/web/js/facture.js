/* =================================================================================== */
/* JQUERY CONTEXT */
/* =================================================================================== */
(function($)
{
    var _doc = $(document);

    _doc.ready(function()
    {
        $('.data-sum-element').change(function() {
            try {
                var elements = JSON.parse($(this).attr('data-sum-element'));
            } catch (e) {

            }

            if(!elements) {
                sumElement($($(this).attr('data-sum-element')));
                return;
            }

            for(keyElement in elements) {
                console.log($(elements[keyElement]));
                sumElement($(elements[keyElement]));
            }
        });

       

    });

    var sumElement = function(element) {
        var sum = 0;
        try {
            var expression = element.attr('data-sum').replace(/(#[0-9a-zA-Z_-]+)/g, 'parseFloat(($("$1").val()) ? $("$1").val() : 0)');
            sum = eval(element.attr('data-sum').replace(/(#[0-9a-zA-Z_-]+)/g, 'parseFloat(($("$1").val()) ? $("$1").val() : 0)'));
        } catch (e) {

        }

        element.val(sum);

        element.change();
    }
})(jQuery);