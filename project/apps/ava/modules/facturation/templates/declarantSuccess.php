<ol class="breadcrumb">
    <?php if(!$sf_user->isAdmin()): ?>
        <li><a href="<?php echo url_for('accueil'); ?>">Accueil</a></li>
    <?php endif; ?>
  <li><a href="<?php if($sf_user->isAdmin()): ?><?php echo url_for('facturation'); ?><?php else: ?><?php echo url_for('facturation_declarant', ['identifiant' => $compte]); ?><?php endif; ?>">Facturation</a></li>
  <li class="active"><a href=""><?php echo $compte->getNomAAfficher() ?> (<?php echo $compte->getIdentifiantAAfficher() ?>)</a></li>
</ol>

<?php use_helper('Date'); ?>
<?php use_helper('Float'); ?>
<?php use_helper('Generation'); ?>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<?php if ($sf_user->hasFlash('error')): ?>
    <div class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('error') ?></div>
<?php endif; ?>

<div class="page-header">
    <h2>Espace Facture</h2>
</div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-1">Date</th>
            <th class="col-xs-1">Numéro</th>
            <th class="col-xs-1">Type</th>
            <th class="col-xs-3">Origine</th>
            <th class="col-xs-2">Montant TTC</th>
            <th class="col-xs-2">Paiement</th>
            <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
            <th class="text-center" style="width: 0;"><span class="glyphicon glyphicon-list-alt"></span></th>
            <th class="text-center" style="width: 0;"><span class="glyphicon glyphicon-euro"></span></th>
            <th style="witdth: 0;"></th>
            <?php endif; ?>
            <th style="witdth: 0;"></th>
            <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
            <th style="witdth: 0;"><span title="Téléchargé par l'opérateur" class="glyphicon glyphicon-eye-open"></span></th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($factures as $facture) : ?>
        <tr>
            <td><?php echo format_date($facture->date_facturation, "dd/MM/yyyy", "fr_FR"); ?></td>
            <td>N°&nbsp;<?php echo $facture->numero_ava ?></td>
            <td><?php if($facture->isAvoir()): ?>AVOIR<?php else: ?>FACTURE<?php endif; ?></td>
            <td><?php echo implode(", ", $facture->getOrigineTypes()->getRawValue()) ?></td>
            <td class="text-right"><?php echo Anonymization::hideIfNeeded(echoFloat($facture->total_ttc)); ?>&nbsp;€</td>
            <td class="text-center">
                <?php if($facture->isPayee() && !$facture->isAvoir() && !$facture->versement_comptable_paiement && $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
                    <a style="<?php if($facture->versement_comptable_paiement): ?>cursor: not-allowed;<?php endif; ?>" href="<?php if(!$facture->versement_comptable_paiement): ?><?php echo url_for("facturation_paiements", array("id" => $facture->_id)) ?><?php else: ?>#<?php endif; ?>" class="btn btn-sm btn-default" data-toggle="tooltip" title="Paiement&nbsp;de&nbsp;<?php echo Anonymization::hideIfNeeded(echoFloat($facture->montant_paiement)); ?> €&nbsp;reçu&nbsp;le&nbsp;<?php echo format_date($facture->date_paiement, "dd/MM/yyyy", "fr_FR"); ?>"><span class="glyphicon glyphicon-ok-sign"></span> Reçu</a>
                <?php elseif($facture->isPayee() && !$facture->isAvoir()): ?>
                    <a style="cursor: help;" href="<?php echo url_for("facturation_paiements", array("id" => $facture->_id)) ?>" class="btn btn-sm btn-default" data-toggle="tooltip" title="Paiement&nbsp;de&nbsp;<?php echo Anonymization::hideIfNeeded(echoFloat($facture->montant_paiement)); ?> €&nbsp;reçu&nbsp;le&nbsp;<?php echo format_date($facture->date_paiement, "dd/MM/yyyy", "fr_FR"); ?>"><span class="glyphicon glyphicon-ok-sign"></span> Reçu</a>
                <?php elseif(!$facture->isAvoir() && $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
                    <a class="btn btn-sm btn-default-step" href="<?php echo url_for("facturation_paiements", array("id" => $facture->_id)) ?>"><span class="glyphicon glyphicon-pencil"></span> Saisir</a>
                <?php elseif($facture->isAvoir()): ?>
                    <span style="opacity: 0.4;" class="text-muted"><span class="glyphicon glyphicon-ban-circle"></span></span>
                <?php else : ?>
                <?php endif; ?>
            </td>
            <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
            <td class="text-center">
                <?php if($facture->versement_comptable): ?><span style="cursor: help;" data-toggle="tooltip" title="Facture versée en comptabilité" class="glyphicon glyphicon-check"></span><?php else: ?><span class="glyphicon glyphicon-unchecked text-muted" style="opacity: 0.4;"></span><?php endif; ?>
            </td>
            <td class="text-center">
                <?php if($facture->isVersementComptablePaiement() && !$facture->isAvoir()): ?><span style="cursor: help;" data-toggle="tooltip" title="Paiement versé en comptabilité" class="glyphicon glyphicon-check"></span><?php elseif(!$facture->isAvoir()): ?><span style="opacity: 0.4;" class="glyphicon glyphicon-unchecked text-muted"></span><?php else: ?><span style="opacity: 0.4;" class="text-muted"><span class="glyphicon glyphicon-ban-circle"></span></span><?php endif; ?>
            </td>
            <td>
                <button type="button" class="btn btn-default btn-default-step btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-cog"></span>&nbsp;<span class="caret"></span></button>
                <ul class="dropdown-menu">
                    <?php if(!$facture->isPayee() && !$facture->versement_comptable): ?>
                    <li><a href="<?php if(!$facture->isPayee() && !$facture->versement_comptable): ?><?php echo url_for("facturation_ava_edition", array("id" => $facture->_id)) ?><?php endif; ?>">Modifier</a></li>
                    <?php else: ?>
                        <li class="disabled"><a href="">Modifier</a></li>
                    <?php endif; ?>
                    <?php if(!$facture->isAvoir()): ?>
                    <li><a href="<?php echo url_for("facturation_ava_avoir", array("id" => $facture->_id)) ?>">Créer un avoir <small>(à partir de cette facture)</small></a></li>
                    <?php else: ?>
                        <li class="disabled"><a href="">Créer un avoir <small>(à partir de cette facture)</small></a></li>
                    <?php endif; ?>
                    <?php if(!$facture->isAvoir() && !$facture->isVersementComptablePaiement()): ?>
                    <li><a href="<?php echo url_for("facturation_paiements", array("id" => $facture->_id)) ?>">Saisir / modifier le paiement</a></li>
                    <?php else: ?>
                        <li class="disabled"><a href="">Saisir / modifier le paiement</a></li>
                    <?php endif; ?>
                </ul>
            </td>
            <?php endif; ?>
            <td class="text-right">
                <a href="<?php echo url_for("facturation_pdf", array("id" => $facture->_id)) ?>" class="btn btn-sm btn-default-step"><span class="glyphicon glyphicon-file"></span>&nbsp;Visualiser</a>
            </td>
            <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
                <td><span style="opacity: 0.8;" data-toggle="tooltip" title="La facture a été téléchargée par l'opérateur" class="glyphicon glyphicon-eye-open <?php if(!$facture->isTelechargee()): ?>invisible<?php endif; ?>"></span></td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
    <?php include_partial('facturation/mouvements', array('mouvements' => $mouvements)); ?>
<?php endif; ?>

<?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
    <div style="margin-top: 30px;">
    <?php include_partial('facturation/generationForm', array('form' => $form, 'massive' => false)); ?>
    </div>
<?php endif; ?>
