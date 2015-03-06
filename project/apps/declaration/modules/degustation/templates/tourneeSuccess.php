<?php use_javascript('lib/angular.min.js') ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_javascript('tournee.js'); ?>

<div ng-app="myApp" ng-controller="tourneeCtrl">
    <section ng-class="{'hidden': active != 'recap' }" id="mission" style="page-break-after: always;">
        <div style="padding-left: 10px;" class="page-header">
            <h2>Mission du 23 Janvier 2015 <small>Vicky CHAN</small></h2>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="list-group">
                    <a ng-repeat="(key, operateur) in operateurs" href="" ng-click="updateActive(key)" class="list-group-item col-xs-12 link-to-section">
                        <div class="col-xs-2">
                            <strong style="font-size: 32px;">{{ operateur.heure }}</strong>
                        </div>
                        <div class="col-xs-9">
                        <strong class="lead">{{ operateur.raison_sociale }}</strong><br />
                        {{ operateur.adresse }}, {{ operateur.code_postal }} {{ operateur.commune }}
                        </div>
                        <div class="col-xs-1">
                            <span class="glyphicon glyphicon-unchecked" style="font-size: 40px; margin-top: 5px;"></span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="row row-margin hidden-print">
            <div class="col-xs-6">
                <a href="<?php echo url_for('degustation') ?>" class="btn btn-default btn-default-step btn-lg btn-upper btn-block">Retour</a>
            </div>
            <div class="col-xs-6">
                <a href="" class="btn btn-warning btn-lg btn-upper btn-block link-to-section">Transmettre</a>
            </div>
        </div>
    </section>
    <section ng-repeat="(key, operateur) in operateurs" id="detail_mission_{{ key }}" ng-class="{'hidden': active != key }" style="page-break-after: avoid;">
        <div class="page-header text-center">
            <h2>Mission de {{ operateur.heure }}</h2>
        </div>

        <div class="row">
            <div class="col-xs-6">
                <address>
                  <span class="lead text-muted"><strong>{{ operateur.raison_sociale }}</strong></span><br />
                  <span class="lead">{{ operateur.adresse }}</span><br />
                  <span class="lead">{{ operateur.code_postal }} {{ operateur.commune }}</span><br /><br />
                  <abbr>Bureau</abbr> : 0389201627<br />
                  <abbr>Privé</abbr> : 0389201627<br />
                  <abbr>Mobile</abbr> : 0689201627<br />
                </address>
            </div>
            <div class="col-xs-6">
                <!--<div id="carte_{{ key }}" data-title="" data-point="[[{{ operateur.lat }},{{ operateur.lon }}]]" class="col-xs-12 carte hidden-print" style="height: 250px; margin-bottom: 20px;"></div>-->
            </div>
        </div>
        <div ng-repeat="(prelevement_key, prelevement) in operateur.prelevements" id="saisie_mission_{{ key }}_{{ prelevement_key }}">
            <div style="padding-left: 10px;" class="page-header">
                <h2>Lot n°{{ prelevement_key + 1 }} - {{ prelevement.libelle }} <small><a class="text-warning hidden-print" href="#">(changer)</a></small>
                </h2>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <div class="col-xs-6">
                                <input id="saisie_{{ key }}_{{ prelevement_key }}" ng-model="prelevement.saisie" type="checkbox" ng-true-value="1" ng-false-value="0" />&nbsp;&nbsp;&nbsp;
                                <span class="lead"><label for="saisie_{{ key }}_{{ prelevement_key }}">N° d'anonymat</label> : {{ prelevement.anonymat_prelevement }}</span>
                            </div>
                            <div class="col-xs-6">
                                <input ng-class="{ 'invisible': !prelevement.saisie }" ng-model="prelevement.cuve" type="text" class="form-control" placeholder="Saisir le numéro de cuve" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-margin hidden-print">
            <div class="col-xs-6">
                <a href="" ng-click="precedent()" class="btn btn-primary btn-lg col-xs-6 btn-block btn-upper link-to-section">Précédent</a>
            </div>
            <div class="col-xs-6 pull-right">
                <a href="" ng-click="precedent()" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Terminer</a>
            </div>
        </div>
    </section>
</div>