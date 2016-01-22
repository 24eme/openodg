<?php use_helper('Date') ?>
<?php include_partial('degustation/step', array('tournee' => $tournee, 'active' => false)); ?>

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
                <div class="col-xs-8"><span><?php echo $tournee->appellation ?></span></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-10">
            <h3>Prélévements</h3>
            <div class="form-group">
                <strong  class="col-xs-5 text-right">Date de prélevement</strong>
                <div class="col-xs-7"><span>du <?php echo format_date($tournee->date_prelevement_debut, "D", "fr_FR") ?> au <?php echo format_date($tournee->date_prelevement_fin, "D", "fr_FR") ?></span></div>
            </div>
            <div class="form-group">
                <strong  class="col-xs-5 text-right">Nombre d'opérateurs concernés</strong>
                <div class="col-xs-7"><span><?php echo count($operateurs) ?></span> <small class="text-muted">(hors reports de la précédente tournée)</small></div>
            </div>
            <div class="form-group <?php if($form["nombre_operateurs_a_prelever"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["nombre_operateurs_a_prelever"]->renderError(); ?>
                <?php echo $form["nombre_operateurs_a_prelever"]->renderLabel("Nombre d'opérateurs à prélever", array("class" => "col-xs-5 control-label")); ?>
                <div class="col-xs-2">
                    <?php echo $form["nombre_operateurs_a_prelever"]->render(array("class" => "form-control")); ?>
                </div>
                <label class="control-label text-muted" style="font-weight: normal;"> + <strong><?php echo $nb_reports ?></strong> reports de la précédente tournée</label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-8">
            <h3>Dégustation</h3>
            <div class="form-group">
                <strong  class="col-xs-6 text-right">Date de la dégustation</strong>
                <div class="col-xs-6"><span><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></span></div>
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
            <?php if(!$tournee->isNew()): ?>
            <a href="<?php echo url_for('degustation_suppression', $tournee) ?>" class="btn btn-danger btn-lg btn-upper">Supprimer</a>
            <?php else: ?>
            <a href="<?php echo url_for('degustation') ?>" class="btn btn-danger btn-lg btn-upper">Annuler</a>
            <?php endif; ?>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer</a>
        </div>
    </div>
</form>