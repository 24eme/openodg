<?php include_partial('degustation/breadcrumb'); ?>

<div class="page-header no-border">
    <h2>Création d'une dégustation</h2>
</div>
<form action="<?php echo url_for('degustation') ?>" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    
    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>
    
    <div class="row">
        <div class="col-sm-10 col-xs-12">
            <div class="form-group <?php if($form["date"]->getError()): ?>has-error<?php endif; ?>">
                <?php echo $form["date"]->renderError(); ?>
                <?php echo $form["date"]->renderLabel("Date de dégustation", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-sm-6 col-xs-8">
                    <div class="input-group date-picker-week">
                        <?php echo $form["date"]->render(array("class" => "form-control")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group <?php if($form["lieu"]->getError()): ?>has-error<?php endif; ?>">
                <?php echo $form["lieu"]->renderError(); ?>
                <?php echo $form["lieu"]->renderLabel("Lieu de dégustation", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-sm-6 col-xs-8">
                	<?php echo $form["lieu"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
            <div class="form-group text-right">
                <div class="col-sm-4 col-sm-offset-6 col-xs-12">
                    <button type="submit" class="btn btn-default btn-block btn-upper">Créer</button>
                </div>
            </div>
        </div>
    </div>
</form>