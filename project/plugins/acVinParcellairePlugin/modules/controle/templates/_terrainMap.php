<div class="mt-0 text-center">
    <div class="pull-right" >
        <a href="#" onclick="history.go(-1);return false;"><span class="glyphicon glyphicon-remove"></span></a>
    </div>
    <h3>Carte</h3>
</div>
<hr class="mt-2 mb-4" />
<div id="map" style="height: 70vh;"></div>
<div class="col-xs-offset-10 col-xs-2 mt-2" >
    <button class="btn btn-default btn-sm pull-right mr-4" @click="downloadKml">Télécharger KML</button>
</div>
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
