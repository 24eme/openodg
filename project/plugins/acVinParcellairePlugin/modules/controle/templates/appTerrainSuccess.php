<script id="dataJson" type="application/json">
<?php echo $sf_data->getRaw('json') ?>
</script>
<script>
    const { createWebHashHistory, createRouter, useRoute, useRouter } = VueRouter
    const { createApp } = Vue;

    const templates = [];

    <?php foreach(['listing', 'map', 'operateur', 'parcelle', 'audit'] as $template): ?>
        templates["<?php echo $template ?>"] = { template: "<?php echo str_replace(['"', "\n"], ['\"', ""], get_partial('controle/terrain'.ucfirst($template))) ?>" }
    <?php endforeach; ?>

    let controles = JSON.parse(localStorage.getItem("controles")) || {}
    const server_controle = JSON.parse(document.getElementById("dataJson").textContent);
    let localstorage_updated = false;
    for (let i in server_controle) {
        if (controles[server_controle[i]._id]) {
            console.log(['ignore controle exists', controles[server_controle[i]._id]]);
            continue;
        }
        controles[server_controle[i]._id] = server_controle[i];
        localstorage_updated = true;
    }
    if (localstorage_updated) {
        localStorage.setItem("controles", JSON.stringify(controles));
    }
    function parseString(dlmString){
        let mydlm = [];
        dlmString.split("|").forEach(function(str){
            mydlm.push(JSON.parse(str));
        });
        return mydlm;
    }
    const array_parcelles = parseString('<?php echo addslashes(json_encode(current($controles->getRawValue())['geojson'])) ?>');

    const routes = [
      { path: '/', name: "listing", component: templates.listing },
      { path: '/map', name: "map", component: templates.map },
      { path: '/:id', name: "operateur", component: templates.operateur },
      { path: '/:id/audit', name: "audit", component: templates.audit },
      { path: '/:id/parcelle/:parcelle', name: "parcelle", component: templates.parcelle },
    ]

    const router = createRouter({
      history: createWebHashHistory(),
      routes,
    })

    const app = createApp({
        data() {
        console.log(controles);
          return {
              controles: controles,
            }
        },
        template: '<RouterView />',
        watch: {
          controles: {
            handler(newControles) {
              console.log(newControles)
              if (newControles) {
                  localStorage.setItem("controles", JSON.stringify(newControles));
              }
            },
            deep: true
          }
        },
      });
    app.use(router)
    app.mount('#content')

    templates.listing.data = function() {
        return {
          controles: controles
        }
    };

    templates.operateur.data = function() {
        const route = useRoute()

        return {
          controleCourant: controles[route.params.id]
        }
    };
    templates.operateur.methods = {
      nbParcellesControlees() {
        return (Object.keys(this.controleCourant.parcelles || {}).filter(k => this.controleCourant.parcelles[k].controle.saisie == 1)).length;
      },
      startAudit() {
        router.push({ name: 'audit', params: { id: this.controleCourant._id } })
      },
      echoFloat(val, nbDecimal = 5) {
        return val ? Number(val).toFixed(nbDecimal) : '';
      }
    };

    templates.parcelle.data = function() {
        const route = useRoute()

        return {
          controleCourant: controles[route.params.id],
          parcelleCourante: controles[route.params.id].parcelles[route.params.parcelle]
        }
    };
    templates.parcelle.methods = {
      save() {
        this.parcelleCourante.controle.saisie = 1;
        router.push({ name: 'operateur', params: { id: this.controleCourant._id } })
      },
      echoFloat(val, nbDecimal = 5) {
        return val ? Number(val).toFixed(nbDecimal) : '';
      }
    };

    templates.audit.data = function() {
        const route = useRoute()
        if(!controles[route.params.id].audit) {
          controles[route.params.id].audit = {}
        }
        if (!controles[route.params.id].audit.saisie) {
            let obs = '';
            for (let p in controles[route.params.id].parcelles) {
                if (controles[route.params.id].parcelles[p].controle.observations) {
                    obs += controles[route.params.id].parcelles[p].parcelle_id+' : '+controles[route.params.id].parcelles[p].controle.observations+'\n';
                }
            }
            controles[route.params.id].audit.observations = obs;
        }
        return {
          controleCourant: controles[route.params.id]
        }
    };
    templates.audit.methods = {
      save() {
        this.controleCourant.audit.saisie = 1;
        router.push({ name: 'operateur', params: { id: this.controleCourant._id } })
      }
    };
     templates.map.mounted = function() {
        const map = new L.map('map');
        map.setView([43.8293, 7.2977], 8);
        const tileLayer = L.tileLayer.offline('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
            minZoom: 8,
            maxZoom: 19,
            subdomains: "abc",
            attribution: 'Map data &copy;' +
            '<a href="https://www.24eme.fr/">24eme Société coopérative</a>, ' +
            '<a href="https://cadastre.data.gouv.fr/">Cadastre</a>, ' +
            'Imagery © <a href="https://www.ign.fr/">IGN</a>'
        });
        tileLayer.addTo(map)

        // GPS
        const gps = new L.Control.Gps({
            autoCenter:true
        });//inizialize control

        gps
        .on('gps:located', function(e) {
            // e.marker.bindPopup(e.latlng.toString()).openPopup()
        })
        .on('gps:disabled', function(e) {
            e.marker.closePopup()
        });

        gps.addTo(map);
        // Fin GPS

        const parcellesLayer = L.geoJSON(array_parcelles);
        parcellesLayer.addTo(map);
        map.fitBounds(parcellesLayer.getBounds());

        const controlSaveTiles = L.control.savetiles(tileLayer, {
            confirm(layer, succescallback) {
                // eslint-disable-next-line no-alert
                if (window.confirm(`Save ${layer._tilesforSave.length}`)) {
                    succescallback();
                }
            },
            confirmRemoval(layer, successCallback) {
                // eslint-disable-next-line no-alert
                if (window.confirm("Remove all the tiles?")) {
                    successCallback();
                }
            },
            saveText:
            '<span class="glyphicon glyphicon-floppy-disk"></span>',
            rmText:
            '<span class="glyphicon glyphicon-trash"></span>'
        });

        let tilesToSave = [];
        controlSaveTiles.addTo(map);
        for(layerIndex in parcellesLayer._layers) {
            let layer = parcellesLayer._layers[layerIndex];
            for(let zoom = 18; zoom >=8; zoom--) {
                    const area = L.bounds(map.project(layer.getBounds().getNorthWest(), zoom), map.project(layer.getBounds().getSouthEast(), zoom));
                    tilesToSave = tilesToSave.concat(tileLayer.getTileUrls(area, zoom));
            }
        }

        let uniqTiles = [];
        for(tile of tilesToSave) {
            uniqTiles[tile.key] = tile
        }
        let tiles = [];
        for(tileIndex in uniqTiles) {
            tiles.push(uniqTiles[tileIndex])
        }
        const loader = () => __awaiter(this, void 0, void 0, function* () {
                const tile = tiles.shift();
                if (tile === undefined) {
                    return Promise.resolve();
                }
                const blob = yield controlSaveTiles._loadTile(tile);
                if (blob) {
                    yield controlSaveTiles._saveTile(tile, blob);
                }
                return loader();
            });
        const parallel = Math.min(tiles.length, controlSaveTiles.options.parallel);
        for (let i = 0; i < parallel; i += 1) {
            loader();
        }
    };

    function __awaiter(thisArg, _arguments, P, generator) {
        function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
        return new (P || (P = Promise))(function (resolve, reject) {
            function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
            function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
            function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
            step((generator = generator.apply(thisArg, _arguments || [])).next());
        });
    }
</script>
