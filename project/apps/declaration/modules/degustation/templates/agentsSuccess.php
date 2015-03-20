<?php use_helper('Date'); ?>
<?php use_javascript("degustation.js?201503201907", "last") ?>

<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_AGENTS)); ?>

<div class="page-header">
    <h2>Choix des agents de prélevements</h2>
</div>

<form id="form_degustation_choix_operateurs" action="" method="post" class="form-horizontal ajaxForm">

<div class="row">
    <div class="col-xs-12" style="padding-bottom: 15px;">
        <div class="btn-group">
            <a data-state="active" data-filter="" class="btn btn-info active nav-filter" href="">Tous <span class="badge">25</span></a>
            <a data-state="active" data-filter="active" class="btn btn-default nav-filter"  href="">Séléctionné <span class="badge"><?php echo count($degustation->agents) ?></span></a>
        </div>
    </div>
    <div class="col-xs-12">
        <div id="listes_operateurs" class="list-group">
            <?php foreach($agents as $agent): ?>
            <?php $exist = $degustation->agents->exist($agent->_id); ?>
            <div <?php if($exist): ?>data-state="active"<?php endif; ?> class="list-group-item list-group-item-item col-xs-12 <?php if(!$exist): ?>clickable<?php else: ?>list-group-item-success<?php endif; ?>">
                <div class="col-xs-5"><?php echo $agent->nom_a_afficher ?> <br /><small class="text-muted"><?php echo $agent->adresse ?> <?php echo $agent->commune ?></small></div>
                <div class="col-xs-6">
                    <select name="agents[<?php echo $agent->_id ?>][]" <?php if(!$exist): ?>disabled="disabled"<?php endif; ?> multiple="multiple" data-placeholder="Sélectionner des dates" class="form-control select2 select2-offscreen select2autocomplete <?php if(!$exist): ?>hidden<?php endif; ?>">
                        <option></option>
                        <?php foreach($jours as $jour): ?>
                        <option <?php if($exist && in_array($jour, $degustation->agents->get($agent->_id)->dates->toArray(true, false)->getRawValue())): ?>selected="selected"<?php endif; ?> value="<?php echo $jour ?>"><?php echo format_date($jour, "P", "fr_FR") ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-xs-1">
                    <button class="btn btn-success btn-sm pull-right <?php if($exist): ?>hidden<?php endif; ?>" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                    <button class="btn btn-danger btn-sm pull-right <?php if(!$exist): ?>hidden<?php endif; ?>" style="opacity: 0.7;" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-6">
        <a href="<?php echo url_for('degustation_degustateurs', $degustation) ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
    </div>
    <div class="col-xs-6 text-right">
        <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer</button>
    </div>
</div>

</form>