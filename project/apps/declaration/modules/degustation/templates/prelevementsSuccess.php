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
        <div class="btn-group" style="margin-bottom: 15px;">
            <a data-filter="" href="" class="btn btn-default btn-default-step nav-filter active">Tous <span class="badge" style="color: #fff;">25</span></a>
            <a data-state="vicky" data-filter="vicky" href="" class="btn btn-default btn-default-step nav-filter">Vicky <small class="text-muted">30/01</small> <span class="badge" style="color: #fff">0</span></a>
            <a href="" data-state="martine" data-filter="martine" class="btn btn-default btn-default-step nav-filter">Martine <small class="text-muted">30/01</small> <span class="badge" style="color: #fff">0</span></a>
        </div>
    </div>
    <div class="col-xs-5">
        <ul id="listes_operateurs" class="list-group sortable" style="height: 450px; overflow-y: auto; overflow-x:hidden; padding-right: 2px; margin-top: 0;">
                <?php for($i = 8; $i <= 18; $i++): ?>
                    <li class="list-group-item col-xs-12 list-group-item-info text-center" style="padding-top: 4px; padding-bottom: 4px; border-color: #fff;"><small><span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;<?php echo sprintf("%02d", $i) ?> h</small></li>
                <?php endfor; ?>
                <?php for($i = 0; $i <= 24; $i++): ?>
                <li data-state="" data-value="<?php echo $i ?>" data-title="M. NOM PRENOM <?php echo $i ?>" data-point="<?php echo (rand(47859760, 48504231) / 1000000) ?>,<?php echo (rand(7151756, 7529755) / 1000000) ?>" class="list-group-item list-group-item-item col-xs-12 clickable">
                    <div class="col-xs-1">
                        <span class="glyphicon glyphicon-map-marker" style="padding-top: 8px; padding-bottom: 0; margin-bottom: 0px; margin-left: -12px; font-size: 24px;"></span>
                    </div>
                    <div class="col-xs-11">
                        <div class="pull-right">
                            <button class="btn btn-success btn-sm hidden" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                            <button class="btn btn-danger btn-sm hidden" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                            <span class="glyphicon glyphicon glyphicon-move" style="padding-top: 8px; padding-bottom: 0; margin-bottom: 0px; margin-left: -12px; font-size: 24px;"></span>
                        </div>
                      M. NOM PRENOM <?php echo $i ?><br />
                      <small class="text-muted">COMMUNE</small>
                    </div>
                </li>
            <?php endfor; ?>
        </ul>
    </div>
    <div class="col-xs-7">
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