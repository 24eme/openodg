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
    <div class="col-xs-12" data-spy="affix" data-offset-top="290" style="background: #fff; width: 910px; z-index: 1000; display: block; padding-top: 10px">
        <div class="btn-group" style="margin-bottom: 15px;">
            <a data-filter="" 
                href="" 
                class="btn btn-default btn-default-step nav-filter active">
                Tous <span class="badge" style="color: #fff;">25</span>
            </a>
            <a href="" class="btn btn-default btn-default-step nav-filter"
               data-state="vicky" 
               data-color="#C77289" 
               style="color: #C77289;" 
               data-filter="vicky">
                <span class="glyphicon glyphicon-map-marker"></span> Vicky <small class="text-muted">30/01</small> <span class="badge" style="color: #fff">0</span>
            </a>
            <a href="" class="btn btn-default btn-default-step nav-filter"
               data-color="#E97F02" 
               style="color: #E97F02;"
               data-state="martine"
               data-filter="martine">
                <span class="glyphicon glyphicon-map-marker"></span> Martine <small class="text-muted">30/01</small> <span class="badge" style="color: #fff">0</span>
            </a>
        </div>
    </div>
    <div class="col-xs-6">
        <ul id="listes_operateurs" data-spy="affix" data-offset-top="300" class="list-group sortable" style="margin-top: 0;">
                <?php for($i = 8; $i <= 18; $i++): ?>
                    <li class="list-group-item col-xs-12 list-group-item-info text-center" style="padding-top: 4px; padding-bottom: 4px; border-color: #fff; background: #e2e2e2; color: #555"><small><span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;<?php echo sprintf("%02d", $i) ?> h</small></li>
                <?php endfor; ?>
                <?php for($i = 0; $i <= 24; $i++): ?>
                <li data-state="" data-value="<?php echo $i ?>" data-title="M. NOM PRENOM <?php echo $i ?>" data-point="<?php echo (rand(47859760, 48504231) / 1000000) ?>,<?php echo (rand(7151756, 7529755) / 1000000) ?>" class="list-group-item list-group-item-item col-xs-12 clickable">
                    <div class="col-xs-12">
                        <div class="pull-right">
                            <button class="btn btn-success btn-xs hidden" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                            <button class="btn btn-danger btn-xs hidden" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                        </div>
                        <div style="margin-right: 10px; margin-bottom: -5px;" class="pull-left">
                            <span class="glyphicon glyphicon-resize-vertical" style="opacity: 0.4; font-size: 24px; margin-left: -20px;"></span>
                            <span class="glyphicon glyphicon-map-marker" style="color: #c2c2c2; font-size: 24px;"></span>
                        </div>
                        M. NOM PRENOM <?php echo $i ?>&nbsp;<small class="text-muted">Commune</small>
                    </div>
                </li>
            <?php endfor; ?>
        </ul>
    </div>
    <div class="col-xs-6">
        <div data-spy="affix" data-offset-top="290" data-offset-bottom="190" class="col-xs-12" id="carte" style="height: 380px; width: 440px;"></div>
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