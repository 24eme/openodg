<h3>Synthèse Lots</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="text-center col-xs-8">Produit</th>
            <th class="text-center col-xs-2">Volume</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($syntheseLots as $produit => $millesimes): ?>
            <?php foreach ($millesimes as $millesime => $couleurs): ?>
                <?php foreach ($couleurs as $couleur => $volume): ?>
                    <tr>
                        <td><?php echo implode(' ', [$produit, $couleur, $millesime]) ?></td>
                        <td class="text-right"><?php echo echoFloat($volume) ?> <small class="text-muted">hl</small></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($couleurs) > 1): ?>
                    <tr>
                        <td><strong>Total <?php echo implode(' ', [$produit, $millesime]) ?></strong></td>
                        <td class="text-right"><strong><?php echo echoFloat(array_sum($syntheseLots->getRawValue()[$produit][$millesime])) ?> <small class="text-muted">hl</small></strong></td>
                    </tr>
                <?php endif ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>
