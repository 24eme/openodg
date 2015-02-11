<?php use_helper('Date'); ?>
<?php use_javascript("degustation.js", "last") ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_javascript('lib/leaflet/marker.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_stylesheet('/js/lib/leaflet/marker.css'); ?>

<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => 'prelevements')); ?>

<div class="page-header">
    <h2>Affectation des prélevements</h2>
</div>

<form id="form_degustation_choix_operateurs" action="" methode="post" class="form-horizontal">

<div class="row">
    <div class="col-xs-12">
        <div class="btn-group btn-group-justified" style="margin-bottom: 15px;">
            <a data-filter="" 
                href="" 
                class="btn btn-default btn-default-step nav-filter active">
                Tous <span class="badge" style="color: #fff;"><?php echo count($degustation->prelevements) ?></span>
            </a>
            <?php $i = 0; ?>
            <?php foreach($degustation->agents as $agent): ?>
                <?php foreach($agent->dates as $date): ?>
                <a href="" class="btn btn-default btn-default-step nav-filter"
                   data-state="<?php echo sprintf("%s-%s", $agent->getKey(), $date) ?>" 
                   data-color="<?php echo $couleurs[$i] ?>" 
                   style="color: <?php echo $couleurs[$i] ?>" 
                   data-filter="<?php echo sprintf("%s-%s", $agent->getKey(), $date) ?>">
                    <span class="glyphicon glyphicon-map-marker"></span> <?php echo $agent->nom ?><br /><small class="text-muted"><?php echo format_date($date, "dddd dd MMMM") ?></small> <span class="badge" style="color: #fff">0</span>
                </a>
                <?php $i++;?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-xs-6">
        <ul id="listes_operateurs" class="list-group sortable" style="height: 500px; overflow-y: auto; overflow-x:hidden; padding-right: 2px; margin-top: 0;">
                <?php foreach($heures as $key_heure => $libelle_heure): ?>
                    <li data-value="<?php echo $key_heure ?>" class="list-group-item col-xs-12 list-group-item-info list-group-item-container text-center" style="padding-top: 4px; padding-bottom: 4px; border-color: #fff; background: #e2e2e2; color: #555"><small><span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;<?php echo $libelle_heure ?> h</small></li>
                    <?php if(isset($prelevements[$key_heure])): ?>
                    <?php foreach($prelevements[$key_heure] as $prelevement): ?>
                        <li data-state="" data-value="<?php echo $prelevement->getKey() ?>" data-title="<?php echo $prelevement->raison_sociale ?>" data-point="<?php echo (rand(47859760, 48504231) / 1000000) ?>,<?php echo (rand(7151756, 7529755) / 1000000) ?>" class="list-group-item list-group-item-item col-xs-12 clickable">
                            <input type="hidden" name="prelevements[<?php echo $prelevement->getKey() ?>][heure]" value="" />
                            <div class="col-xs-12">
                                <div class="pull-right">
                                    <button class="btn btn-success btn-xs hidden" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                                    <button class="btn btn-danger btn-xs hidden" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                                </div>
                                <div style="margin-right: 10px; margin-bottom: -5px;" class="pull-left">
                                    <span class="glyphicon glyphicon-resize-vertical" style="opacity: 0.4; font-size: 24px; margin-left: -20px;"></span>
                                    <span class="glyphicon glyphicon-map-marker" style="color: #c2c2c2; font-size: 24px;"></span>
                                </div>
                                <?php echo $prelevement->raison_sociale ?>&nbsp;<small class="text-muted"><?php echo $prelevement->commune ?></small>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
        </ul>
    </div>
    <div class="col-xs-6">
        <div class="col-xs-12" id="carte" style="height: 500px;"></div>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-6">
        <a href="<?php echo url_for('degustation_agents', $degustation) ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
    </div>
    <div class="col-xs-6 text-right">
        <a href="<?php echo url_for('degustation_validation') ?>" class="btn btn-default btn-lg btn-upper">Continuer</a>
    </div>
</div>

</form>