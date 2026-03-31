<script id="dataJson" type="application/json">
<?php echo $sf_data->getRaw('json') ?>
</script>
<script id="dataConf" type="application/json">
<?php echo $sf_data->getRaw('points_de_controle') ?>
</script>
<script>
    const { createWebHashHistory, createRouter, useRoute, useRouter } = VueRouter
    const { createApp } = Vue;

    const templates = [];

    <?php foreach(['listing', 'map', 'operateur', 'parcelle', 'audit'] as $template): ?>
        templates["<?php echo $template ?>"] = { template: "<?php echo str_replace(['"', "\n"], ['\"', ""], get_partial('controle/terrain'.ucfirst($template))) ?>" }
    <?php endforeach; ?>

    const date_tournee = "<?php echo $date_tournee ?>"
    let controles = JSON.parse(localStorage.getItem("controles_" + date_tournee)) || {}
    let no_by_default = {}

    submitNeedsToBeSaved(controles);

    const server_controle = JSON.parse(document.getElementById("dataJson").textContent);
    const points_de_controle = JSON.parse(document.getElementById("dataConf").textContent);
    let localstorage_updated = false;
    for (let i in server_controle) {
        if (controles[server_controle[i]._id]) {
            server_rev = server_controle[i]._rev.split('-')[0];
            local_rev = (controles[server_controle[i]._id]._rev) ? controles[server_controle[i]._id]._rev.split('-')[0] : 0;
            if (local_rev >= server_rev) {
                console.log(['ignore controle loading : exists and local storage rev is older', 'controle id', server_controle[i]._id, 'local_rev', local_rev, 'server_rev', server_rev, controles[server_controle[i]._id]]);
                continue;
            }
        }
        controles[server_controle[i]._id] = server_controle[i];
        localstorage_updated = true;
    }
    if (localstorage_updated) {
        localStorage.setItem("controles_" + date_tournee, JSON.stringify(controles));
    }

    const routes = [
      { path: '/', name: "listing", component: templates.listing },
      { path: '/map', name: "map", component: templates.map },
      { path: '/map/:idu', name: "map_parcelle", component: templates.map },
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
          return {
              controles: controles,
            }
        },
        computed: {
          isSynchro() {
            return this.checkNeedsToBeSaved(this.controles);
          }
        },

        methods: {
            checkNeedsToBeSaved(controles) {
              for (const controle of Object.values(controles)) {
                if (controle.audit.needs_to_be_saved === true) {
                  return false;
                }

                for (const parcelle of Object.values(controle.parcelles)) {
                  if (parcelle.needs_to_be_saved === true) {
                    return false;
                  }
                }
              }

              return true;
            }
        },
        template: '<RouterView :key="$route.fullPath" />',
        // watch: {
        //   controles: {
        //     handler(newControles) {
        //       if (newControles) {
        //           localStorage.setItem("controles_" + date_tournee, JSON.stringify(newControles));
        //       }
        //     },
        //     deep: true
        //   }
        // },
      });
    app.use(router)
    app.mount('#content')

    templates.listing.mounted = function() {
        submitNeedsToBeSaved(controles);
    }

    templates.listing.data = function() {
        return {
            controles: controles,
            date_tournee: date_tournee
        }
    };

    templates.listing.computed = {
        agentIdentifiant() {
            const path = window.location.pathname
            const parts = path.split('/').filter(Boolean)
            return parts[parts.length - 1]
        },

        filteredControles() {
            return Object.values(this.controles).filter(c =>
                c.agent_identifiant === this.agentIdentifiant
            )
        }
    }

    templates.listing.methods = {
        nbParcellesControlees(controleCible) {
          return (Object.keys(controleCible.parcelles || {}).filter(k => controleCible.parcelles[k].controle.saisie == 1)).length;
      },
      libelleTournee() {
          const items = Object.values(controles);
          if (!items.length) return '';
          const [y, m, d] = items[0].date_tournee.split('-');
          const agent = items[0].agent_libelle;
          return `Tournée du ${d}/${m}/${y} par ${agent}`;
      }
    };

    templates.operateur.mounted = function() {
        submitNeedsToBeSaved(controles);
    }
    templates.operateur.data = function() {
        const route = useRoute()

        return {
          controleCourant: controles[route.params.id],
        }
    };
    templates.operateur.methods = {
      countConstatForThisParcelle(parcelleCouranteId) {
          let ret = 0;
          const parcelleCourante = this.controleCourant.parcelles[parcelleCouranteId];
          for (const pointKey in parcelleCourante.controle.points) {
              const point = parcelleCourante.controle.points[pointKey];
              if (point.conformite != 'NC') {continue;}
              for (const constatKey in point.constats) {
                  const constat = point.constats[constatKey];
                  if (constat.conformite == true) {
                      ret += 1;
                  }
              }
          }
          return ret;
      },
      nbParcellesControlees() {
        return (Object.keys(this.controleCourant.parcelles || {}).filter(k => this.controleCourant.parcelles[k].controle.saisie == 1)).length;
      },
      startAudit() {
        router.push({ name: 'audit', params: { id: this.controleCourant._id } })
      },
      echoFloat(val, nbDecimal = 5) {
        return val ? Number(val).toFixed(nbDecimal) : '';
      },
      printableSiret() {
          return this.controleCourant.declarant.siret.substring(0,3)+" "+
                 this.controleCourant.declarant.siret.substring(3,6) +" "+
                 this.controleCourant.declarant.siret.substring(6,9) +" "+
                 this.controleCourant.declarant.siret.substring(9);
      },
      libelleTournee() {
            const [y, m, d] = this.controleCourant.date_tournee.split('-');
            const heure = this.controleCourant.heure_tournee;
            const agent = this.controleCourant.agent_libelle;
            return `Tournée du ${d}/${m}/${y} à ${heure} par ${agent}`;
      }
    };

    templates.parcelle.mounted = function() {
        submitNeedsToBeSaved(controles);
    }

    templates.parcelle.data = function() {
        const route = useRoute()
        for (const pointKey in controles[route.params.id].parcelles[route.params.parcelle].controle.points) {
            const point = controles[route.params.id].parcelles[route.params.parcelle].controle.points[pointKey];
            if (no_by_default[pointKey]) {
                point.conformite = 'NO';
            }
        }
        return {
          controleCourant: controles[route.params.id],
          parcelleCourante: controles[route.params.id].parcelles[route.params.parcelle],
          pointsDeControle: points_de_controle,
          date_tournee: date_tournee
        }
    };
    templates.parcelle.methods = {
        allOtherConforme() {
            for (const pointKey in this.parcelleCourante.controle.points) {
                const point = this.parcelleCourante.controle.points[pointKey];
                if (point.conformite) {
                    continue;
                }
                point.conformite = 'C';
            }
        },
        save() {
            this.parcelleCourante.controle.saisie = 1;
            this.parcelleCourante.needs_to_be_saved = true;
            for (const pointKey in this.parcelleCourante.controle.points) {
                if (this.parcelleCourante.controle.points[pointKey].conformite == 'NO') {
                    no_by_default[pointKey] = 1;
                } else {
                    no_by_default[pointKey] = 0;
                }
            }
            router.push({ name: 'operateur', params: { id: this.controleCourant._id } })
        },
        echoFloat(val, nbDecimal = 5) {
            return val ? Number(val).toFixed(nbDecimal) : '';
        },
        printableSiret() {
            return this.controleCourant.declarant.siret.substring(0,3)+" "+
                   this.controleCourant.declarant.siret.substring(3,6) +" "+
                   this.controleCourant.declarant.siret.substring(6,9) +" "+
                   this.controleCourant.declarant.siret.substring(9);
        },
        libelleTournee() {
            const [y, m, d] = this.controleCourant.date_tournee.split('-');
            const heure = this.controleCourant.heure_tournee;
            const agent = this.controleCourant.agent_libelle;
            const parcelle = this.parcelleCourante.parcelle_id;
            return `Tournée du ${d}/${m}/${y} à ${heure} par ${agent} parcelle ${parcelle} `;
        }
    };
    templates.audit.mounted = function() {
        submitNeedsToBeSaved(controles);
        let signatureBase64 = null;
        const controleCourant = this.controleCourant;
        const signaturePad = new SignaturePad(document.getElementById('signature'), {
            penColor: 'rgb(0, 0, 0)',
            minWidth: 1,
            maxWidth: 2,
            onEnd: function () {
                if (!signaturePad.isEmpty()) {
                    controleCourant.audit.operateur_signature = signaturePad.toDataURL();
                }
            }
        });
        document.getElementById('signature-pad-reset').addEventListener('click', function () {
            signaturePad.clear();
            controleCourant.audit.operateur_signature = null;
        });
        if (controleCourant.audit.operateur_signature) {
            signaturePad.fromDataURL(controleCourant.audit.operateur_signature);
        }
    }
    templates.audit.data = function() {
        const route = useRoute()
        if(!controles[route.params.id].audit) {
          controles[route.params.id].audit = {}
        }
        return {
          controleCourant: controles[route.params.id]

        }
    };
    templates.audit.methods = {
      countPointsTotal() {
          let ret = 0;
          for (const parcelleId in this.controleCourant.parcelles) {
              const parcelle = this.controleCourant.parcelles[parcelleId];
              ret += Object.entries(parcelle.controle.points).length;
          }
          return ret;
      },
      countPointsNCetGetLibelles() {
          let ret = {nombreNC:0, manquements:[]};
          for (const parcelleId in this.controleCourant.parcelles) {
              const parcelle = this.controleCourant.parcelles[parcelleId];
              for (const pointKey in parcelle.controle.points) {
                  const point = parcelle.controle.points[pointKey];
                  if (point.conformite == 'NC') {
                      ret.nombreNC += 1;
                      for (const constatKey in point.constats) {
                          const constat = point.constats[constatKey];
                          if (! constat.conformite) {
                              continue ;
                          }
                          ret.manquements.push(point.libelle + "\n" + constat.libelle + ' - ' + constatKey + "\n" + parcelleId + ' - '+ constat.observations);
                      }
                  }
              }
          }
          return ret;
      },
      save() {
        this.controleCourant.audit.saisie = 1;
        this.controleCourant.audit.needs_to_be_saved = true;
        router.push({ name: 'operateur', params: { id: this.controleCourant._id } })
    },
      devalider() {
          this.controleCourant.audit.saisie = 0;
          this.controleCourant.audit.needs_to_be_saved = true;
      },
      printableSiret() {
          return this.controleCourant.declarant.siret.substring(0,3)+" "+
                 this.controleCourant.declarant.siret.substring(3,6) +" "+
                 this.controleCourant.declarant.siret.substring(6,9) +" "+
                 this.controleCourant.declarant.siret.substring(9);
      }

    };
    templates.map.data = function() {
        const route = useRoute()
        let parcelles = {};
        for(let id in controles) {
            const controle = controles[id]
            for(let parcelleId in controle.parcelles) {
                parcelles[parcelleId] = controle.parcelles[parcelleId]
                parcelles[parcelleId].controle_id = id;
            }
        }
        return {
          parcelles: parcelles,
          idu: route.params.idu
        }
    };
    templates.map.methods = {
        downloadKml() {
          kml_content = '<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2"><Document>';
          kml_content += '<Style id="parcelle-style"><LineStyle><width>2</width></LineStyle><PolyStyle><color>7d0000ff</color></PolyStyle></Style>';
          for(let keyc in controles) {
              for(let keyp in controles[keyc].parcelles) {
                  kml_content += controles[keyc].parcelles[keyp].kml_placemark;
              }
          }
          kml_content += '</Document></kml>';

          const blob = new Blob([kml_content], { type: 'application/vnd.google-earth.kml+xml' });
          const url = window.URL.createObjectURL(blob);
          const link = document.createElement('a');
          link.href = url;
          link.download = `parcelles.kml`;
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          window.URL.revokeObjectURL(url);
        },
        echoFloat(val, nbDecimal = 5) {
            return val ? Number(val).toFixed(nbDecimal) : '';
        }
    };
    templates.map.mounted = function() {
        let data = templates.map.data();
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

        gps.addTo(map);

        let parcelles = data.parcelles

        let parcellesGeojson = { features: []}
        for(let parcelleId in parcelles) {
            let feature = JSON.parse(parcelles[parcelleId].geojson);
            if(parcelles[parcelleId].controle.saisie) {
                feature.properties.success = true
            }
            parcellesGeojson.features.push(feature)
        }
        const parcellesLayer = L.geoJSON(parcellesGeojson, { style: {
            fillColor: 'red',
            weight: 3,
            opacity: 1,
            color: 'red',
            fillOpacity: 0.15
        }, onEachFeature: function (feature, layer) {
            layer.on({
                click: function(e) {
                    router.push({ name: 'map_parcelle', params: { idu: feature.id } })
                }
            });
            if(feature.properties.success) {
                layer.setStyle({color: 'green', fillColor: 'green'});
            }
            if(feature.id == data.idu) {
                layer.setStyle({color: 'blue'});
                map.fitBounds(layer.getBounds());
                map.setZoom(map.getZoom() - 1);
            }
        }
        });

        parcellesLayer.addTo(map);
        if(!data.idu) {
            map.fitBounds(parcellesLayer.getBounds());
        }
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

    function submitNeedsToBeSaved(controles) {
      for (const controle of Object.values(controles)) {
        let reloadStatus = false;

        if (controle.audit.needs_to_be_saved === true) {

          const response = submitElement(controle, null, controle.audit);
          if (response && response.success === true) {
            if (response.reloadStatus) {
                  reloadStatus = true;
            }
            localStorage.setItem("controles_" + date_tournee, JSON.stringify(controles));
          }
        }

        for (const parcelle of Object.values(controle.parcelles)) {

          if (parcelle.needs_to_be_saved === true) {

            localStorage.setItem("controles_" + date_tournee, JSON.stringify(controles));

            const response = submitElement(controle, parcelle.parcelle_id, parcelle.controle);
            if (response && response.success === true) {
              if (response.reloadStatus) {
                  reloadStatus = true;
              }
              localStorage.setItem("controles_" + date_tournee, JSON.stringify(controles));
            }
          }
        }
        if (reloadStatus) {
            controle._rev = "00-Needs Update";
            alert("Rev app < Rev couchdb - Rechargez l'app");
        }
      }
    }

    function submitElement(controle, idParcelle, element)
    {
      try {
        const revision = controle._rev;
        const idControle = controle._id;
        const response = fetch('<?php echo url_for('controle_transmission_data'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                revision,
                idControle,
                idParcelle,
                element
            })
        });

        const data = response.json();
        controle._rev = data.revision;
        element.needs_to_be_saved = false;
        return data;
      } catch(exception) {
        console.log(['submitElement exception', exception]);
        return { success: false };
      }
    }

</script>
