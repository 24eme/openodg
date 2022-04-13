var parcellesStr = window.parcelles;
var delimitationStr = window.delimitation;
var allIdu = window.all_idu;
var parcelleSelected = null;
var minZoom = 17;

function parseString(dlmString){
    var mydlm = [];
    dlmString.split("|").forEach(function(str){
        mydlm.push(JSON.parse(str));
    });
    return mydlm;
}

var map = L.map('map');
map.on('click', function(e) { if(e.target && e.target.feature) { return; } clearParcelleSelected() });

L.tileLayer('https://wxs.ign.fr/{ignApiKey}/geoportail/wmts?'+
        '&REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0&TILEMATRIXSET=PM'+
        '&LAYER={ignLayer}&STYLE={style}&FORMAT={format}'+
        '&TILECOL={x}&TILEROW={y}&TILEMATRIX={z}',
        {
          ignApiKey: 'pratique',
          ignLayer: 'ORTHOIMAGERY.ORTHOPHOTOS',
          style: 'normal',
          format: 'image/jpeg',
          service: 'WMTS',
    maxZoom: 19,
    attribution: 'Map data &copy;' +
        '<a href="https://www.24eme.fr/">24eme Société coopérative</a>, ' +
        '<a href="https://cadastre.data.gouv.fr/">Cadastre</a>, ' +
        'Imagery © <a href="https://www.ign.fr/">IGN</a>',
    id: 'mapbox.light'
}).addTo(map);

/***** Location position ****/

$('#locate-position').on('click', function(){
    map.locate({setView: true});
});

var icon = L.divIcon({className: 'glyphicon glyphicon-record'});

function onLocationFound(e) {
    var radius = e.accuracy / 100;
    L.marker(e.latlng,{icon: icon}).addTo(map);
    L.circle(e.latlng, radius).addTo(map);
    map.setView(e.latlng, minZoom);    
}
function onLocationError(e) {
    alert("Vous n'êtes actuellement pas localisable. Veuillez activer la localisation.");
}

map.on('locationfound', onLocationFound);

map.on('locationerror', onLocationError);

/****** End location position *****/

function getColor(d) {

    return d.includes("rouge") ? '#790000' :
           d.includes("rosé") ? '#f95087':
           d.includes("blanc") ? '#edcb09':'#ffffff';
}

/**
* Css style for parcelles according product color ie "Côtes de Provence Rouge GRENACHE"
* Color will be Red
**/

function style(feature) {
    var color;
    color = getColor(feature.properties.parcellaires['0'].Produit);
    return {
        fillColor: '#fff',
        weight: 2,
        opacity: 1,
        color: 'red',
        fillOpacity: 0.3
    };
}

/**
* Css style default
**/
function styleDelimitation(color, opacity){
    return {
        fillColor: color,
        weight: 0,
        opacity: opacity,
        dashArray: '5',
        color: 'black',
        fillOpacity: 0.4
    }
}

function zoomOnMap(){
    map.fitBounds(layers["Parcelles"].getBounds());
    clearParcelleSelected()
}
var sections = []
function onEachFeature(feature, layer) {
    layer.on({
        mouseover: highlightFeature,
        mouseout: resetHighlight,
        click: zoomToFeature
    });
    let parcelle_text = feature.id.substring(5).replace(/0/g, '');
    let section_text = feature.id.substring(5, 10).replace(/0/g, '');
    if (!sections[section_text]) {
        sections[section_text] = layer.getBounds();
    }else{
        sections[section_text].extend(layer.getBounds());
    }
    map.addLayer( new L.Marker(
                    layer.getBounds().getCenter(),
                    {
                        title: "MyLocation",
                        icon: L.divIcon( iconOptions = {
                                iconSize  : [15, 15],
                                className : 'parcellelabel',
                                html: '<b>' +  parcelle_text + '</b>'
                        })
                    }
                )
            );
}
var layers = [];

for(i in aires) {
  layers[aires[i]['name']] = L.geoJSON(parseString(aires[i]['geojson']), { style: styleDelimitation(aires[i]['color'], 0.5) });
  layers[aires[i]['name']].addTo(map);
};
layers["Parcelles"] = L.geoJSON(parseString(parcelles), { style: style, onEachFeature: onEachFeature });
for(i in sections) {
    map.addLayer( new L.Marker(
                    sections[i].getCenter(),
                    {
                        title: "MyLocation",
                        icon: L.divIcon( iconOptions = {
                                iconSize  : [15, 15],
                                className : 'sectionlabel',
                                html: '<b>' +  i + '</b>'
                        })
                    }
                )
            );
}
layers["Parcelles"].addTo(map);

L.control.layers({}, layers, {position: 'bottomleft'}).addTo(map);
map.addEventListener('overlayadd', function(e) {
    for(name in layers) {
        layers[name].bringToFront();
    }
});

zoomOnMap();


map.on('zoomend', function() {
    if (map.getZoom() > 15){
        $('.parcellelabel').show();
        $('.sectionlabel').hide();
    } else {
        $('.parcellelabel').hide();
        $('.sectionlabel').show();
    }
});
$('.parcellelabel').hide();
$('.sectionlabel').show();

function zoomToFeature(e) {
  zoomToParcelle(e.target);
  e.preventDefault();
}

function zoomToParcelle(layer) {
  clearParcelleSelected();
  map.fitBounds(layer.getBounds());
  parcelleSelected = layer;
  info.update(layer);
}

function clearParcelleSelected() {
  if(!parcelleSelected) {
    return;
  }
  parcelleSelected.setStyle(style(parcelleSelected.feature));
  parcelleSelected = null;
  info.update(null);
}

var info = L.control();

info.onAdd = function (map) {
    this._div = L.DomUtil.create('div', 'info'); // create a div with a class "info"
    this._div.style.display = 'none';
    this.update();
    return this._div;
};

// method that we will use to update the control based on feature properties passed
info.update = function (layer) {
  this._div.style.background = 'rgba(255,255,255,0.9)';
  if(parcelleSelected) {
    layer = parcelleSelected;
    this._div.style.background = 'rgba(255,255,255,1)';
    parcelleSelected.setStyle({
        fillOpacity: 0.8
    });
  }
  if(!layer) {
    this._div.style.display = 'none';
    return null
  }
  let props = layer.feature.properties;

  this._div.style.display = 'block';
  var Cepages = "<th>Produits et cepages</th>";
  var numParcelles = "<th>Section&nbsp;/&nbspN°</th>";
  var Superficies = "<th>Superficies  <span>(ha)</span></th>";
  var ecartPied = "<th>Écart Pieds</th>";
  var ecartRang = "<th>Écart Rang</th>";
  var compagnes = "<th>Année plantat°</th>";
  props.parcellaires.forEach(function(parcelle){
      numParcelles += '<td>'+parcelle["Section"]+" "+parcelle["Numero parcelle"]+'</td>';
      Cepages += '<td><span class="text-muted">'+parcelle.Produit+'</span> '+parcelle.Cepage+'</td>';
      compagnes += '<td>'+parcelle.Campagne+'</td>';
      Superficies += '<td>'+parcelle.Superficie+'</td>';
      ecartPied += '<td>'+parcelle["Ecart pied"]+'</td>';
      ecartRang +='<td>'+parcelle["Ecart rang"]+'</td>';
  });

  var popupContent ='<button id="btn-close-info" type="button" style="position: absolute; right: 10px; top: 5px;" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button> <table class="table table-bordered table-condensed table-striped"><tbody>'+
                  '<tr>'+numParcelles+'</tr>'+
                  '<tr>'+Cepages+'</tr>'+
                  '<tr>'+compagnes+'</tr>'+
                  '<tr>'+Superficies+'</tr>'+
                  '<tr>'+ecartPied+'</tr>'+
                  '<tr>'+ecartRang+'</tr>'+
                  '</tbody></table>';
    this._div.innerHTML = popupContent;
};

$('#btn-close-info').on('click', function() {
  clearParcelleSelected();
});

info.addTo(map);

function highlightFeature(e) {
  e.target.setStyle({
      fillOpacity: 0.5
  });
  info.update(e.target);
}

function resetHighlight(e) {
  e.target.setStyle({
      fillOpacity: 0.3
  });
  info.update();
}

function showParcelle(id){
    let layer = getParcelleLayer(id);

    if(!layer) {
      return;
    }

    zoomToParcelle(layer);
    document.getElementById("jump").scrollIntoView();
}

function getParcelleLayer(id) {
  let layerFinded = null;

  map.eachLayer(function(layer) {
      if(layer.feature){
          if(Object.keys(layer.feature.properties).includes('parcellaires')){
              if(layer.feature.properties.parcellaires[0].IDU == id){
                layerFinded = layer;
              }
          }
      }
  });

  return layerFinded;
}


/**
* On select words filter, we filter map layers also.
* myfilters it's input element
**/
function filterMap() {
    clearParcelleSelected();
    let filters = $('#hamzastyle').val();
    let terms = filters.split(",");
    if(!terms.length) {
      layers['Parcelles'].eachLayer(function(layer) {
        layer._path.style.display = 'block';
      })
      return;
    }

    layers['Parcelles'].eachLayer(function(layer) {
      layer._path.style.display = 'none';
    });

    $(".hamzastyle-item").each(function(i, val){
        let words = val.getAttribute("data-words");
        terms.forEach(function(term) {
          if(words.indexOf(term) > -1) {
            getParcelleLayer(val.lastElementChild.firstElementChild.getAttribute("id"))._path.style.display = 'block';
          }
        });
    });
}

/**
* Keep filter if the page reload
**/
$(window).on("load", function() {
    if($("input#hamzastyle").val()){
        filterMap();
    }
});

/*
* filter tab after page load (when all ready)
* check parcelles which there aren't data geojson and put message not-found
*/
$(document).ready(function(){
   allIdu.forEach(function(idu){
    var found = false;
    if(map) {
        for(key in Object.keys(map._layers)){
            if(map._layers[Object.keys(map._layers)[key]].hasOwnProperty("feature")
                && map._layers[Object.keys(map._layers)[key]].feature.hasOwnProperty("properties")
                && map._layers[Object.keys(map._layers)[key]].feature.properties.hasOwnProperty("parcellaires")
                && map._layers[Object.keys(map._layers)[key]].feature.properties.id == idu){
                found = true;
            }
        };
        if(!found){
            document.querySelectorAll('[id="'+idu+'"]').forEach(element=> {
                if(!element.style.display.length){
                    var parent = element.parentNode;
                    var p = document.createElement("p");
                    p.innerHTML = "non-trouvée";
                    parent.append(p);
                    element.style.display = 'none';
                }
            });
        }
    }
   });
})

