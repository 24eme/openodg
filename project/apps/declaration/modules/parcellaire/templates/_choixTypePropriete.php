<?php echo $parcellaireTypeProprietaireForm->renderHiddenFields() ?>
<?php echo $parcellaireTypeProprietaireForm->renderGlobalErrors() ?>
<div class="row">
    <div class="row col-xs-12 ">  

        <div class="form-group">
            <?php echo $parcellaireTypeProprietaireForm["type_proprietaire"]->renderError(); ?>
            <?php //echo $parcellaireTypeProprietaireForm["type_proprietaire"]->renderLabel("type_proprietaire", array("class" => "col-xs-3 control-label")); ?>
            <div class="col-xs-9">
                <?php echo $parcellaireTypeProprietaireForm["type_proprietaire"]->render(array("class" => "checkbox-inline")); ?>
            </div>
        </div>

        <div class="form-group">
            <?php echo $parcellaireTypeProprietaireForm["acheteurs_select"]->renderError(); ?>
            <?php echo $parcellaireTypeProprietaireForm["acheteurs_select"]->renderLabel("Vos acheteurs", array("class" => "col-xs-3 control-label")); ?>
            <div class="col-xs-9">
                <?php echo $parcellaireTypeProprietaireForm["acheteurs_select"]->render(array("class" => "form-control select2 select2-offscreen select2autocomplete", "placeholder" => "Selectionner des acheteurs")); ?>
            </div>
        </div>

    </div>
</div>
