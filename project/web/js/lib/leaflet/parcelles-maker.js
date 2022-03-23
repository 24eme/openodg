var parcellesStr = window.parcelles;
var delimitationStr = window.delimitation;
var allIdu = window.all_idu;
var myMarker;
var mygeojson;
var myLayer=[];
var fitBound;
var minZoom = 17;

function parseString(dlmString){
    var mydlm = [];
    dlmString.split("|").forEach(function(str){
        mydlm.push(JSON.parse(str));
    });
    return mydlm;
}

var map = L.map('map');


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

/**
* Close popup and delete marker showing on map
**/
function closeDisplayer(){
    var res = false;
    
    if(myMarker){
        map.removeLayer(myMarker);//remove preview marker, show one marker at the same time
        res = true;
    }
    if(map._popup != null){
        map.closePopup();//close popup if is opened
        res = true;
    }
    return res;
}

function zoomOnMap(){

    closeDisplayer();
    myMarker = null;

    map.fitBounds(layers["Parcelles"].getBounds());
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
    if(!closeDisplayer() || map.getZoom() < minZoom){

        myMarker = L.marker(e.target.getCenter()).addTo(map); 
        var f = map.fitBounds(e.target.getBounds());
    }else{
        map.openPopup(e.target._popup);
        var popup = $(".leaflet-popup-content")[0];
        minPopupWidth = popup.style.width;
        var width = (e.target.feature.properties.parcellaires.length +1) * 80 +"px";
        if(width > minPopupWidth){
            popup.style.overflowX = "scroll";
        }   
    }
}

function onEachFeature(feature, layer) {
    layer.on({
        click: zoomToFeature,
    });
    
    var Cepages = "<th>Produits et cepages</th>";
    var numParcelles = "<th>Parcelle N°</th>";
    var Superficies = "<th>Superficies  <span>(ha)</span></th>";
    var ecartPied = "<th>Écart Pieds</th>";
    var ecartRang = "<th>Écart Rang</th>";
    var compagnes = "<th>Année plantat°</th>";
    feature.properties.parcellaires.forEach(function(parcelle){
        numParcelles += '<td>'+parcelle["Numero parcelle"]+'</td>';
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

    if (feature.properties && feature.properties.popupContent) {
        popupContent += feature.properties.popupContent;
    }

    layer.bindPopup(popupContent);

    layer._events.click.reverse();

}

function showParcelle(id){
    let layer = getParcelleLayer(id);

    if(!layer) {
      return;
    }

    closeDisplayer();
    this.myLayer = layer;
    center = myLayer.getCenter();
    this.myMarker = L.marker(center, {}).addTo(map);

    this.map.fitBounds(this.myLayer.getBounds());
    var carte = document.getElementById("jump");
    carte.scrollIntoView();
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

