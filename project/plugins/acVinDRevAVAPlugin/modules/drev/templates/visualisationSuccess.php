<?php use_helper('Date') ?>

<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>

<?php if (isset($form)): ?>
  <form action="<?php echo url_for('drev_visualisation', $drev) ?>" method="post">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
<?php endif; ?>

<div class="page-header no-border">
    <h2>Déclaration de Revendication <?php echo $drev->campagne ?>
    <?php if($drev->isPapier()): ?>
    <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration papier<?php if($drev->validation && $drev->validation !== true): ?> reçue le <?php echo format_date($drev->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?><?php if($drev->isSauvegarde()): ?> <span class="text-danger">Non facturable</span><?php endif; ?> <?php if(!$drev->isNonFactures()): ?>
        <span class="btn btn-default-step btn-xs">Facturé</span>
    <?php endif; ?></small></small>
    <?php elseif($drev->validation): ?>
    <small class="pull-right"><?php if ($drev->exist('automatique') && $drev->automatique): ?>Générée automatiquement<?php else: ?>Télédéclaration<?php endif; ?><?php if($drev->validation && $drev->validation !== true): ?> validée le <?php echo format_date($drev->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?><?php if($drev->isSauvegarde()): ?> <span class="text-danger">Non facturable</span><?php endif; ?> <?php if(!$drev->isNonFactures()): ?>
        <span class="btn btn-default-step btn-xs">Facturé</span>
    <?php endif; ?></small>
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

<?php if (isset($form)): ?>
</form>
<?php endif; ?>

<?php if(DRevSecurity::getInstance($sf_user, $drev->getRawValue())->isAuthorized(DRevSecurity::VALIDATION_ADMIN) && $drev->exist('commentaire') && $drev->commentaire): ?>
<h3 class="">Commentaire interne <small>(seulement visible par l'ODG)</small></h3>
    <?php if ($drev->getValidationOdg()): ?>
        <pre>
        <?php echo $drev->commentaire; ?>
        </pre>
    <?php else: ?>
      <form id="formUpdateCommentaire" action="<?php echo url_for('drev_update_commentaire', $drev) ?>" method="post">
            <?php echo $drevCommentaireValidationForm->renderHiddenFields(); ?>
            <?php echo $drevCommentaireValidationForm->renderGlobalErrors(); ?>
            <?php echo $drevCommentaireValidationForm['commentaire']->render(['class' => 'form-control']) ?>
            <br/>
            <div class="form-group">
                <button type="submit" form="formUpdateCommentaire" class="btn btn-default btn-lg btn-upper">Modifier le commentaire</button>
            </div>
      </form>
    <?php endif; ?>
<?php endif; ?>

<div class="row row-margin row-button">
    <div class="col-xs-5">
        <a href="<?php if(isset($service)): ?><?php echo $service ?><?php else: ?><?php echo url_for("declaration_etablissement", $drev->getEtablissementObject()) ?><?php endif; ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
    <div class="col-xs-2 text-center">
            <a href="<?php echo url_for("drev_export_pdf", $drev) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
            </a>
    </div>
    <div class="col-xs-5 text-right">
        <div class="btn-group">
        <?php if (DRevSecurity::getInstance($sf_user, $drev->getRawValue())->isAuthorized(DRevSecurity::DEVALIDATION)): ?>
                    <a class="btn btn-default-step btn-lg" onclick='return confirm("Étes vous sûr de vouloir dévalider cette déclaration")' href="<?php echo url_for('drev_devalidation', $drev) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider</a>
        <?php endif; ?>
        <?php if(DRevSecurity::getInstance($sf_user, $drev->getRawValue())->isAuthorized(DRevSecurity::EDITION)): ?>
                <a href="<?php echo url_for("drev_edit", $drev) ?>" class="btn btn-default-step btn-lg"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
        <?php endif; ?>
        <?php if(DRevSecurity::getInstance($sf_user, $drev->getRawValue())->isAuthorized(DRevSecurity::VALIDATION_ADMIN)): ?>
                <?php if($drev->hasCompleteDocuments()): ?>
                <a href="<?php echo url_for("drev_validation_admin", array("sf_subject" => $drev, "service" => isset($service) ? $service : null)) ?>" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;&nbsp;Approuver</a>
                <?php else: ?>
                    <button type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Enregistrer</button>
                <?php endif; ?>
        <?php endif; ?>
        <?php if(DRevSecurity::getInstance($sf_user, $drev->getRawValue())->isAuthorized(DRevSecurity::MODIFICATRICE)): ?>
            <!--<a class="btn btn-lg btn-default-step" href="<?php echo url_for('drev_modificative', $drev) ?>">Modifier</a>-->
        <?php endif; ?>
        </div>
    </div>
</div>

