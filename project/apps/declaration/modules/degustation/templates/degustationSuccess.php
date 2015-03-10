<?php use_helper("Date"); ?>
<?php use_javascript('lib/angular.min.js') ?>
<?php use_javascript('lib/angular-local-storage.min.js') ?>
<?php use_javascript('tournee.js?201503100031'); ?>
<div ng-app="myApp" ng-init='url_json="<?php echo url_for("degustation_degustation_json", array('sf_subject' => $degustation, 'commission' => 1)) ?>"; url_state="<?php echo url_for('auth_state') ?>";'>
    <div ng-controller="degustationCtrl">
        <!--<section>
            <div class="page-header">
                <h2>Dégustateurs présents<br /><small>Commission 1</small></h2>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="list-group">
                        <a href="#" class="list-group-item col-xs-12">
                            <div class="col-xs-11">
                                <span class="lead">Dégustateur</span>
                            </div>
                            <div class="col-xs-1 text-right">
                                <span style="font-size: 26px;" class="glyphicon glyphicon-check glyphicon"></span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row row-margin">
                <div class="col-xs-6">
                    <a href="#commissions" class="btn btn-default btn-default-step btn-lg col-xs-6 btn-block btn-upper link-to-section">Retour</a>
                </div>
                <div class="col-xs-6">
                    <a href="#vins_<?php echo $i ?>" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Démarrer</a>
                </div>
            </div>
        </section>-->

        <section ng-show="active == 'recapitulatif'">
            <div class="page-header text-center">
                <h2>Commission {{ degustation.commission }}</small></h2>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="list-group">
                        <a href="" ng-repeat="prelevement in degustation.prelevements" class="list-group-item col-xs-12 link-to-section" ng-click="showCepage(prelevement)" ng-class="{ 'list-group-item-success': prelevement.termine, 'list-group-item-danger': (prelevement.erreurs)}">
                            <div class="col-xs-1">
                                <strong style="font-size: 32px;">{{ prelevement.anonymat_degustation }}</strong>
                            </div>
                            <div class="col-xs-4">
                                <span class="lead">{{ prelevement.libelle }}</span>
                            </div>
                            <div class="col-xs-5 text-right">
                                <span ng-show="prelevement.termine" ng-repeat="(key_note, note) in prelevement.notes"><span>{{ degustation.notes[key_note] }} : <span>{{ note.note }}</span></span><br /></span>
                            </div>
                            <div class="col-xs-2 text-right">
                                <span ng-show="!prelevement.termine" class="glyphicon glyphicon-unchecked" style="font-size: 32px; margin-top: 6px;"></span>
                                <span ng-show="prelevement.termine" class="glyphicon glyphicon-check" style="font-size: 32px; margin-top: 6px;"></span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div ng-show="!state" class="alert alert-warning col-xs-12" style="margin-top: 10px;">
            Vous n'êtes plus authentifié à la plateforme, veuiller vous <a href="<?php echo url_for("degustation_affectation", array('sf_subject' => $degustation)) ?>">reconnecter</a> pour pouvoir transmettre vos données.</a>
            </div>
            <div ng-show="transmission && !transmission_result" class="alert alert-danger col-xs-12" style="margin-top: 10px;">
            La transmission a échoué :-( <small>(vous n'avez peut être pas de connexion internet, veuillez réessayer plus tard)</small>
            </div>
            <div ng-show="transmission && transmission_result" class="alert alert-success col-xs-12" style="margin-top: 10px;">
            La transmission a réussi :-)
            </div>
            <div class="row row-margin hidden-print">
                <div class="col-xs-12">
                    <a href="" ng-show="!transmission_progress" ng-click="transmettre()" class="btn btn-warning btn-lg btn-upper btn-block link-to-section">Transmettre</a>
                    <small ng-show="transmission_progress">Transmission en cours...</small>
                </div>
            </div>
        </section>

        <section ng-repeat="prelevement in degustation.prelevements" ng-show="active == 'cepage_' + prelevement.anonymat_degustation">
            <div class="page-header text-center">
                <h2>Lot n°{{ prelevement.anonymat_degustation }} de {{ prelevement.libelle }}</h2>
            </div>
            <div class="row">
                <div class="col-xs-12">
                   <form class="form-horizontal">
                        <?php foreach(DegustationClient::$note_type_libelles as $key_note_type => $note_type_libelle): ?>
                        <div class="form-group form-group-lg" ng-class="{ 'has-error': prelevement.notes.<?php echo $key_note_type ?>.erreurs }">
                            <div class="col-xs-12">
                                <label class="col-xs-3 control-label lead"><?php echo $note_type_libelle ?></label>
                                <div class="col-xs-2">
                                    <select ng-model="prelevement.notes.<?php echo $key_note_type ?>.note" class="form-control input-lg">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                                <div class="col-xs-7">
                                   <select multiple="multiple" data-placeholder="Séléction de défaut(s)" class="form-control input-lg select2 select2-offscreen" ng-class="{ 'select2autocomplete': true }" ng-model="prelevement.notes.<?php echo $key_note_type ?>.defauts">
                                    <?php foreach(DegustationClient::$note_type_defaults[$key_note_type] as $defaut): ?>
                                    <option value="<?php echo $defaut ?>"><?php echo $defaut ?></option>
                                    <?php endforeach; ?>
                                </select>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div ng-show="prelevement.erreurs" class="alert alert-danger text-center">
                            Vous devez saisir toutes les notes
                        </div>
                        <div class="form-group form-group-lg" style="padding-top: 20px;">
                            <label class="col-xs-3 control-label lead text-muted">Appréciations</label>
                            <div class="col-xs-9">
                                <div class="col-xs-12">
                                    <textarea placeholder="Saisissez vos appréciations" class="form-control" ng-model="prelevement.appreciations"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row row-margin">
                <div class="col-xs-6">
                    <a href="" ng-click="precedent()" class="btn btn-primary btn-lg col-xs-6 btn-block btn-upper link-to-section">Retour</a>
                </div>
                <div class="col-xs-6">
                    <a href="" ng-click="valider(prelevement)" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Valider</a>
                </div>
            </div>
        </section>
    </div>
</div>