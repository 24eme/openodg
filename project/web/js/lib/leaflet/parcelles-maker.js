var parcelles = window.parcelles;
var myMarker;
var mygeojson;
var myLayer=[];
var listIdLayer=[];
var center = [parcelles["features"][0]["geometry"]["coordinates"][0][0][1], parcelles["features"][0]["geometry"]["coordinates"][0][0][0]];
var map = L.map('map').setView(center, 10);
L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
    maxZoom: 30,
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
        '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
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
        fillColor: getColor(feature.properties.parcellaires.Produit),
        weight: 2,
        opacity: 2,
        color: 'white',
        dashArray: '5',
        fillOpacity: 0.7
    };
} 

function closeDisplayer(){
    if(this.myMarker){
        this.map.removeLayer(myMarker);//remove preview marker, show one marker at the same time
    }
    if(this.map._popup){
        this.map.closePopup();//close popup if is opened
    }
}

function loadGeoJson(){
    mygeojson = L.geoJSON(parcelles, {
    style: style,
    onEachFeature: onEachFeature,
    }).addTo(map);

    map.fitBounds(mygeojson.getBounds());
}

//map.fitBounds(mygeojson.getBounds());
loadGeoJson();

function zoomToFeature(e) {
    closeDisplayer();
    map.fitBounds(e.target.getBounds());
}

function isAlready(layer){
    console.log("Exist");
    return listIdLayer.includes(layer._leaflet_id);
}

function onEachFeature(feature, layer) {
    layer.on({
        click: zoomToFeature
    });
    if(!isAlready(layer)){
        console.log("Exist");
    }else{
        listIdLayer.push(layer);
    }
    var popupContent = "<div id='parcelle"+feature.properties.numero+"' style='margin:15px'><p>Parcelle : N° "+ feature.properties.numero+ " Section "+feature.properties.parcellaires.Section+"</p>"+
    "<p>Cépage : "+feature.properties.parcellaires.Cepage+"</p>"+
    "<p>Produit : "+feature.properties.parcellaires.Produit+"</p>"+
    "<p>Compagne : "+feature.properties.parcellaires.Campagne+"</p>"+
    "<p>Surface : "+feature.properties.parcellaires.Superficie+"ha</p>"+
    "<p>Ecart pied : "+feature.properties.parcellaires["Ecart pied"]+"</p>"+
    "<p>Ecart rang : "+feature.properties.parcellaires["Ecart rang"]+"</p></div>";

    if (feature.properties && feature.properties.popupContent) {
        popupContent += feature.properties.popupContent;
    }
    layer.bindPopup(popupContent);
}

function styleParcelle(){
    return {fillColor :'blue'};
}

function showParcelle(id, htmlObj){

    if(this.map) {
        this.map.eachLayer(function(layer) {
            if(layer.feature && layer.feature.id == id){
                closeDisplayer();
                this.myLayer = layer;
                center = myLayer.getCenter();
                this.myMarker = L.marker(center,  {

                }).addTo(map);
                
                this.map.fitBounds(this.myLayer.getBounds());
            }
            $(window).scrollTop(0);

        });
    }else{
        alert("Error: Map empty !");
    }
}