<h3 class="mt-0"><span class="glyphicon glyphicon-chevron-left"></span> Carte des opérateurs</h3>
<hr class="mt-2 mb-4" />
<div id="map" style="height: 70vh;"></div>
<hr />

<table class="table table-bordered table-stripped">
    <thead>
        <tr>
            <th>Opérateur</th>
        </tr>
    </thead>
    <tbody>
        <tr v-for="controle in controles">
            <td>{{ controle.declarant.nom }}<td>
        </tr>
    </tbody>
</table>
