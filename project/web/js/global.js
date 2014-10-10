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
(function($)
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

    $.initDatePickers = function()
    {
        var datePickers = $('.date-picker');

        datePickers.each(function()
        {
            var currentDp = $(this);

            hasValue = currentDp.find('input').val();

            currentDp.datetimepicker
                    ({
                        language: 'fr',
                        pickTime: false,
                        useCurrent: false,
                        daysOfWeekDisabled: [0, 2, 3, 4, 5, 6]
                    });

            if(!hasValue) {
                currentDp.find('input').val('');
            }

            currentDp.on('focus', 'input', function()
            {
                currentDp.data('DateTimePicker').show();
            });
        });
        
        var datePickers = $('.date-picker-all-days');

        datePickers.each(function()
        {
            var currentDp = $(this);

            currentDp.datetimepicker
                    ({
                        language: 'fr',
                        pickTime: false
                    });

            currentDp.on('focus', 'input', function()
            {
                currentDp.data('DateTimePicker').show();
            });
        });
    };

    $.initSelect2Autocomplete = function()
    {

        $('.select2autocomplete').select2({allowClear: true, placeholder: true});
    }

    $.initCheckboxRelations = function()
    {
        $('.checkbox-relation').click(function() {
            $($(this).attr('data-relation')).toggleClass("hidden");
        })

    }
    
        /**
     * Contrôle la bonne saisie de nombres dans
     * un champ
     * $(s).saisieNum(float, callbackKeypress);
     ******************************************/
    $.fn.saisieNum = function(float, callbackKeypress, callbackBlur)
    {
        var champ = $(this);
        
        // A chaque touche pressée
        champ.keypress(function(e)
        {
            var val = $(this).val();
            var touche = e.which;
            var ponctuationPresente = (val.indexOf('.') != -1 || val.indexOf(',') != -1);
            var chiffre = (touche >= 48 && touche <= 57); // Si chiffre

            // touche "entrer"
            if(touche == 13) return e;

            // touche "entrer"
            if(touche == 0) return e;
                    
            // Champ nombre décimal
            if(float)
            { 
                // !backspace && !null && !point && !virgule && !chiffre
                if(touche != 8 && touche != 0 && touche != 46 && touche != 44 && !chiffre) return false;    
                // point déjà présent
                if(touche == 46 && ponctuationPresente) e.preventDefault(); 
                // virgule déjà présente
                if(touche == 44 && ponctuationPresente) e.preventDefault(); 
                // 2 décimales
                if(val.match(/[\.\,][0-9][0-9]/) && chiffre && e.currentTarget && e.currentTarget.selectionStart > val.length - 3) e.preventDefault();
            }
            // Champ nombre entier
            else
            {
                if(touche != 8 && touche != 0 && !chiffre) e.preventDefault();
            }
            
            if(callbackKeypress) callbackKeypress();
            return e;
        });
        
        // A chaque touche pressée
        champ.keyup(function(e)
        {
            var touche = e.which;
            
            // touche "retour"
            if(touche == 8)
            {
                if(callbackKeypress) callbackKeypress(); 
                return e;
            }
        });
        
        
        // A chaque fois que l'on quitte le champ
        champ.blur(function()
        {
            console.log('blur');
            $(this).nettoyageChamps();
            if(callbackBlur) callbackBlur();
        });
    };
    
    
    /**
     * Nettoie les champs après la saisie
     * $(champ).nettoyageChamps();
     ******************************************/
    $.fn.nettoyageChamps = function()
    {
        var champ = $(this);
        var val = champ.val();
        var float = champ.hasClass('num_float');
        console.log(val);
        // Si quelque chose a été saisi
        if(val)
        {
            // Remplacement de toutes les virgules par des points
            if(val.indexOf(',') != -1) val = val.replace(',', '.');
            
            // Si un point a été saisi sans chiffre
            if(val.indexOf('.') != -1 && val.length == 1) val = ''; //val = '0';
            
            // Un nombre commençant par 0 peut être interprété comme étant en octal
            if(val.indexOf('0') == 0 && val.length > 1) val = val.substring(1);
            
            // Comparaison nombre entier / flottant
            if(float || parseInt(val) != parseFloat(val)) val = parseFloat(val).toFixed(2);     
            else val = parseInt(val);
        }
        // Si rien n'a été saisi
        //else val = 0;
        else val = '';
        
        // Si ce n'est pas un nombre (ex : copier/coller d'un texte)
        if(isNaN(val)) val = ''; //val = 0;

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
    _doc.ready(function()
    {
        $.initDatePickers();
        $.initSelect2Autocomplete();
        $.initCheckboxRelations();
        $('input.num_float').saisieNum(true);
        $('input.num_int').saisieNum(false);
        $('.btn-tooltip').tooltip();
    });

})(jQuery);