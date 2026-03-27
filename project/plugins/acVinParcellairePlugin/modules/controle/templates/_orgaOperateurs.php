<h3 class="mt-0">{{  libelleTournee() }}</h3>
<hr class="mt-2 mb-4" />
<div id="map" style="height: 70vh;"></div>
<hr />

<table class="table table-bordered table-stripped">
    <thead>
        <tr>
            <th style="width: 0;"></th>
            <th>Opérateurs</th>
            <th class="text-center" style="width: 0;">Parcelles&nbsp;sélectionnées</th>
            <th class="text-center" style="width: 0;">Superficie&nbsp;sélectionnée</th>
            <th class="text-center" style="width: 0;">Heure&nbsp;de&nbsp;début</th>
        </tr>
    </thead>
    <tbody>
        <tr :class="{ 'text-muted': nbParcellesSelectionnees(controle._id) == 0 }" v-for="(controle, numero, index) in controlesSorted()" :key="refreshList">
            <td><span v-if="nbParcellesSelectionnees(controle._id) > 0" class="label label-primary lead" style="border-radius: 24px;cursor:pointer;" :title="controle._id" @click="navigator.clipboard.writeText(controle._id)">{{ index + 1 }}</span></td>
            <td><RouterLink :to="{ name: 'operateur', params: { id: controle._id } }">{{ controle.declarant.nom }}</RouterLink></td>
            <td class="text-center">{{ nbParcellesSelectionnees(controle._id) }} / {{ nbParcelles(controle._id) }} <small>parcelle(s)</small></td>
            <td class="text-center">{{ pourcentageSelectionne(controle._id) }}%</td>
            <td class="text-center"><input v-if="nbParcellesSelectionnees(controle._id) > 0" v-model="controle.heure_tournee" type="time" @input="forceRerender()" /></td>
        </tr>
    </tbody>
</table>

<div class="row">
    <div class="col-xs-6"><a class="btn btn-default" href="<?php echo url_for("controle_index") ?>"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
    <div class="col-xs-6"><a class="btn btn-success pull-right" href="#" onclick="return document.getElementById('btn_save').click();"><span class="glyphicon glyphicon-check"></span> Valider l'organisation</a></div>
</div>
