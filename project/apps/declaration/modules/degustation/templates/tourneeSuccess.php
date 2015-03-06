<?php use_javascript('lib/angular.min.js') ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_javascript('tournee.js'); ?>

<div ng-app="myApp" ng-controller="tourneeCtrl">
    <section id="mission" style="page-break-after: always;">
        <div style="padding-left: 10px;" class="page-header">
            <h2>Mission du 23 Janvier 2015 <small>Vicky CHAN</small></h2>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="list-group">
                    <a ng-repeat="(key, operateur) in operateurs" href="#detail_mission_{{ key }}" class="list-group-item col-xs-12 link-to-section">
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
    <div ng-repeat="(key, operateur) in operateurs" style="page-break-after: always;">
        <section id="detail_mission_{{ key }}">
            <div style="padding-left: 10px;" class="page-header">
                <h2>Détail de la mission de {{ operateur.heure }}</h2>
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
                    <div id="carte_{{ key }}" data-title="" data-point="[[48.100901,7.36105]]" class="col-xs-12 carte hidden-print" style="height: 250px; margin-bottom: 20px;"></div>
                </div>
                <div class="col-xs-12 hidden-print">
                    <span class="lead text-muted">{{ operateur.prelevements.length }} lots à prélever</span>
                    <ul class="list-group">
                    <li ng-repeat="(prelevement_key, prelevement) in operateur.prelevements" class="list-group-item col-xs-12">
                       <div class="col-xs-1">
                           <strong>{{ prelevement_key + 1 }}</strong>
                       </div>
                       <div class="col-xs-11">
                            {{ prelevement.libelle }}
                       </div>
                    </li>
                    </ul>
                </div>
            </div>
            <div class="row row-margin hidden-print">
                <div class="col-xs-6">
                    <a href="#mission" class="btn btn-primary btn-lg btn-upper btn-block link-to-section">Retour</a>
                </div>
                <div class="col-xs-6">
                    <a href="#saisie_mission_{{ key }}" class="btn btn-warning btn-lg btn-upper btn-block link-to-section">Démarrer la Saisie</a>
                </div>
            </div>
        </section>
        <div id="saisie_mission_{{ key }}">
            <section ng-repeat="(prelevement_key, prelevement) in operateur.prelevements" id="saisie_mission_{{ key }}_{{ prelevement_key }}">
                <div style="padding-left: 10px;" class="page-header">
                    <h2>Lot n°{{ prelevement_key + 1 }} - {{ prelevement.libelle }} <small><a class="text-warning hidden-print" href="#">(changer)</a></small>
                    </h2>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <span class="lead"><input type="checkbox" value="" checked="checked"/>&nbsp;&nbsp;&nbsp;<strong>N° d'anonymat</strong> : {{ prelevement.anonymat_prelevement }}</span>
                                </div>
                                <div class="col-xs-6">
                                    <input type="text" class="form-control" placeholder="Saisir le numéro de cuve" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row hidden-print">
                    <div ng-show="(prelevement_key - 1) >= 0" class="col-xs-4">
                        <a href="#saisie_mission_{{ key }}_{{ prelevement_key - 1 }}" class="btn btn-primary btn-lg col-xs-6 btn-block btn-upper link-to-section">Précédent</a>
                    </div>
                    <div ng-show="(prelevement_key - 1) < 0" class="col-xs-4">
                        <a href="#detail_mission_{{ key }}" class="btn btn-primary btn-lg col-xs-4 btn-block btn-upper link-to-section">Précédent</a>
                    </div>
                    <div ng-show="(prelevement_key + 1) >= operateur.prelevements.length" class="col-xs-4">
                            <a href="#saisie_mission_" class="btn btn-warning btn-lg col-xs-6 btn-block btn-upper link-to-section">Saisir un autre</a>
                    </div>
                    <div ng-show="(prelevement_key + 1) < operateur.prelevements.length" class="col-xs-4 pull-right">
                        <a href="#saisie_mission_{{ key }}_{{ prelevement_key + 1 }}" class="btn btn-default btn-lg btn-block btn-upper link-to-section link-to-section">Continuer</a>
                    </div>
                    <div ng-show="(prelevement_key + 1) >= operateur.prelevements.length" class="col-xs-4 pull-right">
                        <a href="#mission" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Terminer</a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>