<h3 class="mt-0"><RouterLink :to="{ name: 'operateur', params: { id: controleCourant._id } }"><span class="glyphicon glyphicon-chevron-left"></span></RouterLink> {{ parcelleCourante.parcelle_id }} <RouterLink :to="{ name: 'map_parcelle', params: { idu: parcelleCourante.idu }}" class="pull-right"><span class="glyphicon glyphicon-map-marker"></span></RouterLink></h3>
<hr class="mt-2" />

<?php include_partial('controle/terrainBlocDeclarant'); ?>

<hr />

<div class="table-responsive">
    <table class="table table-bordered table-condensed">
        <thead>
            <tr class="active">
                <th colspan="2">
                    <h4 class="strong" style="margin: 0;">Parcelle n° {{ parcelleCourante.parcelle_id }}</h4>
                </th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td><strong>Section / N° parcelle</strong></td>
                <td>{{ parcelleCourante.section }} {{ parcelleCourante.numero_parcelle }}</td>
            </tr>
            <tr>
                <td><strong>Superficie</strong></td>
                <td>{{ echoFloat(parcelleCourante.superficie) }} <span class="text-muted">ha</span></td>
            </tr>
            <tr>
                <td><strong>Commune / Lieu-dit</strong></td>
                <td>{{ parcelleCourante.commune }} {{ parcelleCourante.lieu }}</td>
            </tr>
            <tr>
                <td><strong>Cépage</strong></td>
                <td>{{ parcelleCourante.cepage }}</td>
            </tr>
            <tr>
                <td><strong>Appellation</strong></td>
                <td>{{ parcelleCourante.source_produit_libelle }}</td>
            </tr>
            <tr>
                <td><strong>Année de plantation</strong></td>
                <td>{{ parcelleCourante.campagne_plantation }}</td>
            </tr>
            <tr>
                <td><strong>Écart Pieds/Rang</strong></td>
                <td>{{ parcelleCourante.ecart_pieds }} / {{ parcelleCourante.ecart_rang }}</td>
            </tr>
            <tr>
                <td><strong>Manquants</strong></td>
                <td>{{ parcelleCourante.pourcentage }}%</td>
            </tr>

            <tr v-if="parcelleCourante.irrigation.materiel.length">
                <td><strong>Irrigation</strong></td>
                <td>
                    <div><strong>Matériel :</strong> {{ parcelleCourante.irrigation.materiel }}</div>
                    <div><strong>Ressource :</strong> {{ parcelleCourante.irrigation.ressource }}</div>
                </td>
            </tr>
            <tr v-else>
                <td><strong>Irrigation</strong></td>
                <td class="text-muted">Pas d'irrigation</td>
            </tr>
        </tbody>
    </table>
</div>

<a href="" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-road" /> Ouvrir sur le GPS</a>
<hr />

<h2>Points de contrôle</h2>
<form class="form-horizontal">
   <div class="form-group" v-for="(val, key) in parcelleCourante.controle.points" :key="key">
       <label class="col-sm-2 control-label">{{ key }}</label>
       <div class="col-sm-10">
           <label class="radio-inline">
             <input type="radio" value="C" v-model="parcelleCourante.controle.points[key]" /> Conforme
           </label>
           <label class="radio-inline">
             <input type="radio" value="NC" v-model="parcelleCourante.controle.points[key]" /> Non Conforme
           </label>
       </div>
   </div>

   <div class="form-group">
       <label class="col-sm-2 control-label">Observations</label>
       <div class="col-sm-10">
           <textarea rows="4" class="form-control" placeholder="Saisir les observations terrain" v-model="parcelleCourante.controle.observations"></textarea>
       </div>
   </div>

</form>

<hr />

<RouterLink class="btn btn-default" :to="{ name: 'operateur', params: { id: controleCourant._id } }"><span class="glyphicon glyphicon-chevron-left"></span> Retour</RouterLink>
<button class="btn btn-primary pull-right" @click="save()">Valider</button>
