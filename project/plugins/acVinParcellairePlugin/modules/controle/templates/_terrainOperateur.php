<?php include_partial('controle/terrainBlocDeclarant'); ?>

<h2>Parcelles contrôlées ({{ nbParcellesControlees() }} / {{ Object.keys(controleCourant.parcelles).length }})</h2>
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
        <tr v-for="(parcelle, key) in controleCourant.parcelles" :class="{ 'success': parcelle.controle.saisie == 1 }">
            <td>{{ parcelle.commune }}</td>
            <td>{{ parcelle.lieu }}</td>
            <td class="text-center">{{ parcelle.section }} {{ parcelle.numero_parcelle }}<br /><span class="text-muted">{{ parcelle.parcelle_id }}</span></td>
            <td><span class="text-muted">{{ parcelle.source_produit_libelle }}</span><br />{{ parcelle.cepage }}</td>
            <td class="text-center">{{ parcelle.campagne_plantation }}</td>
            <td class="text-right">{{ echoFloat(parcelle.superficie) }}</td>
            <td class="text-center">{{ parcelle.ecart_pieds }} / {{ parcelle.ecart_rang }}</td>
            <td class="text-center">
                <RouterLink :to="{ name: 'parcelle', params: { id: controleCourant._id, parcelle: key }}"><span v-show="!parcelle.controle.saisie">Saisir</span><span v-show="parcelle.controle.saisie">Voir</span></RouterLink>
            </td>
        </tr>
    </tbody>
</table>

<RouterLink class="btn btn-default" :to="{ name: 'listing' }"><span class="glyphicon glyphicon-chevron-left"></span> Retour</RouterLink>
<button class="btn btn-primary pull-right" @click="startAudit()" :disabled="nbParcellesControlees() != Object.keys(controleCourant.parcelles).length"><span class="glyphicon glyphicon-edit"></span> Saisir l'audit</button>
