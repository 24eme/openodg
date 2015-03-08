<?php use_helper("Date"); ?>
<?php use_javascript('lib/angular.min.js') ?>
<?php use_javascript('lib/angular-local-storage.min.js') ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_javascript('tournee.js?201503082155'); ?>
<div ng-app="myApp" ng-init='produits=<?php echo json_encode($produits->getRawValue()) ?>; url_json="<?php echo url_for("degustation_tournee_json", array('sf_subject' => $degustation, 'agent' => $agent->getKey(), 'date' => $date)) ?>"'>
<div ng-controller="tourneeCtrl">
    <section ng-show="active == 'recapitulatif'" class="visible-print-block" id="mission" style="page-break-after: always;">
        <div class="text-center" class="page-header">
            <h2>Tournée du<span class="hidden-sm hidden-md hidden-lg"><br /></span><span class="hidden-xs">&nbsp;</span><?php echo ucfirst(format_date($date, "P", "fr_FR")) ?><br /><small><?php echo $agent->nom ?></small></h2>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="list-group">
                    <a ng-repeat="(key, operateur) in operateurs" href="" ng-click="updateActive(key)" ng-class="{ 'list-group-item-success': operateur.termine, 'list-group-item-danger': (operateur.erreurs)}" class="list-group-item col-xs-12 link-to-section" style="padding-right: 0; padding-left: 0;">
                        <div class="col-xs-2 col-sm-1 text-left">
                            <strong class="lead" style="font-weight: bold;">{{ operateur.heure }}</strong>
                        </div>
                        <div class="col-xs-8 col-sm-10">
                        <strong class="lead">{{ operateur.raison_sociale }}</strong><br />
                        {{ operateur.adresse }}, {{ operateur.code_postal }} {{ operateur.commune }}
                        </div>
                        <div class="col-xs-2 col-sm-1 text-right">
                            <span ng-if="!operateur.termine" class="glyphicon glyphicon-unchecked" style="font-size: 28px; margin-top: 5px;"></span>
                            <span ng-if="operateur.termine" class="glyphicon glyphicon-check" style="font-size: 28px; margin-top: 5px;"></span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="row row-margin hidden-print">
            <div class="col-xs-6">
                <a href="<?php echo url_for('degustation_visualisation', $degustation) ?>" class="btn btn-default btn-default-step btn-lg btn-upper btn-block">Retour</a>
            </div>
            <div class="col-xs-6">
                <a href="" class="btn btn-warning btn-lg btn-upper btn-block link-to-section">Transmettre</a>
            </div>
        </div>
    </section>
    <section ng-repeat="(key, operateur) in operateurs" id="detail_mission_{{ key }}" ng-show="active == key" class="visible-print-block" style="page-break-after: always;">
    <button ng-click="precedent(operateur)" class="btn btn-default btn-default-step btn-upper pull-left btn-sm"><span class="eleganticon arrow_carrot-left"></span></button>
        <div class="page-header text-center">
            <h2>Mission de {{ operateur.heure }}</h2>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <address>
                  <span class="lead"><strong>{{ operateur.raison_sociale }}</strong> ({{ operateur.cvi }})</span><br />
                  <span class="lead">{{ operateur.adresse }}</span><br />
                  <span class="lead">{{ operateur.code_postal }} {{ operateur.commune }}</span><br /><br />
                  <span ng-if="operateur.telephone_bureau"><abbr >Bureau</abbr> : <a class="btn-link" href="tel:{{ operateur.telephone_bureau }}">{{ operateur.telephone_bureau }}</a><br /></span>
                  <span ng-if="operateur.telephone_prive"><abbr>Privé</abbr> : <a class="btn-link" href="tel:{{ operateur.telephone_prive }}">{{ operateur.telephone_prive }}</a><br /></span>
                  <span ng-if="operateur.telephone_mobile"><abbr>Mobile</abbr> : <a class="btn-link" href="tel:{{ operateur.telephone_mobile }}">{{ operateur.telephone_mobile }}</a><br /></span>
                </address>
            </div>
        </div>
        <div class="row">
            <div ng-repeat="(prelevement_key, prelevement) in operateur.prelevements" id="saisie_mission_{{ key }}_{{ prelevement_key }}" ng-class="{ 'opacity_50': !prelevement.preleve }" class="col-xs-12">
                <div class="page-header form-inline">
                    <h3 ng-style="!prelevement.preleve && {'opacity': '0.8'}" ng-class="{ 'text-danger': prelevement.erreurs['hash_produit'] }"><input ng-click="updatePreleve(prelevement)" id="preleve_{{ key }}_{{ prelevement_key }}" ng-model="prelevement.preleve" type="checkbox" ng-true-value="1" ng-false-value="0" class="hidden-print" /><span ng-click="togglePreleve(prelevement)">&nbsp;&nbsp;Lot de </span><span ng-show="!prelevement.show_produit && prelevement.hash_produit" ng-click="togglePreleve(prelevement)">{{ prelevement.libelle }}</span>
                    <select style="display: inline-block; width: auto;" class="hidden-print form-control" ng-show="prelevement.show_produit || (!prelevement.hash_produit && prelevement.preleve)" ng-change="updateProduit(prelevement)" ng-model="prelevement.hash_produit" ng-options="key as value for (key , value) in produits"></select>
                    <small><a ng-show="!prelevement.show_produit && prelevement.hash_produit" ng-click="prelevement.show_produit = true" ng-if="prelevement.hash_produit" class="text-warning hidden-print" href="">(changer)</a></small>
                    <small><a ng-show="!prelevement.show_produit && !prelevement.preleve && !prelevement.hash_produit" ng-click="prelevement.show_produit = 1" class="text-warning hidden-print" href="">(définir)</a></small>
                    </h3>
                </div>
                <div ng-show="prelevement.preleve" class="visible-print-block" class="row">
                    <div class="col-xs-12">
                        <div class="form-horizontal">
                            <div ng-class="{ 'hidden': !prelevement.erreurs['hash_produit'] }" class="alert alert-danger">
                            Vous devez séléctionner un cépage
                            </div>
                            <div ng-class="{ 'hidden': !prelevement.erreurs['cuve'] }" class="alert alert-danger">
                                Vous devez saisir le(s) numéro(s) de cuve(s)
                            </div>
                            <div class="row">
                                <div class="form-group col-xs-12 col-sm-6 lead" >
                                    <label class="control-label col-xs-5 col-sm-7 col-md-5" for="preleve_{{ key }}_{{ prelevement_key }}"><strong>N°&nbsp;d'anonymat</strong>&nbsp;:</label>
                                    <span class="control-label col-xs-7 col-sm-5 col-md-7" style="font-weight: normal; text-align: left;">{{ prelevement.anonymat_prelevement }}</span>
                                </div>
                                <div ng-class="{ 'has-error': prelevement.erreurs['cuve'] }" class="form-group col-xs-12 col-sm-6 lead">
                                    <label for="cuve_{{ key }}_{{ prelevement_key }}" class="col-xs-5 control-label">N°&nbsp;Cuves&nbsp;:</label>
                                    <div class="col-xs-7">
                                        <input id="cuve_{{ key }}_{{ prelevement_key }}" ng-model="prelevement.cuve" type="text" class="form-control input-md hidden-sm hidden-md hidden-lg" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                                        <input id="cuve_{{ key }}_{{ prelevement_key }}" ng-model="prelevement.cuve" type="text" class="form-control input-lg hidden-xs" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                                    </div>
                                </div>
                            </div>
                            <!--<small ng-show="!prelevement.hash_produit" class="hidden visible-print-block">Veuillez préfixer le numéro d'anonymat avec le code du cépage :<br />
                            Chasselas: <strong>CH</strong>, Sylvaner: <strong>SY</strong>, Auxerrois: <strong>AU</strong>, Pinot Blanc: <strong>PB</strong>, Pinot: <strong>PI</strong>, Assemblage: <strong>ED</strong>, Riesling: <strong>RI</strong>, Pinot Gris: <strong>PG</strong>, Muscat: <strong>MU</strong>, Muscat Ottonel: <strong>MO</strong>, Gewurzt.: <strong>GW</strong>, Pinot Noir Rosé: <strong>PN</strong>, Pinot Noir Rouge: <strong>PR</strong>, Savagnin Rose: <strong>KL</strong></small>-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-margin hidden-print">
            <div class="col-xs-6">
                <a href="" ng-click="precedent(operateur)" class="btn btn-primary btn-lg col-xs-6 btn-block btn-upper link-to-section">Précédent</a>
            </div>
            <div class="col-xs-6 pull-right">
                <a href="" ng-click="terminer(operateur)" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Terminer</a>
            </div>
        </div>
    </section>
</div>
</div>