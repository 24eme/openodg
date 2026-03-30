<h3 class="mt-0"><span class="glyphicon glyphicon-th-list"></span> Tournée du {{ date_tournee }} <RouterLink :to="{ name: 'map' }" class="pull-right"><span class="glyphicon glyphicon-map-marker"></span></RouterLink><span class="pull-right mr-5" :class="$root.isSynchro ? 'glyphicon glyphicon-floppy-saved' : 'glyphicon glyphicon-floppy-remove'" :style="$root.isSynchro ? 'color: #8da42a' : 'color: #aaaaaa'"></span></h3>
<hr class="mt-2" />

<h2>Opérateurs à contrôler</h2>

<div class="list-group mt-5">
    <RouterLink v-for="(controle, index) in filteredControles" :key="controle._id" :to="{ name: 'operateur', params: { id: controle._id } }" class="list-group-item" :class="{ 'list-group-item-success': controle.audit.saisie == 1 && controle.validation == true }">
        <div class="row">
            <div class="col-xs-2 col-md-1" style="font-size: 20px;">
                <strong>{{ (10 + index) }}:00</strong>
            </div>
            <div class="col-xs-8 col-md-9">
                <h4 class="list-group-item-heading">{{ controle.declarant.nom }}  <small>{{ controle.declarant.cvi }}</small></h4>
                <p class="list-group-item-text">{{ controle.declarant.adresse }}<br />{{ controle.declarant.code_postal }} {{ controle.declarant.commune }}</p>
                    <div class="mt-2">
                        <label class="label label-primary" :class="{ 'label-success': nbParcellesControlees(controle) == Object.keys(controle.parcelles).length, 'label-warning': nbParcellesControlees(controle) > 0 && nbParcellesControlees(controle) < Object.keys(controle.parcelles).length }">
                        {{ nbParcellesControlees(controle) }}&nbsp;/&nbsp;{{ Object.keys(controle.parcelles).length }} parcelles
                        </label>
                        <label class="label label-primary ml-2" :class="{ 'label-success': controle.audit.saisie == 1 }">
                            Audit&nbsp;<span v-if="controle.audit.saisie == 1" class="glyphicon glyphicon-ok"></span><span v-else class="glyphicon glyphicon-remove"></span>
                        </label>
                    </div>
            </div>
            <div class="col-xs-2 text-right" :class="{ 'text-primary': controle.audit.saisie != 1 }">
                <span class="glyphicon glyphicon-chevron-right h1"></span>
            </div>
        </div>
    </RouterLink>
    <div class="row">
        <div class="text-center mt-5">
            <a class="btn btn-primary" href="<?php echo url_for("controle_index") ?>">Quitter l'app Terrain</a>
        </div>
    </div>
</div>
