<?php use_helper("Date"); ?>
<?php use_javascript('lib/angular.min.js') ?>
<?php use_javascript('lib/angular-local-storage.min.js') ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_javascript('tournee_vtsgn.js?201505080324'); ?>
<div ng-app="myApp" ng-init='url_json="<?php echo url_for("tournee_rendezvous_agent_json", array('sf_subject' => $tournee, 'unlock' => !$lock)) ?>"; reload="1"; url_state="<?php echo url_for('auth_state') ?>";'>
<div ng-controller="tournee_vtsgnCtrl">    
    <section ng-show="active == 'recapitulatif'" class="visible-print-block" id="mission" style="page-break-after: always;">        
        <div ng-show="!loaded" class="row">
            <div class="col-xs-12 text-center lead text-muted-alt" style="padding-top: 30px;">Chargement en cours ...</div>
        </div>
        <div class="row" ng-show="loaded">
            <div class="col-xs-12">
                <div class="list-group print-list-group-condensed">
                    <a ng-repeat="(key,rdv) in planification | orderBy: ['position']" href="" ng-click="updateActive(key)" ng-class="{ 'list-group-item-success': operateur.termine && operateur.nb_prelevements, 'list-group-item-danger': (operateur.has_erreurs), 'list-group-item-warning': operateur.termine && !operateur.nb_prelevements }" class="list-group-item col-xs-12 link-to-section" style="padding-right: 0; padding-left: 0;">
                        <div class="col-xs-2 col-sm-1 text-left">
                            <strong ng-show="!rdv['rendezvous'].termine || rdv['rendezvous'].nb_prelevements" class="lead" style="font-weight: bold;">{{ rdv['rendezvous'].heure_reelle }}</strong>
                            <strong ng-show="rdv['rendezvous'].termine && !rdv['rendezvous'].nb_prelevements" class="lead" style="text-decoration: line-through;">{{ rdv['rendezvous'].heure_reelle }}</strong><br />
                            <label ng-show="rdv['rendezvous'].transmission_collision" class="btn btn-xs btn-danger">Collision</label>
                        </div>
                        <div class="col-xs-8 col-sm-10">
                            <strong class="lead">{{ rdv['rendezvous'].compte_raison_sociale }}</strong><span class="text-muted hidden-xs"> {{ rdv['rendezvous'].compte_cvi }}</span><span ng-show="rdv['rendezvous'].termine && rdv['rendezvous'].nb_prelevements">&nbsp;<button class="btn btn-xs btn-success"></button></span>
                            <br />
                            {{ rdv['rendezvous'].compte_adresse }}, {{ rdv['rendezvous'].compte_code_postal }} {{ rdv['rendezvous'].compte_commune }}<span class="text-muted hidden-xs">&nbsp;&nbsp;&nbsp;<span class="glyphicon glyphicon-phone-alt"></span>&nbsp;{{ (rdv['rendezvous'].compte_telephone_mobile) ? rdv['rendezvous'].compte_telephone_mobile : rdv['rendezvous'].compte_telephone_bureau }}</span>
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
    
   <section ng-repeat="(key,rdv) in planification" id="detail_mission_{{ key }}" ng-show="active == key" ng-class="" style="page-break-after: always;">
        <div href="" ng-click="precedent(rendezvous)" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></div>
        <div class="page-header text-center">
            <h2>Constat {{ rdv['rendezvous'].heure_reelle }}</h2>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <address>
                  <span class="lead"><strong>{{ rdv['rendezvous'].compte_raison_sociale }}</strong> <small class="hidden-xs">({{ rdv['rendezvous'].compte_cvi }})</small></span><br />
                  <span class="lead">{{ rdv['rendezvous'].compte_adresse }}</span><br />
                  <span class="lead">{{ rdv['rendezvous'].compte_code_postal }} {{ rdv['rendezvous'].compte_commune }}</span><br /><br />
                  <span ng-if="rdv['rendezvous'].compte_telephone_bureau"><abbr >Bureau</abbr> : <a class="btn-link" href="tel:{{ rdv['rendezvous'].compte_telephone_bureau }}">{{ rdv['rendezvous'].compte_telephone_bureau }}</a><br /></span>
                  <span ng-if="rdv['rendezvous'].compte_telephone_prive"><abbr>Privé</abbr> : <a class="btn-link" href="tel:{{ rdv['rendezvous'].compte_telephone_prive }}">{{ rdv['rendezvous'].compte_telephone_prive }}</a><br /></span>
                  <span ng-if="rdv['rendezvous'].compte_telephone_mobile"><abbr>Mobile</abbr> : <a class="btn-link" href="tel:{{ rdv['rendezvous'].compte_telephone_mobile }}">{{ rdv['rendezvous'].compte_telephone_mobile }}</a><br /></span>
                </address>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div style="border-bottom: 0;" class="page-header">
                    <h3 style="margin-top: 15px" class="text-warning">
<!--                    <a class="text-warning" href="" ng-click="toggleOperateurAucun(operateur)">
                    <span class="ng-hide visible-print-inline"><span class="glyphicon glyphicon-unchecked" style="font-size: 20px;"></span></span>
                    <span ng-show="!operateur.aucun_prelevement" class="glyphicon glyphicon-unchecked hidden-print" style="font-size: 20px;"></span><span ng-show="operateur.aucun_prelevement" class="glyphicon glyphicon-check hidden-print" style="font-size: 20px;"></span>&nbsp;&nbsp;<strong class="lead text-warning">Aucun prélèvement</strong><span class="ng-hide visible-print-inline lead"> : Report, Plus de vin, Soucis, Déclassement  <small><i>(Veuillez entourer la raison)</i></small></span>
                    </a>-->
                    </h3>
                </div>                
            </div>
            <div ng-class="{ 'bloc-transparent': !rdv['constats'].produit }" ng-show="!operateur.aucun_prelevement" id="saisie_mission_{{ rdv['constats']._id }}_{{ rdv['constats'] }}" class="col-xs-12 print-margin-bottom">
                <div class="page-header form-inline print-page-header" style="margin-top: 5px">
                    <h3 style="margin-top: 15px" ng-class="{ 'text-danger': prelevement.erreurs['hash_produit'] }">
                    <span ng-show="!prelevement.show_produit && prelevement.hash_produit" class="lead" ng-click="togglePreleve(prelevement)">{{ prelevement.libelle }}<small ng-show="prelevement.libelle_produit" class="text-muted-alt"> ({{ prelevement.libelle_produit }})</small></span>
                    <select style="display: inline-block; width: auto;" class="hidden-print form-control" ng-change="updateProduit(prelevement)" ng-model="prelevement.produit" ng-options="produit.libelle_complet for produit in produits track by produit.trackby"></select>
                    </h3>
                </div>
               
                <div class="visible-print-block" class="row">
                    <div class="col-xs-12">
                        <div class="form-horizontal">
                            <div ng-class="{ 'hidden': !prelevement.erreurs['hash_produit'] }" class="alert alert-danger">
                            Vous devez séléctionner un cépage
                            </div>
                            <div ng-class="{ 'hidden': !prelevement.erreurs['cuve'] }" class="alert alert-danger">
                                Vous devez saisir le(s) numéro(s) de cuve(s)
                            </div>
                            <div ng-show="!prelevement.aucun_prelevement" class="row">
                                
                                <div ng-class="{ 'has-error': prelevement.erreurs['cuve'] }" class="form-group col-xs-12 col-sm-6 col-md-4 lead">
                                    <div class="col-xs-7">
                                        <input id="cuve_{{ operateur._id }}_{{ prelevement_key }}" ng-model="prelevement.cuve" type="text" class="form-control input-md hidden-sm hidden-md hidden-lg" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                                    </div>
                                    <label for="nb_{{ operateur._id }}_{{ prelevement_key }}" class="col-xs-5 control-label">N°&nbsp;Cuves&nbsp;:</label>
                                </div>
                                <div class="form-group col-xs-12 col-sm-6 col-md-4 lead">
                                    <label class="control-label col-xs-5" for="volume_{{ operateur._id }}_{{ prelevement_key }}"><strong>Vol.&nbsp;<small>(hl)</small></strong>&nbsp;:</label>
                                    <div class="col-xs-7">
                                        <input id="volume_{{ operateur._id }}_{{ prelevement_key }}" ng-model="prelevement.volume_revendique" type="number" class="form-control input-md hidden-sm hidden-md hidden-lg hidden-print" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                                        <input id="volume_{{ operateur._id }}_{{ prelevement_key }}" ng-model="prelevement.volume_revendique" type="number" class="form-control input-lg hidden-xs hidden-print" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                                        <input ng-model="prelevement.volume_revendique" type="text" class="form-control input-lg ng-hide visible-print-inline" /> 
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