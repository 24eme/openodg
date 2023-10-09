<?php use_helper('Lot'); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_VISUALISATION)); ?>

<div class="page-header no-border">
  <h2>
    <?php if ($degustation->getType() === DegustationClient::TYPE_MODEL): ?>
        Dégustation
    <?php else: ?>
        Tournée
    <?php endif; ?>
    du
    <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?>
    <small><?php echo $degustation->getLieuNom(); ?></small>
  </h2>
</div>

<h4>Lots prélevés (<?php echo count($lots->getRawValue()) ?>)</h4>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-3">Opérateur</th>
            <th>Prov.</th>
            <th>Nº Lot</th>
            <th>Nº logement</th>
            <th class="col-xs-3">Produit</th>
            <th>Volume</th>
            <th>Date de prélèvement</th>
            <?php if ($degustation->getType() === DegustationClient::TYPE_MODEL): ?>
                <th>Conformité</th>
            <?php endif ?>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($lots) < 1): ?>
        <tr><td colspan=4 class='text-center'>Aucun lot prélevé</td></tr>
    <?php endif ?>
    <?php foreach ($lots as $lot): ?>
        <tr>
            <td><?php echo $lot->declarant_nom ?></td>
            <td class="text-center"><?php echo $lot->getTypeProvenance() ?></td>
            <td class="text-center"><a href="<?php echo url_for('degustation_lot_historique', ['identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id]) ?>"><?php echo $lot->numero_archive; ?></a></td>
            <td class="text-center"><?php echo $lot->numero_logement_operateur; ?></td>
            <td><?php echo showOnlyProduit($lot); ?></td>
            <td class="text-right"><?php echo $lot->volume ?> <small class="text-muted">hl</small></td>
            <td class='text-center'>
                <?php echo DateTimeImmutable::createFromFormat('Y-m-d', $lot->getPreleve())->format('d/m/Y'); ?>
            </td>
            <?php if ($degustation->getType() === DegustationClient::TYPE_MODEL): ?>
                <td class='text-center'>
                <?php if($lot->hasSpecificitePassage()): ?>
                    <span class="label label-danger" style="margin-right: -14px;">&nbsp;</span>
                <?php endif; ?>
                    <span class="label label-<?php if($lot->isManquement())  { echo 'danger'; }
                                                    elseif ($lot->isConformeObs()) { echo 'warning'; }
                                                    else { echo 'success'; } ?>"
                          style="<?php if($lot->hasSpecificitePassage()): ?>border-radius: 0 0.25em 0.25em 0; border-left: 1px solid #fff;<?php endif; ?>">
                        <span class="glyphicon glyphicon-<?= ($lot->isManquement()) ? 'remove' : 'ok' ?>"></span>
                    </span>
                </td>
            <?php endif ?>
            <td class="text-right hidden-print"><a class="btn btn-xs btn-default btn-historique" href="<?php echo url_for('degustation_lot_historique', ['identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id]) ?>">Historique&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

