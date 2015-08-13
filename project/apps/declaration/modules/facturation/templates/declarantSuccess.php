<?php use_helper('Date'); ?>
<?php use_helper('Float'); ?>
<?php use_helper('Generation'); ?>

<?php include_partial('ava/menu', array('active' => 'facturation')); ?>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<?php if ($sf_user->hasFlash('error')): ?>
    <div class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('error') ?></div>
<?php endif; ?>

<form method="post" action="" role="form" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row">
        <div class="col-xs-8 col-xs-offset-2">
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
                <div class="col-xs-6 col-xs-offset-6">
                    <button class="btn btn-default btn-lg btn-block btn-upper" type="submit">Générer la facture</button>
                </div>
            </div>
        </div>
    </div>
</form> 

<div class="row row-margin">
    <div class="col-xs-12">
        <?php if(count($factures)): ?>
        <div class="list-group">
            <?php foreach ($factures as $facture) : ?>
                <li class="list-group-item col-xs-12">
                    <span class="col-xs-2">N° <?php echo $facture->numero_ava ?></span>
                    <span class="col-xs-2"><small class="text-muted">Facturé&nbsp;le&nbsp;</small><?php echo format_date($facture->date_facturation, "dd/MM/yyyy", "fr_FR"); ?></span>
                    <span class="col-xs-2 text-right"><?php echo echoFloat($facture->total_ttc); ?> € TTC</span>
                    <span class="col-xs-6 text-right">
                        <div class="btn-group">
                        <?php if(!$facture->isPayee()): ?>
                        <a href="<?php echo url_for("facturation_regenerate", array("id" => $facture->_id)) ?>" onclick='return confirm("Étes vous sûr de vouloir regénérer la facture ?");'  class="btn btn-sm btn-default btn-default-step"<?php if ($facture->versement_comptable) echo ' disabled="disabled"'; ?>><span class="glyphicon glyphicon-repeat"></span>&nbsp;Regénerer</a>
                        <?php endif; ?>
                        <?php if(!$facture->isPayee()): ?>
                        <a href="<?php echo url_for("facturation_edition", array("id" => $facture->_id)) ?>"class="btn btn-sm btn-default btn-default-step"<?php if ($facture->versement_comptable) echo ' disabled="disabled"'; ?>><span class="glyphicon glyphicon-pencil"></span>&nbsp;Modifier</a>
                        <?php endif; ?>
                        <?php if(!$facture->isPayee()): ?>
                        <a href="<?php echo url_for("facturation_paiement", array("id" => $facture->_id)) ?>"  class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-euro"></span>&nbsp;Paiement</a>
                        <?php endif; ?>
                        <?php if($facture->isPayee()): ?>
                            <a href="<?php echo url_for("facturation_paiement", array("id" => $facture->_id)) ?>"  class="btn btn-sm btn-default btn-default-step"><span class="glyphicon glyphicon-euro"></span>&nbsp;Modifier le Paiement</a>
                        <?php endif; ?>
                        <a href="<?php echo url_for("facturation_pdf", array("id" => $facture->_id)) ?>" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-file"></span>&nbsp;Visualiser</a>
                        </div>
                    </span>
                    <span class="col-xs-8 col-xs-offset-2">
                        <?php if(!$facture->isPayee()): ?>
                            <span class="label label-warning">Paiement non reçu</span>
                        <?php endif; ?>
                        <?php if($facture->versement_comptable): ?>
			    <span class="label label-success">Versé comptablement</span>
                        <?php endif; ?>
                        <?php if($facture->versement_comptable_paiement): ?>
                            <span class="label label-success">Paiement versé comptablement</span>
                        <?php endif; ?>

                        <?php if($facture->isPayee()): ?>
                        <span class="label label-success">Paiement&nbsp;reçu</span><small class="text-muted">&nbsp;le&nbsp;</small><?php echo format_date($facture->date_paiement, "dd/MM/yyyy", "fr_FR"); ?>
                        <span class="text-muted">(<?php echo $facture->reglement_paiement ?>)</span>
                        <?php endif; ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-center text-muted"><i>Aucune Facture</i></p>
        <?php endif; ?>
    </div>
</div>
