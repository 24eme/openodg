<?php include_partial('admin/menu', array('active' => 'export')); ?> 

<form method="post" action="" role="form" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row">
        <div class="col-xs-6 col-xs-offset-3">
            <div class="form-group <?php if($form["generation"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["generation"]->renderError() ?>
                <div class="col-xs-12">
                <?php echo $form["generation"]->render(array("class" => "form-control input-lg select2 select2-offscreen select2autocomplete", "placeholder" => "Sélectionner un export")); ?>
                </div>
            </div>

            <div class="form-group text-right">
                <div class="col-xs-6 col-xs-offset-6">
                    <button class="btn btn-default btn-lg btn-block btn-upper" type="submit">Générer l'export</button>
                </div>
            </div>
        </div>
    </div>
</form> 


<?php include_partial('generation/list', array('generations' => $generationsList)); ?>