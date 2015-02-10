<?php include_partial('admin/menu', array('active' => 'tournees')); ?>

<form action="" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row">
        <div class="col-xs-8">
            <div class="form-group <?php if($form["date"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["date_prelevement_debut"]->renderError(); ?>
                <?php echo $form["date_prelevement_debut"]->renderLabel("Date de début de prélévement", array("class" => "col-xs-6 control-label")); ?>
                <div class="col-xs-6">
                    <div class="input-group date-picker-all-days">
                        <?php echo $form["date_prelevement_debut"]->render(array("class" => "form-control")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group <?php if($form["date"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["date"]->renderError(); ?>
                <?php echo $form["date"]->renderLabel("Date de dégustation", array("class" => "col-xs-6 control-label")); ?>
                <div class="col-xs-6">
                    <div class="input-group date-picker-all-days">
                        <?php echo $form["date"]->render(array("class" => "form-control")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group <?php if($form["appellation"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["appellation"]->renderError(); ?>
                <?php echo $form["appellation"]->renderLabel("Appellation / Mention", array("class" => "col-xs-6 control-label")); ?>
                <div class="col-xs-6">
                    <?php echo $form["appellation"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
            <div class="form-group text-right">
                <div class="col-xs-6 col-xs-offset-6">
                    <button type="submit" class="btn btn-default btn-lg btn-block btn-upper">Créer</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row" style="margin-top: 20px;">
        <div class="col-xs-12">
            <div class="list-group">
                <a href="<?php //echo url_for('degustation_prelevements') ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-2 text-muted">
                    20/02/2014
                    </span>
                    <span class="col-xs-2 text-muted">
                    AOC Alsace
                    </span>
                    <span class="col-xs-6 text-muted">
                    50 opérateurs, 12 dégustateurs et 5 tournées
                    </span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-default">Saisie</span>
                    </span>
                </a>
                <a href="<?php echo url_for('degustation_tournee') ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-2 text-muted">
                    20/02/2014
                    </span>
                    <span class="col-xs-2 text-muted">
                    AOC Alsace
                    </span>
                    <span class="col-xs-6 text-muted">
                    50 opérateurs, 12 dégustateurs et 5 tournées
                    </span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-info">Tournée</span>
                    </span>
                </a>
                <a href="<?php echo url_for('degustation_affectation') ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-2 text-muted">
                    20/02/2014
                    </span>
                    <span class="col-xs-2 text-muted">
                    AOC Alsace
                    </span>
                    <span class="col-xs-6 text-muted">
                    50 opérateurs, 12 dégustateurs et 5 tournées
                    </span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-warning">Affectation des vins</span>
                    </span>
                </a>
                <a href="<?php echo url_for('degustation_degustation') ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-2 text-muted">
                    20/02/2014
                    </span>
                    <span class="col-xs-2 text-muted">
                    AOC Alsace
                    </span>
                    <span class="col-xs-6 text-muted">
                    50 opérateurs, 12 dégustateurs et 5 tournées
                    </span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-danger">Dégustation</span>
                    </span>
                </a>
                <a href="" class="list-group-item col-xs-12">
                    <span class="col-xs-2 text-muted">
                    20/02/2014
                    </span>
                    <span class="col-xs-2 text-muted">
                    AOC Alsace
                    </span>
                    <span class="col-xs-6 text-muted">
                    50 opérateurs, 12 dégustateurs et 5 tournées
                    </span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-success">Terminé</span>
                    </span>
                </a>
            </div>
</div>
</form>
