<div class="row chai form-horizontal" id="form_chai_<?php echo $indice ?>">
    <div class="col-xs-2  text-center" >
        <label class="control-label">Chai N°&nbsp;<?php echo $indice + 1; ?></label>

    </div> 
    <div class="col-xs-4  text-center" >
        <?php echo $form['adresse']->renderError() ?>
        <?php if (isset($existChai) && $existChai): ?>
            <?php echo $form['adresse']->render(array("class" => "form-control")); ?>
        <?php else : ?>
            <input id="compte_modification_chais_<?php echo $indice ?>_adresse" class="form-control" type="text" name="compte_modification[chais][<?php echo $indice ?>][adresse]">
        <?php endif; ?>
    </div> 
    <div class="col-xs-3  text-center" >
        <?php echo $form['commune']->renderError() ?>
        <?php if (isset($existChai) && $existChai): ?>
            <?php echo $form['commune']->render(array("class" => "form-control")); ?>
        <?php else : ?>
            <input id="compte_modification_chais_<?php echo $indice ?>_commune" class="form-control" type="text" name="compte_modification[chais][<?php echo $indice ?>][commune]">
        <?php endif; ?>
    </div> 
    <div class="col-xs-3  text-center" >
        <?php echo $form['code_postal']->renderError() ?>
        <?php if (isset($existChai) && $existChai): ?>
            <?php echo $form['code_postal']->render(array("class" => "form-control")); ?>
        <?php else : ?>
            <input id="compte_modification_chais_<?php echo $indice ?>_code_postal" class="form-control" type="text" name="compte_modification[chais][<?php echo $indice ?>][code_postal]">
        <?php endif; ?>
    </div>
</div>
<br/>