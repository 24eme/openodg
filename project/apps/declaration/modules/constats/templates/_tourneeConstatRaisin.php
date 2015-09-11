<div id="saisie_constat_raisin_{{ keyConstatNode}}" ng-show="active == 'saisie'" class="col-xs-12 print-margin-bottom">
    <div class="form-horizontal">
        <div class="form-group">
            <div ng-class="{ 'hidden': !constat.erreurs['produit'] }" class="alert alert-danger">
                Vous devez saisir un produit
            </div>
            <div class="col-xs-12">
                <select class="form-control input-lg" ng-change="updateProduit(constat)" ng-model="constat.produit" ng-options="produit_key as produit_libelle for (produit_key, produit_libelle) in produits"></select>
            </div>
        </div>
        <div class="form-group">
            <div ng-class="{ 'hidden': !constat.erreurs['nb_botiche'] }" class="alert alert-danger">
                Vous devez saisir une quantité 
            </div>
            <div ng-class="{ 'hidden': !constat.erreurs['contenant'] }" class="alert alert-danger">
                Vous devez saisir un type de contenant
            </div>
            <div class="col-xs-6">
                <input placeholder="Nombre" id="nb_botiche_{{ keyConstatNode}}" ng-model="constat.nb_botiche" type="number" class="form-control input-lg" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
            </div>
            <div class="col-xs-6">
                <select class="form-control input-lg" ng-change="updateContenant(constat)" ng-model="constat.contenant" ng-options="contenant_key as contenant_libelle for (contenant_key, contenant_libelle) in contenants"></select>
            </div>
        </div>
        <div class="form-group">
            <div ng-class="{ 'hidden': !constat.erreurs['degre_potentiel_raisin'] }" class="alert alert-danger">
                Vous devez saisir le degré potentiel
            </div>
            <div class="col-xs-6">
                <input placeholder="Degré potentiel" id="degre_potentiel_raisin_{{ keyConstatNode}}" ng-model="constat.degre_potentiel_raisin" type="text" class="form-control input-lg" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
            </div>
            <div class="col-xs-6 lead">
            <p style="margin: 0;" class="form-control-static">° potentiel</p>
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-6">
            <a href="" ng-click="refuserConfirmation(constat)" class="btn btn-danger btn-lg col-xs-6 btn-block btn-upper link-to-section"><span class="glyphicon glyphicon-remove-circle"></span> Refuser</a>
        </div>
        <div class="col-xs-6">
            <a href="" ng-click="approuverConstatRaisin(constat)" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section"><span class=" glyphicon glyphicon-ok-circle"></span> Approuver</a>
        </div>
    </div>
</div>
<div ng-show="active == 'refuser_confirmation'" class="form-horizontal">
    <div class="col-xs-12">
        <div class="col-xs-12">
            <p class="lead text-center"> 
            {{ constat.produit_libelle }}<br />
            {{ constat.degre_potentiel_raisin }} degré potentiel<br />
            {{ constat.nb_botiche }} {{ constat.type_botiche.nom }}(s)<br />
            </p>
        </div>
        <div class="form-group">
            <label for="raison_refus_raisin_{{ keyConstatNode}}" class="col-xs-4 control-label lead">Raison du refus</label>
            <div class="col-xs-8">
            <select id="raison_refus_raisin_{{ keyConstatNode}}" ng-model="constat.raison_refus" class="hidden-print form-control input-lg">
                <option value="DEGRE_INSUFFISANT">Degré insuffisant</option>
                <option value="MULTI_CEPAGE">Multi cépage</option>
                <option value="PRESSURAGE_EN_COURS">Pressurage en cours</option>
            </select>
            </div>
        </div>
    </div>

    <div class="row row-margin">
        <div class="col-xs-12">
            <div class="col-xs-6">
                <a href="" ng-click="remplir(constat)" class="btn btn-danger btn-lg col-xs-6 btn-block btn-upper link-to-section">Annuler </a>
            </div>
            <div class="col-xs-6">
                <a href="" ng-click="refuserConstatRaisin(constat)" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Confirmer</a>
            </div>
        </div>
    </div>
