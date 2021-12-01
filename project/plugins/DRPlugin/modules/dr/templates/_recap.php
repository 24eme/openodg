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
                <td class="text-right"><?= $produit['lignes']['04'] ?? 0 ?></td>
                <td class="text-right"><?= $produit['lignes']['05'] ?? 0 ?></td>
                <td class="text-right"><?= $produit['lignes']['14'] ?? 0 ?></td>
                <td class="text-right"><?= $produit['lignes']['15'] ?? 0 ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

