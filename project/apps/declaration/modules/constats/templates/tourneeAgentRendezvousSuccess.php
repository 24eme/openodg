<?php use_helper("Date"); ?>
<?php use_javascript('lib/angular.min.js') ?>
<?php use_javascript('lib/angular-local-storage.min.js') ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_javascript('/js/lib/signature_pad.min.js'); ?>
<?php use_javascript('tournee_vtsgn.js?201505080324'); ?>
<div ng-app="myApp" ng-init='produits =<?php echo json_encode($produits->getRawValue(), JSON_HEX_APOS); ?>; contenants =<?php echo json_encode($contenants->getRawValue(), JSON_HEX_APOS); ?>; url_json = "<?php echo url_for("tournee_rendezvous_agent_json", array('sf_subject' => $tournee, 'unlock' => !$lock)) ?>"; reload = "1"; url_state = "<?php echo url_for('auth_state') ?>"; signatureImg=null;'>
    <div ng-controller="tournee_vtsgnCtrl">    
        <section ng-show="active == 'recapitulatif'" class="visible-print-block" id="mission" style="page-break-after: always;">
            <div class="text-center page-header">
                <a href="" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></a>
                <?php if($lock): ?><span class="pull-right"><span class="glyphicon glyphicon-lock"></span></span><?php endif; ?>
                <h2>Tournée du<span class="hidden-sm hidden-md hidden-lg"><br /></span><span class="hidden-xs">&nbsp;</span><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?>&nbsp;<span class="hidden-lg hidden-md hidden-sm"><br /></span><span class="hidden-xs text-muted-alt"> - </span><span class="text-muted-alt" style="font-weight: normal"><?php echo $tournee->getFirstAgent()->nom ?></span></h2>
            </div>      
            <div ng-show="!loaded" class="row">
                <div class="col-xs-12 text-center lead text-muted-alt" style="padding-top: 30px;">Chargement en cours ...</div>
            </div>
            <div class="row" ng-show="loaded">
                <div class="col-xs-12">
                    <div class="list-group print-list-group-condensed">
                        <a ng-repeat="(key,rdv) in planification | orderBy: ['position']" href="" ng-click="mission(rdv)" ng-class="{ 'list-group-item-success': operateur.termine && operateur.nb_prelevements, 'list-group-item-danger': (operateur.has_erreurs), 'list-group-item-warning': operateur.termine && !operateur.nb_prelevements }" class="list-group-item col-xs-12 link-to-section" style="padding-right: 0; padding-left: 0;">
                            <div class="col-xs-2 col-sm-1 text-left">
                                <strong ng-show="!rdv['rendezvous'].termine || rdv['rendezvous'].nb_prelevements" class="lead" style="font-weight: bold;">{{ rdv['rendezvous'].heure}}</strong>
                                <strong ng-show="rdv['rendezvous'].termine && !rdv['rendezvous'].nb_prelevements" class="lead" style="text-decoration: line-through;">{{ rdv['rendezvous'].heure}}</strong><br />
                                <label ng-show="rdv['rendezvous'].transmission_collision" class="btn btn-xs btn-danger">Collision</label>
                            </div>
                            <div class="col-xs-8 col-sm-10">
                                <strong class="lead">{{ rdv['rendezvous'].compte_raison_sociale}}</strong><span class="text-muted hidden-xs"> {{ rdv['rendezvous'].compte_cvi}}</span><span ng-show="rdv['rendezvous'].termine && rdv['rendezvous'].nb_prelevements">&nbsp;<button class="btn btn-xs btn-success"></button></span>
                                <br />
                                {{ rdv['rendezvous'].compte_adresse}}, {{ rdv['rendezvous'].compte_code_postal}} {{ rdv['rendezvous'].compte_commune}}<span class="text-muted hidden-xs">&nbsp;&nbsp;&nbsp;<span class="glyphicon glyphicon-phone-alt"></span>&nbsp;{{ (rdv['rendezvous'].compte_telephone_mobile) ? rdv['rendezvous'].compte_telephone_mobile : rdv['rendezvous'].compte_telephone_bureau}}</span>
                                <br />
                            </div>
                            <div class="col-xs-2 col-sm-1 text-right">
                                <span ng-if="!rdv['rendezvous'].termine" class="glyphicon glyphicon-unchecked" style="font-size: 28px; margin-top: 8px;"></span>
                                <span ng-if="rdv['rendezvous'].termine" class="glyphicon glyphicon-check" style="font-size: 28px; margin-top: 8px;"></span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div ng-show="!state" class="alert alert-warning col-xs-12" style="margin-top: 10px;">
                Vous n'êtes plus authentifié à la plateforme, veuiller vous <a href="<?php echo url_for("degustation_tournee", array('sf_subject' => $tournee, 'agent' => $agent->getKey(), 'date' => $date)) ?>">reconnecter</a> pour pouvoir transmettre vos données.</a>
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
                <div class="col-sm-6 hidden-xs">
                    <a href="" ng-click="print()" class="btn btn-default-step btn-lg btn-upper btn-block"><span class="glyphicon glyphicon-print"></span>&nbsp;&nbsp;Imprimer</a>
                </div>
                <div class="col-xs-12 col-sm-6 text-center">
                    <a href="" ng-show="!transmission_progress" ng-click="transmettre(false)" class="btn btn-warning btn-lg btn-upper btn-block"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;Transmettre</a>
                    <span class="text-muted-alt" ng-show="transmission_progress">Transmission en cours...</span>
                </div>
            </div>
        </section>

        <div ng-repeat="(key,rdv) in planification" id="detail_mission_{{ key}}">
            <section  ng-show="active == 'mission' && activeRdv == rdv" ng-class="" style="page-break-after: always;">
                <div href="" ng-click="precedent(rendezvous)" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></div>
                <div class="page-header text-center">
                    <h2>Rendez-vous de {{ rdv['rendezvous'].heure }}<br /><span class="lead">{{ rdv['rendezvous'].compte_raison_sociale}}</span></h2>
                </div>
                <div class="row">
                    <div class="text-center col-xs-12">
                            <span class="lead">{{ rdv['rendezvous'].compte_adresse}}</span><br />
                            <span class="lead">{{ rdv['rendezvous'].compte_code_postal}} {{ rdv['rendezvous'].compte_commune}}</span><br /><br />
                            <span ng-show="rdv['rendezvous'].compte_telephone_bureau"><abbr >Bureau</abbr> : <a class="btn-link" href="tel:{{ rdv['rendezvous'].compte_telephone_bureau}}">{{ rdv['rendezvous'].compte_telephone_bureau}}</a><br /></span>
                            <span ng-show="rdv['rendezvous'].compte_telephone_prive"><abbr>Privé</abbr> : <a class="btn-link" href="tel:{{ rdv['rendezvous'].compte_telephone_prive}}">{{ rdv['rendezvous'].compte_telephone_prive}}</a><br /></span>
                            <span ng-show="rdv['rendezvous'].compte_telephone_mobile"><abbr>Mobile</abbr> : <a class="btn-link" href="tel:{{ rdv['rendezvous'].compte_telephone_mobile}}">{{ rdv['rendezvous'].compte_telephone_mobile}}</a><br /></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <h3 class="text-center">Constats</h3>
                        <div class="list-group">
                            <a href="" ng-click="showConstat(constat)" ng-class="{ 'list-group-item-success': ((constat.statut_raisin == '<?php echo ConstatsClient::STATUT_APPROUVE ?>' && constat.type_constat == 'raisin') || (constat.type_constat == 'volume' && constat.statut_volume == '<?php echo ConstatsClient::STATUT_APPROUVE ?>')), 'list-group-item-danger': ((constat.statut_raisin == '<?php echo ConstatsClient::STATUT_REFUSE ?>' && constat.type_constat == 'raisin') || (constat.type_constat == 'volume' && constat.statut_volume == '<?php echo ConstatsClient::STATUT_REFUSE ?>')) }" class="list-group-item" ng-repeat="(keyConstatNode,constat) in rdv['constats']">
                                <div ng-show="constat.type_constat == 'raisin'">
                                    <span style="font-size: 18px; margin-right: 6px;" class="icon-raisins"></span>
                                    
                                    <span ng-show="constat.statut_raisin == '<?php echo ConstatsClient::STATUT_NONCONSTATE ?>'">
                                        <span class="pull-right"><span class="label label-default">Non saisi</span></span>
                                        Saisir le constat raisin
                                    </span>
                                    
                                    <span ng-show="constat.statut_raisin == '<?php echo ConstatsClient::STATUT_APPROUVE ?>'">
                                        <span class="pull-right"><span class="label label-success">Approuvé</span></span>
                                        {{ constat.produit_libelle }}
                                        ({{ constat.nb_botiche }} {{ constat.contenant_libelle }}<span ng-show="constat.nb_botiche > 1">s</span>, {{ constat.degre_potentiel_raisin }}°)
                                        
                                    </span>

                                    <span ng-show="constat.statut_raisin == '<?php echo ConstatsClient::STATUT_REFUSE ?>'">
                                    <span class="pull-right"><span class="label label-danger">Refusé</span></span>
                                    {{ constat.raison_refus }}<span ng-show="constat.produit_libelle"><br /><small>{{ constat.produit_libelle }}</small></span>
                                    </span>
                                </div>
                                <div ng-show="constat.type_constat == 'volume'">
                                    <span style="font-size: 18px; margin-right: 6px;" class="icon-mouts"></span>
                                    <span ng-show="constat.statut_volume == '<?php echo ConstatsClient::STATUT_NONCONSTATE ?>'">
                                        <span class="pull-right"><span class="label label-default">Non saisi</span></span>
                                        Saisir le constat volume
                                    </span>
                                    <span ng-show="constat.statut_volume == '<?php echo ConstatsClient::STATUT_APPROUVE ?>'">
                                    <span class="pull-right"><span class="label label-success">Approuvé</span></span>
                                    {{ constat.produit_libelle }}
                                    ({{ constat.degre_potentiel_volume }}°, {{ constat.volume_obtenu }} hl)
                                    </span>
                                    <span ng-show="constat.statut_volume == '<?php echo ConstatsClient::STATUT_REFUSE ?>'">
                                    <span class="pull-right"><span class="label label-danger">Refusé</span></span>
                                    {{ constat.raison_refus }}<span ng-show="constat.produit_libelle"><br /><small>{{ constat.produit_libelle }}</small></span>
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
            <section ng-repeat="(keyConstatNode,constat) in rdv['constats']" ng-show="activeRdv == rdv && activeConstat == constat">
                <div ng-show="constat.type_constat == 'raisin'">
                    <div href="" ng-click="mission(rdv)" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></div>
                    <div class="page-header text-center">
                        <h2>Saisie constat raisin <br /><span class="lead">{{ rdv['rendezvous'].compte_raison_sociale}}</span></h2>
                    </div>
                    <?php include_partial('constats/tourneeConstatRaisin'); ?> 
                </div>
                <div ng-show="constat.type_constat == 'volume'">
                    <div href="" ng-click="mission(rdv)" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></div>
                    <div class="page-header text-center">
                        <h2>Saisie constat volume <br /><span class="lead">{{ rdv['rendezvous'].compte_raison_sociale}}</span></h2>
                    </div>
                    <?php include_partial('constats/tourneeConstatVolume'); ?> 
                </div>
            </section>
        </div>
    </div>
</div>