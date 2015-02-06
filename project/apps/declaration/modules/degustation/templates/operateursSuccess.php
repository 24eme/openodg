<?php use_javascript("degustation.js", "last") ?>
<?php use_helper("Date") ?>

<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => 'operateurs')); ?>

<div class="page-header">
    <h2>Choix des Opérateurs</h2>
</div>

<form id="form_degustation_choix_operateurs" action="" methode="post" class="form-horizontal">

<input type="hidden" id="nb_a_prelever" value="20"/>

<div class="row">
    <div class="col-xs-12" style="padding-bottom: 15px;">
        <div id="recap_cepages" class="btn-group">
            <button class="btn btn-default btn-default-step" data-cepage="Riesling">Riesling <span class="badge" style="color: white">0</span></button>
                <button class="btn btn-default btn-default-step" data-cepage="Chasselas">Chasselas <span class="badge" style="color: white">0</span></button>
                <button class="btn btn-default btn-default-step" data-cepage="Pinot Gris">Pinot Gris <span class="badge" style="color: white">0</span></button>
                <button class="btn btn-default btn-default-step" data-cepage="Gewurztraminer">Gewurztraminer <span class="badge" style="color: white">0</span></button>
        </div>
    </div>
    <div class="col-xs-12" style="padding-bottom: 15px;">
        <div class="btn-group">
            <a data-state="active" data-filter="" class="btn btn-info active nav-filter" href="">Tous <span class="badge"><?php echo count($prelevements) ?></span></a>
            <a data-state="active" data-filter="active" class="btn btn-default nav-filter"  href="">À preléver <span class="badge">0</span></a>
        </div>
    </div>
    <div class="col-xs-12">
        <div id="listes_operateurs" class="list-group">
            <?php foreach($prelevements as $prelevement): ?>
            <div class="list-group-item list-group-item-item col-xs-12 clickable">
                <div class="col-xs-4"><?php echo $prelevement->raison_sociale ?> <small class="text-muted">à <?php echo $prelevement->commune ?></small></div>
                <div class="col-xs-3 text-left"><small class="text-muted">Pour le </small><?php echo format_date($prelevement->date, "D", "fr_FR") ?><!--<small class="text-muted">Prélevé le</small> 2012, 2014--></div>
                <div class="col-xs-4">
                    <select data-auto="true" data-placeholder="Sélectionner" class="form-control input-sm hidden">
                        <option></option>
                        <?php foreach($prelevement->lots as $lot): ?>
                        <option value="<?php echo $lot->hash_produit ?>"><?php echo $lot->libelle ?> - <?php echo $lot->nb ?> lot(s)</option>
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
        <a href="<?php echo url_for('degustation_creation', $degustation) ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
    </div>
    <div class="col-xs-6 text-right">
        <a href="<?php echo url_for('degustation_degustateurs') ?>" class="btn btn-default btn-lg btn-upper">Continuer</a>
    </div>
</div>

</form>
