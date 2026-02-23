<h3 class="mt-0">Carte<RouterLink :to="{ name: 'listing' }" class="pull-right"><span class="glyphicon glyphicon-remove"></span></RouterLink><button class="btn btn-default btn-sm pull-right mr-4" @click="downloadKml">Télécharger KML</button></h3>
<hr class="mt-2 mb-4" />
<div id="map" style="height: 70vh;"></div>
<hr />
<RouterLink :to="{ name: 'map' }" v-if="idu">Supprimer la séléction</RouterLink>
<div class="list-group mt-4">
    <template v-for="(parcelle, key) in parcelles">
    <RouterLink :to="{ name: 'map_parcelle', params: { idu: parcelle.idu }}" v-if="!idu ||parcelle.idu == idu" class="list-group-item" :class="{ 'list-group-item-success': parcelle.controle.saisie == 1 }">
        <div class="row">
            <div class="col-xs-2 text-left" :class="{'text-primary': idu && parcelle.controle.saisie != 1 }">
                <span class="glyphicon glyphicon-map-marker h1"></span>
            </div>
            <div class="col-xs-8">
                <h4 class="list-group-item-heading">{{ parcelle.cepage }} <small><br />{{ parcelle.source_produit_libelle }}<br />{{ parcelle.campagne_plantation }}</small></h4>
                <p class="list-group-item-text">{{ parcelle.commune }} {{ parcelle.lieu }}</p>
                <div class="mt-2">
                    <label class="label label-default" :class="{'label-primary': idu && parcelle.controle.saisie != 1, 'label-success': parcelle.controle.saisie == 1}">
                    {{ echoFloat(parcelle.superficie, 2) }} ha
                    </label>
                </div>
            </div>
            <div class="col-xs-2 text-right" :class="{'hidden': !idu }">
                <RouterLink :to="{ name: 'parcelle', params: { id: parcelle.controle_id, parcelle: parcelle.parcelle_id }}" :class="{ 'text-success': parcelle.controle.saisie == 1 }"><span class="glyphicon glyphicon-chevron-right h1"></span></RouterLink>
            </div>
        </div>
    </RouterLink>
    </template>
</div>
