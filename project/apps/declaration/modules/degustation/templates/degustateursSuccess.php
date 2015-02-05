<?php use_javascript("degustation.js", "last") ?>

<?php include_partial('degustation/step', array('active' => 'degustateurs')); ?>

<div class="page-header">
    <h2>Choix des dégustateurs</h2>
</div>

<ul class="nav nav-tabs">
  <li role="presentation"><a href="<?php echo url_for('degustation_degustation') ?>">Dégustation</a></li>
  <li role="presentation" class="active"><a href="<?php echo url_for('degustation_degustateurs') ?>">Porteur de mémoire</a></li>
  <li role="presentation"><a href="#">Technicien du produit</a></li>
  <li role="presentation"><a href="#">Usagers du produit</a></li>
</ul>

<form action="" method="post" class="form-horizontal">
    <div class="row">
        <div class="col-xs-12" style="padding-bottom: 15px;">
            <div class="btn-group">
                <a data-state="active" data-filter="" class="btn btn-info active nav-filter" href="">Tous <span class="badge">60</span></a>
                <a data-state="active" data-filter="active" class="btn btn-default nav-filter"  href="">Séléctionné <span class="badge">0</span></a>
            </div>
        </div>
        <div class="col-xs-12">
            <div id="listes_operateurs" class="list-group">
                <?php for($i = 0; $i <= 60; $i++): ?>
                <div class="list-group-item list-group-item-item col-xs-12 clickable">
                    <div class="col-xs-5">M. NOM PRENOM  <?php echo $i ?></div>
                    <div class="col-xs-3 text-left"><small class="text-muted">Venu en</small> 2012, 2014</div>
                    <div class="col-xs-3">
                        <small class="text-muted">Formé en</small> 2013, 2014
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
            <a href="<?php echo url_for('degustation_degustation') ?>" class="btn btn-primary btn-primary-step btn-lg btn-upper">Précédent</a>
        </div>
        <div class="col-xs-6 text-right">
            <a href="<?php echo url_for('degustation_agents') ?>" class="btn btn-default btn-default-step btn-lg btn-upper">Continuer</a>
        </div>
    </div>
</form>
