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
    const parcellesSelectionneesControles = [];
    for(controleId in controles) {
        let controle = controles[controleId]
        const parcellesIds = [];
        for(parcelleId in controle.parcelles) {
            parcellesIds.push(parcelleId)
        }
        parcellesSelectionneesControles[controle._id] = parcellesIds;
    }

    let activeMap = null;

    const app = createApp({
        data() {
          return {
              controles: controles,
            }
        },
        template: '<RouterView :key="$route.fullPath" />'
    });
    app.use(router);
    app.mount('#content');

    /* Action Operateurs */

    templates.operateurs.data = function() {
        return {
          controles: controles
        }
    };
    templates.operateurs.methods = {
        nbParcellesSelectionnees(controleId) {
            if (controleId in parcellesSelectionneesControles) {
                return parcellesSelectionneesControles[controleId].length;
            }
            return 0;
        },
        nbParcelles(controleId) {
            if (controleId in controles) {
                return Object.entries(controles[controleId].parcellaire_parcelles).length;
            }
            return 0;
        },
        getControlesSorted() {
            const controlesSorted = [];
            for(let controleId in parcellesSelectionneesControles) {
                if(parcellesSelectionneesControles[controleId].length) {
                    controlesSorted.push(controles[controleId])
                }
            }
             for(let controleId in parcellesSelectionneesControles) {
                if(!parcellesSelectionneesControles[controleId].length) {
                    controlesSorted.push(controles[controleId])
                }
            }
            return controlesSorted;
        }
    }
    templates.operateurs.mounted = function() {
        const map = new L.map('map');
        activeMap = map;
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
        for (const [idControle, controle] of Object.entries(controles)) {
            for (const [idFeature, feature] of Object.entries(controle.parcellaire_geojson.features)) {
                feature.properties.controleId = idControle;
                parcelles.push(feature);
            }
        }

        const parcellesLayer = L.geoJSON(parcelles, { onEachFeature: onEachFeature });
        parcellesLayer.addTo(map);
        map.fitBounds(parcellesLayer.getBounds());
        function onEachFeature(feature, layer) {
            let find = false;
            for(controleId in parcellesSelectionneesControles) {
                for(parcelleId of parcellesSelectionneesControles[controleId]) {
                    if(parcelleId.match(feature.id)) {
                        find = true;
                    }
                }
            }
            if(find) {
                layer.setStyle({fillColor: '#c80064', color: '#c80064'});
            } else {
                layer.setStyle({fillColor: '#3388ff', color: '#3388ff'});
            }
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
            const controleId = e.target.feature.properties.controleId
            activeMap.eachLayer(function(layer) {
                if(!layer.feature || !layer.feature.id) {
                    return;
                }
                if(layer.feature.properties.controleId == controleId) {
                    layer.setStyle({fillOpacity: 1, opacity: 1 });
                } else {
                    layer.setStyle({fillOpacity: 0.3, opacity: 0.3 });
                }
            });
            popup.update(e.target);
        }

        function resetHighlight(e) {
            activeMap.eachLayer(function(layer) {
                if(!layer.feature || !layer.feature.id) {
                    return;
                }
                layer.setStyle({fillOpacity: 0.3, opacity: 1 });
            });
            popup.update();
        }
    };

    /* Action Operateur */
    templates.operateur.data = function() {
        const route = useRoute();

        const controleCourant = controles[route.params.id];

        const parcelles = [];
        for (const [idFeature, feature] of Object.entries(controleCourant.parcellaire_geojson.features)) {
            feature.properties.controleId = controleCourant._id;
            for(ordre in feature.properties.parcellaires) {
                feature.properties.parcellaires[ordre].parcelleId=feature.id+'-'+String(ordre).padStart(2, '0');
            }
            parcelles.push(feature);
        }

        let parcellesSelectionnees = [];
        if(parcellesSelectionneesControles[controleCourant._id]) {
            parcellesSelectionnees = parcellesSelectionneesControles[controleCourant._id]
        }

        return {
          controleCourant: controleCourant,
          parcelles: parcelles,
          parcellesSelectionnees: parcellesSelectionnees,
        }
    };

    templates.operateur.mounted = function() {
        const map = new L.map('map');
        activeMap = map;
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
            L.DomEvent.disableClickPropagation(this._div);
            L.DomEvent.disableScrollPropagation(this._div);
            return this._div;
        };
        popup.update = async (layer) => {
            document.querySelectorAll('.bloc_parcelle').forEach(function(item) {
                item.classList.add('hidden');
            });
            if(layer) {
                document.getElementById(layer.feature.id).classList.remove('hidden');
            }
        }
        popup.addTo(map);

        const parcellesLayer = L.geoJSON(this.parcelles, { onEachFeature: onEachFeature });
        parcellesLayer.addTo(map);
        map.fitBounds(parcellesLayer.getBounds());

        function onEachFeature(feature, layer) {
            layer.on({
                click: function (e) {
                    L.DomEvent.stopPropagation(e);
                    map.fitBounds(e.target.getBounds());
                    popup.update(e.target);
                    return false;
                },
            });
        }
        map.on('click', function(e) { clearParcelleSelected(); });

        function clearParcelleSelected() {
          popup.update();
        }
        document.addEventListener('click', function(e) {
          const btn = e.target.closest('#btn-close-info');
          if (!btn) return;
          e.stopPropagation();
          clearParcelleSelected();
        });

        this.updateMap();
    };

    templates.operateur.methods = {
        updateMap() {
            const parcellesSelectionnees = this.parcellesSelectionnees;
            activeMap.eachLayer(function(layer) {
                if(!layer.feature || !layer.feature.id) {
                    return;
                }
                let find = false;
                for(parcelleId of parcellesSelectionnees) {
                    if(parcelleId.match(layer.feature.id)) {
                        find = true;
                    }
                }
                if(find) {
                    layer.setStyle({fillColor: '#c80064', color: '#c80064'});
                } else {
                    layer.setStyle({fillColor: '#3388ff', color: '#3388ff'});
                }
            });
        },
        pourcentageSelectionne() {
            const parcellesSelectionnees = this.parcellesSelectionnees;
            const controleCourant = this.controleCourant;
            let superficieTotale = 0;
            let superficieSelectionnee = 0;
            for (const [parcelleId, parcelle] of Object.entries(controleCourant.parcellaire_parcelles)) {
                superficieTotale += parcelle.superficie;
                if (parcellesSelectionnees.includes(parcelleId)) {
                    superficieSelectionnee += parcelle.superficie;
                }
            }
            if (superficieTotale > 0) {
                return Math.round(superficieSelectionnee / superficieTotale * 100);
            }
            return 0;
        },
    };

    templates.operateur.watch = {
        parcellesSelectionnees: {
            handler(parcelles) {
                this.updateMap();
                parcellesSelectionneesControles[this.controleCourant._id] = this.parcellesSelectionnees
                const data = {};
                for(let id in parcellesSelectionneesControles) {
                    data[id] = [];
                    for(parcelleId of parcellesSelectionneesControles[id]) {
                        data[id].push(parcelleId);
                    }
                }
                document.getElementById('form_data').value = JSON.stringify(data);
            },
            deep: true
        }
    };
</script>

<form action="<?php echo url_for('controle_apporga_save', ['date' => $date_tournee]) ?>" method="POST">
    <input id="form_data" type="hidden" name="data" value="" />
    <button id="btn_save" type="submit" class="btn btn-primary">Enregistrer</button>
</form>
