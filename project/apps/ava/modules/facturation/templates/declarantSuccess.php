<ol class="breadcrumb">
    <?php if(!$sf_user->isAdmin()): ?>
        <li><a href="<?php echo url_for('accueil'); ?>">Accueil</a></li>
    <?php endif; ?>
  <li><a href="<?php if($sf_user->isAdmin()): ?><?php echo url_for('facturation'); ?><?php else: ?><?php echo url_for('facturation_declarant', $compte); ?><?php endif; ?>">Facturation</a></li>
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

<?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
<h3>Générer une facture</h3>
<form method="post" action="" role="form" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row">
        <div class="col-sm-8 col-xs-12">
          <?php if(isset($form["modele"])): ?>
            <div class="form-group <?php if($form["modele"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["modele"]->renderError() ?>
                <?php echo $form["modele"]->renderLabel("Type de facture", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                <?php echo $form["modele"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
          <?php endif; ?>
            <div class="form-group <?php if($form["date_facturation"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["date_facturation"]->renderError(); ?>
                <?php echo $form["date_facturation"]->renderLabel("Date de facturation", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <div class="input-group date-picker-week">
                        <?php echo $form["date_facturation"]->render(array("class" => "form-control", "placeholder" => "Date de facturation")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group text-right">
                <div class="col-xs-6 col-xs-offset-6">
                    <button class="btn btn-default btn-block btn-upper" type="submit">Générer la facture</button>
                </div>
            </div>
        </div>
    </div>
</form>
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
            <th class="col-xs-3">Libellé</th>
            <th class="col-xs-2">Montant TTC</th>
            <th class="col-xs-2">Paiement</th>
            <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
            <th class="text-center" style="width: 0;"><span class="glyphicon glyphicon-list-alt"></span></th>
            <th class="text-center" style="width: 0;"><span class="glyphicon glyphicon-euro"></span></th>
            <th style="witdth: 0;"></th>
            <?php endif; ?>
            <th style="witdth: 0;"></th>
            <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
            <th style="witdth: 0;"><span title="Téléchargé par l'opérateur" class="glyphicon glyphicon-eye-open"></span</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($factures as $facture) : ?>
        <tr>
            <td><?php echo format_date($facture->date_facturation, "dd/MM/yyyy", "fr_FR"); ?></td>
            <td>N°&nbsp;<?php echo $facture->numero_ava ?></td>
            <td><?php if($facture->isAvoir()): ?>AVOIR<?php else: ?>FACTURE<?php endif; ?></td>
            <td><?php if(!$facture->isAvoir()): ?><?php echo $facture->getTemplate()->libelle ?><?php endif; ?></td>
            <td class="text-right"><?php echo Anonymization::hideIfNeeded(echoFloat($facture->total_ttc)); ?>&nbsp;€</td>
            <td class="text-center">
                <?php if($facture->isPayee() && !$facture->isAvoir() && !$facture->versement_comptable_paiement && $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
                    <a style="<?php if($facture->versement_comptable_paiement): ?>cursor: not-allowed;<?php endif; ?>" href="<?php if(!$facture->versement_comptable_paiement): ?><?php echo url_for("facturation_paiement", array("id" => $facture->_id)) ?><?php else: ?>#<?php endif; ?>" class="btn btn-sm btn-default" data-toggle="tooltip" title="Paiement&nbsp;de&nbsp;<?php echo Anonymization::hideIfNeeded(echoFloat($facture->montant_paiement)); ?> €&nbsp;reçu&nbsp;le&nbsp;<?php echo format_date($facture->date_paiement, "dd/MM/yyyy", "fr_FR"); ?>
                        <?php if($facture->reglement_paiement): ?>(<?php echo $facture->reglement_paiement ?>)<?php endif; ?>"><span class="glyphicon glyphicon-ok-sign"></span> Reçu
                    </a>
                <?php elseif($facture->isPayee() && !$facture->isAvoir()): ?>
                    <a style="cursor: help;" href="#" class="btn btn-sm btn-default" data-toggle="tooltip" title="Paiement&nbsp;de&nbsp;<?php echo Anonymization::hideIfNeeded(echoFloat($facture->montant_paiement)); ?> €&nbsp;reçu&nbsp;le&nbsp;<?php echo format_date($facture->date_paiement, "dd/MM/yyyy", "fr_FR"); ?>
                        <?php if($facture->reglement_paiement): ?>(<?php echo $facture->reglement_paiement ?>)<?php endif; ?>"><span class="glyphicon glyphicon-ok-sign"></span> Reçu
                    </a>
                <?php elseif(!$facture->isAvoir() && $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
                    <a class="btn btn-sm btn-default-step" href="<?php echo url_for("facturation_paiement", array("id" => $facture->_id)) ?>"><span class="glyphicon glyphicon-pencil"></span> Saisir</a>
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
                <?php if($facture->versement_comptable_paiement && !$facture->isAvoir()): ?><span style="cursor: help;" data-toggle="tooltip" title="Paiement versé en comptabilité" class="glyphicon glyphicon-check"></span><?php elseif(!$facture->isAvoir()): ?><span style="opacity: 0.4;" class="glyphicon glyphicon-unchecked text-muted"></span><?php else: ?><span style="opacity: 0.4;" class="text-muted"><span class="glyphicon glyphicon-ban-circle"></span></span><?php endif; ?>
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
                    <?php if(!$facture->isAvoir() && !$facture->isPayee() && !$facture->versement_comptable): ?>
                        <li><a onclick='return confirm("Étes vous sûr de vouloir regénérer la facture ?");' href="<?php echo url_for("facturation_regenerate", array("id" => $facture->_id)) ?>">Regénerer</a></li>
                    <?php else: ?>
                        <li class="disabled"><a href="">Regénerer</a></li>
                    <?php endif; ?>
                    <?php if(!$facture->isAvoir() && !$facture->versement_comptable_paiement): ?>
                    <li><a href="<?php echo url_for("facturation_paiement", array("id" => $facture->_id)) ?>">Saisir / modifier le paiement</a></li>
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
