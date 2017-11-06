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
    <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration papier<?php if($drev->validation && $drev->validation !== true): ?> reçue le <?php echo format_date($drev->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?><?php if($drev->isSauvegarde()): ?> <span class="text-danger">Non facturé</span><?php endif; ?></small>
    <?php elseif($drev->validation): ?>
    <small class="pull-right">Télédéclaration<?php if($drev->validation && $drev->validation !== true): ?> validée le <?php echo format_date($drev->validation, "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?><?php if($drev->isSauvegarde()): ?> <span class="text-danger">Non facturable</span><?php endif; ?> <?php if(!$drev->isNonFactures()): ?>
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
        Cette déclaration est en <strong>attente de validation</strong> par l'ODG
    </div>
<?php endif; ?>

<?php if(isset($validation) && $validation->hasPoints()): ?>
    <?php include_partial('drev/pointsAttentions', array('drev' => $drev, 'validation' => $validation)); ?>
<?php endif; ?>

<?php include_partial('drev/recap', array('drev' => $drev, 'form' => $form)); ?>

<!--<div class="row">
    <div class="col-xs-12">
        <?php include_partial('drev/documents', array('drev' => $drev, 'form' => isset($form) ? $form : null)); ?>
    </div>
</div>-->

<div class="row row-margin row-button">
    <div class="col-xs-5">
        <a href="<?php if(isset($service)): ?><?php echo $service ?><?php else: ?><?php echo url_for("declaration_etablissement", array('identifiant' => $drev->identifiant, 'campagne' => $drev->campagne)); ?><?php endif; ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
    <div class="col-xs-2 text-center">
            <a href="<?php echo url_for("drev_export_pdf", $drev) ?>" class="btn btn-success">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
            </a>
    </div>

    <div class="col-xs-2 text-right">
        <?php if ($drev->validation && DRevSecurity::getInstance($sf_user, $drev->getRawValue())->isAuthorized(DRevSecurity::DEVALIDATION)): ?>
                    <a class="btn btn-xs btn-default pull-right" href="<?php echo url_for('drev_devalidation', $drev) ?>" onclick="return confirm('Êtes-vous sûr de vouloir dévalider cette DRev ?');"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider</a>
        <?php elseif ($drev->validation && $sf_user->isAdmin() && !$drev->isLectureSeule()): ?>
                  <a class="btn btn-xs btn-default-step pull-right hidden-xs" onClick="return confirm('Attention, cette DRev a sans doute été facturée. Si vous changez un volume, pensez à en faire part au service comptable !!');" href="<?php echo url_for('drev_devalidation', $drev) ?>"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Réouvrir</a>
        <?php endif; ?>
    </div>
    <div class="col-xs-3 text-right">
        <?php if(!$drev->validation): ?>
                <a href="<?php echo url_for("drev_edit", $drev) ?>" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Continuer la saisie</a>
        <?php elseif(!$drev->validation_odg && $sf_user->isAdmin()): ?>
                <?php if($drev->hasCompleteDocuments() || true): ?>
                <div class="btn-group">
                <a href="" class="btn btn-default btn-upper dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-ok-sign"></span>&nbsp;&nbsp;Approuver <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a href="<?php echo url_for("drev_validation_admin", array("sf_subject" => $drev, "service" => isset($service) ? $service : null)) ?>">Partiellement</a></li>
                  <li><a href="<?php echo url_for("drev_validation_admin", array("sf_subject" => $drev, "service" => isset($service) ? $service : null)) ?>">Totalement</a></li>
                </ul>
                </div>
                <?php else: ?>
                    <button type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Enregistrer</button>
                <?php endif; ?>
        <?php else: ?>
            <a onclick="return confirm('Étes vous sûr de vouloir créer une modificatrice ?')" class="btn btn-default pull-right" href="<?php echo url_for('drev_modificative', $drev) ?>">Créer une modificatrice</a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($form)): ?>
</form>
<?php endif; ?>
