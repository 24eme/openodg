<div id="app">

    <div v-if="controleCourant && !parcelleCourante">
        <div class="well">
            <div class="row">
                <div class="col-xs-12">
                    <div class="row">
                        <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">&nbsp;</div>
                        <div style="margin-bottom: 5px" class="col-xs-9">
                            <h4 class="strong">{{ controleCourant.declarant.nom }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="row">
                        <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                            Ids&nbsp;:
                        </div>
                        <div style="margin-bottom: 5px" class="col-xs-9">
                            <span class="text-muted">{{ controleCourant.identifiant }} - CVI : {{ controleCourant.declarant.cvi }} - SIRET : {{ controleCourant.declarant.siret }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="row">
                        <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                            Adresse&nbsp;:
                        </div>
                        <div style="margin-bottom: 5px" class="col-xs-9">
                            <address style="margin-bottom: 0;">
                                {{ controleCourant.declarant.adresse }} {{ controleCourant.declarant.code_postal }} {{ controleCourant.declarant.commune }}
                            </address>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="row">
                        <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                            Contact&nbsp;:
                        </div>
                        <div style="margin-bottom: 5px" class="col-xs-9">
                            <a href="mailto:{{ controleCourant.declarant.email }}">{{ controleCourant.declarant.email }}</a> / <a href="callto:{{ controleCourant.declarant.telephone_bureau }}">{{ controleCourant.declarant.telephone_bureau }}</a> / <a href="callto:{{ controleCourant.declarant.telephone_mobile }}">{{ controleCourant.declarant.telephone_mobile }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <h2>Parcelles ({{ Object.keys(controleCourant.parcelles).length }})</h2>
        <table class="table table-bordered table-condensed table-striped tableParcellaire">
            <thead>
                <tr>
                    <th class="col-xs-2">Commune</th>
                    <th class="col-xs-1">Lieu-dit</th>
                    <th class="col-xs-1 text-center">Section / N° parcelle</th>
                    <th class="col-xs-4">Cépage</th>
                    <th class="col-xs-1 text-center">Année plantat°</th>
                    <th class="col-xs-1 text-right">Superficie <span class="text-muted small">(ha)</span></th>
                    <th class="col-xs-1 text-center">Écart Pieds/Rang</th>
                    <th class="col-xs-1 text-center">Controle</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(parcelle, key) in controleCourant.parcelles">
                    <td>{{ parcelle.commune }}</td>
                    <td>{{ parcelle.lieu }}</td>
                    <td class="text-center">{{ parcelle.section }} {{ parcelle.numero_parcelle }}<br /><span class="text-muted">{{ parcelle.parcelle_id }}</span></td>
                    <td><span class="text-muted">{{ parcelle.source_produit_libelle }}</span><br />{{ parcelle.cepage }}</td>
                    <td class="text-center">{{ parcelle.campagne_plantation }}</td>
                    <td class="text-right">{{ echoFloat(parcelle.superficie) }}</td>
                    <td class="text-center">{{ parcelle.ecart_pieds }} / {{ parcelle.ecart_rang }}</td>
                    <td class="text-center">
                        <a href="#" @click.prevent="setParcelleCourante(key)">Saisir</a>
                    </td>
                </tr>
            </tbody>
        </table>

        <button class="btn btn-default" @click="controleCourant = null"><span class="glyphicon glyphicon-chevron-left"></span> Retour</button>
    </div>

    <div v-else-if="controleCourant && parcelleCourante">
        <div class="well">
            <div class="row">
                <div class="col-xs-12">
                    <div class="row">
                        <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">&nbsp;</div>
                        <div style="margin-bottom: 5px" class="col-xs-9">
                            <h4 class="strong">{{ controleCourant.declarant.nom }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="row">
                        <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                            Ids&nbsp;:
                        </div>
                        <div style="margin-bottom: 5px" class="col-xs-9">
                            <span class="text-muted">{{ controleCourant.identifiant }} - CVI : {{ controleCourant.declarant.cvi }} - SIRET : {{ controleCourant.declarant.siret }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="row">
                        <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                            Adresse&nbsp;:
                        </div>
                        <div style="margin-bottom: 5px" class="col-xs-9">
                            <address style="margin-bottom: 0;">
                                {{ controleCourant.declarant.adresse }} {{ controleCourant.declarant.code_postal }} {{ controleCourant.declarant.commune }}
                            </address>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="row">
                        <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                            Contact&nbsp;:
                        </div>
                        <div style="margin-bottom: 5px" class="col-xs-9">
                            <a href="mailto:{{ controleCourant.declarant.email }}">{{ controleCourant.declarant.email }}</a> / <a href="callto:{{ controleCourant.declarant.telephone_bureau }}">{{ controleCourant.declarant.telephone_bureau }}</a> / <a href="callto:{{ controleCourant.declarant.telephone_mobile }}">{{ controleCourant.declarant.telephone_mobile }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <h2>Parcelle <span class="text-muted small">{{ parcelleCourante.parcelle_id }}</span></h2>
        <table class="table table-bordered table-condensed table-striped tableParcellaire">
            <thead>
                <tr>
                    <th class="col-xs-2">Commune</th>
                    <th class="col-xs-1">Lieu-dit</th>
                    <th class="col-xs-1 text-center">Section / N° parcelle</th>
                    <th class="col-xs-4">Cépage</th>
                    <th class="col-xs-1 text-center">Année plantat°</th>
                    <th class="col-xs-1 text-right">Superficie <span class="text-muted small">(ha)</span></th>
                    <th class="col-xs-1 text-center">Écart Pieds/Rang</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ parcelleCourante.commune }}</td>
                    <td>{{ parcelleCourante.lieu }}</td>
                    <td class="text-center">{{ parcelleCourante.section }} {{ parcelleCourante.numero_parcelle }}</td>
                    <td><span class="text-muted">{{ parcelleCourante.source_produit_libelle }}</span> {{ parcelleCourante.cepage }}</td>
                    <td class="text-center">{{ parcelleCourante.campagne_plantation }}</td>
                    <td class="text-right">{{ echoFloat(parcelleCourante.superficie) }}</td>
                    <td class="text-center">{{ parcelleCourante.ecart_pieds }} / {{ parcelleCourante.ecart_rang }}</td>
                </tr>
            </tbody>
        </table>
        <h2>Points de contrôle</h2>
        <form class="form-horizontal">
           <div class="form-group" v-for="(val, key) in parcelleCourante.controle.points" :key="key">
               <label class="col-sm-2 control-label">{{ key }}</label>
               <div class="col-sm-10">
                   <label class="radio-inline">
                     <input type="radio" value="C" v-model="parcelleCourante.controle.points[key]" /> C
                   </label>
                   <label class="radio-inline">
                     <input type="radio" value="NC" v-model="parcelleCourante.controle.points[key]" /> NC
                   </label>
               </div>
           </div>


           <div class="form-group">
               <label class="col-sm-2 control-label">Observations</label>
               <div class="col-sm-10">
                   <textarea rows="4" class="form-control" placeholder="Saisir les observations terrain" v-model="parcelleCourante.controle.observations"></textarea>
               </div>
           </div>

          <div class="form-group">
              <label class="col-sm-2 control-label">Superficie à retirer (ha)</label>
              <div class="col-sm-10">
                  <input type="text" class="form-control" v-model="parcelleCourante.controle.superficie_a_retirer" />
              </div>
          </div>

       </form>

        <button class="btn btn-primary pull-right" @click="saveControle()">Valider</button>

    </div>

    <div v-else>
        <h2>Opérateurs à contrôler</h2>
        <table class="table table-bordered table-condensed table-striped tableParcellaire">
            <thead>
                <tr>
                    <th class="col-xs-4">Opérateur</th>
                    <th class="col-xs-5">Infos</th>
                    <th class="col-xs-1 text-center">Parcelles</th>
                    <th class="col-xs-1 text-center">Détail</th>
                    <th class="col-xs-1 text-center">Audit</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(controle, key) in controles">
                    <td>
                        <strong>{{ controle.declarant.nom }}</strong> <span class="small">{{ controle.identifiant }}</span><br />
                        <span class="text-muted">CVI {{ controle.declarant.cvi }} - SIRET {{ controle.declarant.siret }}</span>
                    </td>
                    <td>
                        {{ controle.declarant.adresse }} {{ controle.declarant.code_postal }} {{ controle.declarant.commune }}<br />
                        <a href="mailto:{{ controle.declarant.email }}">{{ controle.declarant.email }}</a> / <a href="callto:{{ controle.declarant.telephone_bureau }}">{{ controle.declarant.telephone_bureau }}</a> / <a href="callto:{{ controle.declarant.telephone_mobile }}">{{ controle.declarant.telephone_mobile }}</a>
                    </td>
                    <td class="text-center">
                        {{ Object.keys(controle.parcelles).length }}
                    </td>
                    <td class="text-center">
                        <a href="#" @click.prevent="setControleCourant(key)"><span class="glyphicon glyphicon-search"></span></a>
                    </td>
                    <td class="text-center">
                        <a href="#"><span class="glyphicon glyphicon-edit"></span></a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script>


const { createApp } = Vue;

createApp({
  data() {
    return {
      controles: {},
      controleCourant: null,
      parcelleCourante: null
    };
  },
  mounted() {
    this.controles = JSON.parse(localStorage.getItem("controles")) || {};
  },
  methods: {
    setControleCourant(id) {
      this.controleCourant = this.controles[id];
      this.parcelleCourante = null;
    },
    setParcelleCourante(id) {
        this.parcelleCourante = (this.controleCourant)? this.controleCourant.parcelles[id] : null;
    },
    saveControle() {
        this.parcelleCourante.controle.saisie = 1;
        this.parcelleCourante = null
    },
    nbParcellesControlees() {
        return (Object.keys(this.controleCourant.parcelles || {}).filter(k => this.controleCourant.parcelles[k].controle.saisie == 1)).length;
    },
    echoFloat(val, nbDecimal = 5) {
        return val ? Number(val).toFixed(nbDecimal) : '';
    },
  },
  watch: {
    controleCourant: {
      handler(newVal) {
        if (newVal) {
            const controles = JSON.parse(localStorage.getItem("controles")) || {};
            controles[newVal._id] = newVal;
            localStorage.setItem("controles", JSON.stringify(controles));
        }
      },
      deep: true
    }
  },
}).mount("#app");

</script>
