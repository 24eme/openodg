<?php use_helper('Date'); ?>
<?php use_javascript("organisation.js?201505150308", "last") ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_javascript('lib/leaflet/marker.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_stylesheet('/js/lib/leaflet/marker.css'); ?>

<?php include_partial('admin/menu', array('active' => 'constats')); ?>

<div class="page-header">
    <div class="row">
        <div class="col-xs-8 col-xs-offset-2">
            <div class="row">
                <div class="col-xs-2 text-left">
                    <h2><a class="text-muted" href="<?php echo url_for('constats_planifications', array('date' => Date::addDelaiToDate("-1 day", $jour))); ?>">
                        <span class="glyphicon glyphicon-arrow-left"></span>
                    </a></h2>
                </div>
                <div class="col-xs-8 text-center">
                    <h2><?php echo ucfirst(format_date($jour, "P", "fr_FR")); ?></h2>
                </div>
                <div class="col-xs-2 text-right">
                    <h2><a class="text-muted" href="<?php echo url_for('constats_planifications', array('date' => Date::addDelaiToDate("+1 day", $jour))); ?>">
                        <span class="glyphicon glyphicon-arrow-right"></span>
                    </a></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="form_planification" action="" method="post" class="form-horizontal ajaxForm">

    <div class="row">
        <div class="col-xs-12">
            <div class="btn-group">
                    <btn class="active organisation-tournee btn btn-lg btn-default-step" href="">Tous</btn>
                <?php foreach($tournees as $t): ?>
                    <btn style="color: <?php echo $tourneesCouleur[$t->_id] ?>;" data-per-hour="4" data-hour="09:00" data-color="<?php echo $tourneesCouleur[$t->_id] ?>" id="<?php echo $t->_id ?>" class="organisation-tournee btn btn-lg btn-default-step"><?php echo $t->getFirstAgent()->nom ?></btn>
                <?php endforeach; ?>
                <a href="<?php echo url_for('constats_planification_ajout_agent', array('jour' => $jour)) ?>" class="btn btn-lg btn-default btn-default-step"><span class="glyphicon glyphicon-plus"></span> Agent</a>
            </div>


        </div>
    </div>

    <div class="row row-margin">
        <div class="col-xs-6">
            <div class="well" style="padding: 0 5px; margin-bottom: 5px;">
                <h4 class="text-center" style="text-transform: uppercase;"><span class="glyphicon glyphicon-time"></span> En attente de planification</h4>
                <ul class="organisation-list-wait list-group">
                    <?php foreach($rdvsPris as $rdv_id => $rdv): ?>
                        <li id="<?php echo $rdv_id ?>" data-tournee="" data-hour="<?php echo preg_replace("/^([0-9]+):[0-9]+$/", '\1:00', $rdv->heure) ?>" data-title="<?php echo $rdv->raison_sociale ?>" data-point="<?php echo $rdv->lat*1 ?>,<?php echo $rdv->lon*1 ?>" class="organisation-item list-group-item col-xs-12">
                                <input type="hidden" class="input-hour" name="rdvs[<?php echo $rdv->_id ?>][heure]" value="" />
                                <input type="hidden" class="input-tournee" name="rdvs[<?php echo $rdv->_id ?>][tournee]" value="" />
                                <div class="col-xs-12">
                                    <div style="margin-top: 6px;" class="pull-right">
                                        <button data-item="#<?php echo $rdv_id ?>" class="btn btn-success btn-sm hidden" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                                        <button data-item="#<?php echo $rdv_id ?>" class="btn btn-danger btn-sm hidden" type="button"><span class="glyphicon glyphicon-minus-sign"></span></button>
                                    </div>
                                    <div style="padding-right: 16px; margin-top: 4px;" class="pull-right">
                                        <span style="font-size: 20px;" class="icon-raisins"></span>
                                        <span style="font-size: 16px;"><?php echo str_replace(":", "h", $rdv->heure) ?></span>
                                    </div>
                                    <div style="margin-right: 10px; margin-top: 9px;" class="pull-left">
                                        <span class="glyphicon glyphicon-resize-vertical hidden" style="opacity: 0.4; font-size: 24px; margin-left: -8px;"></span>
                                        <span class="glyphicon glyphicon-map-marker" style="font-size: 24px; color: #e2e2e2"></span>
                                    </div>
                                    <?php echo $rdv->raison_sociale ?><br /><small class="text-muted"><?php echo $rdv->commune ?></small>
                                </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="well" style="padding: 0 5px; ">
            <h4 class="text-center" style="text-transform: uppercase;"><span class="glyphicon glyphicon-check"></span> Planifié</h4>
            <ul class="organisation-list list-group sortable">
                <?php foreach($heures as $key_heure => $libelle_heure): ?>
                    <li data-value="<?php echo $key_heure ?>" class="organisation-hour list-group-item col-xs-12 disabled text-center">
                        <small><span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;<?php echo $libelle_heure ?> h</small>
                    </li>
                    <?php if(!isset($rdvs[$key_heure])): continue; endif; ?>
                    <?php foreach($rdvs[$key_heure] as $tournee_id => $tourneeRdvs): ?>
                        <?php foreach($tourneeRdvs as $rdv_id => $rdv): ?>
                        <li id="<?php echo $rdv_id ?>" data-tournee="<?php echo $tournee_id ?>" data-title="<?php echo $rdv->compte_raison_sociale ?>" data-point="<?php echo $rdv->compte_lat*1 ?>,<?php echo $rdv->compte_lon*1 ?>" data-hour="<?php echo preg_replace("/^([0-9]+):[0-9]+$/", '\1:00', $rdv->heure) ?>" class="organisation-item list-group-item col-xs-12">
                            <input type="hidden" class="input-hour" name="rdvs[<?php echo $rdv_id ?>][heure]" value="<?php echo sprintf("%s", $rdv->heure_reelle) ?>" />
                            <input type="hidden" class="input-tournee" name="rdvs[<?php echo $rdv_id ?>][tournee]" value="<?php echo $tournee_id ?>" />
                            <div class="col-xs-12">
                                <div style="margin-top: 6px;" class="pull-right">
                                    <button data-item="#<?php echo $rdv_id ?>" class="btn btn-success btn-sm hidden" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                                    <button data-item="#<?php echo $rdv_id ?>" class="btn btn-danger btn-sm" type="button"><span class="glyphicon glyphicon-minus-sign"></span></button>
                                </div>
                                <div style="padding-right: 16px; margin-top: 4px;" class="pull-right">
                                    <span style="font-size: 20px;" class="icon-raisins"></span>
                                    <span style="font-size: 16px;"><?php echo str_replace(":", "h", $rdv->heure) ?></span>
                                </div>
                                <div style="margin-right: 10px; margin-top: 9px;" class="pull-left">
                                    <span class="glyphicon glyphicon-resize-vertical" style="opacity: 0.4; font-size: 24px; margin-left: -8px;"></span>
                                    <span class="glyphicon glyphicon-map-marker" style="font-size: 24px; color: <?php echo $tourneesCouleur[$tournee_id] ?>"></span>
                                </div>
                                <?php echo $rdv->compte_raison_sociale ?> 
                             <br /><small class="text-muted"><?php echo $rdv->compte_commune ?></small>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
            </div>
        </div>
        <div class="col-xs-6">
            <div class="col-xs-12" id="carteOrganisation" style="height: 650px;"></div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-12 text-right">
            <button class="btn btn-lg btn-default btn-upper" type="submit">Valider</button>
        </div>
    </div>
</form>