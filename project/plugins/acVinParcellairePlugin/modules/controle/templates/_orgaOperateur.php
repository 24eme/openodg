<h3 class="mt-0"><RouterLink :to="{ name: 'operateurs' }"><span class="glyphicon glyphicon-chevron-left"></span></RouterLink> {{ controleCourant.declarant.nom }} - Séléction des parcelles</h3>
<hr class="mt-2 mb-4" />
<div style="position: relative;">
    <div id="map" style="height: 700px;"></div>
    <div v-for="parcelle in parcelles" :id="parcelle.id" class="hidden bloc_parcelle" style="position: absolute; bottom: 0; z-index: 10000; background: white; opacity: 0.9;">
        <button id="btn-close-info" type="button" style="position: absolute; right: 10px; top: 5px;" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <table class="table table-bordered table-condensed table-striped" style="margin-bottom: 0;">
        <tbody>
            <tr><th>Commune</th><td v-for="p in parcelle.properties.parcellaires">{{ p["Commune"] }}</td></tr>
            <tr><th>Section&nbsp;/&nbspN°</th><td v-for="p in parcelle.properties.parcellaires">{{ p["Section"] }} {{ p["Numero parcelle"] }}</td></tr>
            <tr><th>Produits<br/>et cepages</th><td v-for="p in parcelle.properties.parcellaires"><span class="text-muted"> {{ p["Produit"] }}</span><br/>{{ p["Cepage"] }}</td></tr>
            <tr><th>Année plantat°</th><td v-for="p in parcelle.properties.parcellaires">{{ p["Campagne"] }}</td></tr>
            <tr><th>Superficies <span>(ha)</span></th><td v-for="p in parcelle.properties.parcellaires">{{ p["Superficie"] }}</td></tr>
            <tr><th>Écart Pieds</th><td v-for="p in parcelle.properties.parcellaires">{{ p["Ecart pied"] }}</td></tr>
            <tr><th>Écart Rang</th><td v-for="p in parcelle.properties.parcellaires">{{ p["Ecart rang"] }}</td></tr>
            <tr><th>Sélectionner</th><td v-for="p in parcelle.properties.parcellaires" class="text-center"><label class="switch"><input class="selectParcelle" type="checkbox" v-model="parcellesSelectionnees" :value="p.parcelleId" /><span class="slider round"></span></label></td></tr>
        </tbody>
        </table>
    </div>
</div>
<h3 class="">Parcelles sélectionnées <span class="label label-primary">{{ pourcentageSelectionne() }}%</span>&nbsp;&nbsp;<small><a v-on:click="displayList">Afficher la liste des parcelles</a></small></h3>

<table id="listeParcelles" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th style="width: 0;"></th>
            <th class="col-xs-2">Commune</th>
            <th class="col-xs-2">Lieu-dit</th>
            <th class="col-xs-1" style="text-align: right;">Section</th>
            <th class="col-xs-1">N° parcelle</th>
            <th class="col-xs-3">Cépage</th>
            <th class="col-xs-1">Année plantat°</th>
            <th class="col-xs-1" style="text-align: right;">Surface <span class="text-muted small"><?php echo ParcellaireConfiguration::getInstance()->isAres() ? 'ares' : 'ha' ?></span></th>
            <th style="width: 0;"></th>
        </tr>
    </thead>
    <tbody>
        <tr :class="{ 'text-muted': !isParcelleSelectionnee(parcelleId) }" v-for="(parcelleId, numero) in getParcellesSorted()">
            <td><span v-if="isParcelleSelectionnee(parcelleId)" class="label label-primary lead" style="border-radius: 24px;">{{ numero + 1 }}</span></td>
            <td>{{ controleCourant.parcellaire_parcelles[parcelleId].commune }}</td>
            <td>{{ controleCourant.parcellaire_parcelles[parcelleId].lieu }}</td>
            <td class="text-right">{{ controleCourant.parcellaire_parcelles[parcelleId].section }}</td>
            <td class="text-center">{{ controleCourant.parcellaire_parcelles[parcelleId].numero_parcelle }}</td>
            <td><span class="text-muted">{{ controleCourant.parcellaire_parcelles[parcelleId].source_produit_libelle }}</span> {{ controleCourant.parcellaire_parcelles[parcelleId].cepage }}</td>
            <td class="text-center">{{ controleCourant.parcellaire_parcelles[parcelleId].campagne_plantation }}</td>
            <td class="text-right">{{ controleCourant.parcellaire_parcelles[parcelleId].superficie }}</td>
            <td><button v-if="isParcelleSelectionnee(parcelleId)" class="btn btn-link"><span class="glyphicon glyphicon-trash"></span></button></td>
        </tr>
    </tbody>
</table>
<div class="hidden">
<hr class="mt-2 mb-4" />
<h3 class="">Toutes les parcelles</h3>
<table id="tableParcelle" class="table table-bordered table-condensed table-striped tableParcellaire">
    <thead>
        <tr>
            <th class="col-xs-2">Commune</th>
            <th class="col-xs-2">Lieu-dit</th>
            <th class="col-xs-1" style="text-align: right;">Section</th>
            <th class="col-xs-1">N° parcelle</th>
            <th class="col-xs-3">Cépage</th>
            <th class="col-xs-1">Année plantat°</th>
            <th class="col-xs-1" style="text-align: right;">Surface <span class="text-muted small"><?php echo ParcellaireConfiguration::getInstance()->isAres() ? 'ares' : 'ha' ?></span></th>
            <th class="col-xs-1 text-center">Contrôle ?</th>
        </tr>
    </thead>
    <tbody>
        <tr style="cursor: pointer;" class="parcellerow switch-to-higlight" v-for="parcelle in controleCourant.parcellaire_parcelles">
            <td>{{ parcelle.commune }}</td>
            <td>{{ parcelle.lieu }}</td>
            <td class="text-right">{{ parcelle.section }}</td>
            <td class="text-center">{{ parcelle.numero_parcelle }}</td>
            <td><span class="text-muted">{{ parcelle.source_produit_libelle }}</span> {{ parcelle.cepage }}</td>
            <td class="text-center">{{ parcelle.campagne_plantation }}</td>
            <td class="text-right">{{ parcelle.superficie }}</td>
            <td class="text-center inputTd">
                <label class="switch-xl">
                    <input type="checkbox" name="parcelles[]" :value="parcelle.parcelle_id" v-model="parcellesSelectionnees" />
                    <span class="slider-xl round"></span>
                </label>
            </td>
        </tr>
    </tbody>
</table>
</div>

<RouterLink class="btn btn-primary" :to="{ name: 'operateurs' }"><span class="glyphicon glyphicon-chevron-left"></span> Retour</RouterLink>
