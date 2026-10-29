<?php use_helper('Float') ?>
<h3>Revendication</h3>
<table class="table">
    <thead>
        <tr>
            <th class="col-md-<?php if($drev->hasProduitsVCI()): ?>4<?php else: ?>6<?php endif; ?>">Appellation</th>
            <?php if(!$drev->isNonRecoltant()): ?>
            <th class="text-center col-md-2">Superficie<br />totale</th>
        	<?php endif; ?>
        	<?php if ($drev->canHaveSuperficieVinifiee()): ?>
        	<th class="text-center col-md-<?php if(!$drev->isNonRecoltant()): ?>2<?php else: ?>3<?php endif; ?>">Superficie<?php if(!$drev->isNonRecoltant()): ?><br /><?php else: ?> <?php endif; ?>vinifiée</th>
            <?php endif; ?>
            <?php if($drev->hasProduitsVCI() || $drev->declaration->hasVolumeRevendiqueVci()): ?>
            <th class="text-center col-md-2">Volume<br />issu du VCI</th>
            <?php endif ?>
            <th class="text-center col-md-<?php if(!$drev->isNonRecoltant()): ?>2<?php else: ?>3<?php endif; ?>">Volume<?php if(!$drev->isNonRecoltant()): ?><br /><?php else: ?> <?php endif; ?>revendiqué <?php if($drev->hasDR()): ?><a title="Les volumes ventilés par cépage sont ceux issus du volume sur place de la déclaration de récolte, usages industriels et vci inclus" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md pull-right"><span class="glyphicon glyphicon-question-sign"></span></a><?php endif; ?></th>
        </tr>
    </thead>
    <tbody id="revendication_accordion" >
        <?php
        $cpt = 0;
        $totalVolRevendique = 0;
        $totalSuperficie = 0;
        $totalSuperficieVinifiee = 0;
        $totalVciRevendique = 0;
        $hasSuperficieVinifiee = false;
        ?>
        <?php foreach ($drev->declaration->getProduits() as $produit) : ?>
            <?php $totalVolRevendique += $produit->volume_revendique; ?>
            <?php $totalSuperficie += $produit->superficie_revendique; ?>
            <?php $totalSuperficieVinifiee += ($produit->exist('superficie_vinifiee'))? $produit->superficie_vinifiee : 0; ?>
            <?php $totalVciRevendique += ($produit->exist('volume_revendique_vci'))? $produit->volume_revendique_vci : 0; ?>
            <?php if ($produit->exist('superficie_vinifiee') || $produit->exist('superficie_vinifiee_vtsgn')) { $hasSuperficieVinifiee = true; } ?>
            <?php if($produit->volume_revendique || $produit->superficie_revendique || ($produit->exist('superficie_vinifiee') && $produit->superficie_vinifiee)): ?>
            <?php include_partial('drev/revendicationProduit', array('produit' => $produit, 'drev' => $drev, 'cpt' => $cpt, 'vtsgn' => false)); ?>
            <?php $cpt++; ?>
            <?php endif; ?>
            <?php if($produit->canHaveVtsgn() && ($produit->volume_revendique_vtsgn || $produit->superficie_revendique_vtsgn || ($produit->exist('superficie_vinifiee_vtsgn') && $produit->superficie_vinifiee_vtsgn))): ?>
                <?php $totalVolRevendique += $produit->volume_revendique_vtsgn; ?>
                <?php $totalSuperficie += $produit->superficie_revendique_vtsgn; ?>
            	<?php $totalSuperficieVinifiee += ($produit->exist('superficie_vinifiee_vtsgn'))? $produit->superficie_vinifiee_vtsgn : 0; ?>
                <?php include_partial('drev/revendicationProduit', array('produit' => $produit, 'drev' => $drev, 'cpt' => $cpt, 'vtsgn' => true)); ?>
                <?php $cpt++; ?>
            <?php endif; ?>
        <?php endforeach; ?>
        <tr class="<?php echo ($cpt % 2) ? "" : "table_td_zebra"; ?>" >
            <td><strong><div class="float-left col-xs-10">Total</div></strong></td>
            <?php if(!$drev->isNonRecoltant()): ?>
            <td class="text-center"><strong><?php echoFloat($totalSuperficie) ?></strong><?php if (!is_null($totalSuperficie)): ?> <small class="text-muted">ares</small><?php endif; ?></td>
        	<?php endif; ?>
        	<?php if ($drev->canHaveSuperficieVinifiee()): ?>
        	<td class="text-center"><?php if ($hasSuperficieVinifiee): ?><strong><?php echoFloat($totalSuperficieVinifiee) ?></strong><?php if (!is_null($totalSuperficieVinifiee)): ?> <small class="text-muted">ares</small><?php endif; ?><?php endif; ?></td>
        	<?php endif; ?>
            <?php if($drev->hasProduitsVCI() || $drev->declaration->hasVolumeRevendiqueVci()): ?>
            <td class="text-center"><strong><?php echoFloat($totalVciRevendique) ?></strong><?php if (!is_null($totalVciRevendique)): ?> <small class="text-muted">hl</small><?php endif; ?></td>
            <?php endif; ?>
            <td class="text-center"><strong><?php echoFloat($totalVolRevendique) ?></strong><?php if (!is_null($totalVolRevendique)): ?> <small class="text-muted">hl</small><?php endif; ?></td>
        </tr>
    </tbody>
</table>
