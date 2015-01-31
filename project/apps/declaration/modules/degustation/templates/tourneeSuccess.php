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
                <?php $nb_validee = 1; ?>
                <?php for($i=1; $i <= 8; $i++): ?>
                <a href="#detail_mission_<?php echo $i ?>" class="list-group-item <?php if($nb_validee >= $i): ?>list-group-item-success<?php endif; ?> col-xs-12 link-to-section">
                    <div class="col-xs-1">
                        <strong style="font-size: 32px;"><?php echo $i ?></strong>
                    </div>
                    <div class="col-xs-10">
                    <strong class="lead">EARL XXXXXX XXXXXXXX</strong><br />
                    6 rue Principale, 68000 COLMAR
                    </div>
                    <div class="col-xs-1">
                        <span class="<?php if($nb_validee >= $i): ?>glyphicon glyphicon-check<?php else: ?>glyphicon glyphicon-unchecked<?php endif; ?>" style="font-size: 40px; margin-top: 5px;"></span>
                    </div>
                </a>
                <?php endfor; ?>
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

<?php for($i=1; $i <= 8; $i++): ?>
<section id="detail_mission_<?php echo $i ?>" class="hidden">
    <div class="page-header">
        <h2>Détail de la mission n° <?php echo $i ?></h2>
    </div>

    <div class="row">
        <div class="col-xs-6">
            <address>
              <span class="lead text-muted"><strong>EARL XXXXXX XXXXXXXX</strong></span><br />
              <span class="lead">6 rue Principale</span><br />
              <span class="lead">68000 COLMAR</span><br /><br />
              <abbr title="Phone">Bureau</abbr> : 0389201627<br />
              <abbr title="Phone">Privé</abbr> : 0389201627<br />
              <abbr title="Phone">Mobile</abbr> : 0689201627<br />
            </address>
        </div>
        <div class="col-xs-6">
            <div id="carte_<?php echo $i ?>" data-title="" data-point="48.100901,7.36105" class="col-xs-12 carte" style="height: 250px; margin-bottom: 20px;"></div>
        </div>
        <div class="col-xs-12">
            <span class="lead text-muted">3 lots à prélever</span>
            <ul class="list-group">
            <?php for($j=1; $j <= 3; $j++): ?>
            <li class="list-group-item col-xs-12">
               <div class="col-xs-1">
                   <strong><?php echo $j ?></strong>
               </div>
               <div class="col-xs-11">
                    Riesling
               </div>
            </li>
            <?php endfor; ?>
            </ul>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-6">
            <a href="#mission" class="btn btn-primary btn-lg btn-upper btn-block link-to-section">Retour</a>
        </div>
        <div class="col-xs-6">
            <a href="#saisie_mission_<?php echo $i ?>_1" class="btn btn-warning btn-lg btn-upper btn-block link-to-section">Démarrer la Saisie</a>
        </div>
    </div>
</section>
<?php for($j=1; $j <= 4; $j++): ?>
<section id="saisie_mission_<?php echo $i ?>_<?php echo $j ?>" class="hidden">
    <div class="page-header">
        <h2>Lot n°<?php echo $j ?> - <?php if($j <= 3): ?>Cépage Riesling <small><a class="text-warning" href="#">(changer)</a></small><?php else: ?>Aucun Cépage <small><a class="text-warning" href="#">(définir)</a></small><?php endif; ?>
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
        <?php if($j < 3): ?>
            <div class="col-xs-6">
                <a href="<?php if($j == 1): ?>#detail_mission_<?php echo $i ?><?php else: ?>#saisie_mission_<?php echo $i ?>_<?php echo $j + 1 ?><?php endif; ?>" class="btn btn-primary btn-primary-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Précédent</a>
            </div>
            <div class="col-xs-6">
                <a href="#saisie_mission_<?php echo $i ?>_<?php echo $j + 1 ?>" class="btn btn-default btn-lg btn-block btn-upper link-to-section link-to-section">Valider et continuer</a>
            </div>
        <?php else: ?>
            <div class="col-xs-4">
                <a href="#saisie_mission_<?php echo $i ?>_<?php echo $j - 1 ?>" class="btn btn-primary btn-lg col-xs-6 btn-block btn-upper link-to-section">Précédent</a>
            </div>
            <div class="col-xs-4">
                <a href="#saisie_mission_<?php echo $i ?>_<?php echo $j + 1 ?>" class="btn btn-warning btn-lg col-xs-6 btn-block btn-upper link-to-section">Saisir un autre lot</a>
            </div>
            <div class="col-xs-4">
                <a href="#mission" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Terminer</a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endfor; ?>
<?php endfor; ?>