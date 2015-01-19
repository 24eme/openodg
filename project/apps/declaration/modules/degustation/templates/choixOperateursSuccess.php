<?php use_javascript("degustation.js", "last") ?>

<div class="page-header">
    <h2>Choix des Opérateurs</h2>
</div>

<form id="form_degustation_choix_operateurs" action="" methode="post" class="form-horizontal">

<div class="row">
    <div class="col-xs-12">
        <ul id="recap_cepages">
            <li data-cepage="Riesling">Riesling <span class="badge">0</span></li>
            <li data-cepage="Chasselas">Chasselas <span class="badge">0</span></li>
            <li data-cepage="Pinot Gris">Pinot Gris <span class="badge">0</span></li>
            <li data-cepage="Gewurztraminer">Gewurztraminer <span class="badge">0</span></li>
        </ul>
    </div>

    <div class="col-xs-12">
        <ul class="nav nav-pills">
            <li id="nav_tous" class="active" role="presentation"><a href="">Tous <span class="badge">60</span></a></li>
            <li id="nav_a_prelever"><a href="">À preléver <span class="badge">0</span></a></li>
        </ul>
    </div>
    <div class="col-xs-12" style="padding-top: 20px;">
        <div id="listes_operateurs" class="list-group">
            <?php for($i = 0; $i <= 60; $i++): ?>
            <div class="list-group-item col-xs-12 clickable">
                <div class="col-xs-3">M. NOM PRENOM  <?php echo $i ?></div>
                <div class="col-xs-3">AMMERSCHWIHR</div>
                <div class="col-xs-3">Prélevé en 2012, 2014</div>
                <div class="col-xs-2">
                    <select data-placeholder="Sélectionner" class="form-control input-sm hidden">
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
        <!--<div id="a_choisir" class="list-group">
            <?php for($i = 0; $i <= 60; $i++): ?>
            <a id="a_choisir_<?php echo $i ?>" href="" class="list-group-item col-xs-12">
                <div class="col-xs-3">M. NOM PRENOM <?php echo $i ?></div>
                <div class="col-xs-3">AMMERSCHWIHR</div>
                <div class="col-xs-3">Prélevé en 2012, 2014</div>
                <div class="col-xs-2"></div>
                <div class="col-xs-1">
                    <button class="btn btn-success btn-sm pull-right" type="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                    <button class="btn btn-danger hidden btn-sm pull-right" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                </div>
            </a>
            <?php endfor; ?>
        </div>-->
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