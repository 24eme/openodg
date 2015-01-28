<?php include_partial('degustation/step', array('active' => 'degustateurs')); ?>

<div class="page-header">
    <h2>Dégustation</h2>
</div>

<ul class="nav nav-tabs">
  <li role="presentation" class="active"><a href="<?php echo url_for('degustation_degustation') ?>">Dégustation</a></li>
  <li role="presentation"><a href="<?php echo url_for('degustation_degustateurs') ?>">Porteur de mémoire</a></li>
  <li role="presentation"><a href="#">Technicien du produit</a></li>
  <li role="presentation"><a href="#">Usagers du produit</a></li>
</ul>
<form action="" method="post" class="form-horizontal">
    
    <div class="row">
        <div class="col-xs-8">
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-6 control-label">Nombre de commissions</label>
                <div class="col-sm-2">
                    <input type="email" class="form-control" id="inputEmail3" placeholder="" value="">
                </div>
            </div>
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-6 control-label">Jour</label>
                <div class="col-sm-5">
                    <div class="input-group date-picker-all-days">
                        <input type="email" class="form-control" id="inputEmail3" placeholder="">
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-6 control-label">Heure</label>
                <div class="col-sm-5">
                    <div class="input-group date-picker-all-days">
                        <input type="email" class="form-control" id="inputEmail3" placeholder="">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-dashboard"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <strong class="col-sm-6 text-right">Lieu</strong>
                <span class="col-sm-6">Colmar</span>
            </div>
        </div>
    </div>

    <div class="row row-margin row-button">
    <div class="col-xs-6">
        <a href="<?php echo url_for('degustation_operateurs') ?>" class="btn btn-primary btn-lg btn-upper">Précédent</a>
    </div>
    <div class="col-xs-6 text-right">
        <a href="<?php echo url_for('degustation_degustateurs') ?>" class="btn btn-default btn-default-step btn-lg btn-upper">Continuer</a>
    </div>
    </div>

    
</form>
