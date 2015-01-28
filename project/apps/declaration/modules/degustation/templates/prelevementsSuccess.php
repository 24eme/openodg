<?php use_javascript("degustation.js", "last") ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>

<?php include_partial('degustation/step', array('active' => 'prelevements')); ?>

<div class="page-header">
    <h2>Affectation des prélevements</h2>
</div>

<form id="form_degustation_choix_operateurs" action="" methode="post" class="form-horizontal">

<div class="row">
    <div class="col-xs-12">
    <h2>Vicky CHAN <small>30 Janvier 2015</small></h2>
    </div>
    <div class="col-xs-5">
        <div id="listes_operateurs" class="list-group" style="height: 400px; overflow-y: auto; overflow-x:hidden; padding-right: 2px;">
            <?php for($i = 0; $i <= 24; $i++): ?>
            <div data-title="M. NOM PRENOM <?php echo $i ?>" data-point="<?php echo (rand(47859760, 48504231) / 1000000) ?>,<?php echo (rand(7151756, 7529755) / 1000000) ?>" class="list-group-item col-xs-12 clickable">
                <div class="col-xs-1">
                    <span class="glyphicon glyphicon-map-marker" style="padding-top: 8px; padding-bottom: 0; margin-bottom: 0px; margin-left: -12px; font-size: 24px;"></span>
                </div>
                <div class="col-xs-11">
                    <div class="pull-right">
                        <button class="btn btn-success btn-sm" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                        <button class="btn btn-danger btn-sm hidden" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                    </div>
                  M. NOM PRENOM <?php echo $i ?><br />
                  <small class="text-muted">COMMUNE</small>
                </div>
            </div>
            <?php endfor; ?>
        </div>
       
    </div>
    <div class="col-xs-7">
        <div class="btn-group">
            <a id="nav_tous" class="btn btn-info active" href="">Tous <span class="badge">25</span></a>
            <a  id="nav_a_prelever" class="btn btn-default"  href="">Séléctionné <span class="badge">0</span></a>
        </div>
        <div id="carte" class="col-xs-12" style="height: 353px; margin-top: 15px;">
            
        </div>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-6">
        <a href="<?php echo url_for('degustation_agents') ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
    </div>
    <div class="col-xs-6 text-right">
        <a href="<?php echo url_for('degustation_validation') ?>" class="btn btn-default btn-lg btn-upper">Continuer</a>
    </div>
</div>

</form>