<?php use_helper('Float') ?>
<?php use_helper('Version') ?>

<h3>Revendication</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-4">Appellation revendiquée</th>
            <th class="col-xs-3 text-center">Superficie revendiquée</th>
            <th class="col-xs-3 text-center">Volume revendiqué net total</th>
            <th class="col-xs-4 text-center"></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($drev->declaration->getProduits() as $produit) : ?>
            <tr>
                <td><?php echo $produit->getLibelleComplet() ?></td>
                <td class="text-right <?php echo isVersionnerCssClass($produit, 'superficie_revendique') ?>"><?php echoFloat($produit->superficie_revendique) ?> <small class="text-muted">ha</small></td>
                <td class="text-right <?php echo isVersionnerCssClass($produit, 'volume_revendique_avec_vci') ?>"><?php echoFloat($produit->volume_revendique_avec_vci) ?> <small class="text-muted">hl</small></td>
                <td style="visibility: hidden;"></td>
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
                <th class="text-center">Stock 2015</th>
                <th class="text-center">Complément</th>
                <th class="text-center">Substitution</th>
                <th class="text-center">A détruire</th>
                <th class="text-center">Rafraichi</th>
                <th class="text-center">Constitué</th>
                <th class="text-center">Stock 2016</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($drev->declaration->getProduitsVci() as $produit) : ?>
                <tr>
                    <td><?php echo $produit->getLibelleComplet() ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit, 'vci_stock_initial') ?>"><?php if($produit->vci_stock_initial): ?><?php echoFloat($produit->vci_stock_initial) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit, 'vci_complement_dr') ?>"><?php if($produit->vci_complement_dr): ?><?php echoFloat($produit->vci_complement_dr) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit, 'vci_substitution') ?>"><?php if($produit->vci_substitution): ?><?php echoFloat($produit->vci_substitution) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit, 'vci_destruction') ?>"><?php if($produit->vci_destruction): ?><?php echoFloat($produit->vci_destruction) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit, 'vci_rafraichi') ?>"><?php if($produit->vci_rafraichi): ?><?php echoFloat($produit->vci_rafraichi) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit, 'vci') ?>"><?php if($produit->vci): ?><?php echoFloat($produit->vci) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit, 'vci_stock_final') ?>"><?php if($produit->vci_stock_final): ?><?php echoFloat($produit->vci_stock_final) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include_partial('drev/prelevements', array('drev' => $drev)); ?>
