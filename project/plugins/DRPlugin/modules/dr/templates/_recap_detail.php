<?php use_helper('Float') ?>

<h3>Détail par produit</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-4" style="border-top: hidden; border-left: hidden"></th>
            <th colspan="8" class="text-center">Lignes</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th class="text-center col-xs-5 clearfix">Produits</th>
            <?php $produits = $dr->getProduitsDetail(); ?>
            <?php foreach ($produits['lignes']->getRawValue() as $libelle): ?>
                <th class="text-center" style="cursor: help" title="<?= DouaneCsvFile::getCategories()[$libelle] ?>">L<?= $libelle ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($produits['produits']->getRawValue() as $hash => $produit): ?>
            <tr>
                <td><?= $produit['libelle'] ?></td>
                <?php foreach ($produit['lignes'] as $l => $p): ?>
                <td class="text-right" title="Ligne L<?= $l ?>">
                  <?= ($p['val'] === '—') ? '—' : round($p['val'], $p['decimals'] ?? 2) ?> <span class="text-muted"><?= $p['unit'] ?? '' ?></span>
                </td>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

