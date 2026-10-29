<?php use_helper("Date"); ?>
<?php use_javascript('lib/angular.min.js') ?>
<?php use_javascript('lib/angular-local-storage.min.js') ?>
<?php use_javascript('tournee.js?201710101634'); ?>

<ol class="breadcrumb hidden-xs hidden-sm">
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href="<?php echo url_for('degustation_visualisation', $tournee); ?>"><?php echo $tournee->getLibelle(); ?>  le <?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></a></li>
  <li><a href="<?php echo url_for('degustation_degustations', $tournee); ?>">Commissions</a></li>
  <li class="active"><a href="">Dégustation</a></li>
</ol>

<div ng-app="myApp" ng-init='url_json="<?php echo url_for("degustation_degustation_json", array('sf_subject' => $tournee, 'commission' => $commission, 'unlock' => !$lock)) ?>"; url_state="<?php echo url_for('auth_state') ?>"; commission=<?php echo $commission ?>; notes=<?php echo json_encode(DegustationClient::getInstance()->getNotesTypeByAppellation($tournee->appellation)) ?>; defauts=<?php echo json_encode(DegustationClient::$note_type_defauts, JSON_HEX_APOS) ?>;'>
    <div ng-controller="degustationCtrl">
        <section ng-show="active == 'recapitulatif'">
            <a href="<?php echo url_for("degustation_degustations", $tournee) ?>" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></a>
            <?php if($lock): ?><span class="pull-right"><span class="glyphicon glyphicon-lock"></span></span><?php endif; ?>
            <div class="page-header text-center">
                <h2>Commission {{ commission }}</small></h2>
            </div>
            <div ng-show="!loaded" class="row">
                <div class="col-xs-12 text-center lead text-muted-alt" style="padding-top: 30px;">Chargement en cours ...</div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="list-group">
                        <a href="" ng-repeat="prelevement in prelevements | orderBy: ['anonymat_degustation']" class="list-group-item col-xs-12 link-to-section" ng-click="showCepage(prelevement)" ng-class="{ 'list-group-item-success': prelevement.termine, 'list-group-item-danger': (prelevement.has_erreurs)}">
                            <div class="col-xs-1">
                                <strong style="font-size: 32px;">{{ prelevement.anonymat_degustation }}</strong>
                            </div>
                            <div class="col-xs-5">
                                <span class="lead" ng-show="prelevement.libelle">{{ prelevement.libelle }}</span>
                                <span class="text-muted" ng-show="prelevement.fermentation_lactique"><br />FML</span>
                                <span class="text-muted" ng-show="prelevement.composition"><br /> {{prelevement.composition}}</span>
                                <span ng-show="!prelevement.libelle && !prelevement.composition">(Sans mention de cépage)</span>
                            </div>
                            <div class="col-xs-5 text-left">
                                <span ng-show="prelevement.termine" ng-repeat="note_key in notes_key"><span>{{ notes[note_key] }} : <span>{{ prelevement.notes[note_key].note }}</span> <small ng-show="prelevement.notes[note_key].defauts.length">({{ prelevement.notes[note_key].defauts.join(', ') }})</small></span><br /></span>
                                <div ng-show="prelevement.appreciations "><small><i>{{ prelevement.appreciations }}</i></small></div>
                            </div>
                            <div class="col-xs-1 text-right">
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
        </section>

        <section ng-repeat="prelevement in prelevements" ng-show="active == 'cepage_' + prelevement.anonymat_degustation + prelevement.degustation_id + prelevement.hash_produit">
            <div href="" ng-click="precedent()" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></div>
            <div class="page-header text-center">
                <h2>Lot n° {{ prelevement.anonymat_degustation }} <span ng-show="prelevement.libelle">de {{ prelevement.libelle }}</span> <small class="text-muted-alt" ng-show="prelevement.fermentation_lactique"> - FML</small>
                <small class="text-muted-alt" ng-show="prelevement.composition"> - {{prelevement.composition}}</small>
                <small ng-show="!prelevement.libelle && !prelevement.composition">(Sans mention de cépage)</small></h2>
            </div>
            <div class="row">
                <div class="col-xs-12">
                   <form class="form-horizontal form-group-lg">
                        <div class="col-xs-12">
                            <div class="col-xs-3 col-md-3 col-lg-2 text-right"></div>
                            <div class="col-xs-2 col-md-3 col-lg-3 text-muted-alt lead text-center">Note</div>
                            <div class="col-xs-7 col-md-6 col-lg-7 text-muted-alt lead text-center">Défauts</div>
                        </div>
                        <?php foreach(DegustationClient::getInstance()->getNotesTypeByAppellation($tournee->appellation) as $key_note_type => $note_type_libelle): ?>
                        <div class="form-group form-group-lg" ng-class="{ 'has-error': prelevement.notes.<?php echo $key_note_type ?>.has_erreurs }">
                            <div class="col-xs-12">
                                <div class="col-xs-3 col-md-3 col-lg-2 text-right">
                                <label class="control-label lead"><?php echo $note_type_libelle ?></label>
                                <?php if(isset(DegustationClient::$note_type_libelles_help[$key_note_type])): ?><div class="text-muted"><?php echo DegustationClient::$note_type_libelles_help[$key_note_type] ?></div><?php endif; ?>
                                </div>
                                <div class="col-xs-2 col-md-3 col-lg-3">
                                    <select ng-model="prelevement.notes.<?php echo $key_note_type ?>.note" class="form-control input-lg">
                                        <?php foreach(DegustationClient::$note_type_notes[$key_note_type] as $key_note_note => $libelle_note_note): ?>
                                        <option value="<?php echo $key_note_note ?>"><?php echo $libelle_note_note ?></option>
                                        <?php endforeach; ?>
                                        <option value="X">X - Échantillon non dégusté</option>
                                    </select>
                                </div>
                                <div class="col-xs-7 col-md-6 col-lg-7">
                                    <button ng-class="{ 'btn-danger': prelevement.notes.<?php echo $key_note_type ?>.has_erreurs && prelevement.notes.<?php echo $key_note_type ?>.erreurs['defaut'] }" ng-click="showAjoutDefaut(prelevement, '<?php echo $key_note_type ?>')" class="btn btn-warning btn-lg"><span class="glyphicon glyphicon-plus-sign"></span></button>
                                    <div class="btn-group">
                                        <button class="btn btn-default btn-default-step btn-lg" confirm="Etes vous sûr de vouloir supprimer ce défaut ?" ng-repeat="defaut in prelevement.notes.<?php echo $key_note_type ?>.defauts" ng-click="removeDefaut(prelevement, '<?php echo $key_note_type ?>', defaut)" >{{ defaut }}&nbsp;&nbsp;</button>
                                    </div>
                                </select>
                                </div>
                            </div>

                        </div>
                        <?php endforeach; ?>
                        <div ng-show="prelevement.has_erreurs && prelevement.erreurs['requis']" class="alert alert-danger text-center">
                            Vous devez saisir toutes les notes
                        </div>
                        <div ng-show="prelevement.has_erreurs && prelevement.erreurs['defaut']" class="alert alert-danger text-center">
                            Vous devez saisir au moins un défaut pour les notes 0, 1, 2, C ou D
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
                    <a href="" ng-click="valider(prelevement)" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Valider et Continuer</a>
                </div>
            </div>
        </section>

        <section ng-show="active == 'ajout_defaut'">
            <div href="" ng-click="showCepage(ajout_defaut.prelevement)" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></div>
            <div class="page-header text-center">
                <h2>Ajouter un défaut</h2>
            </div>
            <div class="row">
                <div class="col-xs-12 form-horizontal">
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input type="search" placeholder="Rechercher ou saisir un défaut" class="form-control input-lg" ng-keypress="blurOnEnter($event)" ng-change="" ng-model="ajout_defaut.query" />
                        </div>
                    </div>
                    <div class="list-group">
                        <a class="list-group-item lead" href="" ng-repeat="defaut in defauts_filter = (defauts[ajout_defaut.note_key] | filter: ajout_defaut.query)" ng-click="ajouterDefaut(ajout_defaut.prelevement, ajout_defaut.note_key, defaut)">{{ defaut }}</a>
                        <a ng-show="!defauts_filter.length" class="list-group-item lead" href="" ng-click="ajouterDefaut(ajout_defaut.prelevement, ajout_defaut.note_key, ajout_defaut.query)">{{ ajout_defaut.query }}</a>
                    </div>
                </div>
            </div>
            <div class="row row-margin">
                <div class="col-xs-6">
                    <a href="" ng-click="showCepage(ajout_defaut.prelevement)" class="btn btn-danger btn-lg col-xs-6 btn-block btn-upper link-to-section">Annuler</a>
                </div>
                <div class="col-xs-6">
                    <a ng-show="defauts_filter.length > 1" ng-disabled="defauts_filter.length != 1" ng-click="ajouterDefaut(ajout_defaut.prelevement, ajout_defaut.note_key, defauts_filter[0])" href="" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Ajouter</a>
                    <a ng-show="!defauts_filter.length" ng-click="ajouterDefaut(ajout_defaut.prelevement, ajout_defaut.note_key, ajout_defaut.query)" href="" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Ajouter</a>
                </div>
            </div>
        </section>
    </div>
</div>
