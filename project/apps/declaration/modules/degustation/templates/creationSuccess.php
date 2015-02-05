<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => false)); ?>

<div class="page-header">
    <h2>Fin de la création</h2>
</div>

<form action="" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row">
        <div class="col-xs-12">
            <h3>Appellation / Mention</h3>
            <div class="form-group">
                <strong class="col-xs-4 text-right"></strong>
                <div class="col-xs-8"><span>AOC Alsace</span></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-8">
            <h3>Prélévements</h3>
            <div class="form-group">
                <strong  class="col-xs-6 text-right">Date de prélevement</strong>
                <div class="col-xs-6"><span>du 12/12/2014 au 02/28/2014</span></div>
            </div>
            <div class="form-group">
                <strong  class="col-xs-6 text-right">Nombre d'opérateurs concernés</strong>
                <div class="col-xs-6"><span>60</span></div>
            </div>
            <div class="form-group <?php if($form["nombre_operateurs_a_prelever"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["nombre_operateurs_a_prelever"]->renderError(); ?>
                <?php echo $form["nombre_operateurs_a_prelever"]->renderLabel("Nombre d'opérateurs à prélever", array("class" => "col-xs-6 control-label")); ?>
                <div class="col-xs-2">
                    <?php echo $form["nombre_operateurs_a_prelever"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-8">
            <h3>Dégustation</h3>
            <div class="form-group">
                <strong  class="col-xs-6 text-right">Date de la dégustation</strong>
                <div class="col-xs-6"><span>01/03/2014</span></div>
            </div>
            <div class="form-group <?php if($form["nombre_commissions"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["nombre_commissions"]->renderError(); ?>
                <?php echo $form["nombre_commissions"]->renderLabel("Nombre de commissions", array("class" => "col-xs-6 control-label")); ?>
                <div class="col-xs-2">
                    <?php echo $form["nombre_commissions"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
            <div class="form-group <?php if($form["heure"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["heure"]->renderError(); ?>
                <?php echo $form["heure"]->renderLabel("Heure", array("class" => "col-xs-6 control-label")); ?>
                <div class="col-xs-3">
                    <div class="input-group date-picker-time">
                        <?php echo $form["heure"]->render(array("class" => "form-control")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-time"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <strong  class="col-xs-6 text-right">Lieu</strong>
                <div class="col-xs-6">Colmar</div>
            </div>
            <?php if(isset($form['lieu'])): ?>
            <div class="form-group <?php if($form["lieu"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["lieu"]->renderError(); ?>
                <?php echo $form["lieu"]->renderLabel("Lieu", array("class" => "col-xs-6 control-label")); ?>
                <div class="col-xs-6">
                    <?php echo $form["lieu"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for('degustation') ?>" class="btn btn-danger btn-lg btn-upper">Annuler</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer</a>
        </div>
    </div>
</form>