<div class="page-header">
    <h2>Paiement de la facture</h2>
</div>

<form action="" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row">
        <div class="col-xs-9">
            <div class="form-group <?php if($form["date_paiement"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["date_paiement"]->renderError(); ?>
                <?php echo $form["date_paiement"]->renderLabel("Date du paiement", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-5">
                    <div class="input-group date-picker">
                        <?php echo $form["date_paiement"]->render(array("class" => "form-control")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group <?php if($form["reglement_paiement"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["reglement_paiement"]->renderError(); ?>
                <?php echo $form["reglement_paiement"]->renderLabel("Réglement", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <?php echo $form["reglement_paiement"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for('facturation_declarant', array("id" => "COMPTE-".$this->facture->identifiant)) ?>" class="btn btn-danger btn-lg btn-upper">Annuler</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Valider</a>
        </div>
    </div>
</form>