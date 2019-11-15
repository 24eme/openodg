var parcelles = window.parcelles;
    var map = L.map('map', {
    center: parcelles["features"][0]["geometry"]["coordinates"][0][0].reverse(),
    zoom: 12
});
    L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
        maxZoom: 30,
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
            '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
            'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
        id: 'mapbox.light'
    }).addTo(map);

 var myStyle = {
    "color": "#07a8ed",
    "weight": 5,
    "opacity": 0.8
};

    

L.geoJSON(parcelles, {
style: myStyle,
onEachFeature: onEachFeature}).addTo(map);
function onEachFeature(feature, layer) {
    var popupContent = "Superficie : "+ feature.properties.parcellaires.Superficie
    + "<br>" + "Produit : "+ feature.properties.parcellaires.Produit
        ;

    if (feature.properties && feature.properties.popupContent) {
        popupContent += feature.properties.popupContent;
    }

    layer.bindPopup(popupContent);
}



