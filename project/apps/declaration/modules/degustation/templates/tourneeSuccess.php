<?php use_javascript('lib/angular.min.js') ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_javascript('tournee.js'); ?>
<div ng-app="myApp" ng-controller="tourneeCtrl">
<section id="mission">
    <div class="page-header">
        <h2>Mission du 23 Janvier 2015 <small>Vicky CHAN</small></h2>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="list-group">
                <a ng-repeat="(key, prelevement) in prelevements" href="#detail_mission_{{ key }}" class="list-group-item col-xs-12 link-to-section">
                    <div class="col-xs-2">
                        <strong style="font-size: 32px;">{{ prelevement.heure }}</strong>
                    </div>
                    <div class="col-xs-9">
                    <strong class="lead">{{ prelevement.raison_sociale }}</strong><br />
                    {{ prelevement.adresse }}, {{ prelevement.code_postal }} {{ prelevement.commune }}
                    </div>
                    <div class="col-xs-1">
                        <span class="glyphicon glyphicon-unchecked" style="font-size: 40px; margin-top: 5px;"></span>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-6">
            <a href="<?php echo url_for('degustation') ?>" class="btn btn-default btn-default-step btn-lg btn-upper btn-block">Retour</a>
        </div>
        <div class="col-xs-6">
            <a href="" class="btn btn-warning btn-lg btn-upper btn-block link-to-section">Transmettre</a>
        </div>
    </div>
</section>

<section ng-repeat="(key, prelevement) in prelevements" id="detail_mission_{{ key }}">
    <div class="page-header">
        <h2>Détail de la mission de {{ prelevement.heure }}</h2>
    </div>

    <div class="row">
        <div class="col-xs-6">
            <address>
              <span class="lead text-muted"><strong>{{ prelevement.raison_sociale }}</strong></span><br />
              <span class="lead">{{ prelevement.adresse }}</span><br />
              <span class="lead">{{ prelevement.code_postal }} {{ prelevement.commune }}</span><br /><br />
              <abbr title="Phone">Bureau</abbr> : 0389201627<br />
              <abbr title="Phone">Privé</abbr> : 0389201627<br />
              <abbr title="Phone">Mobile</abbr> : 0689201627<br />
            </address>
        </div>
        <div class="col-xs-6">
            <div id="carte_{{ key }}" data-title="" data-point="48.100901,7.36105" class="col-xs-12 carte" style="height: 250px; margin-bottom: 20px;"></div>
        </div>
        <div class="col-xs-12">
            <span class="lead text-muted">{{ prelevement.prelevements.length }} lots à prélever</span>
            <ul class="list-group">
            <li ng-repeat="(lot_key, lot) in prelevement.prelevements" class="list-group-item col-xs-12">
               <div class="col-xs-1">
                   <strong>{{ lot_key + 1 }}</strong>
               </div>
               <div class="col-xs-11">
                    {{ lot.libelle }}
               </div>
            </li>
            </ul>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-6">
            <a href="#mission" class="btn btn-primary btn-lg btn-upper btn-block link-to-section">Retour</a>
        </div>
        <div class="col-xs-6">
            <a href="#saisie_mission_{{ key }}" class="btn btn-warning btn-lg btn-upper btn-block link-to-section">Démarrer la Saisie</a>
        </div>
    </div>
</section>
<div id="saisie_mission_{{ key }}" ng-repeat="(key, prelevement) in prelevements">
<section ng-repeat="(lot_key, lot) in prelevement.prelevements" id="saisie_mission_{{ key }}_{{ lot_key }}">
    <div class="page-header">
        <h2>Lot n°{{ lot_key + 1 }} - {{ lot.libelle }} <small><a class="text-warning" href="#">(changer)</a></small>
        </h2>
    </div>
    <div class="row">
        <div class="col-xs-12">
        <p class="lead text-center"><strong>N° d'anonymat</strong> : P</p>
        <form class="form-horizontal">
            <div class="form-group">
                <div class="col-xs-12">
                    <input type="text" class="form-control" placeholder="Volume prelevé">
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-12">
                    <input type="text" class="form-control" placeholder="Numéro de cuve">
                </div>
            </div>
        </form>
        </div>
    </div>
    <div class="row">
        <div ng-show="(lot_key - 1) >= 0" class="col-xs-4">
            <a href="#saisie_mission_{{ key }}_{{ lot_key - 1 }}" class="btn btn-primary btn-lg col-xs-6 btn-block btn-upper link-to-section">Précédent</a>
        </div>
        <div ng-show="(lot_key - 1) < 0" class="col-xs-4">
            <a href="#detail_mission_{{ key }}" class="btn btn-primary btn-lg col-xs-4 btn-block btn-upper link-to-section">Précédent</a>
        </div>
        <div ng-show="(lot_key + 1) >= prelevement.prelevements.length" class="col-xs-4">
                <a href="#saisie_mission_" class="btn btn-warning btn-lg col-xs-6 btn-block btn-upper link-to-section">Saisir un autre lot</a>
        </div>
        <div ng-show="(lot_key + 1) < prelevement.prelevements.length" class="col-xs-4 pull-right">
            <a href="#saisie_mission_{{ key }}_{{ lot_key + 1 }}" class="btn btn-default btn-lg btn-block btn-upper link-to-section link-to-section">Valider et continuer</a>
        </div>
        <div ng-show="(lot_key + 1) >= prelevement.prelevements.length" class="col-xs-4 pull-right">
            <a href="#mission" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Terminer</a>
        </div>
    </div>
</section>
</div>
</div>