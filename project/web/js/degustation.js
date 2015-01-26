/* =================================================================================== */
/* JQUERY CONTEXT */
/* =================================================================================== */
(function($)
{
    var _doc = $(document);
    var markers = [];
    var greenIcon = null;
    var redIcon = null;
    var pinkIcon = null;
    var blueIcon = null;
    var timerHover = null;

    /* =================================================================================== */
    /* FUNCTIONS CALL */
    /* =================================================================================== */
    _doc.ready(function()
    {
        $("#listes_operateurs .list-group-item .btn-success").click(function() {
            var ligne = $(this).parents(".list-group-item");
            $.addItem(ligne);

            return false;
        });

        $("#listes_operateurs .list-group-item").hover(
            function() {
                var ligne = $(this);
                if(ligne.attr('data-point')) {
                    markers[ligne.attr('data-point')].setIcon(pinkIcon);
                    ligne.find('span.glyphicon-map-marker').addClass('text-pink');
                }
            },
            function() {
                var ligne = $(this);
                if(ligne.attr('data-point')) {
                    $.updateItem(ligne);
                    ligne.find('span.glyphicon-map-marker').removeClass('text-pink');
                }
            }
        );

        $("#listes_operateurs .list-group-item[data-state!=active]").click(function() {
            var ligne = $(this);
            $.addItem(ligne);

            return false;
        });

        $("#listes_operateurs .list-group-item .btn-danger").click(function() {
            var ligne = $(this).parents(".list-group-item");
            $.removeItem(ligne);

            return false;
        });

        $("#nav_a_prelever").click(function() {
            $(this).parent().find('a').removeClass('active')
            $(this).addClass('active');
            $("#listes_operateurs .list-group-item[data-state!=active]").addClass('hidden');
            $("#listes_operateurs .list-group-item[data-state=active]").removeClass('list-group-item-success');

            if($('#carte').length > 0) {
                $("#listes_operateurs .list-group-item.hidden").each(function() {
                    markers[$(this).attr('data-point')].setOpacity(0);
                });
            }

            return false;
        });

        $("#nav_tous").click(function() {
            $(this).parent().find('a').removeClass('active')
            $(this).addClass('active');
            $("#listes_operateurs .list-group-item").removeClass('hidden');
            $("#listes_operateurs .list-group-item[data-state=active]").addClass('list-group-item-success');

            if($('#carte').length > 0) {
                $("#listes_operateurs .list-group-item").each(function() {
                    markers[$(this).attr('data-point')].setOpacity(100);
                });
            }

            return false;
        });

        if($('#carte').length > 0) {
            $.initCarteDegustation();
        }

	for(i = 0 ; i < $('#nb_a_prelever').val() ; i++) {
		$.addItem($("#listes_operateurs .list-group-item").eq(i));
	}

	$("#nav_a_prelever").click();

    });

    $.initCarteDegustation = function()
    {
        greenIcon = new L.Icon.Default({iconUrl: '/js/lib/leaflet/images/marker-icon-green.png'});
        redIcon = new L.Icon.Default({iconUrl: '/js/lib/leaflet/images/marker-icon-red.png'});
        pinkIcon = new L.Icon.Default({iconUrl: '/js/lib/leaflet/images/marker-icon-pink.png'});
        blueIcon = new L.Icon.Default();

        var map = L.map('carte', {minZoom: 8, icon: blueIcon}).setView([48.100901, 7.361051], 9);
        L.tileLayer('https://{s}.tiles.mapbox.com/v3/{id}/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
                '<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
                'Imagery © <a href="http://mapbox.com">Mapbox</a>',
            id: 'examples.map-i875mjb7'
        }).addTo(map);

        var points = [];
        $('#listes_operateurs .list-group-item').each(function () {
            points[$(this).attr('data-point')] = JSON.parse("["+$(this).attr('data-point')+"]");
        })

        for(key in points) {
            var point = points[key];
            var ligne = $('#listes_operateurs .list-group-item[data-point="' + point[0] + "," + point[1] + '"]');
            var marker = L.marker(point, {title: ligne.attr('data-title')});
            marker.addTo(map);

            marker.on('click', function(m) {
                var ligne = $('#listes_operateurs .list-group-item[data-point="' + m.latlng.lat + "," + m.latlng.lng + '"]');
                $.toggleItem(ligne);
                $('#listes_operateurs').scrollTo(ligne, 200, { offset: -150, queue: false });
            });

            marker.on('mouseover', function(m) {
                
                var ligne = $('#listes_operateurs .list-group-item[data-point="' + m.latlng.lat + "," + m.latlng.lng + '"]');
                m.target.setIcon(pinkIcon);
                timerHover = setTimeout(function(){
                    ligne.find('.glyphicon-map-marker').addClass('text-pink');
                    $('#listes_operateurs').scrollTo(ligne, 200, { offset: -150, queue: false });
                }, 600);
            })

            marker.on('mouseout', function(m) {
                clearTimeout(timerHover);
                var ligne = $('#listes_operateurs .list-group-item[data-point="' + m.latlng.lat + "," + m.latlng.lng + '"]');
                ligne.find('span.glyphicon-map-marker').removeClass('text-pink');
                $.updateItem(ligne);
            });

            markers[key] = marker;
        }

        //map.fitBounds(points, {padding: [10, 10]});

    }

    $.addItem = function(ligne) {
        ligne.attr('data-state', 'active');
        $.updateItem(ligne);
    }

    $.removeItem = function(ligne) {
        ligne.attr('data-state', '');
        $.updateItem(ligne);
    }

    $.toggleItem = function(ligne) {
        if(ligne.attr('data-state') == 'active') {
            $.removeItem(ligne);
        } else {
            $.addItem(ligne);
        }
    }

    $.updateItem = function(ligne)
    {
        if(ligne.attr('data-state') == "active") {
            ligne.find('button.btn-danger, select').removeClass('hidden');
            ligne.find('button.btn-success').addClass('hidden');
            if(ligne.hasClass('clickable')) {
                ligne.addClass('list-group-item-success');
            }
            ligne.removeClass('clickable');
            if(ligne.find('select[data-auto=true]').length > 0) {
                if(ligne.find('select option[selected=selected]').length == 0) {
                    $.tireAuSortCepage(ligne.find('select'));
                }
            }
            if(ligne.attr('data-point')) {
                markers[ligne.attr('data-point')].setIcon(greenIcon);
            }
        } else {
            ligne.find('button.btn-danger, select').addClass('hidden');
            ligne.find('button.btn-success').removeClass('hidden');
            ligne.removeClass('list-group-item-success');
            ligne.addClass('clickable');
            if($("#nav_a_prelever").hasClass('active')) {
                ligne.addClass('hidden');
                if(ligne.attr('data-point')) {
                    markers[ligne.attr('data-point')].setOpacity(0);
                }
            }
            ligne.find('select option[selected=selected]').removeAttr('selected');

            if(ligne.attr('data-point')) {
                markers[ligne.attr('data-point')].setIcon(blueIcon);
            }
        }

        $.updateNbPrelever();
        $.updateRecapCepages();
    }

    $.updateNbPrelever = function()
    {
        $("#nav_a_prelever .badge").html($("#listes_operateurs .list-group-item[data-state=active]").length);
    }

    $.tireAuSortCepage = function(select)
    {
        var nb_options = (select.find('option').length - 1);
        select.find('option').eq(Math.floor((Math.random() * nb_options) + 1)).attr('selected', 'selected');
    }

    $.updateRecapCepages = function()
    {
        $('#recap_cepages span.badge').text("0");

        $("#listes_operateurs .list-group-item select option:selected").each(function(index, value) {
            var item = $('#recap_cepages button[data-cepage="'+$(value).html()+'"] .badge');
            item.html(parseInt(item.html()) + 1);
        });

    }

})(jQuery);
