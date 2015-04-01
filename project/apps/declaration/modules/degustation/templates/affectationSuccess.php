<?php use_helper("Date"); ?>
<?php use_javascript('lib/angular.min.js') ?>
<?php use_javascript('lib/angular-local-storage.min.js') ?>
<?php use_javascript('tournee.js?201503311151'); ?>
<div ng-app="myApp" ng-init='url_json="<?php echo url_for("degustation_affectation_json", array('sf_subject' => $tournee)) ?>"; url_state="<?php echo url_for('auth_state') ?>";nombre_commissions=<?php echo $tournee->nombre_commissions ?>'>
    <div ng-controller="affectationCtrl">
        <section ng-show="active == 'recapitulatif'" id="commissions">
            <div class="page-header text-center">
                <h2>Affectation des vins<br /><small>Dégustation du 23/02/2014</small></h2>
            </div>
            <div ng-show="!loaded" class="row">
                <div class="col-xs-12 text-center lead text-muted-alt" style="padding-top: 30px;">Chargement en cours ...</div>
            </div>
            <div ng-show="loaded">
                <p class="lead"><span class="text-muted">Encore </span>{{ (prelevements | filter: { commission: null }).length }} vin(s)<span class="text-muted"> à répartir</span></p>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="list-group">
                            <a ng-repeat="n in commissions" ng-click="showAjout(n)" href="" class="list-group-item col-xs-12">
                                <div class="col-xs-10">
                                <strong class="lead">Commission {{ n }}</strong><br />
                                </div>
                                
                                <div class="col-xs-2 text-right">
                                    <span style="font-size: 26px;" class="lead">{{ (prelevements | filter: { commission: n }).length }}</span>
                                    <small>vins</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div ng-show="!state" class="alert alert-warning col-xs-12" style="margin-top: 10px;">
                Vous n'êtes plus authentifié à la plateforme, veuiller vous <a href="<?php echo url_for("degustation_affectation", array('sf_subject' => $tournee)) ?>">reconnecter</a> pour pouvoir transmettre vos données.</a>
                </div>
                <div ng-show="transmission && !transmission_result" class="alert alert-danger col-xs-12" style="margin-top: 10px;">
                La transmission a échoué :-( <small>(vous n'avez peut être pas de connexion internet, veuillez réessayer plus tard)</small>
                </div>
                <div ng-show="transmission && transmission_result" class="alert alert-success col-xs-12" style="margin-top: 10px;">
                La transmission a réussi :-)
                </div>
                <div class="row row-margin hidden-print">
                    <div class="col-xs-12">
                        <a href="" ng-show="!transmission_progress" ng-click="transmettre(false)" class="btn btn-warning btn-lg btn-upper btn-block link-to-section">Transmettre</a>
                        <small ng-show="transmission_progress">Transmission en cours...</small>
                    </div>
                </div>
            </div>
        </section>
        <section ng-show="active == 'ajout'">
            <div href="" ng-click="terminer()" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></div>
            <div class="page-header text-center">
                <h2>Commission n° {{ commission }}</h2>
            </div>
            <div class="row">
                <div class="col-xs-12 form-horizontal">
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input ng-show="(prelevements | filter: { commission: null }).length > 0" type="tel" placeholder="Rechecher par numéro de prélévement" class="form-control input-lg" ng-keypress="blurOnEnter($event)" ng-change="" ng-model="query.anonymat_prelevement_complet" />
                        </div>
                    </div>
                    <div class="list-group">
                        <li class="list-group-item list-group-item-success lead" href="" ng-repeat="prelevement in prelevements | filter: { commission: commission } | filter: { anonymat_prelevement_complet: (query.anonymat_prelevement_complet) ? query.anonymat_prelevement_complet : '' } | orderBy: ['anonymat_degustation']">N° {{ prelevement.anonymat_degustation }} - {{ prelevement.libelle }} <small>(<span class="muted-alt">{{ prelevement.anonymat_prelevement_complet.substr(0, 2) }}</span> {{ prelevement.anonymat_prelevement_complet.substr(3, 3) }} <span class="muted-alt">{{ prelevement.anonymat_prelevement_complet.substr(-3) }}</span>) <label ng-show="degustations[prelevement.degustation_id].transmission_collision" class="btn btn-xs btn-danger">Collision</label></small>
                        <a ng-click="remove(prelevement)" class="btn btn-danger btn-sm pull-right" href=""><span class="glyphicon glyphicon-trash"></span></a></li> 
                        <a class="list-group-item lead" href="" ng-repeat="prelevement in prelevements_filter = (prelevements | filter: { commission: null } | filter: { anonymat_prelevement_complet: (query.anonymat_prelevement_complet) ? query.anonymat_prelevement_complet : '' })" ng-click="ajouter(prelevement)"><span class="text-muted-alt">{{ prelevement.anonymat_prelevement_complet.substr(0, 2) }}</span> {{ prelevement.anonymat_prelevement_complet.substr(3, 3) }} <span class="text-muted-alt">{{ prelevement.anonymat_prelevement_complet.substr(-3) }}</span> <label ng-show="degustations[prelevement.degustation_id].transmission_collision" class="btn btn-xs btn-danger">Collision</label></a>
                    </div>
                </div>
            </div>
            <div class="row row-margin">
                <div class="col-xs-6">
                    <a href="" ng-click="terminer()" class="btn btn-warning btn-lg col-xs-6 btn-block btn-upper link-to-section">Terminer</a>
                </div>
                <div class="col-xs-6">
                    <a ng-disabled="prelevements_filter.length != 1" ng-click="ajouter(prelevements_filter[0])" href="" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Ajouter</a>
                </div>
            </div>
        </section>
        <section ng-show="active == 'ajout_validation'">
            <div class="page-header text-center">
                <h2>Commission n° {{ commission }}</h2>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <p class="text-center"><span  style="font-size: 36px;" class="text-muted">N° </span><strong style="font-size: 40px;">{{ anonymat_degustation }}</strong></p>

                    <p class="text-muted text-center lead"> {{ prelevement.libelle }} - {{ prelevement.anonymat_prelevement_complet }} </p>
                </div>
            </div>
            <div class="row row-margin">
                <div class="col-xs-6">
                    <a ng-click="showAjout(commission)" href="" class="btn btn-danger btn-lg col-xs-6 btn-block btn-upper link-to-section">Annuler</a>
                </div>
                <div class="col-xs-6">
                    <a href="" ng-click="validation(prelevement, commission)" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Confirmer</a>
                </div>
            </div>
        </section>
    </div>
</div>