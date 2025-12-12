<script id="dataJson" type="application/json">
<?php echo $sf_data->getRaw('json') ?>
</script>
<script>
    const { createWebHashHistory, createRouter, useRoute, useRouter } = VueRouter
    const { createApp } = Vue;

    const templates = [];

    <?php foreach(['operateurs', 'operateur'] as $template): ?>
        templates["<?php echo $template ?>"] = { template: "<?php echo str_replace(['"', "\n"], ['\"', ""], get_partial('controle/orga'.ucfirst($template))) ?>" }
    <?php endforeach; ?>

    const routes = [
      { path: '/', name: "operateurs", component: templates.operateurs },
      { path: '/:id/', name: "operateur", component: templates.operateur },
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

    /* Action Operateurs */

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
                feature.properties.controleId = controle._id;
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
                    router.push({ name: 'operateur', params: { id: e.target.feature.properties.controleId } })
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

    /* Action Operateur */
    templates.operateur.data = function() {
        const route = useRoute()
        console.log(controles);
        console.log(route.params.id);

        return {
          controleCourant: controles[route.params.id],
          parcelleSelected: null
        }
    };
    templates.operateur.mounted = function() {

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

        const parcelles = [];
        let controleIndex = 0;
        for(let controleId in controles) {
            const controle = controles[controleId];
            const obj = JSON.parse(controle.parcellaire_geojson);
            for (let featureId in obj.features) {
                const feature = obj.features[featureId];
                feature.properties.controleIndex = controleIndex;
                feature.properties.controleId = controle._id;
                parcelles.push(feature);
                console.log(feature)
            }
            controleIndex++;
        }

        const popup = L.control({position: "bottomright"});
        popup.onAdd = function (map) {
            this._div = L.DomUtil.create('div', 'popup');
            this._div.style.display = 'none';
            return this._div;
        };
        popup.update = function (layer) {
            var parcelleSelected = this.parcelleSelected
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
                var parcelleSelected = false;
                //var parcelleSelected = document.querySelector('input[type="checkbox"][value="'+parcelleId+'"]').checked;
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
        }
        popup.addTo(map);


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
