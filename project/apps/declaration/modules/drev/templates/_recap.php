<?php use_helper('Float') ?>
<?php use_helper('Version') ?>

<div class="row">
    <div class="col-xs-12">
        <h3>Revendication 2016</h3>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="col-xs-3">Appellation revendiquée</th>
                    <th class="col-xs-1">Superficie revendiquée</th>
                    <th class="col-xs-1">Volume revendiqué sans VCI</th>
                    <th class="col-xs-1">Volume revendiqué avec VCI</th>
                    <th class="col-xs-1">VCI constitué</th>
                    <th class="col-xs-1">Stock VCI</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($drev->declaration->getProduits() as $produit) : ?>
                    <tr>
                        <td><?php echo $produit->getLibelleComplet() ?></td>
                        <td class="text-right <?php echo isVersionnerCssClass($produit, 'superficie_revendique') ?>"><?php echoFloat($produit->superficie_revendique) ?> <small class="text-muted">ares</small></td>
                        <td class="text-right <?php echo isVersionnerCssClass($produit, 'volume_revendique_sans_vci') ?>"><?php echoFloat($produit->volume_revendique_sans_vci) ?> <small class="text-muted">hl</small></td>
                        <td class="text-right <?php echo isVersionnerCssClass($produit, 'volume_revendique_avec_vci') ?>"><?php echoFloat($produit->volume_revendique_avec_vci) ?> <small class="text-muted">hl</small></td>
                        <?php if($produit->hasVci()): ?>
                            <td class="text-right <?php echo isVersionnerCssClass($produit, 'vci') ?>"><?php if($produit->vci): ?><?php echoFloat($produit->vci) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                            <td class="text-right <?php echo isVersionnerCssClass($produit, 'vci_stock_final') ?>"><?php echoFloat($produit->vci_stock_final) ?> <small class="text-muted">hl</small></td>
                        <?php else: ?>
                            <td colspan="2" class="text-center <?php echo isVersionnerCssClass($produit, 'vci_stock_final') ?>"><small class="text-muted"><em>Pas de VCI</em></small></td>
                        <?php endif; ?>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if(count($drev->declaration->getProduitsVci())): ?>
        <h3>Répartition du VCI 2015</h3>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="col-xs-3">Appellation revendiquée</th>
                    <th class="col-xs-1">Stock VCI 2015</th>
                    <th class="col-xs-1">Complément</th>
                    <th class="col-xs-1">Substitution</th>
                    <th class="col-xs-1">A détruire</th>
                    <th class="col-xs-1">Rafraichi</th>
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
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <?php //include_partial('drev/revendication', array('drev' => $drev)); ?>
    </div>
    <div class="col-xs-12">
        <?php include_partial('drev/prelevements', array('drev' => $drev)); ?>
    </div>
</div>
