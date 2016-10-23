<?php use_helper('Float') ?>
<h3>Revendication</h3>
<table class="table">
    <thead>
        <tr>
            <th class="col-md-6">Appellation</th>
            <?php if(!$drev->isNonRecoltant()): ?>
            <th class="text-center col-md-3">Superficie totale</th>
        	<?php endif; ?>
            <th class="text-center col-md-3">Volume revendiqué <?php if($drev->hasDR()): ?><a title="Les volumes ventilés par cépage sont ceux issus de la déclaration de récolte, usages industriels inclus" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md pull-right"><span class="glyphicon glyphicon-question-sign"></span></a><?php endif; ?></th>
        </tr>
    </thead>
    <tbody id="revendication_accordion" >
        <?php
        $cpt = 0;
        $totalVolRevendique = 0;
        $totalSuperficie = 0;
        ?>
        <?php foreach ($drev->declaration->getProduits() as $produit) : ?>
            <?php $totalVolRevendique += $produit->volume_revendique; ?>
            <?php $totalSuperficie += $produit->superficie_revendique; ?>
            <?php include_partial('drev/revendicationProduit', array('produit' => $produit, 'drev' => $drev, 'cpt' => $cpt, 'vtsgn' => false)); ?>
            <?php $cpt++; ?>
            <?php if($produit->getConfig()->hasProduitsVtsgn()): ?>
                <?php $totalVolRevendique += $produit->volume_revendique_vtsgn; ?>
                <?php $totalSuperficie += $produit->superficie_revendique_vtsgn; ?>
                <?php include_partial('drev/revendicationProduit', array('produit' => $produit, 'drev' => $drev, 'cpt' => $cpt, 'vtsgn' => true)); ?>
                <?php $cpt++; ?>
            <?php endif; ?>
        <?php endforeach; ?>
        <tr class="<?php echo ($cpt % 2) ? "" : "table_td_zebra"; ?>" >
            <td><strong><div class="float-left col-xs-10">Total</div></strong></td>
            <?php if(!$drev->isNonRecoltant()): ?>
            <td class="text-center"><strong><?php echoFloat($totalSuperficie) ?></strong><?php if (!is_null($totalSuperficie)): ?> <small class="text-muted">ares</small><?php endif; ?></td>
        	<?php endif; ?>
            <td class="text-center"><strong><?php echoFloat($totalVolRevendique) ?></strong><?php if (!is_null($totalVolRevendique)): ?> <small class="text-muted">hl</small><?php endif; ?></td>
        </tr>
    </tbody>
</table>
