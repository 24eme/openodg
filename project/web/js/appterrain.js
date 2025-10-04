const { createWebHashHistory, createRouter, useRoute, useRouter } = VueRouter

const controles = JSON.parse(localStorage.getItem("controles")) || {};
const defaultFunctions = {
    echoFloat(val, nbDecimal = 5) {
      return val ? Number(val).toFixed(nbDecimal) : '';
    }
};


listingTemplate.data = function() {
    return {
      controles: controles
    }
};

operateurTemplate.data = function() {
    const route = useRoute()

    return {
      controleCourant: controles[route.params.id]
    }
};

operateurTemplate.methods = {
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

parcelleTemplate.data = function() {
    const route = useRoute()

    return {
      controleCourant: controles[route.params.id],
      parcelleCourante: controles[route.params.id].parcelles[route.params.parcelle]
    }
};
parcelleTemplate.methods = {
  save() {
    this.parcelleCourante.controle.saisie = 1;
    router.push({ name: 'operateur', params: { id: this.controleCourant._id } })
  },
  echoFloat(val, nbDecimal = 5) {
    return val ? Number(val).toFixed(nbDecimal) : '';
  }
};

auditTemplate.data = function() {
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
auditTemplate.methods = {
  save() {
    router.push({ name: 'operateur', params: { id: this.controleCourant._id } })
  }
};

const routes = [
  { path: '/', name: "listing", component: listingTemplate },
  { path: '/:id', name: "operateur", component: operateurTemplate },
  { path: '/:id/audit', name: "audit", component: auditTemplate },
  { path: '/:id/parcelle/:parcelle', name: "parcelle", component: parcelleTemplate },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
})

const { createApp } = Vue;
const App = {
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
}

createApp(App)
  .use(router)
  .mount('#content')
