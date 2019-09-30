<?php use_helper('Float') ?>
<?php use_helper('Version') ?>

<?php if ($drev->exist('achat_tolerance') && $drev->get('achat_tolerance')): ?>
<div class="alert alert-info" role="alert">
    <p>Les volumes récoltés ont fait l'objet d'achats réalisés dans le cadre de la tolérance administrative ou sinistre climatique.</p>
</div>
<?php endif; ?>

<h3>Revendication AOC</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="col-xs-6">Appellation revendiquée</th>
            <th class="col-xs-2 text-center">Superficie revendiquée&nbsp;<small class="text-muted">(ha)</small></th>
            <th class="col-xs-2 text-center">Volume revendiqué net total&nbsp;<small class="text-muted">(hl)</small></th>
            <th class="col-xs-2 text-center">Dont millesime <?php echo $drev->campagne-1 ?> issu du VCI&nbsp;<small class="text-muted">(hl)</small></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($drev->declaration->getProduitsWithoutLots() as $produit) : ?>
            <tr>
                <td><?php echo $produit->getLibelleComplet() ?><?php if($produit->isValidateOdg()): ?>&nbsp;&nbsp;&nbsp;<span class="glyphicon glyphicon-ok" ></span><?php endif ?><small class="pull-right <?php if($produit->getRendementEffectif() > $produit->getConfig()->getRendement()): ?>text-danger<?php endif; ?>">&nbsp;<?php echoFloat(round($produit->getRendementEffectif(), 2)); ?> hl/ha</small></td>
                <td class="text-right <?php echo isVersionnerCssClass($produit, 'superficie_revendique') ?>"><?php if($produit->superficie_revendique): ?><?php echoFloat($produit->superficie_revendique) ?> <small class="text-muted">ha</small><?php endif; ?></td>
                <td class="text-right <?php echo isVersionnerCssClass($produit, 'volume_revendique_total') ?>"><?php if($produit->volume_revendique_total !== null): ?><?php echoFloat($produit->volume_revendique_total) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                <td class="text-right <?php echo isVersionnerCssClass($produit, 'volume_revendique_issu_vci') ?>"><?php if($produit->volume_revendique_issu_vci): ?><?php echoFloat($produit->volume_revendique_issu_vci) ?> <small class="text-muted">hl</small><?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if($drev->exist('lots') && count($drev->lots)): ?>
    <h3>Déclaration des lots IGP</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th class="col-xs-1">Date Rev.</th>
                <th class="col-xs-1">Lot</th>
                <th class="text-center col-xs-5">Produit (millesime)</th>
                <th class="text-center col-xs-1">Superficie</th>
                <th class="text-center col-xs-2">Volume</th>
                <th class="text-center col-xs-2">Destination (date)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($drev->getLotsByCouleur() as $couleur => $lots) :
                $volume = 0;
                $synthese_revendication = $drev->summerizeProduitsByCouleur();
                foreach ($lots as  $lot) :
                  ?>
                <tr>
                    <td>
                      <?php $drevDocOrigine = $lot->getDrevDocOrigine(); ?>
                      <?php if($drevDocOrigine): ?><a class="link pull-right" href="<?php echo url_for('drev_visualisation', $drevDocOrigine); ?>"><?php endif; ?>
                        <?php echo $lot->getDateVersionfr(); ?>
                      <?php if($drevDocOrigine): ?></a><?php endif; ?>
                    </td>
                    <td class="<?php echo isVersionnerCssClass($lot, 'numero') ?>" ><?php echo $lot->numero; ?></td>
                    <td class="<?php echo isVersionnerCssClass($lot, 'produit_libelle') ?>" ><?php echo $lot->produit_libelle; echo ($lot->millesime)? " (".$lot->millesime.")" : ""; ?>
                      <?php if(count($lot->cepages)): ?>
                        <small>
                          <?php echo $lot->getCepagesToStr(); ?>
                        </small>
                      <?php endif; ?>
                      <?php if($lot->isProduitValidateOdg()): ?>&nbsp;&nbsp;&nbsp;<span class="glyphicon glyphicon-ok" ></span><?php endif ?>
                    </td>
                    <td>&nbsp;</td>
                    <td class="text-right <?php echo isVersionnerCssClass($lot, 'volume') ?>"><?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small></td>
                    <td class="text-center <?php echo isVersionnerCssClass($lot, 'destination_type') ?>"><?php echo $lot->destination_type; echo ($lot->destination_date) ? " (".$lot->getDestinationDateFr().")" : ''; ?></td>
                </tr>
                <?php $volume += $lot->volume ;
                endforeach;
                ?>
                <tr>
                    <td></td>
                    <td><strong>Total</strong></td>
                    <td><strong><?php echo ($lot->produit_hash)? $lot->getConfigProduit()->getCouleur()->getLibelleComplet() : "pas de produit"; ?></strong><small class="pull-right">&nbsp;<?php if(isset($synthese_revendication[$couleur])): ?><?php echoFloat(round($volume / $synthese_revendication[$couleur]['superficie_totale'], 2)); ?>&nbsp;hl/ha</small><?php endif; ?></td>
                    <td class="text-right"><strong><?php if(isset($synthese_revendication[$couleur])): ?><?php echoFloat($synthese_revendication[$couleur]['superficie_totale']); ?><small class="text-muted">&nbsp;ha</small></strong><?php endif; ?></td>
                    <td class="text-right"><strong><?php echoFloat($volume); ?><small class="text-muted">&nbsp;hl</small></strong></td>
                    <td class="text-center"><?php if(isset($synthese_revendication[$couleur])): ?><?php if($synthese_revendication[$couleur]['volume_total'] > 0): ?><span class="text-muted"><small>il reste donc <?php echoFloat($synthese_revendication[$couleur]['volume_total'] - $volume); ?>&nbsp;hl max à revendiquer</span></small><?php endif; ?><?php endif; ?></td>
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
                <th class="text-center col-xs-1">Rafraichi<br /><small class="text-muted">(hl)</small></th>
                <th class="text-center col-xs-1">Complémt<br /><small class="text-muted">(hl)</small></th>
                <th class="text-center col-xs-1">A détruire<br /><small class="text-muted">(hl)</small></th>
                <th class="text-center col-xs-1">Substitué<br /><small class="text-muted">(hl)</small></th>
                <th class="text-center col-xs-1">Constitué<br /><?php echo $drev->campagne ?>&nbsp;<small class="text-muted">(hl)</small></th>
                <th class="text-center col-xs-2">Stock <?php echo $drev->campagne ?><br /><small class="text-muted">(hl)</small></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($drev->declaration->getProduitsVci() as $produit) : ?>
                <tr>
                    <td><?php echo $produit->getLibelleComplet() ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'stock_precedent') ?>"><?php if($produit->vci->stock_precedent): ?><?php echoFloat($produit->vci->stock_precedent) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'rafraichi') ?>"><?php if($produit->vci->rafraichi): ?><?php echoFloat($produit->vci->rafraichi) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'complement') ?>"><?php if($produit->vci->complement): ?><?php echoFloat($produit->vci->complement) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'destruction') ?>"><?php if($produit->vci->destruction): ?><?php echoFloat($produit->vci->destruction) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'substitution') ?>"><?php if($produit->vci->substitution): ?><?php echoFloat($produit->vci->substitution) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'constitue') ?>"><?php if($produit->vci->constitue): ?><?php echoFloat($produit->vci->constitue) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td class="text-right <?php echo isVersionnerCssClass($produit->vci, 'stock_final') ?>"><?php if($produit->vci->stock_final): ?><?php echoFloat($produit->vci->stock_final) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
