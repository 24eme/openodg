<?php include_partial('parcellaire/step', array('step' => 'propriete', 'parcellaire' => $parcellaire)) ?>

<div class="page-header">
    <h2>Destination des raisins</h2>
</div>

<form action="<?php echo url_for("parcellaire_propriete", $parcellaire) ?>" method="post" class="form-horizontal ajaxForm">
    <?php echo $form->renderHiddenFields() ?>
    <?php if($form->hasGlobalErrors()): ?>
    <div class="alert alert-danger">
    <?php echo $form->renderGlobalErrors(array("class" => "text-left")) ?>
    </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-xs-12">  
            <?php foreach($form as $key => $formDestination): ?>
            <?php if($formDestination->isHidden()): continue; endif; ?>
            <div class="form-group">
                <?php echo $formDestination["declarant"]->renderError(); ?>
                <label class="col-xs-12">
                    <?php echo $formDestination["declarant"]->render(array("class" => "checkbox-relation", "data-relation" => "#autocomplete_acheteurs_".$key)); ?>
                    <?php echo $formDestination["declarant"]->renderLabelName(); ?>
                </label>
            </div>
            <?php if(isset($formDestination["acheteurs"])): ?>
            <div id="autocomplete_acheteurs_<?php echo $key ?>" class="form-group <?php if(!$formDestination["declarant"]->getValue()): ?>hidden<?php endif; ?>">
                <?php echo $formDestination["acheteurs"]->renderError(); ?>
                <div class="col-xs-12">
                    <?php echo $formDestination["acheteurs"]->render(array("class" => "form-control select2 select2-offscreen select2autocomplete", "placeholder" => "Selectionner des acheteurs")); ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
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