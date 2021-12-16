<?php use_helper('Float') ?>

<h3>Détail</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-4" style="border-top: hidden; border-left: hidden"></th>
            <th colspan="8" class="text-center">Produits</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th class="text-center col-xs-5 clearfix">Lignes</th>
            <?php $produits = $dr->getProduitsDetail(); ?>
            <?php foreach ($produits['produits'] as $libelle): ?>
                <th class="text-center"><?= $libelle ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($produits['lignes'] as $ligne => $produit): ?>
            <tr>
                <td><?= $ligne ?></td>
                <?php foreach ($produit as $k => $p): ?>
                <td class="text-right">
                    <?= round($p['val'], 2) ?>
                </td>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
        <tr>
            <th class="text-right"><strong>Total</strong></th>
            <?php /*foreach ($lignesAAfficher as $l): ?>
                <th class="text-right"><strong><?= echoFloat($dr->getTotalValeur($l)) ?></strong> <span class='text-muted'><?= current($dr->getProduits()->getRawValue())['lignes'][$l]['unit'] ?></span></th>
            <?php endforeach */?>
        </tr>
    </tbody>
</table>

