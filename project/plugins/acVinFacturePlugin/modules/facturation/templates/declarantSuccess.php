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

<?php include_partial('global/flash'); ?>

<div class="page-header">
    <h2>Espace Facture</h2>
</div>

<?php if ($sf_user->isAdmin()): ?>
<div class="row row-margin">
    <?php if (isset($formSociete)): ?>
    <div class="col-xs-12">
          <?php include_partial('etablissement/formChoice', array('form' => $formSociete, 'action' => url_for('facturation'), 'noautofocus' => true)); ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="clearfix">
    <div class="pull-right" style="display: inline-block">
        <form method="GET" class="form-inline" style="margin-bottom: 0">
            Campagne :
            <select class="select2SubmitOnChange form-control" name="campagne">
                <option value="">Toutes</option>
                <?php foreach ($campagnes as $c): ?>
                <option <?php echo ($campagne == $c) ? "selected" : "" ?> value="<?php echo $c ?>">
                    <?php echo $c ?>
                </option>
                <?php endforeach ?>
            </select>
        </form>
    </div>
    <h3 style="display: inline-block">Liste des factures</h3>
</div>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-1">Date</th>
            <th class="col-xs-1">Numéro</th>
            <th class="col-xs-2">Type</th>
            <th class="col-xs-4 text-right">Montant TTC Facture</th>
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
            <td class="text-right"><?php echo Anonymization::hideIfNeeded(echoFloat($facture->total_ttc)); ?>&nbsp;€</td>
            <td class="text-right"><?php echo echoFloat($facture->getMontantPaiement()); ?>&nbsp;€</td>
            <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
            <td class="text-center dropdown">
              <button type="button" class="btn btn-default btn-default-step btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-cog"></span>&nbsp;<span class="caret"></span></button>
              <ul class="dropdown-menu dropdown-menu-right">
                  <li>
                  <?php if(!$facture->isAvoir() && !$facture->exist('avoir')): ?>
                    <li>
                      <a href="<?php echo url_for("facturation_avoir_defacturant", array("id" => $facture->_id)) ?>" onclick='return confirm("Étes vous sûr de vouloir créer un avoir ?");' >
                          <span class="glyphicon glyphicon-repeat"></span> Créér un avoir
                    </a>
                  </li>
                  <?php else: ?>
                    <li  class="disabled"><a href=""><span class="glyphicon glyphicon-repeat"></span> Créér un avoir</a></li>
                  <?php endif; ?>

                  <?php if(!$facture->isAvoir()): ?>
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
    <?php include_partial('facturation/generationForm', array('form' => $form, 'massive' => false)); ?>
<?php endif; ?>

<?php if(count($mouvements) && $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
  <h3>Mouvements en attente de facturation</h3>
  <table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-2">Document / Version</th>
            <th class="col-xs-1">Date</th>
            <th class="col-xs-5">Cotisation</th>
            <th class="col-xs-1">Quantite</th>
            <th class="col-xs-1">Prix unit.</th>
            <th class="col-xs-1">Tva</th>
            <th class="col-xs-1">Prix HT</th>
        </tr>
    </thead>
    <tbody>

  <?php foreach ($mouvements as $keyMvt => $mvt):
      $valueMvt = (isset($mvt->value))? $mvt->value : $mvt;
       ?>
    <tr>
        <td><a href="<?php echo url_for("declaration_doc", array("id" => $mvt->id))?>" ><?php echo $valueMvt->type;?><?php if($valueMvt->version): ?>&nbsp;<?php echo $valueMvt->version;?><?php endif; ?>&nbsp;<?php echo $valueMvt->campagne;?></a></td>
        <td><?php echo format_date($valueMvt->date, "dd/MM/yyyy", "fr_FR"); ?></td>
        <td><?php echo $valueMvt->type_libelle ?> <?php echo $valueMvt->detail_libelle ?></td>
        <td class="text-right"><?php echo echoFloat($valueMvt->quantite); ?>&nbsp;<small class="text-muted"><?php if(isset($valueMvt->unite)): ?><?php echo $valueMvt->unite ?><?php else: ?>&nbsp;&nbsp;<?php endif; ?></small></td>
        <td class="text-right"><?php echo echoFloat($valueMvt->taux); ?>&nbsp;€</td>
        <td class="text-right"><?php echo echoFloat($valueMvt->tva * 100, 0, 0); ?>&nbsp;%</td>
        <td class="text-right"><?php echo echoFloat($valueMvt->taux * $valueMvt->quantite); ?>&nbsp;€</td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
