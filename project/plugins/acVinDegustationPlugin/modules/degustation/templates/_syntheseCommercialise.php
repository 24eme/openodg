<h3>Synthèse Lots</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="text-center col-xs-8">Produit</th>
            <th class="text-center col-xs-2">Volume revendiqué</th>
            <th class="text-center col-xs-2">Volume commercialisé</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($syntheseLots as $libelle => $produit): ?>
            <tr>
                <td><strong><?php echo $libelle ?></strong></td>
                <td class="text-right"><?php echo echoFloat($produit['volume_revendique']) ?> <small class="text-muted">hl</small></td>
                <td class="text-right"><?php echo echoFloat($produit['volume_commercialise']) ?> <small class="text-muted">hl</small></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
