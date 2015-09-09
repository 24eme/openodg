<?php use_helper('Date'); ?>
<?php use_javascript("organisation.js?201505150308", "last") ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_javascript('lib/leaflet/marker.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_stylesheet('/js/lib/leaflet/marker.css'); ?>

<?php include_partial('admin/menu', array('active' => 'constats')); ?>

<ul class="nav nav-tabs">
  <li role="presentation" class="active"><a href="#"><?php echo date('Y-m-d') ?></a></li>
</ul>

<ul class="nav nav-tabs">
        <li role="presentation"><a href="#">Tous</a></li>
    <?php foreach($tournees->tourneesJournee as $t): ?>
        <li role="presentation" data-per-hour="4" data-color="#ff0000" id="<?php echo $t->tournee->_id ?>" class="active organisation-tournee"><a href="#"><?php echo $t->tournee->appellation ?></a></li>
    <?php endforeach; ?>
</ul>

<div class="row">
    <div class="col-xs-6">
        <ul class="organisation-list list-group sortable">
            <li class="list-group-item col-xs-12 list-group-item-info list-group-item-container text-center">
                    Liste des rendez-vous à planifier
            </li>
            <?php foreach($rdvsPris as $rdv_id => $operateur): ?>
                <?php $operateur = $operateur->value ?>
                <li id="<?php echo $rdv_id ?>" data-tournee="" data-title="<?php echo $operateur->raison_sociale ?>" data-point="<?php echo $operateur->lat*1 ?>,<?php echo $operateur->lon*1 ?>" class="organisation-item list-group-item col-xs-12">
                        <input type="hidden" class="input-hour" name="rdvs[<?php echo $operateur->_id ?>][heure]" value="<?php echo sprintf("%s", $operateur->heure_reelle) ?>" />
                        <div class="col-xs-12">
                            <div class="pull-right">
                                <button data-item="#<?php echo $rdv_id ?>" class="btn btn-success btn-sm" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                                <button data-item="#<?php echo $rdv_id ?>" class="btn btn-danger btn-sm hidden" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                            </div>
                            <div style="margin-right: 10px; margin-bottom: -5px;" class="pull-left">
                                <span class="glyphicon glyphicon-resize-vertical hidden" style="opacity: 0.4; font-size: 24px; margin-left: -20px;"></span>
                                <span class="glyphicon glyphicon-map-marker" style="font-size: 24px;"></span>
                            </div>
                            <?php echo $operateur->raison_sociale ?>&nbsp;<small class="text-muted"><?php echo $operateur->commune ?></small>
                        </div>
                </li>
            <?php endforeach; ?>
            <?php foreach($heures as $key_heure => $libelle_heure): ?>
                <li data-value="<?php echo $key_heure ?>" class="organisation-hour list-group-item col-xs-12 disabled text-center">
                    <small><span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;<?php echo $libelle_heure ?> h</small>
                </li>
                <?php if(!isset($rdvs[$key_heure])): continue; endif; ?>
                <?php foreach($rdvs[$key_heure] as $rdv_id => $operateur): ?>
                    <li id="<?php echo $rdv_id ?>" data-tournee="<?php echo $tournee->_id ?>" data-title="<?php echo $operateur->compte_raison_sociale ?>" data-point="<?php echo $operateur->compte_lat*1 ?>,<?php echo $operateur->compte_lon*1 ?>" class="organisation-item list-group-item col-xs-12">
                        <input type="hidden" class="input-hour" name="rdvs[<?php echo $rdv_id ?>][heure]" value="<?php echo sprintf("%s", $operateur->heure_reelle) ?>" />
                        <div class="col-xs-12">
                            <div class="pull-right">
                                <button data-item="#<?php echo $rdv_id ?>" class="btn btn-success btn-sm hidden" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                                <button data-item="#<?php echo $rdv_id ?>" class="btn btn-danger btn-sm" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                            </div>
                            <div style="margin-right: 10px; margin-bottom: -5px;" class="pull-left">
                                <span class="glyphicon glyphicon-resize-vertical" style="opacity: 0.4; font-size: 24px; margin-left: -20px;"></span>
                                <span class="glyphicon glyphicon-map-marker" style="font-size: 24px;"></span>
                            </div>
                            <?php echo $operateur->compte_raison_sociale ?>&nbsp;<small class="text-muted"><?php echo $operateur->compte_commune ?></small>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="col-xs-6">
        <div class="col-xs-12" id="carteOrganisation" style="height: 600px;"></div>
    </div>
</div>