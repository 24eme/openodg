<?php include_partial('degustation/breadcrumb', array('tournee' => $tournee )); ?>

<div class="page-header">
    <h2>Création d'une dégustation</h2>
</div>

<form action="<?php echo url_for("degustation_saisie_creation", array("appellation" => $tournee->appellation, "date" => $tournee->date)) ?>" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <div class="form-group <?php if($form["date"]->hasError()): ?>has-error<?php endif; ?>">
        <?php echo $form["date"]->renderError(); ?>
        <?php echo $form["date"]->renderLabel("Date de dégustation", array("class" => "col-xs-3 control-label")); ?>
        <div class="col-xs-3">
            <div class="input-group date-picker-week">
                <?php echo $form["date"]->render(array("class" => "form-control")); ?>
                <div class="input-group-addon">
                    <span class="glyphicon-calendar glyphicon"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group <?php if($form["produit"]->hasError()): ?>has-error<?php endif; ?>">
        <?php echo $form["produit"]->renderError(); ?>
        <?php echo $form["produit"]->renderLabel("Lieu-dit", array("class" => "col-xs-3 control-label")); ?>
        <div class="col-xs-5">
            <?php echo $form["produit"]->render(array("class" => "select2autocomplete form-control select2", "placeholder" => "Séléctionner un produit")); ?>
        </div>
    </div>

    <div class="form-group <?php if($form["millesime"]->hasError()): ?>has-error<?php endif; ?>">
        <?php echo $form["millesime"]->renderError(); ?>
        <?php echo $form["millesime"]->renderLabel("Millésime", array("class" => "col-xs-3 control-label")); ?>
        <div class="col-xs-3">
            <?php echo $form["millesime"]->render(array("class" => "form-control")); ?>
        </div>
    </div>

    <div class="form-group <?php if($form["organisme"]->hasError()): ?>has-error<?php endif; ?>">
        <?php echo $form["organisme"]->renderError(); ?>
        <?php echo $form["organisme"]->renderLabel("Organisme dégustateur", array("class" => "col-xs-3 control-label")); ?>
        <div class="col-xs-5">
            <?php echo $form["organisme"]->render(array("class" => "form-control")); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-6">
        </div>
        <div class="col-xs-6 text-right">
            <button class="btn btn-default btn-lg btn-dynamic-element-submit" type="submit">Valider</button>
        </div>
    </div>
</form>
