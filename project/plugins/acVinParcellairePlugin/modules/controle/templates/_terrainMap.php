<h3 class="mt-0"><a href="javascript: void(0)" @click="$router.back()"><span class="glyphicon glyphicon-chevron-left"></span></a> Carte</h3>
<hr class="mt-2 mb-4" />
<div id="map" style="height: 70vh;"></div>
<hr />
<RouterLink :to="{ name: 'map' }" v-if="idu">Supprimer la séléction</RouterLink>
<div class="list-group mt-4">
    <template v-for="(parcelle, key) in parcelles">
    <RouterLink :to="{ name: 'map_parcelle', params: { idu: parcelle.idu }}" v-if="!idu ||parcelle.idu == idu" class="list-group-item" :class="{ 'list-group-item-success': parcelle.controle.saisie == 1 }">
        <div class="row">
            <div class="col-xs-2 text-left" :class="{'text-primary': idu}">
                <span class="glyphicon glyphicon-map-marker h1"></span>
            </div>
            <div class="col-xs-8">
                <h4 class="list-group-item-heading">{{ parcelle.cepage }} <small><br />{{ parcelle.source_produit_libelle }}<br />{{ parcelle.campagne_plantation }}</small></h4>
                <p class="list-group-item-text">{{ parcelle.commune }} {{ parcelle.lieu }}</p>
                <div class="mt-2">
                    <label class="label label-default" :class="{'label-primary': idu}">
                    {{ echoFloat(parcelle.superficie, 2) }} ha
                    </label>
                </div>
            </div>
            <div class="col-xs-2 text-right" :class="{'hidden': !idu }">
                <RouterLink :to="{ name: 'parcelle', params: { id: parcelle.controle_id, parcelle: parcelle.parcelle_id }}"><span class="glyphicon glyphicon-chevron-right h1"></span></RouterLink>
            </div>
        </div>
    </RouterLink>
    </template>
</div>
