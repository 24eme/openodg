<?php include_partial('admin/menu', array('active' => 'facturation')); ?>

<form method="post" action="" role="form" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row">
        <div class="col-xs-8 col-xs-offset-2">
            <div class="form-group <?php if($form["requete"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["requete"]->renderError() ?>
                <?php echo $form["requete"]->render(array("class" => "form-control input-lg")); ?>
            </div>
            <div class="form-group <?php if($form["modele"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["modele"]->renderError() ?>
                <?php echo $form["modele"]->renderLabel("Type de facture", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                <?php echo $form["modele"]->render(array("class" => "form-control input-lg")); ?>
                </div>
            </div>
            <div class="form-group <?php if($form["date_facturation"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["date_facturation"]->renderError(); ?>
                <?php echo $form["date_facturation"]->renderLabel("Date de facturation", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <div class="input-group date-picker">
                        <?php echo $form["date_facturation"]->render(array("class" => "form-control input-lg", "placeholder" => "Date de facturation")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group text-right">
                <div class="col-xs-8 col-xs-offset-4">
                    <button class="btn btn-default btn-lg btn-block btn-upper" type="submit">Générer</button>
                </div>
            </div>
            
        </div>
    </div>
</form>  
