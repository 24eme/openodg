<?php use_helper('Float') ?>
<?php use_helper('Date') ?>
<?php use_helper('Lot') ?>

<?php include_partial('pmcNc/breadcrumb', array('etablissement' => $etablissement )); ?>

<div class="page-header" style="margin-top: 20px;">
    <h2>Lots en non conformité</h2>
    <p class="text-muted">Sélectionnez ci-dessous le lot que vous souhaitez déclarer :</p>
    <?php if(!count($lots)): ?>
    <p>Aucun lot</p>
    <?php else: ?>
      <table class="table table-condensed table-striped">
        <thead>
            <th class="col-sm-1">Date</th>
            <th class="col-sm-1">N° dossier</th>
            <th class="col-sm-1">N° lot</th>
            <th class="col-sm-1">Provenance</th>
            <th class="col-sm-1">Logement</th>
            <th class="col-sm-3">Produit (millésime, spécificité)</th>
            <th class="col-sm-1 text-center">Volume</th>
            <th class="col-sm-2 text-center">Etat</th>
            <th class="col-sm-1"></th>
        </thead>
        <tbody>
        <?php foreach($lots as $k => $lot): ?>
        <tr>
            <td class="text-center"><?php echo format_date($lot->date, 'dd/MM/yyyy'); ?></td>
            <td><?php echo $lot->numero_dossier; ?></td>
            <td><a href="<?php echo url_for('degustation_lot_historique', array('identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id)); ?>"><?php echo $lot->numero_archive; ?></a></td>
            <td><?php echo str_replace('DRev:Changé', 'Changé', $lot->initial_type); ?></td>
            <td><?php echo $lot->numero_logement_operateur; ?></td>
            <td><?php echo showProduitCepagesLot($lot->getRawValue()) ?></td>
            <td class="text-right"><?php echo echoFloat($lot->volume); ?>&nbsp;<small class="text-muted">hl</small></td>
            <td class="text-muted text-center"><?php echo Lot::getLibelleStatut($lot->statut) ?></td>
            <td><a href="<?php echo url_for('pmcnc_create', array('sf_subject' => $etablissement, 'unique_id' => $lot->unique_id)) ?>" class="btn btn-sm btn-default">Démarrer la télédéclaration</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
  <?php endif; ?>
    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-5">
            <a tabindex="-1" href="<?php echo url_for('declaration_etablissement', $etablissement) ?>" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-remove"></span> Annuler</a>
        </div>
        <?php if ($etablissement->isNegociant() || ($etablissement->isNegociantVinificateur() && $sf_user->isAdmin())): ?>
        <div class="col-xs-5">
            <a tabindex="-1" href="<?php echo url_for('chgtdenom_ajout_lot',array('identifiant' => $etablissement->identifiant,'campagne' => $campagne)) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-plus"></span> Ajouter un lot</a>
        </div>
        <?php endif; ?>
    </div>
</div>
