<?php use_helper("Date"); ?>
<?php use_javascript('lib/angular.min.js') ?>
<?php use_javascript('lib/angular-local-storage.min.js') ?>
<?php use_javascript('tournee.js?201504281909'); ?>

<ol class="breadcrumb hidden-xs hidden-sm">
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href="<?php echo url_for('degustation_visualisation', $tournee); ?>"><?php echo $tournee->getLibelle(); ?>  le <?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></a></li>
  <li class="active"><a href="">Affectation</a></li>
</ol>

<div ng-app="myApp" ng-init='url_json="<?php echo url_for("degustation_affectation_json", array('sf_subject' => $tournee, 'unlock' => !$lock)) ?>"; url_state="<?php echo url_for('auth_state') ?>";nombre_commissions=<?php echo $tournee->nombre_commissions ?>; reload=<?php echo $reload ?>;'>
    <div ng-controller="affectationCtrl">
        <section ng-show="active == 'recapitulatif'" id="commissions">
            <a href="<?php echo url_for("degustation_visualisation", $tournee) ?>" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></a>
            <?php if($lock): ?><span class="pull-right"><span class="glyphicon glyphicon-lock"></span></span><?php endif; ?>
            <div class="page-header text-center">
                <h2>Affectation des vins<br /><small>Dégustation du <?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></small></h2>
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
               <div ng-show="transmission && transmission_result == 'error'" class="alert alert-danger col-xs-12" style="margin-top: 10px;">
                La transmission a échoué :-( <small>(vous n'avez peut être pas de connexion internet, veuillez réessayer plus tard)</small>
                </div>
                <div ng-show="transmission && transmission_result == 'success'" class="alert alert-success col-xs-12" style="margin-top: 10px;">
                La transmission a réussi :-)
                </div>
                <div ng-show="transmission && transmission_result == 'aucune_transmission'" class="alert alert-success col-xs-12" style="margin-top: 10px;">
                Rien à transmettre
                </div>
                <div class="row row-margin hidden-print">
                    <div class="col-xs-12 text-center">
                        <a href="" ng-show="!transmission_progress" ng-click="transmettre(false)" class="btn btn-warning btn-lg btn-upper btn-block"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;Transmettre</a>
                        <span class="text-muted-alt" ng-show="transmission_progress">Transmission en cours...</span>
                    </div>
                </div>
            </div>
        </section>
        <section ng-show="active == 'ajout'">
            <div href="" ng-click="terminer()" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></div>
            <div class="page-header text-center">
                <h2>Commission n° {{ commission }}<br /><button class="btn btn-xs btn-default">{{ (prelevements | filter: { commission: commission }).length }} prélèvements</button>  <button class="btn btn-xs btn-default-step">{{ getMoyennePrelevements() }} en moyenne</button></h2>
            </div>
            <div class="row">
                <div class="col-xs-12 form-horizontal">
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input ng-show="(prelevements | filter: { commission: null }).length > 0" type="tel" placeholder="Rechercher par numéro de prélévement" class="form-control input-lg" ng-keypress="blurOnEnter($event)" ng-change="" ng-model="query.anonymat_prelevement_complet" />
                        </div>
                    </div>
                    <div class="list-group">
                        <li class="list-group-item list-group-item-success lead" href="" ng-repeat="prelevement in prelevements | filter: { commission: commission } | filter: { anonymat_prelevement_complet: (query.anonymat_prelevement_complet) ? query.anonymat_prelevement_complet : '' } | orderBy: ['anonymat_degustation']"><a ng-click="remove(prelevement)" class="btn btn-danger btn-sm pull-right" href=""><span class="glyphicon glyphicon-trash"></span></a>N° {{ prelevement.anonymat_degustation }} - {{ prelevement.libelle }} <small>({{ prelevement.anonymat_prelevement_complet }}) <label ng-show="degustations[prelevement.degustation_id].transmission_collision" class="btn btn-xs btn-danger">Collision</label></small>
                        </li>
                        <a class="list-group-item lead" href="" ng-repeat="prelevement in prelevements_filter = (prelevements | filter: { commission: null } | filter: { anonymat_prelevement_complet: (query.anonymat_prelevement_complet) ? query.anonymat_prelevement_complet : '' })" ng-click="ajouter(prelevement)"><span class="text-muted-alt">{{ getCodeCepageNumero(prelevement.anonymat_prelevement_complet) }}</span> {{ getIncrementalNumero(prelevement.anonymat_prelevement_complet) }} <span class="text-muted-alt">{{ getCodeVerifNumero(prelevement.anonymat_prelevement_complet) }}</span>
                        <small class="text-muted" ng-show="prelevement.fermentation_lactique">- FML</small>
                        <small class="text-muted" ng-show="prelevement.composition">- {{prelevement.composition}}</small>
                        <label ng-show="degustations[prelevement.degustation_id].transmission_collision" class="btn btn-xs btn-danger">Collision</label></a>
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
                    <p ng-show="prelevement.fermentation_lactique || prelevement.composition" class="text-center text-muted-alt lead"> <span ng-show="prelevement.fermentation_lactique">FML</span> <span ng-show="prelevement.composition"><span ng-show="prelevement.fermentation_lactique">-</span> {{prelevement.composition}}</span></p>

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
