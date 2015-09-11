(function ( $ ) {
    
    var defaults = {
        selector: {
            list: '.organisation-list',
            listWait: '.organisation-list-wait',
            item: '.organisation-item',
            itemMarker: '.glyphicon-map-marker',
            itemMove: '.glyphicon-resize-vertical',
            itemAdd: '.btn-success',
            itemRemove: '.btn-danger',
            itemInputHour: '.input-hour',
            itemInputTournee: '.input-tournee',
            hour: '.organisation-hour',
            tournee: '.organisation-tournee',
            tourneeActive: '.active'
        },
        colorEmpty: '#e2e2e2'
    };

    var markers = [];
    var defaultIcon = null;

    $.fn.organisationTournees = function() {
        initMap();
        initTournees();
        initItems();
        initSortable();
    };

    function initTournees() {
        $(defaults.selector.tournee).click(function() {
            $(defaults.selector.tournee).removeClass('active');
            $(this).addClass('active');
            updateItems();

            return false;
        });
    }

    function initItems() {
        $(defaults.selector.item + ' ' + defaults.selector.itemAdd).on('click', function() {
            var ligne = $($(this).attr('data-item'));
            console.log(ligne);
            addItem(ligne);
            return false;
        });

        $(defaults.selector.item + ' ' + defaults.selector.itemRemove).on('click', function() {
            var ligne = $($(this).attr('data-item'));
            console.log(ligne);
            removeItem(ligne);
            return false;
        });
    }

    function initMap() {
        defaultIcon = L.BootstrapMarkers.icon({color: '#e2e2e2'});

        var map = L.map('carteOrganisation', {minZoom: 8, icon: defaultIcon}).setView([48.100901, 7.361051], 9);
        
        L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: 'Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
        }).addTo(map);

        var points = [];
        $(defaults.selector.item).each(function() {
            var ligne = $(this);
            var point = getLignePoint(ligne);

            if(!point) {
                return;
            }

            var marker = L.marker(L.latLng(point.split(',')), {icon: defaultIcon});

            marker.title = ligne.attr('data-title');
            var color = ligne.find(defaults.selector.itemMarker).css('color');
            marker.addTo(map);
            $(marker._icon).find('.marker-inner').css('color', color);

            marker.on('click', function(m) {
                var ligne = latlngToLigne(m.latlng);
                toggleItem(ligne);
                $('#listes_operateurs').scrollTo(ligne, 200, {offset: -150, queue: false});
            });

            marker.on('mouseover', function(m) {
                var ligne = latlngToLigne(m.latlng);
                toggleMarkerHover(m.target, ligne, false, true);
                /*timerHover = setTimeout(function() {
                    $(defaults.selector.list).scrollTo(ligne, 200, {offset: -150, queue: false});
                }, 600);*/
            })

            marker.on('mouseout', function(m) {
                //clearTimeout(timerHover);
                var ligne = latlngToLigne(m.latlng);
                toggleMarkerHover(m.target, ligne, false, true);
                updateItem(ligne);
            });

            markers[point] = marker;
        });
    }

    function initSortable() {
        $(".sortable").sortable(
            {
                placeholder: '<li class="placeholder list-group-item col-xs-12"></li>',
                pullPlaceholder: true,
                handle: defaults.selector.itemMove,
                afterMove: function($placeholder, container, $closestItemOrContainer) {
                    $placeholder.html(container.group.item.eq(0).html());
                },
                onDrop: function($item, container, _super, event) {
                    if(!$item.prevAll(defaults.selector.list + ' ' + defaults.selector.item).length && $item.prevAll(defaults.selector.hour).length < 1) {
                        $item.remove();
                        $item.insertAfter($(defaults.selector.hour).eq(0));
                    }
                    if(!$item.nextAll(defaults.selector.list + ' ' + defaults.selector.item).length && $item.nextAll(defaults.selector.hour).length < 2) {
                        $item.remove();
                        $item.insertBefore($(defaults.selector.hour).eq($(defaults.selector.hour).length - 2));
                    }
                    setValuesBySort();
                    _super($item, container);
                }
            }
        );
    }

    setValuesBySort = function() {
        var heure = "";
        $(defaults.selector.list + ' ' + defaults.selector.item + ', ' + defaults.selector.list + ' ' + defaults.selector.hour).each(function() {
            if ($(this).filter(defaults.selector.hour).length) {
                heure = $(this).attr('data-value');
            } else if($(this).filter(defaults.selector.item).length) {
                $(this).find(defaults.selector.itemInputHour).val(heure);
            }
        });
    }

    function addItem(ligne) {
        if(!getActiveTourneeId()) {
            return;
        }

        if(getLigneTournee(ligne)) {
            return;
        }

        var tournee = getActiveTournee();

        addItemToTournee(ligne, tournee);
    }

    function addItemToTournee(ligne, tournee) {
        ligne.attr('data-tournee', getTourneeId(tournee));
        ligne.find(defaults.selector.itemInputTournee).val(getTourneeId(tournee));
        var hour = tourneeCalculHour(ligne, tournee);
        if(ligne.attr('data-hour') == undefined){
            hour = '06:00';
        }
        ligne.detach().insertBefore(tourneeInsertHourDiv(hour));
        ligne.find(defaults.selector.itemMarker).css('color', getTourneeColor(tournee));
        $(markers[getLignePoint(ligne)]._icon).find('.marker-inner').css('color', getTourneeColor(tournee));
        ligne.find(defaults.selector.itemInputHour).val(hour);
        ligne.find(defaults.selector.itemMove).removeClass('hidden');
       
        updateItem(ligne);
    }

    function updateItems() {
        $(defaults.selector.item).each(function() {
            updateItem($(this));
        });
    }

    function updateItem(ligne)
    {
        if (getLigneTournee(ligne)) {
            ligne.find(defaults.selector.itemRemove).removeClass('hidden');
            ligne.find(defaults.selector.itemAdd).addClass('hidden');

            //ligne.addClass('list-group-item-success');
            //ligne.find('.glyphicon-map-marker').css('color', ligne.attr('data-color'));
            //$(markers[ligne.attr('data-point')]._icon).find('.marker-inner').css('color', ligne.attr('data-color'));
            
            //ligne.removeClass('clickable');
        } else {
            ligne.find(defaults.selector.itemRemove).addClass('hidden');
            ligne.find(defaults.selector.itemAdd).removeClass('hidden');
            
            //ligne.removeClass('list-group-item-success');
            //$(markers[ligne.attr('data-point')]._icon).find('.marker-inner').css('color', '#e2e2e2');
            //ligne.find('.glyphicon-map-marker').css('color', '#e2e2e2');

            /*if ($('.nav-filter.active').attr('data-state')) {
                ligne.addClass('clickable');
                ligne.find('button.btn-success').removeClass('hidden');
            } else {
                ligne.removeClass('clickable');
                ligne.find('button.btn-success').addClass('hidden');
            }*/
        }

        if(getActiveTourneeId() && getLigneTournee(ligne) && getLigneTournee(ligne) != getActiveTourneeId()) {
            hideItem(ligne);
        } else {
            showItem(ligne);
        }

        if(!getActiveTourneeId()) {
            ligne.find(defaults.selector.itemAdd).addClass('hidden');
        }
    }

    function hideItem(ligne) {
        ligne.addClass('hidden');
        $(markers[getLignePoint(ligne)]._icon).addClass('hidden');
    }

    function showItem(ligne) {
        ligne.removeClass('hidden');
        $(markers[getLignePoint(ligne)]._icon).removeClass('hidden');
    }

    function removeItem(ligne) {
        if(!getLigneTournee(ligne)) {
            return;
        }

        ligne.attr('data-tournee', '');
        ligne.find(defaults.selector.itemInputTournee).val("");
        ligne.find(defaults.selector.itemInputHour).val("");
        ligne.find(defaults.selector.itemMove).addClass('hidden');
        ligne.find(defaults.selector.itemMarker).css('color', defaults.colorEmpty);
        $(markers[getLignePoint(ligne)]._icon).find('.marker-inner').css('color', defaults.colorEmpty);

        ligne.detach().appendTo($(defaults.selector.listWait));
        updateItem(ligne);
    }

    function toggleItem (ligne) {
        if (getLigneTournee(ligne)) {
            removeItem(ligne);
        } else {
            addItem(ligne);
        }
    }

    function getActiveTournee() {

        return $(defaults.selector.tournee + defaults.selector.tourneeActive);
    }

    function getActiveTourneeId() {

        return getTourneeId(getActiveTournee());
    }

    function getTourneePerHour(tournee) {

        return tournee.attr('data-per-hour') * 1;
    }

    function getTourneeHour(tournee) {

        return tournee.attr('data-hour');
    }

    function getTourneeColor(tournee) {
        
        return tournee.attr('data-color');
    }

    function getTourneeId(tournee) {

        return tournee.attr('id');
    }

    function tourneeLastHour (tournee) {
        var lastElement = getTourneeItems(tournee).last();

        if(!lastElement.length) {
            
            return getTourneeHour(tournee);
        }

        return lastElement.find(defaults.selector.itemInputHour).val();
    }

    function tourneeCalculHour(ligne, tournee) {
        var hour = tourneeLastHour(tournee);

        if(ligne.attr('data-hour') && $(defaults.selector.hour + '[data-value="'+ ligne.attr('data-hour') + '"]').length) {

            hour = ligne.attr('data-hour');
        }

        if ($(defaults.selector.list + ' ' + defaults.selector.item + '[data-tournee=' + getTourneeId(tournee) + '] ' + defaults.selector.itemInputHour + '[value="' + hour + '"]').length >= getTourneePerHour(tournee)) {

            return tourneeNextHour(hour);
        }

        return hour;
    }

    function tourneeNextHour(hour) {
        next = hour.split(':')[0] * 1 + 1;
        if (next == 13 || next == 14) {
            next = 15;
        }
        if (next > 9) {
            return next + ':00';
        } 
            
        return '0' + next + ':00';
    }

    function tourneeInsertHourDiv(hour) {
        var hourDiv = $(defaults.selector.hour + '[data-value="' + tourneeNextHour(hour) + '"]');
        var nextHourDiv = $(defaults.selector.hour + '[data-value="' + tourneeNextHour(tourneeNextHour(hour)) + '"]');
        if(!hourDiv.length) {

            return $(defaults.selector.hour).eq($(defaults.selector.hour).length-2);
        }

        return hourDiv;
    }

    function getTourneeItems(tournee) {
        return $(defaults.selector.list + ' ' + defaults.selector.item + '[data-tournee=' + getTourneeId(tournee) + ']');
    }

    function getLignePoint(ligne) {

        return ligne.attr('data-point');
    }

    function getLigneDefaultHour(ligne) {

        return ligne.attr('data-default-hour');
    }

    function getLigneTournee(ligne) {

        return ligne.attr('data-tournee');
    }

    function getLigneId(ligne) {

        return ligne.attr('id');
    }

    function latlngToLigne(ll) {
        return $(defaults.selector.item + '[data-point="' + ll.lat + "," + ll.lng + '"]');
    }

    function toggleMarkerHover(marker, ligne, withMarkerOpacity, withLigneOpacity) {
        for (coordonnees in markers) {
            if (withMarkerOpacity) {
                if ($(markers[coordonnees]._icon).css('opacity') == '1') {
                    $(markers[coordonnees]._icon).css('opacity', '0.3');
                } else {
                    $(markers[coordonnees]._icon).css('opacity', '1');
                }
            }
            markers[coordonnees].setZIndexOffset(900);
        }
        if (withLigneOpacity) {
            $(defaults.selector.list + ' ' + defaults.selector.item + ' ' + defaults.selector.itemMarker).each(function() {
                if ($(this).css('opacity') == '1') {
                    $(this).css('opacity', '0.3');
                } else {
                    $(this).css('opacity', '1');
                }
            });
            ligne.find('.glyphicon-map-marker').css('opacity', '1');
        }
        if (withMarkerOpacity) {
            $(marker._icon).css('opacity', '1');
        }
        marker.setZIndexOffset(1000);
    }
 
}( jQuery ));


/* =================================================================================== */
/* JQUERY CONTEXT */
/* =================================================================================== */
(function($)
{
    var _doc = $(document);
    
    
    /* =================================================================================== */
    /* FUNCTIONS CALL */
    /* =================================================================================== */
    _doc.ready(function()
    {
        $.fn.organisationTournees();
    });

})(jQuery);
