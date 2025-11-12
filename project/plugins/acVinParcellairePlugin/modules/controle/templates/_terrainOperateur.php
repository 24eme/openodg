<h3 class="mt-0"><RouterLink :to="{ name: 'listing' }"><span class="glyphicon glyphicon-chevron-left"></span></RouterLink> {{ controleCourant.declarant.nom }} <RouterLink :to="{ name: 'map' }" class="pull-right"><span class="glyphicon glyphicon-map-marker"></span></RouterLink></h3>
<hr class="mt-2" />

<?php include_partial('controle/terrainBlocDeclarant'); ?>

<hr />

<h2>Parcelles contrôlées <span class="badge">{{ nbParcellesControlees() }} / {{ Object.keys(controleCourant.parcelles).length }}<span></h2>

<div class="list-group mt-4">
    <RouterLink v-for="(parcelle, key) in controleCourant.parcelles" :to="{ name: 'parcelle', params: { id: controleCourant._id, parcelle: key }}" class="list-group-item">
        <div class="row">
            <div class="col-xs-10">
                <h4 class="list-group-item-heading">{{ parcelle.cepage }} <small><br />{{ parcelle.source_produit_libelle }}<br />{{ parcelle.campagne_plantation }}</small></h4>
                <p class="list-group-item-text">{{ parcelle.commune }} {{ parcelle.lieu }}</p>
                <div class="mt-2">
                    <label class="label label-primary">
                    {{ echoFloat(parcelle.superficie, 2) }} ha
                    </label>
                </div>
            </div>
            <div class="col-xs-2 text-primary text-right">
                <span class="glyphicon glyphicon-chevron-right h1"></span>
            </div>
        </div>
    </RouterLink>
</div>

<RouterLink class="btn btn-default" :to="{ name: 'listing' }"><span class="glyphicon glyphicon-chevron-left"></span> Retour</RouterLink>
<button class="btn btn-primary pull-right" @click="startAudit()" :disabled="nbParcellesControlees() != Object.keys(controleCourant.parcelles).length"><span class="glyphicon glyphicon-edit"></span> Saisir l'audit</button>
