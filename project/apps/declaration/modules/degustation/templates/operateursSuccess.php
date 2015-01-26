<?php use_javascript("degustation.js", "last") ?>

<?php include_partial('degustation/step', array('active' => 'operateurs')); ?>

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
            <a data-state="active" data-filter="" class="btn btn-info active nav-filter" href="">Tous <span class="badge">60</span></a>
            <a data-state="active" data-filter="active" class="btn btn-default nav-filter"  href="">À preléver <span class="badge">0</span></a>
        </div>
    </div>
    <div class="col-xs-12">
        <div id="listes_operateurs" class="list-group">
            <?php for($i = 0; $i <= 60; $i++): ?>
            <div class="list-group-item col-xs-12 clickable">
                <div class="col-xs-5">M. NOM PRENOM  <?php echo $i ?> <small class="text-muted">à AMMERSCHWIHR</small></div>
                <div class="col-xs-3 text-left"><small class="text-muted">Prélevé le</small> 2012, 2014</div>
                <div class="col-xs-3">
                    <select data-auto="true" data-placeholder="Sélectionner" class="form-control input-sm hidden">
                        <option></option>
                        <option>Chasselas</option>
                        <option>Riesling</option>
                        <option>Pinot Gris</option>
                        <option>Gewurztraminer</option>
                    </select>
                </div>
                <div class="col-xs-1">
                    <button class="btn btn-success btn-sm pull-right" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                    <button class="btn btn-danger btn-sm pull-right hidden" style="opacity: 0.7;" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-6">
        <a href="<?php echo url_for('degustation_creation') ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
    </div>
    <div class="col-xs-6 text-right">
        <a href="<?php echo url_for('degustation_degustation') ?>" class="btn btn-default btn-lg btn-upper">Continuer</a>
    </div>
</div>

</form>
