<?php include_partial('parcellaire/step', array('step' => 'propriete', 'parcellaire' => $parcellaire)) ?>

<div class="page-header">
    <h2>Type de propriété</h2>
</div>

<form action="" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>
    <div class="row">
        <div class="row col-xs-12 ">  

            <div class="form-group">
                <?php echo $form["type_proprietaire"]->renderError(); ?>
                <?php //echo $form["type_proprietaire"]->renderLabel("type_proprietaire", array("class" => "col-xs-3 control-label")); ?>
                <div class="col-xs-9">
                    <?php echo $form["type_proprietaire"]->render(array("class" => "checkbox-inline")); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form["acheteurs_select"]->renderError(); ?>
                <?php echo $form["acheteurs_select"]->renderLabel("Vos acheteurs", array("class" => "col-xs-3 control-label")); ?>
                <div class="col-xs-9">
                    <?php echo $form["acheteurs_select"]->render(array("class" => "form-control select2 select2-offscreen select2autocomplete", "placeholder" => "Selectionner des acheteurs")); ?>
                </div>
            </div>

        </div>
    </div>

    <div class="row row-margin row-button">
        <div class="col-xs-6"><a href="<?php echo url_for("parcellaire_exploitation", $parcellaire) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a></div>
    <?php if ($parcellaire->exist('etape') && $parcellaire->etape == ParcellaireEtapes::ETAPE_VALIDATION): ?>
        <div class="col-xs-6 text-right"><button id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span> Retourner <small>à la validation</small>&nbsp;&nbsp;</button><br /></div>
    <?php else: ?>
        <div class="col-xs-6 text-right"><button type="submit" class="btn btn-default btn-lg btn-upper">Continuer <small>vers les parcelles</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button></div>
            <?php endif; ?>
    </div>

</form>