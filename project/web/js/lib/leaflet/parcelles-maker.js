var parcelles = window.parcelles;
var myMarker;
var mygeojson;
var myLayer=[];
var fitBound;
var minZoom = 17;
var listIdLayer=[];

var map = L.map('map');

L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
    maxZoom: 30,
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> creator, ' +
        '<a href="https://www.24eme.fr/">24eme Société coopérative</a>, ' +
        'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
    id: 'mapbox.light'
}).addTo(map);

function getColor(d) {

    return d.includes("rouge") ? '#790000' :
           d.includes("rosé") ? '#f95087':
           d.includes("blanc") ? '#efeef3':'#2b0c0c';
}



function style(feature) {
    return {
        fillColor: getColor(feature.properties.parcellaires['0'].Produit),
        weight: 2,
        opacity: 2,
        color: 'white',
        dashArray: '5',
        fillOpacity: 0.7
    };
} 

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

function loadGeoJson(){
    mygeojson = L.geoJSON(parcelles, {
    style: style,
    onEachFeature: onEachFeature,
    }).addTo(map);

    zoomOnMap();
}

function zoomOnMap(){

    closeDisplayer();
    myMarker = null;

    map.fitBounds(mygeojson.getBounds());
}

loadGeoJson(); //Create map layer from geojson coordonates 

function zoomToFeature(e) {
    if(!closeDisplayer() || map.getZoom() < minZoom){

        myMarker = L.marker(e.target.getCenter()).addTo(map); 
        var f = map.fitBounds(e.target.getBounds());
        map.openPopup(e.target._popup);
    }else{
        map.openPopup(e.target._popup);
        var popup = $(".leaflet-popup-content")[0];
        minPopupWidth = popup.style.width;
        popup.style.overflow = "scroll";
        var width = (e.target.feature.properties.parcellaires.length +1) * 80 +"px";
        if(width > minPopupWidth){
            popup.style.width = width;
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
var error = true;

function showParcelle(id, htmlObj){

    if(this.map) {

        this.map.eachLayer(function(layer) {
            if(layer.feature){
                if(layer.feature.id == id){
                    error = false;
                    closeDisplayer();
                    this.myLayer = layer;
                    center = myLayer.getCenter();
                    this.myMarker = L.marker(center,  {

                    }).addTo(map);
                    
                    this.map.fitBounds(this.myLayer.getBounds());
                    $(window).scrollTop(0);
                }
            
                
            }

        });
        if(error){
            alert("Erreur: Cette parcelle n'existe pas au cadastre.");
        }
        
    }else{
        alert("Error: Map empty !");
    }


}
