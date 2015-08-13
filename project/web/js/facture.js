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
                sumElement($(elements[keyElement]));
            }
        });

        $('.data-clean-line').click(function() {
            if(!confirm("Étes-vous sur de vouloir supprimer cette ligne ?")) {
                return;
            }
            cleanLine($($(this).attr('data-clean-line')));
        });

        $('.line').hover(function() {
            $(this).find('button').removeClass("hidden");
        }, function() {
            $(this).find('button').addClass("hidden");
        });
    });

    var cleanLine = function(line) {
        line.find('input').each(function() {
            $(this).val(null);
            $(this).change();
        });
        stateLine(line);
    }

    var stateLine = function(line) {
        var empty = true;
        line.find('input').each(function() {
            if($(this).val()) {
                empty = false;
            }
        });

        if(empty) {
            line.css('opacity', '0.6');

            return;
        }

        line.css('opacity', '1');
    }

    var sumElement = function(element) {
        var sum = 0;
        try {
            var expression = element.attr('data-sum').replace(/(#[0-9a-zA-Z_-]+)/g, 'parseFloat(($("$1").val()) ? $("$1").val() : 0)');
            sum = eval(element.attr('data-sum').replace(/(#[0-9a-zA-Z_-]+)/g, 'parseFloat(($("$1").val()) ? $("$1").val() : 0)'));
        } catch (e) {

        }

        element.val(Math.round(sum * 100) / 100);

        element.change();
    }
})(jQuery);