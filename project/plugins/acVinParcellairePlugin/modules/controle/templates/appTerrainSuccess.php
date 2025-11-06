<script id="dataJson" type="application/json">
<?php echo $sf_data->getRaw('json') ?>
</script>
<script>
    const { createWebHashHistory, createRouter, useRoute, useRouter } = VueRouter
    const { createApp } = Vue;

    const templates = [];

    <?php foreach(['listing', 'operateur', 'parcelle', 'audit'] as $template): ?>
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

    const routes = [
      { path: '/', name: "listing", component: templates.listing },
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
</script>
