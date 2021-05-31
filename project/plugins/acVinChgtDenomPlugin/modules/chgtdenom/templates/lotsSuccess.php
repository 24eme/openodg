<?php use_helper('Float') ?>
<?php use_helper('Date') ?>
<?php use_helper('Lot') ?>

<div class="page-header">
    <h2>Changement de dénomination / Déclassement</h2>
    <p class="text-muted">Sélectionnez ci-dessous le lot que vous souhaitez changer</p>
    <?php if(!count($lots)): ?>
    <p>Aucun lot pour la campagne <?php echo isset($chgtDenom) ? $chgtDenom->campagne : $campagne ?></p>
    <?php else: ?>
      <table class="table table-condensed table-striped">
        <thead>
            <th class="col-sm-1">Date</th>
            <th class="col-sm-1">N° dossier</th>
            <th class="col-sm-1">N° lot</th>
            <th class="col-sm-1">Provenance</th>
            <th class="col-sm-1">Logement</th>
            <th class="col-sm-4">Produit (millésime, spécificité)</th>
            <th class="col-sm-1 text-center">Volume</th>
            <th class="col-sm-1 text-center">Etat</th>
            <th class="col-sm-1"></th>
        </thead>
        <tbody>
        <?php foreach($lots as $k => $lot): ?>
        <tr>
            <td class="text-center"><?php echo format_date($lot->date, 'dd/MM/yyyy'); ?></td>
            <td><?php echo $lot->numero_dossier; ?></td>
            <td><?php echo $lot->numero_archive; ?></td>
            <td><a href="<?php  echo url_for(strtolower(strtok($lot->id_document, '-')).'_visualisation', array('id' => $lot->id_document));  ?>"><?php echo $lot->type_document; ?></a></td>
            <td><?php echo $lot->numero_logement_operateur; ?></td>
            <td><?php echo showProduitCepagesLot($lot->getRawValue()) ?></td>
            <td class="text-right"><?php echo echoFloat($lot->volume); ?>&nbsp;<small class="text-muted">hl</small></td>
            <td class="text-muted text-center"><?php echo Lot::getLibelleStatut($lot->statut) ?></td>
            <td><a href="<?php echo url_for("chgtdenom_create_from_lot", array("sf_subject" => $etablissement, 'campagne' => $campagne, 'lot' => $lot->id_document.":".$lot->unique_id)) ?>" class="btn btn-sm btn-default">Modifier</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
  <?php endif; ?>
    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-5">
            <a tabindex="-1" href="<?php echo url_for('declaration_etablissement', $etablissement) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-remove"></span> Annuler</a>
        </div>
        <?php if ($etablissement->isNegociant()): ?>
        <div class="col-xs-5">
            <a tabindex="-1" href="<?php echo url_for('chgtdenom_ajout_lot',array('identifiant' => $etablissement->identifiant,'campagne' => $campagne)) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-plus"></span> Ajouter un lot</a>
        </div>
        <?php endif; ?>
    </div>
</div>
