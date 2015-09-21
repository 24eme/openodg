<?php use_helper("Date"); ?>
<?php use_javascript('lib/angular.min.js') ?>
<?php use_javascript('lib/angular-local-storage.min.js') ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_javascript('/js/lib/signature_pad.min.js'); ?>
<?php use_javascript('tournee_vtsgn.js?201505080324'); ?>
<div ng-app="myApp" ng-init='produits =<?php echo json_encode($produits->getRawValue(), JSON_HEX_APOS); ?>; contenants =<?php echo json_encode($contenants->getRawValue(), JSON_HEX_APOS); ?>; raisons_refus =<?php echo json_encode($raisonsRefus->getRawValue(), JSON_HEX_APOS); ?>; url_json = "<?php echo url_for("tournee_rendezvous_agent_json", array('sf_subject' => $tournee, 'unlock' => !$lock)) ?>"; reload = "1"; url_state = "<?php echo url_for('auth_state') ?>"; date = "<?php echo $tournee->date ?>"; signatureImg = null;'>
    <div ng-controller="tournee_vtsgnCtrl">    
        <section ng-show="active == 'recapitulatif'" class="visible-print-block" id="mission" style="page-break-after: always;">
            <div class="text-center page-header">
                <a href="<?php echo url_for('tournee_agent_accueil'); ?>" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></a>
                <?php if ($lock): ?><span class="pull-right"><span class="glyphicon glyphicon-lock"></span></span><?php endif; ?>
                <h2>Tournée du<span class="hidden-sm hidden-md hidden-lg"><br /></span><span class="hidden-xs">&nbsp;</span><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?>&nbsp;<span class="hidden-lg hidden-md hidden-sm"><br /></span><span class="hidden-xs text-muted-alt"> - </span><span class="text-muted-alt" style="font-weight: normal"><?php echo $tournee->getFirstAgent()->nom ?></span></h2>
            </div>      
            <div ng-show="!loaded" class="row">
                <div class="col-xs-12 text-center lead text-muted-alt" style="padding-top: 30px;">Chargement en cours ...</div>
            </div>
            <div class="row" ng-show="loaded">
                <div class="col-xs-12">
                    <div class="list-group print-list-group-condensed">
                        <a ng-repeat="constatRdv in planification| orderBy: ['typerendezvous', 'heure']" href="" ng-click="mission(constatRdv)" ng-class="{ 'list-group-item-success': constatRdv['rendezvous'].termine }" class="list-group-item col-xs-12 link-to-section" style="padding-right: 0; padding-left: 0;">
                            <div class="col-xs-2 col-sm-2 text-left">
                                <strong ng-show="constatRdv['isRendezvousRaisin']" class="lead" style="font-weight: bold;">{{ constatRdv['rendezvous'].heure}}</strong>
                                <label ng-show="constatRdv['rendezvous'].transmission_collision" class="btn btn-xs btn-danger">Collision</label>    
                            </div>
                            <div class="col-xs-1 col-sm-1">
                                <span ng-show="constatRdv['isRendezvousRaisin']" class="icon-raisins" style="font-size: 20px;" ></span>
                                <span ng-show="!constatRdv['isRendezvousRaisin']" class="icon-mouts" style="font-size: 20px;" ></span>
                            </div>
                            <div class="col-xs-7 col-sm-6">
                                <strong class="lead">{{ constatRdv['rendezvous'].compte_raison_sociale}}</strong><span class="text-muted hidden-xs"> {{ constatRdv['rendezvous'].compte_cvi}}</span><!--<span ng-show="constatRdv['rendezvous'].termine && constatRdv['rendezvous'].nb_prelevements">&nbsp;<button class="btn btn-xs btn-success"></button>--></span>
                                <br />
                                {{ constatRdv['rendezvous'].compte_adresse}}, {{ constatRdv['rendezvous'].compte_code_postal}} {{ constatRdv['rendezvous'].compte_commune}}<span class="text-muted hidden-xs">&nbsp;&nbsp;&nbsp;<span class="glyphicon glyphicon-phone-alt"></span>&nbsp;{{ (constatRdv['rendezvous'].compte_telephone_mobile) ? constatRdv['rendezvous'].compte_telephone_mobile : constatRdv['rendezvous'].compte_telephone_bureau}}</span>
                                <br />
                            </div>
                            <div class="col-xs-2 col-sm-1 text-right">
                                <span ng-if="!constatRdv['rendezvous'].termine" class="glyphicon glyphicon-unchecked" style="font-size: 28px; margin-top: 8px;"></span>
                                <span ng-if="constatRdv['rendezvous'].termine" class="glyphicon glyphicon-check" style="font-size: 28px; margin-top: 8px;"></span>
                            </div>   
                            <div class="col-xs-12 col-sm-2 text-right">
                                <span ng-show="constatRdv['rendezvous'].nb_non_saisis" class="label label-default" style="" >{{ constatRdv['rendezvous'].nb_non_saisis }} non saisi(s)</span>
                                <span ng-show="constatRdv['rendezvous'].nb_approuves" class="label label-success" style="" >{{ constatRdv['rendezvous'].nb_approuves}} approuvé(s)</span>
                                <span ng-show="constatRdv['rendezvous'].nb_refuses" class="label label-danger" style="" >{{ constatRdv['rendezvous'].nb_refuses}} refusé(s)</span>
                            </div>
                            <div ng-show="constatRdv['rendezvous'].rendezvous_commentaire != ''" class="col-xs-12 col-sm-12 text-left" >
                            <span  class="glyphicon glyphicon-warning-sign" style="font-size: 18pt; padding-right: 10px;"></span>&nbsp;&nbsp;{{ constatRdv['rendezvous'].rendezvous_commentaire }}
                            </div>
                            <div ng-show="!constatRdv['isRendezvousRaisin']" class="col-xs-12 col-sm-12 text-center" >
                                <span>Constat raisin à {{ constatRdv['rendezvous'].heure}}</span>
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

        <div ng-repeat="constatRdv in planification" id="detail_mission_{{ constatRdv['idrdv']}}">
            <section  ng-show="active == 'mission' && activeRdv == constatRdv" ng-class="" style="page-break-after: always;">
                <div href="" ng-click="precedent(constatRdv)" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></div>

                <div class="page-header text-center">
                    <h2><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></h2>

                    <h2>Rendez-vous de {{ constatRdv['heure']}}<br /><span class="lead">{{ constatRdv['rendezvous'].compte_raison_sociale}}</span></h2>
                </div>
                <div class="row">
                    <div class="text-center col-xs-12">
                        <span class="lead">{{ constatRdv['rendezvous'].compte_adresse}}</span><br />
                        <span class="lead">{{ constatRdv['rendezvous'].compte_code_postal}} {{ constatRdv['rendezvous'].compte_commune}}</span><br /><br />
                        <span ng-show="constatRdv['rendezvous'].compte_telephone_bureau"><abbr >Bureau</abbr> : <a class="btn-link" href="tel:{{ constatRdv['rendezvous'].compte_telephone_bureau}}">{{ constatRdv['rendezvous'].compte_telephone_bureau}}</a><br /></span>
                        <span ng-show="constatRdv['rendezvous'].compte_telephone_prive"><abbr>Privé</abbr> : <a class="btn-link" href="tel:{{ constatRdv['rendezvous'].compte_telephone_prive}}">{{ constatRdv['rendezvous'].compte_telephone_prive}}</a><br /></span>
                        <span ng-show="constatRdv['rendezvous'].compte_telephone_mobile"><abbr>Mobile</abbr> : <a class="btn-link" href="tel:{{ constatRdv['rendezvous'].compte_telephone_mobile}}">{{ constatRdv['rendezvous'].compte_telephone_mobile}}</a><br /></span>
                    </div>
                </div>

                <div class="row" ng-show="constatRdv['rendezvous'].rendezvous_commentaire != ''" >
                    <div class="text-center col-xs-12 text-muted">
                        <span class="glyphicon glyphicon-warning-sign" style="font-size: 18pt;"></span>&nbsp;&nbsp;<strong class="lead" >Observations : {{ constatRdv['rendezvous'].rendezvous_commentaire}}</strong>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <h3 class="text-center">Constats</h3>
                        <div class="list-group">
                            <a href="" ng-click="showConstat(constat)" ng-class="{ 'list-group-item-success': ((constat.statut_raisin == '<?php echo ConstatsClient::STATUT_APPROUVE ?>' && constat.type_constat == 'raisin') || (constat.type_constat == 'volume' && constat.statut_volume == '<?php echo ConstatsClient::STATUT_APPROUVE ?>')), 'list-group-item-danger': ((constat.statut_raisin == '<?php echo ConstatsClient::STATUT_REFUSE ?>' && constat.type_constat == 'raisin') || (constat.type_constat == 'volume' && constat.statut_volume == '<?php echo ConstatsClient::STATUT_REFUSE ?>')) }" class="list-group-item" ng-repeat="(keyConstatNode,constat) in constatRdv['constats']">
                                <div ng-show="constat.type_constat == 'raisin'">
                                    <span style="font-size: 18px; margin-right: 6px;" class="icon-raisins"></span>

                                    <span ng-show="constat.statut_raisin == '<?php echo ConstatsClient::STATUT_NONCONSTATE ?>'">
                                        <span class="pull-right"><span class="label label-default">Non saisi</span></span>
                                        Saisir le constat raisin
                                    </span>

                                    <span ng-show="constat.statut_raisin == '<?php echo ConstatsClient::STATUT_APPROUVE ?>'">
                                        <span class="pull-right"><span class="label label-success">Approuvé</span></span>
                                        {{ constat.produit_libelle}}
                                        ({{ constat.nb_contenant}} {{ constat.contenant_libelle}}<span ng-show="constat.nb_contenant > 1">s</span>, {{ constat.degre_potentiel_raisin}}°)
                                    </span>

                                    <span ng-show="constat.statut_raisin == '<?php echo ConstatsClient::STATUT_REFUSE ?>'">
                                        <span class="pull-right"><span class="label label-danger">Refusé</span></span>
                                        {{ constat.raison_refus_libelle}}<span ng-show="constat.produit_libelle"><br /><small>{{ constat.produit_libelle}}</small></span>
                                    </span>
                                </div>
                                <div ng-show="constat.type_constat == 'volume'">
                                    <span style="font-size: 18px; margin-right: 6px;" class="icon-mouts"></span>
                                    <span ng-show="constat.statut_volume == '<?php echo ConstatsClient::STATUT_NONCONSTATE ?>'">
                                        <span class="pull-right"><span class="label label-default">Non saisi</span></span>
                                        Saisir le constat volume
                                        {{ constat.produit_libelle}} <span>(Constat raisin fait à {{ constatRdv['rendezvous'].heure}})</span>
                                    </span>
                                    <span ng-show="constat.statut_volume == '<?php echo ConstatsClient::STATUT_APPROUVE ?>'">
                                        <span class="pull-right"><span class="label label-success">Approuvé</span></span>
                                        {{ constat.produit_libelle}} <strong>{{ constat.type_vtsgn}}</strong>
                                        ({{ constat.degre_potentiel_volume}}°, {{ constat.volume_obtenu}} hl) <span>(Constat raisin fait à {{ constatRdv['rendezvous'].heure}})</span>
                                    </span>
                                    <span ng-show="constat.statut_volume == '<?php echo ConstatsClient::STATUT_REFUSE ?>'">
                                        <span class="pull-right"><span class="label label-danger">Refusé</span></span>
                                        {{ constat.raison_refus_libelle}}<span ng-show="constat.produit_libelle"><br /><small>{{ constat.produit_libelle}}</small></span>  <span>(Constat raisin fait à {{ constatRdv['rendezvous'].heure}})</span>
                                    </span>
                                </div>
                                <div ng-show="constat.type_constat == 'volume' && constat.commentaire_raisin" class="text-center">
                                    <span class="glyphicon glyphicon-info-sign"  style="font-size: 14pt; top: 5px;"></span>&nbsp;{{constat.commentaire_raisin}}
                                </div>
                            </a>
                        </div>                        
                        <div ng-show="constatRdv['rendezvous'].type_rendezvous == 'TYPE_RAISINS'">                       
                            <button ng-click="ajoutConstat(constatRdv)" class="btn btn-lg btn-block btn-default btn-default-step" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp; Ajouter un constat raisin</button>
                        </div>
                    </div>
                </div>
            </section>
            <section ng-repeat="(keyConstatNode,constat) in constatRdv['constats']" ng-show="activeRdv == constatRdv && activeConstat == constat && active != 'choix_produit'">
                <div ng-show="constat.type_constat == 'raisin'">
                    <div href="" ng-click="mission(constatRdv)" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></div>
                    <div class="page-header text-center">
                        <h2>Saisie constat raisin <br /><span class="lead">{{ constatRdv['rendezvous'].compte_raison_sociale}}</span></h2>
                    </div>
                    <?php include_partial('constats/tourneeConstatRaisin'); ?> 
                </div>
                <div ng-show="constat.type_constat == 'volume'">
                    <div href="" ng-click="mission(constatRdv)" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></div>
                    <div class="page-header text-center">
                        <h2>Saisie constat volume <br /><span class="lead">{{ constatRdv['rendezvous'].compte_raison_sociale}}</span></h2>
                    </div>
                    <?php include_partial('constats/tourneeConstatVolume'); ?> 
                </div>
            </section>
        </div>
        <div ng-show="active == 'choix_produit' && activeConstat">
            <div href="" ng-click="remplir(activeConstat)" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></div>
            <div class="page-header text-center">
                <h2>Sélectionner un produit</h2>
            </div>
            <h3>Filter par Appellation</h3>
            <div class="form-group">
                <span ng-repeat="produit in produitsAppellation">
                    <button ng-show="produitFilterAppellation.hash == produit.hash" class="btn btn-default btn-block" ng-click="resetFilterAppellation()" type="buttton"><span class="glyphicon glyphicon-remove-sign"></span> {{ produit.libelle}}</button>
                    <button ng-show="!produitFilterAppellation.hash" class="btn btn-default btn-block btn-default-step" ng-click="filterProduitsAppellation(produit.hash)" type="buttton" style="border: 1px solid #000000;">{{ produit.libelle}}</button>
                </span>
            </div>
            <h3>Filtrer par Cépage</h3>
            <div class="form-group">
                <span ng-repeat="produit in produitsCepage">
                    <button ng-show="produitFilterCepage.hash == produit.hash" class="btn btn-default btn-block" ng-click="resetFilterCepage()" type="buttton"><span class="glyphicon glyphicon-remove-sign"></span> {{ produit.libelle}}</button>
                    <button ng-show="!produitFilterCepage.hash" class="btn btn-default btn-default-step btn-block" ng-click="filterProduitsCepage(produit.hash)" type="buttton" style="border: 1px solid #000000;">{{ produit.libelle}}</button>
                </span>
            </div>
            <h3>Liste des produits</h3>
            <div class="list-group">
                <a href="" ng-click="choixProduit(produit)" ng-repeat="produit in produitsAll| filter : produitFilterAppellation | filter: produitFilterCepage" class="list-group-item" style="border: 1px solid #000000;">{{ produit.libelle}}</a>
            </div>
        </div>
    </div>
</div>