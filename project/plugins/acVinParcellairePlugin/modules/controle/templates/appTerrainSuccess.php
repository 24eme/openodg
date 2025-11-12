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
        const tileLayer = L.tileLayer('https://data.geopf.fr/wmts?'+
        '&REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0&TILEMATRIXSET=PM'+
        '&LAYER={ignLayer}&STYLE={style}&FORMAT={format}'+
        '&TILECOL={x}&TILEROW={y}&TILEMATRIX={z}',
        {
          ignApiKey: 'pratique',
          ignLayer: 'ORTHOIMAGERY.ORTHOPHOTOS',
          style: 'normal',
          format: 'image/jpeg',
          service: 'WMTS',
          minZoom: 8,
          maxZoom: 19,
          attribution: 'Map data &copy;' +
          '<a href="https://www.24eme.fr/">24eme Société coopérative</a>, ' +
          '<a href="https://cadastre.data.gouv.fr/">Cadastre</a>, ' +
          'Imagery © <a href="https://www.ign.fr/">IGN</a>',
           id: 'mapbox.light'
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

        /*let tilesUrl = []
        for(layerIndex in parcellesLayer._layers) {
            let layer = parcellesLayer._layers[layerIndex];
            for(let zoom = 19; zoom >=8; zoom--) {
                const area = L.bounds(map.project(layer.getBounds().getNorthWest(), zoom), map.project(layer.getBounds().getSouthEast(), zoom));
                for(tile of getTileUrls(tileLayer, area, zoom)) {
                    tilesUrl[tile.url] = tile.url
                }
            }
        }
        for(tileUrl in tilesUrl) {
            fetch(tileUrl+'?'+tileUrl, { cache: "force-cache" })
        }*/
    };

    function getTileUrls(tileLayer, bounds, zoom) {
            var _a;
            const tiles = [];
            const tilePoints = getTilePoints(bounds, tileLayer.getTileSize());
            for (let index = 0; index < tilePoints.length; index += 1) {
                const tilePoint = tilePoints[index];
                const data = Object.assign(Object.assign({}), { x: tilePoint.x, y: tilePoint.y, z: zoom });
                tiles.push({
                    key: getTileUrl(tileLayer._url, Object.assign(Object.assign({}, data), { s: (_a = tileLayer.options.subdomains) === null || _a === void 0 ? void 0 : _a[0] })),
                    url: getTileUrl(tileLayer._url, Object.assign(Object.assign({}, data), {
                        // @ts-ignore: Undefined
                        s: tileLayer._getSubdomain(tilePoint) })),
                    z: zoom,
                    x: tilePoint.x,
                    y: tilePoint.y,
                    urlTemplate: L._url,
                    createdAt: Date.now(),
                });
            }
            return tiles;
    }

    function getTilePoints(area, tileSize) {
        const points = [];
        if (!area.min || !area.max) {
            return points;
        }
        const topLeftTile = area.min.divideBy(tileSize.x).floor();
        const bottomRightTile = area.max.divideBy(tileSize.x).floor();
        for (let j = topLeftTile.y; j <= bottomRightTile.y; j += 1) {
            for (let i = topLeftTile.x; i <= bottomRightTile.x; i += 1) {
                points.push(new L.Point(i, j));
            }
        }
        return points;
    }

    function getTileUrl(urlTemplate, data) {
        return L.Util.template(urlTemplate, Object.assign(Object.assign({}, data), { r: L.Browser.retina ? '@2x' : '' }));
    }
</script>
