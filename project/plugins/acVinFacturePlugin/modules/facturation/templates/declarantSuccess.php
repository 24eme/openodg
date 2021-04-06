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


<div class="page-header">
    <h2>Espace Facture</h2>
</div>

<h3>Liste des factures</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-1">Date</th>
            <th class="col-xs-1">Numéro</th>
            <th class="col-xs-2">Type</th>
            <th class="col-xs-4">Libellé</th>
            <th class="col-xs-2 text-right">Montant TTC Facture</th>
            <th class="col-xs-2 text-right">Montant payé</th>
            <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
            <th style="witdth: 0;"></th>
            <?php endif; ?>
            <th style="witdth: 0;"></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($factures as $facture) : ?>
        <tr>
            <td><?php echo format_date($facture->date_facturation, "dd/MM/yyyy", "fr_FR"); ?></td>
            <td>N°&nbsp;<?php echo $facture->numero_archive ?></td>
            <td><?php if($facture->isAvoir()): ?>AVOIR<?php else: ?>FACTURE<?php endif; ?></td>
            <td><?php if(!$facture->isAvoir()): ?><?php echo ($facture->getTemplate())? $facture->getTemplate()->libelle : $facture->getPieces()[0]->libelle ?><?php endif; ?></td>
            <td class="text-right"><?php echo Anonymization::hideIfNeeded(echoFloat($facture->total_ttc)); ?>&nbsp;€</td>
            <td class="text-right"><?php echo echoFloat($facture->getMontantPaiement()); ?>&nbsp;€</td>
            <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
            <td class="text-center dropdown">
              <button type="button" class="btn btn-default btn-default-step btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-cog"></span>&nbsp;<span class="caret"></span></button>
              <ul class="dropdown-menu dropdown-menu-right">
                  <li>
                  <?php if(!$facture->isAvoir() && !$facture->versement_comptable_paiement && !$facture->exist('avoir')): ?>
                    <li>
                      <a href="<?php echo url_for("facturation_avoir_defacturant", array("id" => $facture->_id)) ?>" onclick='return confirm("Étes vous sûr de vouloir créer un avoir ?");' >
                          <span class="glyphicon glyphicon-repeat"></span> Créér un avoir
                    </a>
                  </li>
                  <?php else: ?>
                    <li  class="disabled"><a href=""><span class="glyphicon glyphicon-repeat"></span> Créér un avoir</a></li>
                  <?php endif; ?>

                  <?php if(!$facture->isAvoir() && !$facture->versement_comptable_paiement): ?>
                    <li><a href="<?php echo url_for("facturation_paiements", array("id" => $facture->_id)) ?>">Saisir / modifier les paiements</a></li>
                  <?php else: ?>
                    <li class="disabled"><a href="">Saisir / modifier les paiements</a></li>
                  <?php endif; ?>

              </ul>
            </td>
           <?php endif; ?>
            <td class="text-right">
                <a href="<?php echo url_for("facturation_pdf", array("id" => $facture->_id)) ?>" class="btn btn-sm btn-default-step"><span class="glyphicon glyphicon-file"></span>&nbsp;Visualiser</a>
            </td>
        </tr>
        <?php endforeach;
          if(!count($factures)):
        ?>
        <tr>
            <td colspan="<?php echo intval($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))+6 ?>">Aucune facture éditée</td>
        </tr>
      <?php endif; ?>
    </tbody>
</table>

<hr />

<?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
<h3>Génération de facture</h3>
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
            <?php if(isset($form["date_facturation"])): ?>
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
            <?php endif; ?>

            <?php if(isset($form["date_mouvement"])): ?>
            <div class="form-group <?php if($form["date_mouvement"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["date_mouvement"]->renderError(); ?>
                <?php echo $form["date_mouvement"]->renderLabel("Date de mouvements", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <div class="input-group date-picker-week">
                        <?php echo $form["date_mouvement"]->render(array("class" => "form-control", "placeholder" => "Date de prise en compte des mouvements")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(isset($form["message_communication"])): ?>
            <div class="form-group <?php if($form["message_communication"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["message_communication"]->renderError(); ?>
                <?php echo $form["message_communication"]->renderLabel("Message de communication", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <div class="">
                        <?php echo $form["message_communication"]->render(array("class" => "form-control", "placeholder" => "Message")); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(isset($form["type_document"])): ?>
            <div class="form-group <?php if($form["type_document"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["type_document"]->renderError(); ?>
                <?php echo $form["type_document"]->renderLabel("Type de document", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <div class="">
                        <?php echo $form["type_document"]->render(array("class" => "form-control", "placeholder" => "Message")); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group text-right">
                <div class="col-xs-6 col-xs-offset-6">
                    <button class="btn btn-default btn-block btn-upper" type="submit">Générer la facture</button>
                </div>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>

<?php if(count($mouvements) && $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
  <h3>Mouvements en attente de facturation</h3>
  <table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-2">Document / Version</th>
            <th class="col-xs-1">Campagne</th>
            <th class="col-xs-4">Cotisation</th>
            <th class="col-xs-1">Quantite</th>
            <th class="col-xs-1">Prix unit.</th>
            <th class="col-xs-1">Tva</th>
            <th class="col-xs-2">Montant HT</th>
        </tr>
    </thead>
    <tbody>

  <?php foreach ($mouvements as $keyMvt => $mvt):
      $valueMvt = (isset($mvt->value))? $mvt->value : $mvt;
       ?>
    <tr>
        <td><a href="<?php echo url_for("declaration_doc", array("id" => $mvt->id))?>" ><?php echo $valueMvt->type;?><?php echo "&nbsp;".$valueMvt->version;?> <?php echo "&nbsp;".$valueMvt->campagne;?></a></td>
        <td><?php echo format_date($valueMvt->date, "dd/MM/yyyy", "fr_FR"); ?></td>
        <td><?php echo ucfirst($valueMvt->categorie); ?> <?php echo $valueMvt->type_libelle; ?></td>
        <td class="text-right"><?php echo echoFloat($valueMvt->quantite); ?></td>
        <td class="text-right"><?php echo echoFloat($valueMvt->taux); ?></td>
        <td class="text-right"><?php echo echoFloat($valueMvt->tva); ?>&nbsp;%</td>
        <td class="text-right"><?php echo echoFloat($valueMvt->taux * $valueMvt->quantite); ?>&nbsp;€</td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
