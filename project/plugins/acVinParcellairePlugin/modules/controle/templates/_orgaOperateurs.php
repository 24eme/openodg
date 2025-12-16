<h3 class="mt-0"><span class="glyphicon glyphicon-chevron-left"></span> Carte des opérateurs</h3>
<hr class="mt-2 mb-4" />
<div id="map" style="height: 70vh;"></div>
<hr />

<table class="table table-bordered table-stripped">
    <thead>
        <tr>
            <th style="width: 0;"></th>
            <th>Opérateur</th>
            <th class="text-center" style="width: 0;">Parcelles&nbsp;sélectionnées</th>
            <th class="text-center" style="width: 0;">Heure&nbsp;de&nbsp;début</th>
        </tr>
    </thead>
    <tbody>
        <tr v-for="(controle, numero) in getControlesSorted()">
            <td><span class="label label-primary lead" style="border-radius: 24px;">{{ numero + 1 }}</span></td>
            <td>{{ controle.declarant.nom }}</td>
            <td class="text-center">{{ nbParcellesSelectionnees(controle._id) }} parcelles</td>
            <td class="text-center"><input type="time" :value='(10 + numero) + ":00"' /></td>
        </tr>
    </tbody>
</table>
