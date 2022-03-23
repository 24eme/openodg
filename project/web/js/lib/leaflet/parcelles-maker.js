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
map.on('click', function(e) { if(e.target && e.target.feature) { return; } console.log(e); clearParcelleSelected() });
console.log(map);

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
        'Imagery © <a href="https://www.igp.fr/">IGN</a>',
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
        fillColor: color,
        weight: 3,
        opacity: 2,
        color: 'white',
        dashArray: '5',
        fillOpacity: 0.7
    };
}

/**
* Css style default
**/
function styleDelimitation(color, opacity){
    return {
        fillColor: color,
        weight: 3,
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

var layers = [];
layers["Parcelles"] = L.geoJSON(parseString(parcelles), { style: style, onEachFeature: onEachFeature });
layers["Parcelles"].addTo(map);

for(name in aires) {
  layers[aires[name]['name']] = L.geoJSON(parseString(aires[name]['geojson']), { style: styleDelimitation(aires[name]['color'], 0.6 / aires.length) });
  layers[aires[name]['name']].addTo(map);
};

L.control.layers({}, layers, {position: 'bottomleft'}).addTo(map);

zoomOnMap();

function zoomToFeature(e) {
  zoomToParcelle(e.target);
  e.preventDefault();
}

function zoomToParcelle(layer) {
  clearParcelleSelected();
  map.fitBounds(layer.getBounds());
  info.update(layer);
  parcelleSelected = layer;
  parcelleSelected.setStyle({
      weight: 3,
      fillOpacity: 1
  });
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
  if(parcelleSelected) {
    layer = parcelleSelected;
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

  var popupContent ='<table class="table table-bordered table-condensed table-striped"><tbody>'+
                  '<tr>'+numParcelles+'</tr>'+
                  '<tr>'+Cepages+'</tr>'+
                  '<tr>'+compagnes+'</tr>'+
                  '<tr>'+Superficies+'</tr>'+
                  '<tr>'+ecartPied+'</tr>'+
                  '<tr>'+ecartRang+'</tr>'+
                  '</tbody></table>';
    this._div.innerHTML = popupContent;
};

info.addTo(map);

function highlightFeature(e) {
  info.update(e.target);
}

function resetHighlight(e) {
  info.update();
}

function onEachFeature(feature, layer) {
    layer.on({
        mouseover: highlightFeature,
        mouseout: resetHighlight,
        click: zoomToFeature
    });
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

