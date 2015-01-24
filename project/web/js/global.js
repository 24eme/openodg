/* =================================================================================== 
 * File : global.js
 * Description : JS generic functions
 * Authors : Hamza Iqbal - hiqbal[at]actualys.com
 *			 Mikaël Guillin - mguillin[at]actualys.com
 * Copyright : Actualys
 /* =================================================================================== */


/* =================================================================================== */
/* JQUERY CONTEXT */
/* =================================================================================== */
(function ($)
{
    /* =================================================================================== */
    /* GLOBAL VARS */
    /* =================================================================================== */

    // Anchor
    var _anchor = window.location.hash;
    // Main elements
    var _doc = $(document);
    var _win = $(window);
    var _html = $('html');
    var _body = $('body');
    var _header = $('#header');
    var _navigation = $('#navigation');
    var _content = $('#content');
    var _footer = $('#footer');
    // Carousels
    var _carousels = $('.carousel-content');
    var _classNames =
            {
                active: 'active',
                opened: 'opened',
                disabled: 'disabled'
            };
    // Fancybox - Defaut config
    var _fbConfig =
            {
                padding: 0,
                autoSize: true,
                fitToView: true,
                helpers:
                        {
                            title:
                                    {
                                        type: 'outside',
                                        position: 'top'
                                    }
                        }
            };
    /**
     * Equal heights
     ******************************************/
    $.fn.equalHeights = function ()
    {
        var maxHeight = 0,
                $this = $(this);
        $this.each(function () {
            var height = $(this).innerHeight();
            if (height > maxHeight) {
                maxHeight = height;
            }
        });
        return $this.css('height', maxHeight);
    };
    /**
     * Applique la même hauteur sur tous les élément
     * qui ont la classe .equal-height sur chaque ligne
     * $.initEqualHeight();
     ******************************************/
    $.initEqualHeight = function ()
    {
        if ($('.equal-height').length > 0)
        {
            $('.row').each(function ()
            {
                $(this).find('.equal-height').equalHeights();
            });
        }
    };
    $.initDatePickers = function ()
    {
        var datePickers = $('.date-picker');
        datePickers.each(function ()
        {
            var currentDp = $(this);
            hasValue = currentDp.find('input').val();
            currentDp.datetimepicker
                    ({
                        language: 'fr',
                        pickTime: false,
                        useCurrent: false,
                        calendarWeeks: true
                    });
            if (!hasValue) {
                currentDp.find('input').val('');
            }

            currentDp.on('focus', 'input', function ()
            {
                currentDp.data('DateTimePicker').show();
            });
        });
        var datePickers = $('.date-picker-all-days');
        datePickers.each(function ()
        {
            var currentDp = $(this);
            currentDp.datetimepicker
                    ({
                        language: 'fr',
                        pickTime: false
                    });
            currentDp.on('focus', 'input', function ()
            {
                currentDp.data('DateTimePicker').show();
            });
        });
    };
    $.initSelect2Autocomplete = function ()
    {
        $('.select2autocomplete').select2({allowClear: true, placeholder: true, openOnEnter: true});
    }

    $.initSelect2AutocompleteRemote = function ()
    {
        $('.select2autocompleteremote').select2({
                allowClear: true,
                placeholder: true,
                minimumInputLength: 3,
                ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
                    url: $('.select2autocompleteremote').data('url'),
                    dataType: 'json',
                    quietMillis: 250,
                    data: function (term, page) {
                        return {
                            q: term,
                        };
                    },
                    results: function (data, page) {
                        return { results: data };
                    },
                    cache: true
                },
                formatResult: function(item) {
                    if(item.text_html) {

                        return item.text_html;
                    }

                    return item.text;
                }
        });
    }

    $.initSelect2AutocompletePermissif = function ()
    {

        $('.select2autocompletepermissif').select2({
            tags:[],
            tokenSeparators: [','],
            createSearchChoice: function (term) {
                return {
                    id: $.trim(term),
                    text: $.trim(term) + ' (nouveau tag)'
                };
            },
            ajax: {
                url: $('.select2autocompletepermissif').data('url'),
                dataType: 'json',
                data: function (term, page) {
                    return {
                        q: term
                    };
                },
                results: function (data, page) {
                    return {
                        results: data
                    };
                }
            },
            initSelection: function (element, callback) {
                var data = [];
                function splitVal(string, separator) {
                    var val, i, l;
                    if (string === null || string.length < 1)
                        return [];
                    val = string.split(separator);
                    for (i = 0, l = val.length; i < l; i = i + 1)
                        val[i] = $.trim(val[i]);
                    return val;
                }

                $(splitVal(element.val(), ",")).each(function () {
                    data.push({
                        id: this,
                        text: this
                    });
                });



                callback($.map(element.val().split(','), function (id) {
                    return {id: id, text: id};
                }));
            }
        });
    }

    $.initCheckboxRelations = function ()
    {
        $('.checkbox-relation').click(function () {
            $($(this).attr('data-relation')).toggleClass("hidden");
        })

    }

    $.initCollectionAddTemplate = function (element, regexp_replace, callback)
    {

        $(element).click(function ()
        {
            var bloc_html = $($(this).attr('data-template')).html().replace(regexp_replace, UUID.generate());

            try {
                var params = jQuery.parseJSON($(this).attr('data-template-params'));
            } catch (err) {

            }

            for (key in params) {
                bloc_html = bloc_html.replace(new RegExp(key, "g"), params[key]);
            }

            var bloc = $(bloc_html);

            $($(this).attr('data-container')).append(bloc);

            if (callback) {
                callback(bloc);
            }
            return false;
        });
    }

    $.initCarte = function()
    {
        var map = L.map('carte').setView([51.505, -0.09], 13);
        L.tileLayer('https://{s}.tiles.mapbox.com/v3/{id}/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
                '<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
                'Imagery © <a href="http://mapbox.com">Mapbox</a>',
            id: 'examples.map-i875mjb7'
        }).addTo(map);
    }

    /**
     * Contrôle la bonne saisie de nombres dans
     * un champ
     * $(s).saisieNum(float, callbackKeypress);
     ******************************************/
    $.fn.saisieNum = function (float, callbackKeypress, callbackBlur)
    {
        var champ = $(this);
        // A chaque touche pressée
        champ.keypress(function (e)
        {
            var val = $(this).val();
            var touche = e.which;
            var ponctuationPresente = (val.indexOf('.') != -1 || val.indexOf(',') != -1);
            var chiffre = (touche >= 48 && touche <= 57); // Si chiffre

            // touche "entrer"
            if (touche == 13)
                return e;
            // touche "entrer"
            if (touche == 0)
                return e;
            // Champ nombre décimal
            if (float)
            {
                // !backspace && !null && !point && !virgule && !chiffre
                if (touche != 8 && touche != 0 && touche != 46 && touche != 44 && !chiffre)
                    return false;
                // point déjà présent
                if (touche == 46 && ponctuationPresente)
                    e.preventDefault();
                // virgule déjà présente
                if (touche == 44 && ponctuationPresente)
                    e.preventDefault();
                // 2 décimales
                if (val.match(/[\.\,][0-9][0-9]/) && chiffre && e.currentTarget && e.currentTarget.selectionStart > val.length - 3)
                    e.preventDefault();
            }
            // Champ nombre entier
            else
            {
                if (touche != 8 && touche != 0 && !chiffre)
                    e.preventDefault();
            }

            if (callbackKeypress)
                callbackKeypress();
            return e;
        });
        // A chaque touche pressée
        champ.keyup(function (e)
        {
            var touche = e.which;
            // touche "retour"
            if (touche == 8)
            {
                if (callbackKeypress)
                    callbackKeypress();
                return e;
            }
        });
        // A chaque fois que l'on quitte le champ
        champ.blur(function ()
        {
            console.log('blur');
            $(this).nettoyageChamps();
            if (callbackBlur)
                callbackBlur();
        });
    };
    /**
     * Nettoie les champs après la saisie
     * $(champ).nettoyageChamps();
     ******************************************/
    $.fn.nettoyageChamps = function ()
    {
        var champ = $(this);
        var val = champ.val();
        var float = champ.hasClass('num_float');
        console.log(val);
        // Si quelque chose a été saisi
        if (val)
        {
            // Remplacement de toutes les virgules par des points
            if (val.indexOf(',') != -1)
                val = val.replace(',', '.');
            // Si un point a été saisi sans chiffre
            if (val.indexOf('.') != -1 && val.length == 1)
                val = ''; //val = '0';

            // Un nombre commençant par 0 peut être interprété comme étant en octal
            if (val.indexOf('0') == 0 && val.length > 1)
                val = val.substring(1);
            // Comparaison nombre entier / flottant
            if (float || parseInt(val) != parseFloat(val))
                val = parseFloat(val).toFixed(2);
            else
                val = parseInt(val);
        }
        // Si rien n'a été saisi
        //else val = 0;
        else
            val = '';
        // Si ce n'est pas un nombre (ex : copier/coller d'un texte)
        if (isNaN(val))
            val = ''; //val = 0;

        /*if (val == 0) {
         champ.addClass('num_light');
         } else {
         champ.removeClass('num_light');
         }*/
        champ.val(val);
    };
    /* =================================================================================== */
    /* FUNCTIONS CALL */
    /* =================================================================================== */
    _doc.ready(function ()
    {
        $.initDatePickers();
        $.initSelect2Autocomplete();
        $.initSelect2AutocompleteRemote();
        $.initSelect2AutocompletePermissif();
        $.initCheckboxRelations();
        //$.initCarte();
        $('input.num_float').saisieNum(true);
        $('input.num_int').saisieNum(false);
        $('a[data-toggle=tooltip], button[data-toggle=tooltip]').tooltip({'container': 'body'});
        $('input[data-toggle=tooltip]').tooltip({'trigger': 'focus', 'container': 'body'});
        $.initEqualHeight();
    });
})(jQuery);