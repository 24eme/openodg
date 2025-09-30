<div id="app">
    <h1>Parcelles à contrôler {{ controle.identifiant }}</h1>

    <div v-if="page === 'parcelles'">

        <table class="table table-bordered table-condensed table-striped tableParcellaire">
            <thead>
                <tr>
                    <th class="col-xs-2">Commune</th>
                    <th class="col-xs-2">Lieu-dit</th>
                    <th class="col-xs-1" style="text-align: right;">Section</th>
                    <th class="col-xs-1">N° parcelle</th>
                    <th class="col-xs-3">Cépage</th>
                    <th class="col-xs-1">Année plantat°</th>
                    <th class="col-xs-1" style="text-align: right;">Surface <span class="text-muted small"><?php echo ParcellaireConfiguration::getInstance()->isAres() ? 'ares' : 'ha' ?></span></th>
                    <th class="col-xs-1 text-center">Statut</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(parcelle, key) in controle.parcelles" class="parcellerow switch-to-higlight" >
                    <td>{{ parcelle.commune }}</td>
                    <td>{{ parcelle.lieu }}</td>
                    <td style="text-align: right;">{{ parcelle.section }}</td>
                    <td>{{ parcelle.numero_parcelle }}</td>
                    <td><span class="text-muted">{{ parcelle.source_produit_libelle }}</span> {{ parcelle.cepage }}</td>
                    <td class="text-center">{{ parcelle.campagne_plantation }}</td>
                    <td class="text-right">{{ parcelle.superficie }}</td>
                    <td class="text-center">
                        <select v-model="parcelle.controle.statut">
                            <option value=""></option>
                            <option value="ATTENTE">En attente / Controlé</option>
                            <option value="CONFORME">Conforme</option>
                            <option value="NONCONFORME">Non conforme</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="row row-margin row-button">
            <div class="col-xs-8"></div>
            <div class="col-xs-4 text-right"><button @click="page = 'pointsControle'" class="btn btn-primary btn-upper">Continuer <span class="glyphicon glyphicon-chevron-right"></span></button></div>
        </div>
    </div>
    <div v-else-if="page === 'pointsControle'">

        <ul class="nav nav-tabs">
            <li role="presentation" v-for="i in parcellesNonConformes" :i="i" @click="currentKey = i" :class="{active: currentKey === i}"><a href="#">{{ i }}</a></li>
        </ul>
        <form class="form-horizontal" v-if="currentKey">
            <div class="form-group">
                <label class="col-sm-2 control-label">Point résultat</label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                      <input type="radio" value="C" v-model="parcelleCourante.controle.pt_resultat"> (C)
                    </label>
                    <label class="radio-inline">
                      <input type="radio" value="NC" v-model="parcelleCourante.controle.pt_resultat"> (NC)
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Observation</label>
                <div class="col-sm-10">
                    <textarea v-model="parcelleCourante.controle.observation" rows="4" cols="40"></textarea>
                </div>
            </div>
        </form>

        <div class="row row-margin row-button">
            <div class="col-xs-4 text-left"><button @click="page = 'parcelles'" class="btn btn-secondary btn-upper"> <span class="glyphicon glyphicon-chevron-left"></span> Retour</button></div>
            <div class="col-xs-8"></div>
        </div>
    </div>
    <div v-else>
        <p>Aucun résultat</p>
    </div>
  </div>

<script>

const CONTROLE_ID = "<?php echo $controle->_id ?>";

const { createApp } = Vue;

createApp({
  data() {
    return {
      page: "parcelles",
      controle: {},
      currentKey: null
    };
  },
  mounted() {
    const controles = JSON.parse(localStorage.getItem("controles")) || {};
    this.controle = controles[CONTROLE_ID] || [];
    for (let key in this.controle.parcelles) {
        if (!("controle" in this.controle.parcelles[key])) {
            this.controle.parcelles[key].controle = {
                'statut': null,
                'observation': null,
                'pt_resultat': null
            };
        } else {
            if (!this.currentKey && this.controle.parcelles[key].controle.statut === "NONCONFORME") {
                this.currentKey = key;
            }
        }
    }
  },
  watch: {
    controle: {
      handler(newVal) {
        const controles = JSON.parse(localStorage.getItem("controles")) || {};
        controles[CONTROLE_ID] = newVal;
        localStorage.setItem("controles", JSON.stringify(controles));
        console.log("Données sauvegardées");
      },
      deep: true
    }
  },
  computed: {
     parcellesNonConformes() {
       return Object.keys(this.controle.parcelles || {}).filter(k => this.controle.parcelles[k].controle.statut === "NONCONFORME");
     },
     parcelleCourante() {
       return this.controle.parcelles[this.currentKey];
     }
   },
}).mount("#app");

</script>
