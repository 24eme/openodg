/* =================================================================================== */
/* JQUERY CONTEXT */
/* =================================================================================== */
(function($)
{
    var _doc = $(document);
    var markers = [];
    var defaultIcon = null;
    /* =================================================================================== */
    /* FUNCTIONS CALL */
    /* =================================================================================== */
    _doc.ready(function()
    {
        $(".sortable").sortable(
            {
                placeholder: '<li class="placeholder list-group-item list-group-item-item col-xs-12"></li>',
                pullPlaceholder: true,
                handle: 'span.glyphicon.glyphicon-resize-vertical',
                afterMove: function ($placeholder, container, $closestItemOrContainer) {
                    $placeholder.html(container.group.item.eq(0).html());
                },
                onDrop: function ($item, container, _super, event) {
                    $.setValuesBySort();
                    _super($item, container);
                }
            }
        );

        $.setValuesBySort = function() {
            var heure = "";
            $("#listes_operateurs .list-group-item").each(function() {
                if($(this).hasClass('list-group-item-container')) {
                    heure = $(this).attr('data-value');
                } else {
                    $(this).find('input.input-heure').val(heure);
                }
            });
        }

        $("#listes_operateurs .list-group-item-item .btn-success").click(function() {
            var ligne = $(this).parents(".list-group-item-item");
            $.addItem(ligne);

            return false;
        });

        $("#listes_operateurs .list-group-item-item select").change(function() {
            $.updateRecapCepages();
         });

        $("#listes_operateurs .list-group-item-item .glyphicon-map-marker").hover(
            function() {
                var ligne = $(this).parents(".list-group-item-item");
                if(ligne.attr('data-point')) {
                    $.toggleMarkerHover(markers[ligne.attr('data-point')], ligne, true, false);
                }
            },
            function() {
                var ligne = $(this).parents(".list-group-item-item");
                if(ligne.attr('data-point')) {
                    $.updateItem(ligne);
                    $.toggleMarkerHover(markers[ligne.attr('data-point')], ligne, true, false);
                }
            }
        );

        $("#listes_operateurs .list-group-item-item.clickable").click(function() {
            var ligne = $(this);
            $.addItem(ligne);

            return false;
        });

        $("#listes_operateurs .list-group-item-item .btn-danger").click(function() {
            var ligne = $(this).parents(".list-group-item-item");
            $.removeItem(ligne);

            return false;
        });

        $(".nav-filter").click(function() {
            $(this).parent().find('a').removeClass('active')
            $(this).addClass('active');

            $("#listes_operateurs .list-group-item-item").removeClass('hidden');

            if($(this).attr('data-filter')) {
                $("#listes_operateurs .list-group-item-item[data-state!="+$(this).attr('data-filter')+"]").addClass('hidden');
                $('#listes_operateurs .list-group-item-item[data-state=""]').removeClass('hidden');
            }
            if($(this).attr('data-state')) {
                $('#listes_operateurs .list-group-item-item[data-state=""] .btn-success').removeClass('hidden'); 
                $('#listes_operateurs .list-group-item-item[data-state=""]').addClass('clickable');
            } else {
                $('#listes_operateurs .list-group-item-item').removeClass('clickable'); 
                $('#listes_operateurs .list-group-item-item .btn-success').addClass('hidden'); 
            }

            $('#listes_operateurs .list-group-item-item').attr('data-color', null);

            if($(this).attr('data-color')) {
                $('#listes_operateurs .list-group-item-item').attr('data-color', $(this).attr('data-color'));
            }

            if($('#carte').length > 0) {
                $("#listes_operateurs .list-group-item-item").each(function() {
                    if($(this).attr('data-point')) {
                        $(markers[$(this).attr('data-point')]._icon).removeClass('hidden');
                    }
                });
                $("#listes_operateurs .list-group-item-item.hidden").each(function() {
                    if($(this).attr('data-point')) {
                        $(markers[$(this).attr('data-point')]._icon).addClass('hidden');
                    }
                });
            }

            return false;
        });

        if($.isTournee()) {
            $.initCarteDegustation();
        }

        if($('#nb_a_prelever').length > 0) {

            var lignes_a_prelever = new Array();
            var nb_a_prelever = $('#nb_a_prelever').val();

            $("#listes_operateurs .list-group-item-item[data-state!=active]").each(function() {
                lignes_a_prelever.push($(this));
            });

            arrayShuffle(lignes_a_prelever);

            nb_a_prelever = nb_a_prelever - $("#listes_operateurs .list-group-item-item[data-state=active]").length;

    	    for(i = 0 ; i < nb_a_prelever ; i++) {
    		  $.addItem(lignes_a_prelever[i]);
    	    }
        }

        $.updateRecapCepages();

    });

    $.initCarteDegustation = function()
    {
        defaultIcon = L.BootstrapMarkers.icon({ color: '#e2e2e2' });

        var map = L.map('carte', {minZoom: 8, icon: defaultIcon}).setView([48.100901, 7.361051], 9);
        L.tileLayer('https://{s}.tiles.mapbox.com/v3/{id}/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
                '<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
                'Imagery © <a href="http://mapbox.com">Mapbox</a>',
            id: 'examples.map-i875mjb7'
        }).addTo(map);

        var points = [];
        $('#listes_operateurs .list-group-item-item').each(function () {
            if($(this).attr('data-point')) {
                points[$(this).attr('data-point')] = L.latLng($(this).attr('data-point').split(','));
            }
        })

        for(key in points) {
            var point = points[key];
            var marker = L.marker(point, {icon: defaultIcon});
            var ligne = $.latlngToLigne(point);
            marker.title = ligne.attr('data-title');
            var color = ligne.find('.glyphicon-map-marker').css('color');
            marker.addTo(map);
            $(marker._icon).find('.marker-inner').css('color', color);

            marker.on('click', function(m) {
		var ligne = $.latlngToLigne(m.latlng);
                $.toggleItem(ligne);
                $('#listes_operateurs').scrollTo(ligne, 200, { offset: -150, queue: false });
            });

            marker.on('mouseover', function(m) {
                var ligne = $.latlngToLigne(m.latlng);
                $.toggleMarkerHover(m.target, ligne, false, true);
                timerHover = setTimeout(function(){
                    $('#listes_operateurs').scrollTo(ligne, 200, { offset: -150, queue: false });
                }, 600);
            })

            marker.on('mouseout', function(m) {
                clearTimeout(timerHover);
                var ligne = $.latlngToLigne(m.latlng);
                $.toggleMarkerHover(m.target, ligne, false, true);
                $.updateItem(ligne);
            });

            markers[key] = marker;
        }
	$.updateNbFilter();
	tournees = [];
	nbattributed = 0;
	$('.agent').each(function() {
	    tournees[tournees.length] = {point: L.latLng($(this).attr('data-point').split(',')), id: $(this).attr('data-state'), lastPoint: L.latLng($(this).attr('data-point').split(','))};
	    nbattributed += $(this).find('.badge').html()*1;
	});
	/*if (nbattributed == 0)
	    $.attributeTournee(markers, tournees);*/
	
        //map.fitBounds(points, {padding: [10, 10]});

    }

    $.isTournee = function() {
	return ($('#carte').length > 0);
    }
    
    $.attributeTournee = function(themarkers, tournees) {
	var mymarkers = $.extend({}, themarkers);
	i = 0;
	$('li.operateur .input-heure').each(function() {$(this).val('')});
	while(Object.keys(mymarkers).length > 0) {
	    min = 100000000;
	    minkey = '';
	    for(m in mymarkers) {
		distance = tournees[i % tournees.length].lastPoint.distanceTo(mymarkers[m].getLatLng());
		if (distance < min) {
		    min = distance;
		    minkey = m;
		}
	    }

	    $.addItemToTournee($.latlngToLigne(mymarkers[minkey].getLatLng()), tournees[i % tournees.length].id);
	    delete mymarkers[minkey];
	    i++;
	}
    }
    $.latlngToLigne = function(ll) {
	return $('#listes_operateurs .list-group-item-item[data-point="' + ll.lat + "," + ll.lng + '"]');
    }
    $.getTourneeDiv = function(tournee) {
	return $('.nav-filter[data-state='+tournee+']');
    }
    $.tourneeToColor = function(tournee) {
	return $.getTourneeDiv(tournee).attr('data-color');
    }
    $.tourneeToHour = function(tournee) {
	return $.getTourneeDiv(tournee).attr('data-hour');
    }
    $.tourneeToPerHour = function(tournee) {
	return $.getTourneeDiv(tournee).attr('data-perhour')*1;
    }
    $.tourneeToNextHour = function(tournee) {
	next = $.tourneeToHour(tournee).split(':')[0]*1+1;
	if (next == 13 || next == 14) {
	    next = 15;
	}
	if (next > 9) {
	    return next+':00';
	}else{
	    return '0'+next+':00';
	}
    }
    
    $.tourneeToNextHourDiv = function(tournee) {
	return $('li.hour[data-value="'+$.tourneeToNextHour(tournee)+'"]');
    }
    
    $.toggleMarkerHover = function(marker, ligne, withMarkerOpacity, withLigneOpacity) {
        for(coordonnees in markers) {
            if(withMarkerOpacity) {
                if($(markers[coordonnees]._icon).css('opacity') == '1') {
                    $(markers[coordonnees]._icon).css('opacity', '0.3');
                } else {
                    $(markers[coordonnees]._icon).css('opacity', '1');
                }
            }
            markers[coordonnees].setZIndexOffset(900);
        }
        if(withLigneOpacity) {
            $("#listes_operateurs .list-group-item-item .glyphicon-map-marker").each(function() {
                if($(this).css('opacity') == '1') {
                    $(this).css('opacity', '0.3');
                } else {
                    $(this).css('opacity', '1');
                }
            });
            ligne.find('.glyphicon-map-marker').css('opacity', '1');
        }
        if(withMarkerOpacity) {
            $(marker._icon).css('opacity', '1');
        }
        marker.setZIndexOffset(1000);
    }

    $.addItem = function(ligne) {
	tournee = $('.nav-filter.active').attr('data-state');
	$.addItemToTournee(ligne, tournee);
    }
    $.updateHourTournee = function(tournee) {
	if ($('li.operateur[data-state='+tournee+'] .input-heure[value="'+$.tourneeToHour(tournee)+'"]').length >= $.tourneeToPerHour(tournee)) {
	    $.getTourneeDiv(tournee).attr('data-hour', $.tourneeToNextHour(tournee));
	}
    }
    
    $.addItemToTournee = function(ligne, tournee) {
        ligne.attr('data-state', tournee);
        ligne.find('input.input-tournee').val(tournee);
	if ($.isTournee()) {
	    hourDiv = $.tourneeToNextHourDiv(tournee);
	    ligne.detach().insertBefore(hourDiv);
	    ligne.attr('data-color', $.tourneeToColor(tournee));
            ligne.find('input.input-heure').val($.tourneeToHour(tournee));
	    $.updateHourTournee(tournee);
	}
        $.updateItem(ligne);
    }

    $.removeItem = function(ligne) {
        ligne.attr('data-state', '');
	if ($.isTournee()) {
            ligne.find('input.input-tournee').val("");
            ligne.find('input.input-heure').val("");
	    ligne.detach().insertAfter($('#listes_operateurs li:last-child'));
	}
        $.updateItem(ligne);
    }

    $.toggleItem = function(ligne) {
        if(ligne.attr('data-state')) {
            $.removeItem(ligne);
        } else {
            $.addItem(ligne);
        }
    }

    $.updateItem = function(ligne)
    {
        if(ligne.attr('data-state')) {
            ligne.find('button.btn-danger, select').removeClass('hidden');
            ligne.find('button.btn-success').addClass('hidden');
            /*if(ligne.hasClass('clickable')) {
                ligne.addClass('list-group-item-success');
            }*/
            ligne.addClass('list-group-item-success');
            ligne.removeClass('clickable');
            ligne.find('input, select').removeAttr('disabled');
            if(ligne.find('select[data-auto=true]').length > 0) {
                if(ligne.find('select option[selected=selected]').length == 0) {
                    $.tireAuSortCepage(ligne.find('select'));
                }
            }
            if(ligne.attr('data-point')) {
                if(ligne.attr('data-color')) {
                    ligne.find('.glyphicon-map-marker').css('color', ligne.attr('data-color'));
                    $(markers[ligne.attr('data-point')]._icon).find('.marker-inner').css('color', ligne.attr('data-color'));
                }
            }
        } else {
            ligne.find('button.btn-danger, select').addClass('hidden');
            if(!$.isTournee()) {
                ligne.find('input, select').attr('disabled', 'disabled');
            }
            ligne.removeClass('list-group-item-success');
            if($('.nav-filter.active').attr('data-state')) {
                ligne.addClass('clickable');
                ligne.find('button.btn-success').removeClass('hidden');
            } else {
                ligne.removeClass('clickable');
                ligne.find('button.btn-success').addClass('hidden');
            }
            ligne.find('select option[selected=selected]').removeAttr('selected');

            if(ligne.attr('data-point')) {
                $(markers[ligne.attr('data-point')]._icon).find('.marker-inner').css('color', '#e2e2e2');
                ligne.find('.glyphicon-map-marker').css('color', '#e2e2e2');
            }
            
        }

        $.updateNbFilter();
        $.updateRecapCepages();
    }

    $.updateNbFilter = function()
    {
        $(".nav-filter").each(function() {
            if(!$(this).attr('data-filter')) {
                return;
            }
            $(this).find('.badge').html($("#listes_operateurs .list-group-item-item[data-state="+$(this).attr('data-filter')+"]").length);
        });
    }

    $.tireAuSortCepage = function(select)
    {
        var nb_options = select.find('option').length;
        select.find('option').eq(Math.floor((Math.random() * nb_options))).attr('selected', 'selected');
    }

    $.updateRecapCepages = function()
    {
        $('#recap_cepages span.badge').text("0");
        $("#listes_operateurs .list-group-item-item select:visible option:selected").each(function(index, value) {
            var item = $('#recap_cepages button[data-cepage="'+$(value).val()+'"] .badge');
            item.html(parseInt(item.html()) + 1);
        });

    }

})(jQuery);
