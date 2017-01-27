<?php use_helper('Date') ?>

<?php if (isset($form)): ?>
    <form action="<?php echo url_for('drev_visualisation', $drev) ?>" method="post">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
<?php endif; ?>

<div class="page-header no-border">
    <h2>Déclaration de Revendication <?php echo $drev->campagne ?>
    <?php if($drev->isPapier()): ?>
    <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration papier<?php if($drev->validation && $drev->validation !== true): ?> reçue le <?php echo format_date($drev->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?></small>
    <?php elseif($drev->validation): ?>
    <small class="pull-right">Télédéclaration<?php if($drev->validation && $drev->validation !== true): ?> validée le <?php echo format_date($drev->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?></small>
    <?php endif; ?>
    </h2>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>


<?php if(!$drev->validation): ?>
<div class="alert alert-warning">
    La saisie de cette déclaration n'est pas terminée elle est en cours d'édition
</div>
<?php endif; ?>

<?php if($drev->validation && !$drev->validation_odg && $sf_user->isAdmin()): ?>
    <div class="alert alert-warning">
        Cette déclaration est en <strong>attente de validation</strong> par l'AVA
    </div>
<?php endif; ?>

<?php if(isset($validation) && $validation->hasPoints()): ?>
    <?php include_partial('drev/pointsAttentions', array('drev' => $drev, 'validation' => $validation)); ?>
<?php endif; ?>

<?php include_partial('drev/recap', array('drev' => $drev, 'form' => $form)); ?>

<div class="row">
    <div class="col-xs-12">
        <?php include_partial('drev/documents', array('drev' => $drev, 'form' => isset($form) ? $form : null)); ?>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php if(isset($service)): ?><?php echo $service ?><?php else: ?><?php echo url_for("home") ?><?php endif; ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
    <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("drev_export_pdf", $drev) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
            </a>
    </div>
    <?php if(!$drev->validation): ?>
    <div class="col-xs-4 text-right">
            <a href="<?php echo url_for("drev_edit", $drev) ?>" class="btn btn-warning btn-lg"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
    </div>
    <?php elseif(!$drev->validation_odg && $sf_user->isAdmin()): ?>
    <div class="col-xs-4 text-right">
            <?php if($drev->hasCompleteDocuments()): ?>
            <a href="<?php echo url_for("drev_validation_admin", array("sf_subject" => $drev, "service" => isset($service) ? $service : null)) ?>" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;&nbsp;Approuver</a>
            <?php else: ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Enregistrer</button>
            <?php endif; ?>
    </div>
    <?php elseif (DRevSecurity::getInstance($sf_user, $drev->getRawValue())->isAuthorized(DRevSecurity::DEVALIDATION)): ?>
    <div class="col-xs-4 text-right">
            <a class="btn btn-xs btn-warning pull-right" href="<?php echo url_for('drev_devalidation', $drev) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider la déclaration</a>
    </div>
    <?php elseif ($sf_user->isAdmin()): ?>
      <div class="col-xs-4 text-right">
              <a class="btn btn-xs btn-warning pull-right" onClick="return confirm('Attention, cette DRev a sans doute été facturée. Si vous changez un volume, pensez à en faire part au service comptable !!');" href="<?php echo url_for('drev_devalidation', $drev) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Réouvrir la déclaration</a>
      </div>
    <?php endif; ?>
</div>

<?php if (isset($form)): ?>
</form>
<?php endif; ?>
