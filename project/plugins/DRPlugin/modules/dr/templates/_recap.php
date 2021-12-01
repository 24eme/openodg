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
                    <td class="text-right"><?= $produit['lignes'][$l] ?? 0 ?></td>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
        <tr>
            <th class="text-right"><strong>Total</strong></th>
            <?php foreach ($lignesAAfficher as $l): ?>
                <th class="text-right"><strong><?= $dr->getTotalValeur($l) ?></strong></th>
            <?php endforeach ?>
        </tr>
    </tbody>
</table>

