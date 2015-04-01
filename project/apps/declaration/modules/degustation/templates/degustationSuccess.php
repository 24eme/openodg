<?php use_helper("Date"); ?>
<?php use_javascript('lib/angular.min.js') ?>
<?php use_javascript('lib/angular-local-storage.min.js') ?>
<?php use_javascript('tournee.js?201503100031'); ?>
<div ng-app="myApp" ng-init='url_json="<?php echo url_for("degustation_degustation_json", array('sf_subject' => $tournee, 'commission' => $commission)) ?>"; url_state="<?php echo url_for('auth_state') ?>"; commission=<?php echo $commission ?>; notes=<?php echo json_encode(DegustationClient::$note_type_libelles) ?>;'>
    <div ng-controller="degustationCtrl">
        <section ng-show="active == 'recapitulatif'">
            <a href="<?php echo url_for("degustation_degustations", $tournee) ?>" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></a>
            <div class="page-header text-center">
                <h2>Commission {{ commission }}</small></h2>
            </div>
            <div ng-show="!loaded" class="row">
                <div class="col-xs-12 text-center lead text-muted-alt" style="padding-top: 30px;">Chargement en cours ...</div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="list-group">
                        <a href="" ng-repeat="prelevement in prelevements | orderBy: ['anonymat_degustation']" class="list-group-item col-xs-12 link-to-section" ng-click="showCepage(prelevement)" ng-class="{ 'list-group-item-success': prelevement.termine, 'list-group-item-danger': (prelevement.erreurs)}">
                            <div class="col-xs-1">
                                <strong style="font-size: 32px;">{{ prelevement.anonymat_degustation }}</strong>
                            </div>
                            <div class="col-xs-4">
                                <span class="lead">{{ prelevement.libelle }}</span>
                            </div>
                            <div class="col-xs-5 text-left">
                                <span ng-show="prelevement.termine" ng-repeat="note_key in notes_key"><span>{{ notes[note_key] }} : <span>{{ prelevement.notes[note_key].note }}</span> <small>({{ prelevement.notes[note_key].defauts.join(', ') }})</small></span><br /></span>
                                <div ng-show="prelevement.appreciations "><small><i>{{ prelevement.appreciations }}</i></small></div>
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
            Vous n'êtes plus authentifié à la plateforme, veuiller vous <a href="<?php echo url_for("degustation_degustation", array('sf_subject' => $tournee, 'commission' => $commission)) ?>">reconnecter</a> pour pouvoir transmettre vos données.</a>
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
        </section>

        <section ng-repeat="prelevement in prelevements" ng-show="active == 'cepage_' + prelevement.anonymat_degustation">
            <div href="" ng-click="precedent()" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></div>
            <div class="page-header text-center">
                <h2>Lot n°{{ prelevement.anonymat_degustation }} de {{ prelevement.libelle }}</h2>
            </div>
            <div class="row">
                <div class="col-xs-12">
                   <form class="form-horizontal">
                        <?php foreach(DegustationClient::$note_type_libelles as $key_note_type => $note_type_libelle): ?>
                        <div class="form-group form-group-lg" ng-class="{ 'has-error': prelevement.notes.<?php echo $key_note_type ?>.erreurs }">
                            <div class="col-xs-12">
                                <div class="col-xs-3 text-right">
                                <label class="control-label lead"><?php echo $note_type_libelle ?></label>
                                <?php if(isset(DegustationClient::$note_type_libelles_help[$key_note_type])): ?><div class="text-muted"><?php echo DegustationClient::$note_type_libelles_help[$key_note_type] ?></div><?php endif; ?>
                                </div>
                                <div class="col-xs-2">
                                    <select ng-model="prelevement.notes.<?php echo $key_note_type ?>.note" class="form-control input-lg">
                                        <?php foreach(DegustationClient::$note_type_notes[$key_note_type] as $key_note_note => $libelle_note_note): ?>
                                        <option value="<?php echo $key_note_note ?>"><?php echo $libelle_note_note ?></option>
                                        <?php endforeach; ?>
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