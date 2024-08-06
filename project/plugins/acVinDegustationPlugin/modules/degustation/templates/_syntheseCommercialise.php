<?php use_helper('Float') ?>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
<?php if (class_exists(PMC::class) && in_array('PMC', sfConfig::get('sf_enabled_modules'))): ?>
            <th class="text-center col-xs-6">Produit</th>
            <th class="text-center col-xs-2">DRev</th>
            <th class="text-center col-xs-2">PMC</th>
    <?php if (isset($restant) && $restant): ?>
            <th class="text-center col-xs-2">Volume restant à comm.</th>
    <?php else: ?>
            <th class="text-center col-xs-2">Vol. agréés</th>
    <?php endif; ?>
<?php else: ?>
    <th class="text-center col-xs-10">Produit</th>
    <th class="text-center col-xs-2">Vol. agréés</th>
<?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($syntheseLots as $produit => $millesimes): ?>
            <?php foreach ($millesimes as $millesime => $couleurs): ?>
                <?php foreach ($couleurs as $couleur => $volumes): ?>
                    <tr>
                        <td><?php echo implode(' ', [$produit, $couleur, $millesime]) ?></td>
<?php if (class_exists(PMC::class) && in_array('PMC', sfConfig::get('sf_enabled_modules'))): ?>
                        <td class="text-right"><?php echo echoFloat(@$volumes["DRev"]) ?> <small class="text-muted">hl</small><?php if(isset($volumes["DRevVCI"]) && $volumes["DRevVCI"] > 0): ?><small class="text-muted"><br />dont <?php echo echoFloat(@$volumes["DRevVCI"]) ?> hl</small><?php endif; ?></td>
                        <td class="text-right"><?php echo echoFloat(@$volumes["PMC"]) ?> <small class="text-muted">hl</small></td>
                        <?php if (isset($restant) && $restant): ?>
                        <td class="text-right"><?php echo echoFloat(@$volumes["DRev"] - @$volumes["PMC"]) ?> <small class="text-muted">hl</small></td>
                        <?php else: ?>
                        <td class="text-right"><?php echo echoFloat(@$volumes["Lot"]) ?> <small class="text-muted">hl</small></td>
                        <?php endif; ?>
<?php else: ?>
                        <td class="text-right"><?php echo echoFloat(@$volumes["Lot"]) ?> <small class="text-muted">hl</small></td>
<?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($couleurs) > 1): ?>
                    <tr>
                        <td><strong>Total <?php echo implode(' ', [$produit, $millesime]) ?></strong></td>
<?php if (class_exists(PMC::class) && in_array('PMC', sfConfig::get('sf_enabled_modules'))): ?>
                        <td class="text-right"><strong><?php $nb = 0; foreach($couleurs as $couleur): $nb += @$couleur['DRev']; endforeach; echo echoFloat($nb); ?> <small class="text-muted">hl</small></strong></td>
                        <td class="text-right"><strong><?php $nb = 0; foreach($couleurs as $couleur): $nb += @$couleur['PMC']; endforeach; echo echoFloat($nb); ?> <small class="text-muted">hl</small></strong></td>
                        <?php if (isset($restant) && $restant): ?>
                        <td class="text-right"><strong><?php $nb = 0; foreach($couleurs as $couleur): $nb += @$couleur['DRev'] - @$couleur['PMC']; endforeach; echo echoFloat($nb); ?> <small class="text-muted">hl</small></strong></td>
                        <?php else: ?>
                            <td class="text-right"><strong><?php $nb = 0; foreach($couleurs as $couleur): $nb += @$couleur['Lot']; endforeach; echo echoFloat($nb); ?> <small class="text-muted">hl</small></strong></td>
                        <?php endif; ?>
<?php else: ?>
                        <td class="text-right"><strong><?php $nb = 0; foreach($couleurs as $couleur): $nb += @$couleur['Lot']; endforeach; echo echoFloat($nb); ?> <small class="text-muted">hl</small></strong></td>
<?php endif; ?>
                    </tr>
                <?php endif ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>
