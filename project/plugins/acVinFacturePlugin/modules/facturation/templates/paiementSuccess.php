<?php use_helper('Date'); ?>
<?php use_helper('Float'); ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('facturation'); ?>">Facturation</a></li>
  <li><a href="<?php echo url_for('facturation_declarant', $facture->getCompte()); ?>"><?php echo $facture->getCompte()->getNomAAfficher() ?> (<?php echo $facture->getCompte()->getIdentifiantAAfficher() ?>)</a></li>
  <li class="active"><a href="">Paiement de la facture n°<?php echo $facture->numero_odg ?></a></li>
</ol>

<div class="page-header">
    <h2>Paiement de la facture n°<?php echo $facture->numero_odg ?> <small> du <?php echo format_date($facture->date_facturation, "dd/MM/yyyy", "fr_FR"); ?></small></h2>
</div>

<form action="" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row">
        <div class="col-xs-9">
            <div class="form-group">
                <label class="col-xs-4 control-label">Montant à payer</label>
                <div class="col-xs-5">
                    <div class="form-control-static"><?php echo echoFloat($facture->total_ttc); ?> € TTC</div>
                </div>
            </div>
            <div class="form-group <?php if($form["montant_paiement"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["montant_paiement"]->renderError(); ?>
                <?php echo $form["montant_paiement"]->renderLabel("Montant du paiement", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-5">
                    <div class="input-group">
                        <?php echo $form["montant_paiement"]->render(array("class" => "form-control num_float")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-euro"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group <?php if($form["date_paiement"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["date_paiement"]->renderError(); ?>
                <?php echo $form["date_paiement"]->renderLabel("Date du paiement", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-5">
                    <div class="input-group date-picker-week">
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
            <a href="<?php echo url_for('facturation_declarant', array("id" => "COMPTE-".$facture->identifiant)) ?>" class="btn btn-danger btn-lg btn-upper">Annuler</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Valider</a>
        </div>
    </div>
</form>
