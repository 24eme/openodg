<?php use_helper('Float') ?>

<h3>Synthèse</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="text-center col-xs-5">Produit (millesime)</th>
            <th class="text-center">Superficie (L4) <span class="text-muted">(ha)</span></th>
            <th class="text-center">Récolte L5 <span class="text-muted">(hl)</span></th>
            <th class="text-center">Volume revendiqué L14 <span class="text-muted">(hl)</span></th>
            <th class="text-center">Volume revendiqué L15 <span class="text-muted">(hl)</span></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($dr->getProduits() as $produit): ?>
            <tr>
                <td><?= $produit['libelle'] ?></td>
                <?php foreach ($lignesAAfficher as $l): ?>
                    <td class="text-right">
                        <?php if (array_key_exists($l, $produit['lignes']->getRawValue())): ?>
                            <?= sprintf('%.0'.$produit['lignes'][$l]['decimals'].'f', $produit['lignes'][$l]['val']) ?>
                            <span class="text-muted"><?= $produit['lignes'][$l]['unit'] ?></span>
                        <?php else: ?>
                            <?= echoFloat(0); ?>
                        <?php endif ?>
                    </td>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
        <tr>
            <th class="text-right"><strong>Total</strong></th>
            <?php foreach ($lignesAAfficher as $l): ?>
                <th class="text-right"><strong><?= echoFloat($dr->getTotalValeur($l)) ?></strong></th>
            <?php endforeach ?>
        </tr>
    </tbody>
</table>

