<?php use_javascript('lib/angular.min.js') ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_javascript('tournee.js'); ?>
<div ng-app="myApp" ng-init='produits=<?php echo json_encode($produits->getRawValue()) ?>; url_json="/declaration_dev.php/degustation/tournee/DEGUSTATION-20150311-ALSACE/COMPTE-A008482/2015-03-09.json"'>
<div ng-controller="tourneeCtrl">
    <section ng-class="{'hidden': active != 'recap' }" id="mission" style="page-break-after: always;">
        <div style="padding-left: 10px;" class="page-header">
            <h2>Mission du lundi 9 mars 2015 <small>Sabrina M.</small></h2>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="list-group">
                    <a ng-repeat="(key, operateur) in operateurs" href="" ng-click="updateActive(key)" ng-class="{ 'list-group-item-success': operateur.termine, 'list-group-item-danger': (operateur.erreurs)}" class="list-group-item col-xs-12 link-to-section">
                        <div class="col-xs-2">
                            <strong style="font-size: 32px;">{{ operateur.heure }}</strong>
                        </div>
                        <div class="col-xs-9">
                        <strong class="lead">{{ operateur.raison_sociale }}</strong><br />
                        {{ operateur.adresse }}, {{ operateur.code_postal }} {{ operateur.commune }}
                        </div>
                        <div class="col-xs-1">
                            <span ng-if="!operateur.termine" class="glyphicon glyphicon-unchecked" style="font-size: 40px; margin-top: 5px;"></span>
                            <span ng-if="operateur.termine" class="glyphicon glyphicon-check" style="font-size: 40px; margin-top: 5px;"></span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="row row-margin hidden-print">
            <div class="col-xs-6">
                <a href="<?php echo url_for('degustation') ?>" class="btn btn-default btn-default-step btn-lg btn-upper btn-block">Retour</a>
            </div>
            <div class="col-xs-6">
                <a href="" class="btn btn-warning btn-lg btn-upper btn-block link-to-section">Transmettre</a>
            </div>
        </div>
    </section>
    <section ng-repeat="(key, operateur) in operateurs" id="detail_mission_{{ key }}" ng-class="{'hidden': active != key }" style="page-break-after: always;">
        <div class="page-header text-center">
            <h2>Mission de {{ operateur.heure }}</h2>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <address>
                  <span class="lead text-muted"><strong>{{ operateur.raison_sociale }}</strong> ({{ operateur.cvi }})</span><br />
                  <span class="lead">{{ operateur.adresse }}</span><br />
                  <span class="lead">{{ operateur.code_postal }} {{ operateur.commune }}</span><br /><br />
                  <span ng-if="operateur.telephone_bureau"><abbr>Bureau</abbr> : {{ operateur.telephone_bureau }}<br /></span>
                  <span ng-if="operateur.telephone_prive"><abbr>Privé</abbr> : {{ operateur.telephone_prive }}<br /></span>
                  <span ng-if="operateur.telephone_mobile"><abbr>Mobile</abbr> : {{ operateur.telephone_mobile }}<br /></span>
                </address>
            </div>
        </div>
        <div ng-repeat="(prelevement_key, prelevement) in operateur.prelevements" id="saisie_mission_{{ key }}_{{ prelevement_key }}">
            <div style="padding-left: 10px;" class="page-header">
                <h2><input id="preleve_{{ key }}_{{ prelevement_key }}" ng-model="prelevement.preleve" type="checkbox" ng-true-value="1" ng-false-value="0" />&nbsp;&nbsp;&nbsp;Lot <span ng-if="prelevement.hash_produit">n°{{ prelevement_key + 1 }}</span> <span ng-if="prelevement.hash_produit" ng-show="!prelevement.show_produit"> - {{ prelevement.libelle }}</span> <span ng-if="!prelevement.hash_produit">de : </span> 
                <select ng-show="prelevement.show_produit" ng-change="updateProduit(prelevement)" ng-model="prelevement.hash_produit" ng-options="key as value for (key , value) in produits"></select>
                <small><a ng-show="!prelevement.show_produit" ng-click="prelevement.show_produit = true" ng-if="prelevement.hash_produit" class="text-warning hidden-print" href="#">(changer)</a></small>
                
                </h2>
            </div>
            <div ng-class="{ 'hidden': !prelevement.preleve }" class="row" >
                <div class="col-xs-12">
                    <div class="form-horizontal">
                        <div ng-class="{ 'hidden': !operateur.prelevements.erreurs['cuve'] }" class="alert alert-danger">
                        Vous devez saisir le(s) numéro(s) de cuve(s)
                        </div>
                        <div ng-class="{ 'has-error': operateur.prelevements.erreurs['cuve'] }" class="form-group" >
                            <div class="col-xs-6">
                                <label class="control-label lead" for="preleve_{{ key }}_{{ prelevement_key }}"><strong>N° d'anonymat</strong> : {{ prelevement.anonymat_prelevement }}</label></span>
                            </div>
                            <div  class="col-xs-6">
                                <label for="inputEmail3" class="col-xs-4 control-label">N°&nbsp;Cuves : </label>
                                <div class="col-xs-8">
                                    <input id="cuve_{{ key }}_{{ prelevement_key }}" ng-model="prelevement.cuve" type="text" class="form-control" />
                                </div>
                            </div>
                        </div>
                        <small ng-if="!prelevement.hash_produit" class="text-muted hidden visible-print-block">Veuillez préfixer le numéro d'anonymat avec le code du cépage :<br />
                        Chasselas: <strong>CH</strong>, Sylvaner: <strong>SY</strong>, Auxerrois: <strong>AU</strong>, Pinot Blanc: <strong>PB</strong>, Pinot: <strong>PI</strong>, Assemblage: <strong>ED</strong>, Riesling: <strong>RI</strong>, Pinot Gris: <strong>PG</strong>, Muscat: <strong>MU</strong>, Muscat Ottonel: <strong>MO</strong>, Gewurzt.: <strong>GW</strong>, Pinot Noir Rosé: <strong>PN</strong>, Pinot Noir Rouge: <strong>PR</strong>, Savagnin Rose: <strong>KL</strong></small>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-margin hidden-print">
            <div class="col-xs-6">
                <a href="" ng-click="precedent()" class="btn btn-primary btn-lg col-xs-6 btn-block btn-upper link-to-section">Précédent</a>
            </div>
            <div class="col-xs-6 pull-right">
                <a href="" ng-click="valide(key)" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Terminer</a>
            </div>
        </div>
    </section>
</div>
</div>