<h3 class="mt-0"><span class="glyphicon glyphicon-th-list"></span> Tournée du 12/11/2025 <RouterLink :to="{ name: 'map' }" class="pull-right"><span class="glyphicon glyphicon-map-marker"></span></RouterLink></h3>
<hr class="mt-2" />

<div id="map" style="height: 70vh;"></div>

<hr />

<h2>Opérateurs à contrôler</h2>

<div class="list-group mt-5">
    <RouterLink v-for="(controle, key) in controles" :to="{ name: 'operateur', params: { id: key } }" class="list-group-item" :class="{ 'list-group-item-success': controle.audit.saisie == 1 }">
        <div class="row">
            <div class="col-xs-10">
                <h4 class="list-group-item-heading">{{ controle.declarant.nom }}  <small>{{ controle.declarant.cvi }}</small></h4>
                <p class="list-group-item-text">{{ controle.declarant.adresse }}<br />{{ controle.declarant.code_postal }} {{ controle.declarant.commune }}</p>
                <div class="mt-2">
                    <label class="label label-primary" :class="{ 'label-success': controle.audit.saisie == 1 }">
                    {{ Object.keys(controle.parcelles).length }} parcelles
                    </label>
                </div>
            </div>
            <div class="col-xs-2 text-right" :class="{ 'text-primary': controle.audit.saisie != 1 }">
                <span class="glyphicon glyphicon-chevron-right h1"></span>
            </div>
        </div>
    </RouterLink>
</div>
