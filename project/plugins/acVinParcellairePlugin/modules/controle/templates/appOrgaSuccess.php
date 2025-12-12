<script id="dataJson" type="application/json">
<?php echo $sf_data->getRaw('json') ?>
</script>
<script>
    const { createWebHashHistory, createRouter, useRoute, useRouter } = VueRouter
    const { createApp } = Vue;

    const templates = [];

    <?php foreach(['operateurs'] as $template): ?>
        templates["<?php echo $template ?>"] = { template: "<?php echo str_replace(['"', "\n"], ['\"', ""], get_partial('controle/orga'.ucfirst($template))) ?>" }
    <?php endforeach; ?>

    const routes = [
      { path: '/', name: "operateurs", component: templates.operateurs },
    ]

    const router = createRouter({
      history: createWebHashHistory(),
      routes,
    });


    const controles = JSON.parse(document.getElementById("dataJson").textContent);

    const app = createApp({
        data() {
          return {
              controles: controles,
            }
        },
        template: '<RouterView :key="$route.fullPath" />',
    });
    app.use(router);
    app.mount('#content');


    templates.operateurs.data = function() {
        return {
          controles: controles
        }
    };
    templates.operateurs.mounted = function() {

        const map = new L.map('map');
        map.setView([43.8293, 7.2977], 8);
        const tileLayer = L.tileLayer('https://data.geopf.fr/wmts?&REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0&TILEMATRIXSET=PM&LAYER={ignLayer}&STYLE={style}&FORMAT={format}&TILECOL={x}&TILEROW={y}&TILEMATRIX={z}',
        {
            ignApiKey: 'pratique',
            ignLayer: 'ORTHOIMAGERY.ORTHOPHOTOS',
            style: 'normal',
            format: 'image/jpeg',
            service: 'WMTS',
            minZoom: 8,
            maxZoom: 19,
            attribution: 'Map data &copy;<a href="https://www.24eme.fr/">24eme Société coopérative</a>, <a href="https://cadastre.data.gouv.fr/">Cadastre</a>, Imagery © <a href="https://www.ign.fr/">IGN</a>',
            id: 'mapbox.light'
        });
        tileLayer.addTo(map)
        const gps = new L.Control.Gps({
            autoCenter:true
        });
        gps.addTo(map);
        const popup = L.control({position: "bottomright"});
        popup.onAdd = function (map) {
            this._div = L.DomUtil.create('div', 'popup');
            this._div.style.display = 'none';
            return this._div;
        };
        popup.update = function (layer) {
          this._div.style.background = 'rgba(255,255,255,0.9)';
          if(!layer) {
            this._div.style.display = 'none';
            return null
          }
          let props = layer.feature.properties;
          this._div.style.display = 'block';

          props.parcellaires.forEach(function(parcelle){
              opNom = parcelle['Nom Operateur'];
              opCvi = parcelle['CVI Operateur'];
              opSiret = parcelle['Siret Operateur'];
          });
          var popupContent = '<h2 class="text-center">'+opNom+'<br /><small class="muted">CVI : '+opCvi+' Siret : '+opSiret+'</small></h2>';
          this._div.innerHTML = popupContent;
        };
        popup.addTo(map);

        const parcelles = [];
        let controleIndex = 0;
        for(let controleId in controles) {
            const controle = controles[controleId];
            const obj = JSON.parse(controle.parcellaire_geojson);
            for (let featureId in obj.features) {
                const feature = obj.features[featureId];
                feature.properties.controleIndex = controleIndex;
                parcelles.push(feature);
                console.log(feature)
            }
            controleIndex++;
        }

        const parcellesLayer = L.geoJSON(parcelles, { onEachFeature: onEachFeature });
        parcellesLayer.addTo(map);
        map.fitBounds(parcellesLayer.getBounds());

        function onEachFeature(feature, layer) {
            layer.on({
                click: function (e) {
                    L.DomEvent.stopPropagation(e);
                    map.fitBounds(e.target.getBounds());
                    return false;
                },
                mouseover: highlightFeature,
                mouseout: resetHighlight,
            });
        }

        function highlightFeature(e) {
            e.target.setStyle({fillOpacity: 0.6 });
            popup.update(e.target);
        }

        function resetHighlight(e) {
            e.target.setStyle({fillOpacity: 0.3 });
            popup.update();
        }

        /*
        * Fin Map
        */
    };
</script>
