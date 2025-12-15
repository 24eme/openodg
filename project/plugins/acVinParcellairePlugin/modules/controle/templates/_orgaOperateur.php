<h3 class="mt-0">Opérateur {{ controleCourant.declarant.nom }}</h3>
<hr class="mt-2 mb-4" />
<div id="map" style="height: 70vh;"></div>
<h3 class="">Parcelles</h3>
<hr class="mt-2 mb-4" />
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
    <tbody >
        <tr style="cursor: pointer;" class="parcellerow switch-to-higlight" v-for="parcelle in controleCourant.parcellaire_parcelles" >
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
<div class="row row-margin row-button">
    <div class="col-xs-8"></div>
    <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Sauvegarder</div>
</div>
