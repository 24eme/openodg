<?php use_helper('Float') ?>
<?php use_helper('Version') ?>

<h3>Revendication</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-6">Appellation revendiquée</th>
            <th class="col-xs-2 text-center">Superficie revendiquée<br /><small class="text-muted">(ha)</small></th>
            <th class="col-xs-2 text-center">Volume revendiqué<br />net total<br /><small class="text-muted">(hl)</small></th>
            <th class="col-xs-2 text-center">Dont issu du VCI<br /><small class="text-muted">(hl)</small></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($drev->declaration->getProduits() as $produit) : ?>
            <tr>
                <td><?php echo $produit->getLibelleComplet() ?></td>
                <td class="text-right <?php echo isVersionnerCssClass($produit, 'superficie_revendique') ?>"><?php if($produit->superficie_revendique): ?><?php echoFloat($produit->superficie_revendique) ?> <small class="text-muted">ha</small><?php endif; ?></td>
                <td class="text-right <?php echo isVersionnerCssClass($produit, 'volume_revendique_toral') ?>"><?php if($produit->volume_revendique_total): ?><?php echoFloat($produit->volume_revendique_total) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                <td class="text-right <?php echo isVersionnerCssClass($produit, 'volume_revendique_issu_vci') ?>"><?php if($produit->volume_revendique_issu_vci): ?><?php echoFloat($produit->volume_revendique_issu_vci) ?> <small class="text-muted">hl</small><?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if(count($drev->declaration->getProduitsVci())): ?>
    <h3>Répartition du VCI</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th class="col-xs-4">Appellation revendiquée</th>
                <th class="text-center">Stock <?php echo $drev->campagne - 1 ?><br /><small class="text-muted">(hl)</small></th>
                <th class="text-center">Complément<br /><small class="text-muted">(hl)</small></th>
                <th class="text-center">Substitution<br /><small class="text-muted">(hl)</small></th>
                <th class="text-center">A détruire<br /><small class="text-muted">(hl)</small></th>
                <th class="text-center">Rafraichi<br /><small class="text-muted">(hl)</small></th>
                <th class="text-center">Constitué<br /><small class="text-muted">(hl)</small></th>
                <th class="text-center">Stock <?php echo $drev->campagne ?><br /><small class="text-muted">(hl)</small></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($drev->declaration->getProduitsVci() as $produit) : ?>
                <tr>
                    <td><?php echo $produit->getLibelleComplet() ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'stock_precedent') ?>"><?php if($produit->vci->stock_precedent): ?><?php echoFloat($produit->vci->stock_precedent) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'complement') ?>"><?php if($produit->vci->complement): ?><?php echoFloat($produit->vci->complement) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'substitution') ?>"><?php if($produit->vci->substitution): ?><?php echoFloat($produit->vci->substitution) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'destruction') ?>"><?php if($produit->vci->destruction): ?><?php echoFloat($produit->vci->destruction) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'rafraichi') ?>"><?php if($produit->vci->rafraichi): ?><?php echoFloat($produit->vci->rafraichi) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'constitue') ?>"><?php if($produit->vci->constitue): ?><?php echoFloat($produit->vci->constitue) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'stock_final') ?>"><?php if($produit->vci->stock_final): ?><?php echoFloat($produit->vci->stock_final) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include_partial('drev/prelevements', array('drev' => $drev)); ?>
