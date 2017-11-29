<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>
<div class="row">
    <div class="row col-xs-12">
        <div class="col-xs-12">
            <div class="form-group">
                <?php echo $form["email"]->renderError(); ?>
                <?php echo $form["email"]->renderLabel(null, array("class" => "col-xs-2 control-label")); ?>
                <div class="col-xs-10">
                    <?php echo $form["email"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
        </div>
    </div>
</div>