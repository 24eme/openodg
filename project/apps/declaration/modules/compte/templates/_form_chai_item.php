<div class="row chai form-horizontal" id="form_chai_<?php echo $indice ?>">
    <div class="col-xs-3 text-center" >
        <?php echo $form['adresse']->renderError() ?>
        <?php echo $form['adresse']->render(array("class" => "form-control")); ?>
    </div> 
    <div class="col-xs-3 text-center" >
        <?php echo $form['commune']->renderError() ?>
        <?php echo $form['commune']->render(array("class" => "form-control")); ?>
    </div> 
    <div class="col-xs-2 text-center" >
        <?php echo $form['code_postal']->renderError() ?>
        <?php echo $form['code_postal']->render(array("class" => "form-control")); ?>
    </div>
    <div class="col-xs-4 text-center">
        <?php echo $form["attributs"]->renderError(); ?>
        <?php echo $form["attributs"]->render(array("class" => "form-control select2 select2-offscreen select2autocomplete", "placeholder" => "Ajouter des attributs")); ?>
    </div>
</div>
<br/>