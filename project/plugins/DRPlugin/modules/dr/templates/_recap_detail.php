<?php use_helper('Float') ?>

<h3>Détail par produit</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-5" style="border-top: hidden; border-left: hidden"></th>
            <th colspan="7" class="text-center">Lignes</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th class="text-center col-xs-5 clearfix">Produits <small class="pull-right text-muted">Rdmt L5|L15</small></th>
            <?php $produits = $dr->getProduitsDetail(); ?>
            <?php foreach ($produits['lignes']->getRawValue() as $libelle): ?>
                <th class="text-center" style="cursor: help" title="<?= DouaneCsvFile::getCategorieLibelle($dr->type, $libelle) ?>">L<?= $libelle ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($produits['produits']->getRawValue() as $hash => $produit): ?>
            <tr>
                <td>
                    <?= $produit['libelle'] ?>
                    <small class="pull-right text-muted">
                        <span title="Rendement L5" style="cursor: help">
                            <?php if ($produit['lignes']['05']['val'] > 0 && $produit['lignes']['04']['val'] > 0): ?>
                                <?= round($produit['lignes']['05']['val'] / $produit['lignes']['04']['val'], 2) ?>
                            <?php else: echo 0 ?>
                            <?php endif ?>
                        </span> hl/ha
                        |
                        <span title="Rendement L15" style="cursor: help">
                            <?php if ($produit['lignes']['15']['val'] > 0 && $produit['lignes']['04']['val'] > 0): ?>
                                <?= round($produit['lignes']['15']['val'] / $produit['lignes']['04']['val'], 2) ?>
                            <?php else: echo 0 ?>
                            <?php endif ?>
                        </span> hl/ha
                    </small>
                </td>
                <?php foreach ($produit['lignes'] as $l => $p): ?>
                <td class="text-right" title="Ligne L<?= $l ?>">
                  <?= ($p['val'] === '—') ? '—' : round($p['val'], $p['decimals'] ?? 2) ?> <span class="text-muted"><?= $p['unit'] ?? '' ?></span>
                </td>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
        <tr>
            <th class="text-right"><strong>Total</strong></th>
            <?php foreach ($produit['lignes'] as $l => $p): ?>
                <th class="text-right"><strong><?= echoFloat($dr->getTotalValeur($l)) ?></strong> <span class='text-muted'><?= $p['unit'] ?></span></th>
            <?php endforeach ?>
        </tr>
    </tbody>
</table>

