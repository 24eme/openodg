<h3>Synthèse</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="text-center col-xs-8">Produit</th>
            <th class="text-center col-xs-2">Vol revendi.</th>
            <th class="text-center col-xs-2">Vol commerc.</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($syntheseLots as $produit => $millesimes): ?>
            <?php foreach ($millesimes as $millesime => $couleurs): ?>
                <?php foreach ($couleurs as $couleur => $volumes): ?>
                    <tr>
                        <td><?php echo implode(' ', [$produit, $couleur, $millesime]) ?></td>
                        <td class="text-right"><?php echo echoFloat(@$volumes["DRev"]) ?> <small class="text-muted">hl</small></td>
                        <td class="text-right"><?php echo echoFloat(@$volumes["Lot"]) ?> <small class="text-muted">hl</small></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($couleurs) > 1): ?>
                    <tr>
                        <td><strong>Total <?php echo implode(' ', [$produit, $millesime]) ?></strong></td>
                        <td class="text-right"><strong><?php $nb = 0; foreach($couleurs as $couleur): $nb += @$couleur['DRev']; endforeach; echo echoFloat($nb); ?> <small class="text-muted">hl</small></strong></td>
                        <td class="text-right"><strong><?php $nb = 0; foreach($couleurs as $couleur): $nb += @$couleur['Lot']; endforeach; echo echoFloat($nb); ?> <small class="text-muted">hl</small></strong></td>
                    </tr>
                <?php endif ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>
