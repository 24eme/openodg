<?php use_helper('Date'); ?>
<?php use_javascript("degustation.js?201503201907", "last") ?>
<?php use_javascript('lib/leaflet/leaflet.js'); ?>
<?php use_javascript('lib/leaflet/marker.js'); ?>
<?php use_stylesheet('/js/lib/leaflet/leaflet.css'); ?>
<?php use_stylesheet('/js/lib/leaflet/marker.css'); ?>

<div class="row">
    <div class="col-xs-12">
        <div class="btn-group btn-group-justified" style="margin-bottom: 15px;">
            <a data-filter="" 
                href="" 
                class="btn btn-default btn-default-step nav-filter active ajax">
                Tous <span class="badge" style="color: #fff;"><?php echo count($tournee->operateurs) ?></span>
            </a>
            <?php foreach($tournee->agents as $agent): ?>
                <?php foreach($agent->dates as $date): ?>
                <a href="" class="btn btn-default btn-default-step nav-filter agent ajax"
                   data-state="<?php echo sprintf("%s-%s", $agent->getKey(), $date) ?>" 
                   data-color="<?php echo $agents_couleur[$agent->getKey().$date] ?>" 
                   style="color: <?php echo $agents_couleur[$agent->getKey().$date] ?>" 
                   data-filter="<?php echo sprintf("%s-%s", $agent->getKey(), $date) ?>"
                   data-hour="09:00"
                   data-perhour="3"
                   data-point="<?php echo $agent->lat*1 ?>,<?php echo $agent->lon*1 ?>">
                    <span class="glyphicon glyphicon-map-marker"></span> <?php echo $agent->nom ?><br /><small class="text-muted"><?php echo format_date($date, "dddd dd MMMM") ?></small> <span class="badge" style="color: #fff">0</span>
                </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-xs-6">
        <ul id="listes_operateurs" class="list-group sortable">
                <?php foreach($heures as $key_heure => $libelle_heure): ?>
                    <li data-value="<?php echo $key_heure ?>" class="hour list-group-item col-xs-12 list-group-item-info list-group-item-container text-center"><?php if($key_heure != TourneeClient::HEURE_NON_REPARTI): ?><small><span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;<?php echo $libelle_heure ?> h</small><?php else: ?><?php endif; ?></li>
                    <?php if(!isset($operateurs[$key_heure])): continue; endif; ?>
                    <?php foreach($operateurs[$key_heure] as $operateur): ?>
                        <?php $exist = ($operateur->agent && $operateur->date_prelevement); ?>
                        <li data-state="<?php echo ($operateur->agent && $operateur->date_prelevement) ? sprintf("%s-%s", $operateur->agent, $operateur->date_prelevement) : null ?>" data-value="<?php echo $operateur->getIdentifiant() ?>" data-title="<?php echo $operateur->raison_sociale ?>" data-point="<?php echo $operateur->lat*1 ?>,<?php echo $operateur->lon*1 ?>" class="operateur list-group-item list-group-item-item col-xs-12 <?php if(!$exist): ?><?php else: ?>list-group-item-success<?php endif; ?>">
                            <input type="hidden" class="input-heure" name="operateurs[<?php echo $operateur->getIdentifiant() ?>][heure]" value="<?php echo sprintf("%s", $operateur->heure) ?>" />
                            <input type="hidden" class="input-tournee" name="operateurs[<?php echo $operateur->getIdentifiant() ?>][tournee]" value="<?php echo sprintf("%s-%s", $operateur->agent, $operateur->date_prelevement) ?>" />
                            <div class="col-xs-12">
                                <div class="pull-right">
                                    <button class="btn btn-success btn-xs hidden" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                                    <button class="btn btn-danger btn-xs <?php if(!$exist): ?>hidden<?php endif; ?>" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                                </div>
                                <div style="margin-right: 10px; margin-bottom: -5px;" class="pull-left">
                                    <span class="glyphicon glyphicon-resize-vertical <?php if(!$operateur->heure): ?>hidden<?php endif; ?>" style="opacity: 0.4; font-size: 24px; margin-left: -20px;"></span>
                                    <span class="glyphicon glyphicon-map-marker" style="<?php if($exist): ?>color: <?php echo $agents_couleur[$operateur->agent.$operateur->date_prelevement] ?>;<?php else: ?>color: #e2e2e2;<?php endif; ?> font-size: 24px;"></span>
                                </div>
                                <?php echo $operateur->raison_sociale ?>&nbsp;<small class="text-muted"><?php echo $operateur->commune ?></small>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
        </ul>
    </div>
    <div class="col-xs-6">
        <div class="col-xs-12" id="carte" style="height: 600px;"></div>
    </div>
</div>