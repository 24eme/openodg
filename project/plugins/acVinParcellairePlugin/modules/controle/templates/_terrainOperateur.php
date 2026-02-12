<h3 class="mt-0"><RouterLink :to="{ name: 'listing' }"><span class="glyphicon glyphicon-chevron-left"></span></RouterLink> {{ controleCourant.declarant.nom }} <RouterLink :to="{ name: 'map' }" class="pull-right"><span class="glyphicon glyphicon-map-marker"></span></RouterLink></h3>
<hr class="mt-2" />

<?php include_partial('controle/terrainBlocDeclarant'); ?>

<hr />

<h2>Parcelles contrôlées <span class="badge">{{ nbParcellesControlees() }} / {{ Object.keys(controleCourant.parcelles).length }}</span></h2>

<div class="list-group mt-4">
    <RouterLink v-for="(parcelle, key, index) in controleCourant.parcelles" :to="{ name: 'parcelle', params: { id: controleCourant._id, parcelle: key }}" class="list-group-item" :class="{ 'list-group-item-success': parcelle.controle.saisie == 1 }">
        <div class="row">
            <div class="col-xs-2 col-md-1" style="font-size: 20px;">
                <strong>N° {{ index + 1 }}</strong>
            </div>
            <div class="col-xs-8 col-md-9">
                <h4 class="list-group-item-heading">{{ parcelle.cepage }} <small><br />{{ parcelle.source_produit_libelle }}<br />{{ parcelle.campagne_plantation }}</small></h4>
                <p class="list-group-item-text">{{ parcelle.commune }} {{ parcelle.lieu }}</p>
                <div class="mt-2">
                    <label class="label label-primary" :class="{ 'label-success': parcelle.controle.saisie == 1 }">
                    {{ echoFloat(parcelle.superficie, 2) }} ha
                    </label>
                </div>
            </div>
            <div class="col-xs-2 text-right" :class="{ 'text-primary': parcelle.controle.saisie != 1 }">
                <span class="glyphicon glyphicon-chevron-right h1"></span>
            </div>
        </div>
    </RouterLink>
</div>

<div class="row">
    <div class="col-xs-4">
    <RouterLink class="btn btn-default" :to="{ name: 'listing' }"><span class="glyphicon glyphicon-chevron-left"></span> Retour</RouterLink>
    </div>
    <div class="col-xs-4 text-center">
        <button v-if="validationSent" class="btn btn-default btn-success" @click="transmitDataControle()"><span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;Données transmises</button>
        <button v-else class="btn btn-default" @click="transmitDataControle()"><span class="glyphicon glyphicon-send"></span>&nbsp;&nbsp;Transmettre les données</button>
    </div>
    <div class="col-xs-4 text-right">
    <button class="btn btn-primary" @click="startAudit()"><span class="glyphicon glyphicon-edit"></span> Saisir l'audit</button>
    </div>
</div>
