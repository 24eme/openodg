<?php use_javascript('tournee.js'); ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>

<section id="mission">
    <div class="page-header">
        <h2>Mission du 23 Janvier 2015 <small>Vicky CHAN</small></h2>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="list-group">
                <?php foreach($prelevements as $prelevement): ?>
                <a href="#detail_mission_<?php echo $prelevement->getKey() ?>" class="list-group-item col-xs-12 link-to-section">
                    <div class="col-xs-2">
                        <strong style="font-size: 32px;"><?php echo $prelevement->heure ?></strong>
                    </div>
                    <div class="col-xs-9">
                    <strong class="lead"><?php echo $prelevement->raison_sociale ?></strong><br />
                    <?php echo $prelevement->adresse ?>, <?php echo $prelevement->code_postal ?> <?php echo $prelevement->commune ?>
                    </div>
                    <div class="col-xs-1">
                        <span class="glyphicon glyphicon-unchecked" style="font-size: 40px; margin-top: 5px;"></span>
                    </div>
                </a>
                <?php endforeach; ?>
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

<?php foreach($prelevements as $prelevement): ?>
<section id="detail_mission_<?php echo $prelevement->getKey() ?>" class="hidden">
    <div class="page-header">
        <h2>Détail de la mission de <?php echo $prelevement->heure ?></h2>
    </div>

    <div class="row">
        <div class="col-xs-6">
            <address>
              <span class="lead text-muted"><strong><?php echo $prelevement->raison_sociale ?></strong></span><br />
              <span class="lead"><?php echo $prelevement->adresse ?></span><br />
              <span class="lead"><?php echo $prelevement->code_postal ?> <?php echo $prelevement->commune ?></span><br /><br />
              <abbr title="Phone">Bureau</abbr> : 0389201627<br />
              <abbr title="Phone">Privé</abbr> : 0389201627<br />
              <abbr title="Phone">Mobile</abbr> : 0689201627<br />
            </address>
        </div>
        <div class="col-xs-6">
            <div id="carte_<?php echo $prelevement->getKey() ?>" data-title="" data-point="48.100901,7.36105" class="col-xs-12 carte" style="height: 250px; margin-bottom: 20px;"></div>
        </div>
        <div class="col-xs-12">
            <span class="lead text-muted"><?php echo count($prelevement->lots) ?> lots à prélever</span>
            <ul class="list-group">
            <?php $j = 1; ?>
            <?php foreach($prelevement->lots as $lot): ?>
            <li class="list-group-item col-xs-12">
               <div class="col-xs-1">
                   <strong><?php echo $j ?></strong>
               </div>
               <div class="col-xs-11">
                    <?php echo $lot->libelle ?>
               </div>
            </li>
            <?php $j++; ?>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-6">
            <a href="#mission" class="btn btn-primary btn-lg btn-upper btn-block link-to-section">Retour</a>
        </div>
        <div class="col-xs-6">
            <a href="#saisie_mission_<?php echo $prelevement->getKey() ?>_<?php echo $prelevement->lots->getFirst()->getKey() ?>" class="btn btn-warning btn-lg btn-upper btn-block link-to-section">Démarrer la Saisie</a>
        </div>
    </div>
</section>
<?php $j = 1; ?>
<?php foreach($prelevement->lots as $lot): ?>
<section id="saisie_mission_<?php echo $prelevement->getKey() ?>_<?php echo $lot->getKey() ?>" class="hidden">
    <div class="page-header">
        <h2>Lot n°<?php echo $j ?> - <?php if($j <= 3): ?><?php echo $lot->libelle ?> <small><a class="text-warning" href="#">(changer)</a></small><?php else: ?>Aucun Cépage <small><a class="text-warning" href="#">(définir)</a></small><?php endif; ?>
        </h2>
    </div>
    <div class="row">
        <div class="col-xs-12">
        <p class="lead text-center"><strong>N° d'anonymat</strong> : P<?php echo rand(100,999) ?></p>
        <form class="form-horizontal">
            <div class="form-group">
                <div class="col-xs-12">
                    <input type="text" class="form-control" id="volume_<?php echo $i ?>_<?php echo $j ?>" placeholder="Volume prelevé">
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-12">
                    <input type="text" class="form-control" id="num_cuve_<?php echo $i ?>_<?php echo $j ?>" placeholder="Numéro de cuve">
                </div>
            </div>
        </form>
        </div>
    </div>
    <div class="row">
        <?php if($lot->getPreviousSister()): ?>
            <div class="col-xs-4">
                <a href="#saisie_mission_<?php echo $prelevement->getKey() ?>_<?php echo $lot->getPreviousSister()->getKey() ?>" class="btn btn-primary btn-lg col-xs-6 btn-block btn-upper link-to-section">Précédent</a>
            </div>
        <?php else: ?>
            <div class="col-xs-4">
                <a href="#detail_mission_<?php echo $prelevement->getKey() ?>" class="btn btn-primary btn-lg col-xs-6 btn-block btn-upper link-to-section">Précédent</a>
            </div>
        <?php endif; ?>
        <?php if(!$lot->getNextSister()): ?>
        <div class="col-xs-4">
                <a href="#saisie_mission_<?php //echo $i ?>_<?php //echo $j + 1 ?>" class="btn btn-warning btn-lg col-xs-6 btn-block btn-upper link-to-section">Saisir un autre lot</a>
        </div>
        <?php endif; ?>
        <?php if($lot->getNextSister()): ?>
            <div class="col-xs-6">
                <a href="#saisie_mission_<?php echo $prelevement->getKey() ?>_<?php echo $lot->getNextSister()->getKey() ?>" class="btn btn-default btn-lg btn-block btn-upper link-to-section link-to-section">Valider et continuer</a>
            </div>
        <?php else: ?>
            <div class="col-xs-4">
                <a href="#mission" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Terminer</a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php $j++; ?>
<?php endforeach; ?>
<?php endforeach; ?>