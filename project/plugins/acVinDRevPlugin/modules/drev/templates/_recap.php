<?php use_helper('Float') ?>
<?php use_helper('Version') ?>

<h3>Revendication</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-5">Appellation revendiquée</th>
            <th class="col-xs-2 text-center">Superficie revendiquée<br /><small class="text-muted">(ha)</small></th>
            <th class="col-xs-2 text-center">Volume revendiqué<br />net total <small class="text-muted">(hl)</small></th>
            <th class="col-xs-3 text-center">Dont VCI<br /><small class="text-muted">(hl)</small></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($drev->declaration->getProduitsWithoutLots() as $produit) : ?>
            <tr>
                <td><?php echo $produit->getLibelleComplet() ?><small class="pull-right">&nbsp;(<?php echoFloat(round($produit->getRendementEffectif(), 2)); ?> hl/ha)</small></td>
                <td class="text-right <?php echo isVersionnerCssClass($produit, 'superficie_revendique') ?>"><?php if($produit->superficie_revendique): ?><?php echoFloat($produit->superficie_revendique) ?> <small class="text-muted">ha</small><?php endif; ?></td>
                <td class="text-right <?php echo isVersionnerCssClass($produit, 'volume_revendique_toral') ?>"><?php if($produit->volume_revendique_total !== null): ?><?php echoFloat($produit->volume_revendique_total) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                <td class="text-right <?php echo isVersionnerCssClass($produit, 'volume_revendique_issu_vci') ?>"><?php if($produit->volume_revendique_issu_vci): ?><?php echoFloat($produit->volume_revendique_issu_vci) ?> <small class="text-muted">hl</small><?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if($drev->exist('lots') && count($drev->lots)): ?>
    <h3>Déclaration des lots</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th class="col-xs-1">Lot</th>
                <th class="text-center col-xs-4">Produit (millesime)</th>
                <th class="text-center col-xs-2">Superficie</th>
                <th class="text-center col-xs-2">Volume</th>
                <th class="text-center col-xs-3">Destination (date)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($drev->getLotsByCouleur() as $couleur => $lots) :
                $volume = 0;
                $synthese_revendication = $drev->summerizeProduitsByCouleur();
                foreach ($lots as  $lot) : ?>
                <tr>
                    <td><?php echo $lot->numero; ?></td>
                    <td><?php echo $lot->produit_libelle." (".$lot->millesime.")"; ?></td>
                    <td>&nbsp;</td>
                    <td class="text-right"><?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small></td>
                    <td class="text-center"><?php echo $lot->destination_type; echo ($lot->destination_date) ? " (".$lot->getDestinationDateFr().")" : ''; ?></td>
                </tr>
                <?php $volume += $lot->volume ; endforeach; ?>
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong><?php echo $lot->getConfigProduit()->getCouleur()->getLibelleComplet(); ?></strong><small class="pull-right">&nbsp;(<?php echoFloat(round($volume / $synthese_revendication[$couleur]['superficie_totale'], 2)); ?>&nbsp;hl/ha)</small></td>
                    <td class="text-right"><strong><?php echoFloat($synthese_revendication[$couleur]['superficie_totale']); ?><small class="text-muted">&nbsp;ha</small></strong></td>
                    <td class="text-right"><strong><?php echoFloat($volume); ?><small class="text-muted">&nbsp;hl</small></strong> / <?php echoFloat($synthese_revendication[$couleur]['volume_total']); ?><small class="text-muted">&nbsp;hl</small></td>
                    <td class="text-center"><span class="text-muted"><small>il reste donc <?php echoFloat($synthese_revendication[$couleur]['volume_total'] - $volume); ?>&nbsp;hl max à revendiquer</span></small></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php if(count($drev->declaration->getProduitsVci())): ?>
    <h3>Gestion du VCI</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th class="col-xs-3">Appellation revendiquée</th>
                <th class="text-center col-xs-2">Stock <?php echo $drev->campagne - 1 ?><br /><small class="text-muted">(hl)</small></th>
                <th class="text-center col-xs-1">A détruire<br /><small class="text-muted">(hl)</small></th>
                <th class="text-center col-xs-1">Complémt<br /><small class="text-muted">(hl)</small></th>
                <th class="text-center col-xs-1">Substitué<br /><small class="text-muted">(hl)</small></th>
                <th class="text-center col-xs-1">Rafraichi<br /><small class="text-muted">(hl)</small></th>
                <th class="text-center col-xs-1">Constitué<br /><?php echo $drev->campagne ?>&nbsp;<small class="text-muted">(hl)</small></th>
                <th class="text-center col-xs-2">Stock <?php echo $drev->campagne ?><br /><small class="text-muted">(hl)</small></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($drev->declaration->getProduitsVci() as $produit) : ?>
                <tr>
                    <td><?php echo $produit->getLibelleComplet() ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'stock_precedent') ?>"><?php if($produit->vci->stock_precedent): ?><?php echoFloat($produit->vci->stock_precedent) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'destruction') ?>"><?php if($produit->vci->destruction): ?><?php echoFloat($produit->vci->destruction) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'complement') ?>"><?php if($produit->vci->complement): ?><?php echoFloat($produit->vci->complement) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'substitution') ?>"><?php if($produit->vci->substitution): ?><?php echoFloat($produit->vci->substitution) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'rafraichi') ?>"><?php if($produit->vci->rafraichi): ?><?php echoFloat($produit->vci->rafraichi) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'constitue') ?>"><?php if($produit->vci->constitue): ?><?php echoFloat($produit->vci->constitue) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'stock_final') ?>"><?php if($produit->vci->stock_final): ?><?php echoFloat($produit->vci->stock_final) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
