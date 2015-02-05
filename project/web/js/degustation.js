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
                }
            }
        );

        $("#listes_operateurs .list-group-item-item .btn-success").click(function() {
            var ligne = $(this).parents(".list-group-item-item");
            $.addItem(ligne);

            return false;
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

        if($('#carte').length > 0) {
            $.initCarteDegustation();
        }

    	for(i = 0 ; i < $('#nb_a_prelever').val() ; i++) {
    		$.addItem($("#listes_operateurs .list-group-item-item").eq(i));
    	}

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
                points[$(this).attr('data-point')] = JSON.parse("["+$(this).attr('data-point')+"]");
            }
        })

        for(key in points) {
            var point = points[key];
            var ligne = $('#listes_operateurs .list-group-item-item[data-point="' + point[0] + "," + point[1] + '"]');
            var marker = L.marker(point, {title: ligne.attr('data-title'), icon: defaultIcon});
            marker.addTo(map);

            marker.on('click', function(m) {
                var ligne = $('#listes_operateurs .list-group-item-item[data-point="' + m.latlng.lat + "," + m.latlng.lng + '"]');
                $.toggleItem(ligne);
                $('#listes_operateurs').scrollTo(ligne, 200, { offset: -150, queue: false });
            });

            marker.on('mouseover', function(m) {
                
                var ligne = $('#listes_operateurs .list-group-item-item[data-point="' + m.latlng.lat + "," + m.latlng.lng + '"]');
                $.toggleMarkerHover(m.target, ligne, false, true);
                timerHover = setTimeout(function(){
                    $('#listes_operateurs').scrollTo(ligne, 200, { offset: -150, queue: false });
                }, 600);
            })

            marker.on('mouseout', function(m) {
                clearTimeout(timerHover);
                var ligne = $('#listes_operateurs .list-group-item-item[data-point="' + m.latlng.lat + "," + m.latlng.lng + '"]');
                $.toggleMarkerHover(m.target, ligne, false, true);
                $.updateItem(ligne);
            });

            markers[key] = marker;
        }

        //map.fitBounds(points, {padding: [10, 10]});

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
        ligne.attr('data-state', $('.nav-filter.active').attr('data-state'));
        $.updateItem(ligne);
    }

    $.removeItem = function(ligne) {
        ligne.attr('data-state', '');
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
                ligne.find('.glyphicon-map-marker').css('color', '#c2c2c2');
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
        var nb_options = (select.find('option').length - 1);
        select.find('option').eq(Math.floor((Math.random() * nb_options) + 1)).attr('selected', 'selected');
    }

    $.updateRecapCepages = function()
    {
        $('#recap_cepages span.badge').text("0");

        $("#listes_operateurs .list-group-item-item select option:selected").each(function(index, value) {
            var item = $('#recap_cepages button[data-cepage="'+$(value).html()+'"] .badge');
            item.html(parseInt(item.html()) + 1);
        });

    }

})(jQuery);
