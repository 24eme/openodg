<ol class="breadcrumb">
    <?php if(!$sf_user->hasFactureAdmin()): ?>
        <li><a href="<?php echo url_for('accueil'); ?>">Accueil</a></li>
    <?php endif; ?>
  <li><a href="<?php if($sf_user->hasFactureAdmin()): ?><?php echo url_for('facturation'); ?><?php else: ?><?php echo url_for('facturation_declarant', ['identifiant' => $compte->identifiant]); ?><?php endif; ?>">Facturation</a></li>
  <li class="active"><a href=""><?php echo $compte->getNomAAfficher() ?> (<?php echo $compte->getIdentifiantAAfficher() ?>)</a></li>
</ol>

<?php use_helper('Date'); ?>
<?php use_helper('Float'); ?>
<?php use_helper('Generation'); ?>

<?php include_partial('global/flash'); ?>

<div class="page-header">
    <h2>Espace Facture</h2>
</div>

<?php if (($sf_user->hasFactureAdmin()) && isset($formSociete)): ?>
    <?php include_partial('etablissement/formChoice', array('form' => $formSociete, 'action' => url_for('facturation'), 'noautofocus' => true)); ?>
</div>
<?php endif; ?>

<h3>Liste des Factures <?php echo $campagne ?></h3>
<ul class="nav nav-tabs">
  <li class="active"><a href="#factures" aria-controls="factures" role="tab" data-toggle="tab">Factures</a></li>
  <li><a href="#lignes" aria-controls="lignes" role="tab" data-toggle="tab">Lignes</a></li>
  <form method="GET" class="form-inline pull-right" style="display: inline-block;">
      Exercice comptable :
      <select class="select2SubmitOnChange form-control input-sm" name="campagne">
          <option value="tous">Tous</option>
          <?php foreach ($campagnes as $c): ?>
          <option <?php echo ($campagne == $c) ? "selected" : "" ?> value="<?php echo $c ?>">
              <?php echo $c ?>
          </option>
          <?php endforeach ?>
      </select>
      <button type="submit" class="btn btn-sm btn-default">Changer</button>
  </form>
</ul>
<div class="tab-content">
    <div id="factures" class="tab-pane active">
    <table class="table table-bordered table-striped" style="border-width: 0;">
        <thead>
            <tr>
                <th class="col-xs-1">Date</th>
                <th class="col-xs-1">Numéro</th>
                <th class="col-xs-2">Type</th>
                <th class="col-xs-4 text-right">Montant TTC Facture</th>
                <th class="col-xs-2 text-right">Montant payé</th>
                <?php if($sf_user->hasFactureAdmin()): ?>
                <th style="width: 0;"></th>
                <?php endif; ?>
                <th style="width: 0;"></th>
                <?php if($sf_user->hasFactureAdmin()): ?>
                <th style="width: 0;"><span title="Téléchargé par l'opérateur" class="glyphicon glyphicon-eye-open"></span></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($factures as $facture) : ?>
            <tr>
                <td><?php echo format_date($facture->date_facturation, "dd/MM/yyyy", "fr_FR"); ?></td>
                <td>N°&nbsp;<?php echo $facture->numero_odg ?></td>
                <td><?php if($facture->isAvoir()): ?>AVOIR<?php else: ?>FACTURE<?php endif; ?></td>
                <td class="text-right"><?php echo Anonymization::hideIfNeeded(echoFloat($facture->total_ttc)); ?>&nbsp;€</td>
                <td class="text-right">
                    <?php if($facture->getMontantPaiement() != 0 || (!$facture->isAvoir() && !$facture->isRedressee())): ?>
                        <a class="<?php if(!$facture->getMontantPaiement()): ?>transparence-xs<?php endif ?>" title="<?php if(!$facture->getMontantPaiement()): ?>Saisir un paiement<?php else: ?>Voir ou modifier le(s) paiements<?php endif ?>" href="<?php echo url_for("facturation_paiements", array("id" => $facture->_id)) ?>"><?php echo echoFloat($facture->getMontantPaiement()*1); ?>&nbsp;€</a>
                    <?php endif; ?>
                </td>
                <?php if($sf_user->hasFactureAdmin()): ?>
                <td class="text-center">
                  <span class="dropdown">
                  <button type="button" class="btn btn-default btn-default-step btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-cog"></span>&nbsp;<span class="caret"></span></button>
                  <ul class="dropdown-menu dropdown-menu-right">
                      <?php if(!$facture->isAvoir()): ?>
                        <li><a href="<?php echo url_for("facturation_paiements", array("id" => $facture->_id)) ?>">Saisir / modifier les paiements</a></li>
                      <?php else: ?>
                          <li class="disabled"><a href="">Saisir / modifier les paiements</a></li>
                      <?php endif; ?>
                      <?php if(!$facture->isAvoir() && !$facture->exist('avoir')): ?>
                        <li>
                          <a href="<?php echo url_for("facturation_avoir_defacturant", array("id" => $facture->_id)) ?>" onclick='return confirm("Étes vous sûr de vouloir créer un avoir ?");' >
                              <span class="glyphicon glyphicon-repeat"></span> Créér un avoir
                        </a>
                      </li>
                      <?php else: ?>
                        <li class="disabled"><a href=""><span class="glyphicon glyphicon-repeat"></span> Créér un avoir</a></li>
                      <?php endif; ?>
                      <li><a href="<?php echo url_for('facturation_email', array("id" => $facture->_id)) ?>" onclick="return confirm('confirmez-vous l\'envoi de la facture par e-mail ?')"><span class="glyphicon glyphicon-envelope"></span> Envoyer la facture par e-mail</a></li>
                  </ul>
                  </span>
                </td>
               <?php endif; ?>
                <td class="text-right">
                    <a href="<?php echo url_for("facturation_pdf", array("id" => $facture->_id)) ?>" class="" style="white-space: nowrap;"><span class="glyphicon glyphicon-file"></span>&nbsp;Visualiser</a>
                </td>
                <?php if($sf_user->hasFactureAdmin()): ?>
                    <td><?php if($facture->isTelechargee()): ?><span style="opacity: 0.8;" data-toggle="tooltip" title="La facture a été téléchargée par l'opérateur" class="glyphicon glyphicon-eye-open text-primary"></span><?php else: ?><span style="opacity: 0.2;" data-toggle="tooltip" title="La facture n'a pas encore été téléchargée par l'opérateur" class="glyphicon glyphicon-eye-close text-primary"></span><?php endif; ?></td>
                <?php endif; ?>
            </tr>
            <?php endforeach;
              if(!count($factures)):
            ?>
            <tr>
                <td colspan="<?php echo intval($sf_user->hasFactureAdmin())*2+6 ?>">Aucune facture éditée</td>
            </tr>
          <?php endif; ?>
        </tbody>
    </table>
    </div>
    <div id="lignes" class="tab-pane">
    <table class="table table-bordered table-striped"  style="border-width: 0;">
        <thead>
            <tr>
                <th class="col-xs-1">Date</th>
                <th class="col-xs-1">Numéro</th>
                <th class="col-xs-3">Désignation</th>
                <th class="col-xs-1">Code comptable</th>
                <th class="col-xs-1 text-right">Quantité</th>
                <th class="col-xs-1 text-right">Prix unitaire</th>
                <th class="col-xs-1 text-right">TVA</th>
                <th class="col-xs-1 text-right">Total HT</th>
                <th class="col-xs-1 text-right"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($factures as $facture) : ?>
                <?php $first = true; ?>
                <?php foreach ($facture->lignes as $ligne): ?>
                    <?php foreach ($ligne->details as $detail): ?>
                        <tr>
                            <td class="<?php if(!$first): ?>transparence-lg<?php endif; ?>"><?php echo format_date($facture->date_facturation, "dd/MM/yyyy", "fr_FR"); ?></td>
                            <td class="<?php if(!$first): ?>transparence-lg<?php endif; ?>">N°&nbsp;<?php echo $facture->numero_archive ?></td>
                            <td><?php echo $ligne->libelle; ?> <?php echo $detail->libelle; ?></td>
                            <td><span class="text-muted"><?php echo $ligne->produit_identifiant_analytique; ?></span></td>
                            <td class="text-right"><?php echoFloat($detail->quantite); ?> <?php if($detail->exist('unite')): ?><small class="text-muted"><?php echo $detail->unite; ?></small><?php endif; ?></td>
                            <td class="text-right"><?php echoFloat($detail->prix_unitaire); ?> €</td>
                            <td class="text-right"><?php echo ($detail->taux_tva) ? echoFloat($detail->montant_tva)." €" : null; ?></td>
                            <td class="text-right"><?php echo echoFloat($detail->montant_ht); ?> €</td>
                            <td class="text-right">
                                <?php if($first): ?>
                                <a href="<?php echo url_for("facturation_pdf", array("id" => $facture->_id)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Visualiser</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php $first = false; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php if($sf_user->hasFactureAdmin()): ?>
  <?php include_partial('facturation/mouvements', array('mouvements' => $mouvements)); ?>
<?php endif; ?>

<?php if($sf_user->hasFactureAdmin()): ?>
    <div style="margin-top: 30px;">
    <?php include_partial('facturation/generationForm', array('form' => $form, 'massive' => false)); ?>
    </div>
<?php endif; ?>
