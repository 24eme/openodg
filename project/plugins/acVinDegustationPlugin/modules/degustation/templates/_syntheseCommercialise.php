<h3>Synthèse Lots</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="text-center col-xs-6">Produit</th>
            <th class="text-center col-xs-2">Volume revendiqué</th>
            <th class="text-center col-xs-2">Volume commercialisé</th>
            <th class="text-center col-xs-2">VIP2C</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($syntheseLots as $libelle => $produit): ?>
            <tr<?php echo ($produit['vip2c'] && $produit['volume_commercialise'] > $produit['vip2c']) ? " class='danger'" : '' ?>>
                <td><?php echo $libelle ?></td>
                <td><?php echo $produit['volume_revendique'] ?></td>
                <td><?php echo $produit['volume_commercialise'] ?></td>
                <td><?php echo $produit['vip2c'] ?? '-' ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
