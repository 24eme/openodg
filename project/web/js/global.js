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
		active : 'active',
		opened : 'opened',
		disabled : 'disabled'
	};
	
	
	// Fancybox - Defaut config
	var _fbConfig =
	{
		padding: 0,
		autoSize : true,
		fitToView : true,
		helpers :
		{
			title:
			{
				type: 'outside',
				position: 'top'
			}
		}
	};

	
	
	/* =================================================================================== */
	/* FUNCTIONS CALL */
	/* =================================================================================== */
	_doc.ready( function()
	{
	});
	
})(jQuery);