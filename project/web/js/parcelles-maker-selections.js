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

var map = new L.map('map');
//map.scrollWheelZoom.disable();
map.on('click', function(e) { clearParcelleSelected(); });

L.tileLayer('https://data.geopf.fr/wmts?'+
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

// GPS
var gps = new L.Control.Gps({
	//autoActive:true,
	autoCenter:true
});//inizialize control

gps
.on('gps:located', function(e) {
	//	e.marker.bindPopup(e.latlng.toString()).openPopup()
	console.log(e.latlng, map.getCenter())
})
.on('gps:disabled', function(e) {
	e.marker.closePopup()
});

gps.addTo(map);

// Fin GPS

/**
* Color will be Red
**/

function style(feature) {
    return {
        weight: 2,
        opacity: 1,
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
    if (layers[parcelles_name]) {
        map.fitBounds(layers[parcelles_name].getBounds());
    }else{
        for(i in layers) {
            map.fitBounds(layers[i].getBounds());
        }
    }
    clearParcelleSelected()
}

var sections = []
function onEachFeature(feature, layer) {
    layer.on({
        click: zoomToFeature
    });
    let section_text = feature.id.substring(5, 10).replace(/^0*/, '');
    let parcelle_text = section_text+'&nbsp;'+feature.id.substring(10, 15).replace(/^0*/, '');
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
parcelles_name = '<span style="background-color: white; border: 2px solid red; width: 25px; display:inline-block;"> &nbsp; </span> Parcelles'
array_parcelles = [];
if (parcelles) {
    array_parcelles = parseString(parcelles);
}
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

for(i in aires) {
  name = '<span style="background-color: '+aires[i]['color']+'; width: 25px; display:inline-block;"> &nbsp; </span> ' + aires[i]['name'];
  layers[name] = L.geoJSON(parseString(aires[i]['geojson']), { style: styleDelimitation(aires[i]['color'], 0.5) });
  layers[name].addTo(map);
};
if (array_parcelles.length) {
    layers[parcelles_name] = L.geoJSON(array_parcelles, { style: style, onEachFeature: onEachFeature });
    layers[parcelles_name].addTo(map);
}

L.control.layers({}, layers, {position: 'bottomleft'}).addTo(map);
map.addEventListener('overlayadd', function(e) {
    for(name in layers) {
        layers[name].bringToFront();
    }
    if (array_parcelles.length) {
        layers[parcelles_name].bringToFront();
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
  L.DomEvent.stopPropagation(e);
  zoomToParcelle(e.target);
  highlightFeature(e);
  return false;
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

var info = L.control({position: "bottomright"});

info.onAdd = function (map) {
    this._div = L.DomUtil.create('div', 'info'); // create a div with a class "info"
    this._div.style.display = 'none';
    this.update();
    L.DomEvent.disableClickPropagation(this._div);
    L.DomEvent.disableScrollPropagation(this._div);
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
  var Commune = "<th>Commune</th>";
  var Cepages = "<th>Produits<br/>et cepages</th>";
  var numParcelles = "<th>Section&nbsp;/&nbspN°</th>";
  var Superficies = "<th>Superficies  <span>(ha)</span></th>";
  var ecartPied = "<th>Écart Pieds</th>";
  var ecartRang = "<th>Écart Rang</th>";
  var compagnes = "<th>Année plantat°</th>";
  var btnSelection = "<th>Sélectionner</th>";
  props.parcellaires.forEach(function(parcelle, ordre) {
    var parcelleId = parcelle.IDU+'-'+String(ordre).padStart(2, '0');
    var parcelleSelected = document.querySelector('input[type="checkbox"][value="'+parcelleId+'"]').checked;
    var isSuccess = parcelleSelected ? "success" : "";
      Commune += '<td class="colonneInput '+isSuccess+'" data-id="input-'+ordre+'">'+parcelle['Commune']+'</td>';
      numParcelles += '<td class="colonneInput '+isSuccess+'" data-id="input-'+ordre+'">'+parcelle["Section"]+" "+parcelle["Numero parcelle"]+'</td>';
      Cepages += '<td class="colonneInput '+isSuccess+'" data-id="input-'+ordre+'"><span class="text-muted">'+parcelle.Produit+'</span><br/>'+parcelle.Cepage+'</td>';
      compagnes += '<td class="colonneInput '+isSuccess+'" data-id="input-'+ordre+'">'+parcelle.Campagne+'</td>';
      Superficies += '<td class="colonneInput '+isSuccess+'" data-id="input-'+ordre+'">'+parcelle.Superficie+'</td>';
      ecartPied += '<td class="colonneInput '+isSuccess+'" data-id="input-'+ordre+'">'+parcelle["Ecart pied"]+'</td>';
      ecartRang +='<td class="colonneInput '+isSuccess+'" data-id="input-'+ordre+'">'+parcelle["Ecart rang"]+'</td>';
      if (parcelleSelected) {
        btnSelection +='<td class="success" align="center"><label class="switch"><input id="input-'+ordre+'" class="selectParcelle" type="checkbox" data-parcelleid="'+parcelleId+'" checked/><span class="slider round"></span></label></td>';
      } else {
        btnSelection +='<td class="" align="center"><label class="switch"><input id="input-'+ordre+'" class="selectParcelle" type="checkbox" data-parcelleid="'+parcelleId+'" /><span class="slider round"></span></label></td>';
      }

  });

  var popupContent ='<button id="btn-close-info" type="button" style="position: absolute; right: 10px; top: 5px;" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button> <table class="table table-bordered table-condensed table-striped"><tbody>'+
                  '<tr>'+Commune+'</tr>'+
                  '<tr>'+numParcelles+'</tr>'+
                  '<tr>'+Cepages+'</tr>'+
                  '<tr>'+compagnes+'</tr>'+
                  '<tr>'+Superficies+'</tr>'+
                  '<tr>'+ecartPied+'</tr>'+
                  '<tr>'+ecartRang+'</tr>'+
                  '<tr>'+btnSelection+'</tr>'+
                  '</tbody></table>';
    this._div.innerHTML = popupContent;
};

document.addEventListener('click', function(e) {
  const btn = e.target.closest('#btn-close-info');
  if (!btn) return;
  e.stopPropagation();
  clearParcelleSelected();
});

document.addEventListener('click', function(e) {
  const closeBtn = e.target.closest('#btn-close-info');
  if (closeBtn) {
    e.stopPropagation();
    clearParcelleSelected();
    return;
  }
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

function colorOnValidateStatusParcelle(id, status){
  let layer = getParcelleLayer(id);
  if (status == true ) {
    layer.setStyle({
        fillColor: '#03fc1c', color: 'green'});
  } else {
    layer.setStyle({
        fillColor: '#fff', color: 'red'});
  }
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
    updateSuperficieSelectionnee();
    document.querySelectorAll('.inputTd').forEach(function (td) {
      var input = td.querySelector('input');
      colorOnValidateStatusParcelle(input.dataset.parcelleid.split('-')[0], input.checked);
    });
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
                    p.innerHTML = "non-trouvée (<a href='https://www.opendatawine.fr/carte.html?insee="+idu.substr(0, 5)+"#"+idu+"' target='blank'>Vérifier</a>)";
                    parent.append(p);
                    element.style.display = 'none';
                }
            });
        }
    }
   });
})

function updateSuperficieSelectionnee() {
  let total = 0;
  document.querySelectorAll("#tableParcelle input:checked").forEach( function (input) {
    total += parseFloat(input.dataset.superficie);
  });
  document.querySelector('#total_surfaces_selectionnees').textContent = total.toFixed(4);
}

$(document).delegate("#tableParcelle td", "click", function checkRow (e) {
  var inputControle = $(this).parent('tr').find('td:nth-child(8) input')[0];

  inputControle.checked = !inputControle.checked;
  inputControle.dispatchEvent(new Event('change', { bubbles : true }));
})

$(document).delegate(".colonneInput", "click", function checkCol (e) {
  var eTargetId = $(this)[0].dataset.id;
  var targetInput = document.getElementById(eTargetId);

  targetInput.checked = !targetInput.checked;
  targetInput.dispatchEvent(new Event('change', { bubbles : true }));
})

$(document).delegate("input[type=checkbox]", "change", function (e) {
  var origin = $(this)[0];
  document.querySelectorAll('[data-parcelleid="'+origin.dataset.parcelleid+'"]').forEach(function (target) {
    target.checked = origin.checked;
    if (target.id) { /* est ce que l'element fait partie d'une colonne a highlight ? */
      document.querySelectorAll('[data-id='+target.id+']').forEach(function (td) {
        if (target.checked) {
          td.classList.add("success");
          target.parentElement.parentElement.classList.add("success");
        } else {
          td.classList.remove("success");
          target.parentElement.parentElement.classList.remove("success");
        }
      });
    } else { /* ou est ce qu'il fait partie d'une ligne ? */
      target.checked ? target.parentElement.parentElement.parentElement.classList.add("success") : target.parentElement.parentElement.parentElement.classList.remove("success")
    }
  });
  colorOnValidateStatusParcelle(origin.dataset.parcelleid.split('-')[0], origin.checked);
  updateSuperficieSelectionnee();
})
