<?php use_javascript("degustation.js", "last") ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_javascript('lib/leaflet/marker.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_stylesheet('/js/lib/leaflet/marker.css'); ?>

<?php include_partial('degustation/step', array('active' => 'prelevements')); ?>

<div class="page-header">
    <h2>Affectation des prélevements</h2>
</div>

<form id="form_degustation_choix_operateurs" action="" methode="post" class="form-horizontal">

<div class="row">
    <div class="col-xs-12">
        <div class="btn-group" style="margin-bottom: 15px;">
            <a data-filter="" href="" class="btn btn-default btn-default-step nav-filter active">Tous <span class="badge" style="color: #fff;">25</span></a>
            <a data-state="vicky" data-color="#C77289" style="color: #C77289;" data-filter="vicky" href="" class="btn btn-default btn-default-step nav-filter"><span class="glyphicon glyphicon-map-marker"></span> Vicky <small class="text-muted">30/01</small> <span class="badge" style="color: #fff">0</span></a>
            <a href="" data-color="#E97F02" data-state="martine" style="color: #E97F02;" data-filter="martine" class="btn btn-default btn-default-step nav-filter"><span class="glyphicon glyphicon-map-marker"></span> Martine <small class="text-muted">30/01</small> <span class="badge" style="color: #fff">0</span></a>
        </div>
    </div>
    <div class="col-xs-6">
        <ul id="listes_operateurs" class="list-group sortable" style="height: 450px; overflow-y: auto; overflow-x:hidden; padding-right: 2px; margin-top: 0;">
                <?php for($i = 8; $i <= 18; $i++): ?>
                    <li class="list-group-item col-xs-12 list-group-item-info text-center" style="padding-top: 4px; padding-bottom: 4px; border-color: #fff;"><small><span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;<?php echo sprintf("%02d", $i) ?> h</small></li>
                <?php endfor; ?>
                <?php for($i = 0; $i <= 24; $i++): ?>
                <li data-state="" data-value="<?php echo $i ?>" data-title="M. NOM PRENOM <?php echo $i ?>" data-point="<?php echo (rand(47859760, 48504231) / 1000000) ?>,<?php echo (rand(7151756, 7529755) / 1000000) ?>" class="list-group-item list-group-item-item col-xs-12 clickable">
                    <div class="col-xs-2">
                        <span class="glyphicon glyphicon-resize-vertical text-default pull-left" style="padding-top: 8px; padding-bottom: 0; margin-bottom: 0px; margin-left: -12px; font-size: 24px; color: #888888    "></span>
                        <span class="glyphicon glyphicon-map-marker pull-right" style="padding-top: 8px; padding-bottom: 0; margin-bottom: 0px; margin-left: -12px; font-size: 24px; color: #e2e2e2"></span>
                    </div>
                    <div class="col-xs-10">
                        <div class="pull-right">
                            <button class="btn btn-success btn-sm hidden" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                            <button class="btn btn-danger btn-sm hidden" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                        </div>
                      M. NOM PRENOM <?php echo $i ?><br />
                      <small class="text-muted">COMMUNE</small>
                    </div>
                </li>
            <?php endfor; ?>
        </ul>
    </div>
    <div class="col-xs-6">
        <div id="carte" style="height: 450px">
            
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