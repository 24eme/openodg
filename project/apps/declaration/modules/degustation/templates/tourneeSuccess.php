<?php use_helper("Date"); ?>
<?php use_javascript('lib/angular.min.js') ?>
<?php use_javascript('lib/angular-local-storage.min.js') ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_javascript('tournee.js?201503090248'); ?>
<div ng-app="myApp" ng-init='produits=<?php echo json_encode($produits->getRawValue()) ?>; url_json="<?php echo url_for("degustation_tournee_json", array('sf_subject' => $tournee, 'agent' => $agent->getKey(), 'date' => $date)) ?>"; reload=<?php echo $reload ?>; url_state="<?php echo url_for('auth_state') ?>"; motifs=<?php echo json_encode(DegustationClient::$motif_non_prelevement_libelles) ?>'>
<div ng-controller="tourneeCtrl">
    
    <section ng-show="active == 'recapitulatif'" class="visible-print-block" id="mission" style="page-break-after: always;">
        <div class="text-center" class="page-header">
            <h2>Tournée du<span class="hidden-sm hidden-md hidden-lg"><br /></span><span class="hidden-xs">&nbsp;</span><?php echo ucfirst(format_date($date, "P", "fr_FR")) ?>&nbsp;<span class="hidden-lg hidden-md hidden-sm"><br /></span><span class="hidden-xs text-muted-alt"> - </span><span class="text-muted-alt" style="font-weight: normal"><?php echo $agent->nom ?></span></h2>
        </div>
        <div ng-show="!loaded" class="row">
            <div class="col-xs-12 text-center lead text-muted-alt" style="padding-top: 30px;">Chargement en cours ...</div>
        </div>
        <div class="row" ng-show="loaded">
            <div class="col-xs-12">
                <div class="list-group print-list-group-condensed">
                    <a ng-repeat="operateur in operateurs" href="" ng-click="updateActive(operateur._id)" ng-class="{ 'list-group-item-success': operateur.termine && !operateur.motif_non_prelevement, 'list-group-item-danger': (operateur.has_erreurs), 'list-group-item-warning': operateur.termine && operateur.motif_non_prelevement}" class="list-group-item col-xs-12 link-to-section" style="padding-right: 0; padding-left: 0;">
                        <div class="col-xs-2 col-sm-1 text-left">
                            <strong class="lead" style="font-weight: bold;">{{ operateur.heure }}</strong>
                        </div>
                        <div class="col-xs-8 col-sm-10">
                            <strong class="lead">{{ operateur.raison_sociale }}</strong><span class="text-muted hidden-xs"> {{ operateur.cvi }}</span>
                            <button ng-show="motifs[operateur.motif_non_prelevement]" class="btn btn-xs btn-warning">{{ motifs[operateur.motif_non_prelevement] }}</button>
                            <br />
                            {{ operateur.adresse }}, {{ operateur.code_postal }} {{ operateur.commune }}<span class="text-muted hidden-xs">&nbsp;&nbsp;&nbsp;<span class="glyphicon glyphicon-phone-alt"></span>&nbsp;{{ (operateur.telephone_mobile) ? operateur.telephone_mobile : operateur.telephone_bureau }}</span>
                        </div>
                        <div class="col-xs-2 col-sm-1 text-right">
                            <span ng-if="!operateur.termine" class="glyphicon glyphicon-unchecked" style="font-size: 28px; margin-top: 8px;"></span>
                            <span ng-if="operateur.termine" class="glyphicon glyphicon-check" style="font-size: 28px; margin-top: 8px;"></span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div ng-show="!state" class="alert alert-warning col-xs-12" style="margin-top: 10px;">
        Vous n'êtes plus authentifié à la plateforme, veuiller vous <a href="<?php echo url_for("degustation_tournee", array('sf_subject' => $tournee, 'agent' => $agent->getKey(), 'date' => $date)) ?>">reconnecter</a> pour pouvoir transmettre vos données.</a>
        </div>
        <div ng-show="transmission && !transmission_result" class="alert alert-danger col-xs-12" style="margin-top: 10px;">
        La transmission a échoué :-( <small>(vous n'avez peut être pas de connexion internet, veuillez réessayer plus tard)</small>
        </div>
        <div ng-show="transmission && transmission_result" class="alert alert-success col-xs-12" style="margin-top: 10px;">
        La transmission a réussi :-)
        </div>
        <div class="row row-margin hidden-print">
            <div class="col-sm-6 hidden-xs">
                <a href="" ng-click="print()" class="btn btn-default-step btn-lg btn-upper btn-block"><span class="glyphicon glyphicon-print"></span>&nbsp;&nbsp;Imprimer</a>
            </div>
            <div class="col-xs-12 col-sm-6 text-center">
                <a href="" ng-show="!transmission_progress" ng-click="transmettre()" class="btn btn-warning btn-lg btn-upper btn-block"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;Transmettre</a>
                <span class="text-muted-alt" ng-show="transmission_progress">Transmission en cours...</span>
            </div>
        </div>
    </section>
    <section ng-repeat="operateur in operateurs" id="detail_mission_{{ operateur._id }}" ng-show="active == operateur._id" class="visible-print-block" style="page-break-after: always;">
        <div href="" ng-click="precedent(operateur)" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></div>
        <div class="page-header text-center">
            <h2>Mission de {{ operateur.heure }}</h2>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <address>
                  <span class="lead"><strong>{{ operateur.raison_sociale }}</strong> <small class="hidden-xs">({{ operateur.cvi }})</small></span><br />
                  <span class="lead">{{ operateur.adresse }}</span><br />
                  <span class="lead">{{ operateur.code_postal }} {{ operateur.commune }}</span><br /><br />
                  <span ng-if="operateur.telephone_bureau"><abbr >Bureau</abbr> : <a class="btn-link" href="tel:{{ operateur.telephone_bureau }}">{{ operateur.telephone_bureau }}</a><br /></span>
                  <span ng-if="operateur.telephone_prive"><abbr>Privé</abbr> : <a class="btn-link" href="tel:{{ operateur.telephone_prive }}">{{ operateur.telephone_prive }}</a><br /></span>
                  <span ng-if="operateur.telephone_mobile"><abbr>Mobile</abbr> : <a class="btn-link" href="tel:{{ operateur.telephone_mobile }}">{{ operateur.telephone_mobile }}</a><br /></span>
                </address>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div style="border-bottom: 0;" class="page-header">
                    <h3 style="margin-top: 15px" class="text-warning">
                    <a class="text-warning" href="" ng-click="toggleAucunPrelevement(operateur)">
                    <span class="ng-hide visible-print-inline"><span class="glyphicon glyphicon-unchecked" style="font-size: 20px;"></span></span>
                    <span ng-show="!operateur.aucun_prelevement" class="glyphicon glyphicon-unchecked hidden-print" style="font-size: 20px;"></span><span ng-show="operateur.aucun_prelevement" class="glyphicon glyphicon-check hidden-print" style="font-size: 20px;"></span>&nbsp;&nbsp;<strong class="lead text-warning">Aucun prélèvement</strong><span class="ng-hide visible-print-inline lead"> : Report - Plus de vin - Soucis</span>
                    </a>
                    </h3>
                </div>
                <div ng-show="operateur.aucun_prelevement" class="form-horizontal hidden-print">
                    <div class="form-group col-xs-12" >
                        <label class="control-label col-xs-3 lead" for="motif_preleve_{{ operateur._id }">Motif :</label>
                        <div class="col-xs-9">
                            <select ng-change="updateMotif(operateur)" ng-model="operateur.motif_non_prelevement" id="motif_preleve_{{ operateur._id }}" class="form-control" ng-options="key as value for (key , value) in motifs"></select>
                        </div>
                    </div>
                    <div ng-class="{ 'hidden': !operateur.erreurs['motif'] }" class="alert alert-danger col-xs-12">
                        Vous devez saisir un motif de non prélevement
                    </div>
                </div>
            </div>
            <div ng-show="!operateur.aucun_prelevement" ng-repeat="(prelevement_key, prelevement) in operateur.prelevements" id="saisie_mission_{{ operateur._id }}_{{ prelevement_key }}" class="col-xs-12">
                <div class="page-header form-inline print-page-header" style="margin-top: 5px">
                    <h3 style="margin-top: 15px" ng-class="{ 'text-danger': prelevement.erreurs['hash_produit'] }">
                    <span class="ng-hide visible-print-inline"><span class="glyphicon glyphicon-unchecked" style="font-size: 20px;"></span></span><a ng-show="prelevement.preleve" class="text-muted" href="" ng-click="togglePreleve(prelevement)"><span class="glyphicon glyphicon-check hidden-print" style="font-size: 20px;"></span>&nbsp;</a><a ng-show="!prelevement.preleve" class="text-muted" href="" ng-click="togglePreleve(prelevement)"><span class="glyphicon glyphicon-unchecked hidden-print" style="font-size: 20px;"></span>&nbsp;</a>
                    <span ng-show="!prelevement.show_produit && prelevement.hash_produit" class="lead" ng-click="togglePreleve(prelevement)">{{ prelevement.libelle }}</span>
                    <select style="display: inline-block; width: auto;" class="hidden-print form-control" ng-show="prelevement.show_produit || (!prelevement.hash_produit && prelevement.preleve)" ng-change="updateProduit(prelevement)" ng-model="prelevement.hash_produit" ng-options="key as value for (key , value) in produits"></select>
                    <small ng-show="!prelevement.show_produit && prelevement.hash_produit">&nbsp;&nbsp;<a ng-click="prelevement.show_produit = true" ng-if="prelevement.hash_produit" class="text-warning hidden-print" href=""><span class="glyphicon glyphicon-pencil"></span>&nbsp;Changer</a></small>
                    <small ng-show="!prelevement.show_produit && !prelevement.preleve && !prelevement.hash_produit">&nbsp;<a ng-click="prelevement.show_produit = 1" class="text-warning hidden-print" href=""><span class="glyphicon glyphicon-pencil"></span>&nbsp;Définir</a></small>
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
                                    <label class="control-label col-xs-5 col-sm-7 col-md-5" for="preleve_{{ operateur._id }}_{{ prelevement_key }}"><strong>N°&nbsp;d'anon.</strong>&nbsp;:</label>
                                    <span class="control-label col-xs-7 col-sm-5 col-md-7" style="font-weight: normal; text-align: left;">{{ prelevement.anonymat_prelevement }}</span>
                                </div>
                                <div ng-class="{ 'has-error': prelevement.erreurs['cuve'] }" class="form-group col-xs-12 col-sm-6 lead">
                                    <label for="cuve_{{ operateur._id }}_{{ prelevement_key }}" class="col-xs-5 control-label">N°&nbsp;Cuves&nbsp;:</label>
                                    <div class="col-xs-7">
                                        <input id="cuve_{{ operateur._id }}_{{ prelevement_key }}" ng-model="prelevement.cuve" type="text" class="form-control input-md hidden-sm hidden-md hidden-lg" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                                        <input id="cuve_{{ operateur._id }}_{{ prelevement_key }}" ng-model="prelevement.cuve" type="text" class="form-control input-lg hidden-xs" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div ng-class="{ 'hidden': !operateur.erreurs['aucun_prelevement'] }" class="alert alert-danger">
            Vous n'avez saisi aucun lot<br /><small>Vous pouvez cocher "Aucun prélèvement" si il n'y a aucun prélèvement pour cet opérateur</small>
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