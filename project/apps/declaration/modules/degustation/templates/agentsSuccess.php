<?php use_helper('Date'); ?>
<?php use_javascript("degustation.js", "last") ?>

<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => 'agents')); ?>

<div class="page-header">
    <h2>Choix des agents de prélevements</h2>
</div>

<form id="form_degustation_choix_operateurs" action="" methode="post" class="form-horizontal">

<div class="row">
    <div class="col-xs-12" style="padding-bottom: 15px;">
        <div class="btn-group">
            <a data-state="active" data-filter="" class="btn btn-info active nav-filter" href="">Tous <span class="badge">25</span></a>
            <a data-state="active" data-filter="active" class="btn btn-default nav-filter"  href="">Séléctionné <span class="badge">0</span></a>
        </div>
    </div>
    <div class="col-xs-12">
        <div id="listes_operateurs" class="list-group">
            <?php foreach($agents as $agent): ?>
            <div class="list-group-item list-group-item-item col-xs-12 clickable">
                <div class="col-xs-4"><?php echo $agent->nom_a_afficher ?></div>
                <div class="col-xs-7">
                    <select multiple="multiple" data-placeholder="Sélectionner des dates" class="form-control select2 select2-offscreen select2autocomplete hidden">
                        <option></option>
                        <?php foreach($jours as $jour): ?>
                        <option value="<?php echo $jour ?>"><?php echo format_date($jour, "P", "fr_FR") ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-xs-1">
                    <button class="btn btn-success btn-sm pull-right" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                    <button class="btn btn-danger btn-sm pull-right hidden" style="opacity: 0.7;" type="button"><span class="glyphicon glyphicon-trash"></span></button>
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
        <a href="<?php echo url_for('degustation_prelevements') ?>" class="btn btn-default btn-lg btn-upper">Continuer</a>
    </div>
</div>

</form>