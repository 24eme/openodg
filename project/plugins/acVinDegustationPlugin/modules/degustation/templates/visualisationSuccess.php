<?php use_helper('Lot'); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>

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
            <th>Opérateur</th>
            <th>Provenance</th>
            <th>Produit</th>
            <th>Volume</th>
            <th>Date de prélèvement</th>
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
            <td><?php echo $lot->getTypeProvenance() ?></td>
            <td><?php echo showOnlyProduit($lot); ?></td>
            <td class="text-right"><?php echo $lot->volume ?> <small class="text-muted">hl</small></td>
            <td class='text-center'>
                <?php echo DateTimeImmutable::createFromFormat('Y-m-d', $lot->getPreleve())->format('d/m/Y'); ?>
            </td>
            <td>Voir <a href="<?php echo url_for('degustation_lot_historique', ['identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id]) ?>">l'historique du lot</a></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

