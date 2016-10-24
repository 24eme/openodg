<?php $suffixe = ($vtsgn) ? "_vtsgn" : null; ?>
<?php $key_for_tr_id = str_replace("/", '_', $produit->getHash()).$suffixe; ?>
<tr data-toggle="collapse" data-target="#<?php echo $key_for_tr_id ?>" class="accordion-toggle
    <?php echo ($cpt % 2) ? "" : "table_td_zebra"; ?>
    <?php echo (count($produit->getProduitsCepage()) > 0)? 'trAccordion' : ''; ?>" >
    <td>
        <div class="float-left col-xs-10">
            <?php echo $produit->getLibelleComplet() ?> <?php if(!$vtsgn): ?><small class="text-muted">(hors VT/SGN)</small><?php else: ?>VT/SGN<?php endif; ?>
        </div>
        <div class="float-right text-right col-xs-2">
            <?php if (count($produit->getProduitsCepage()) > 0): ?>
            <small style="cursor: pointer;"><span class="glyphicon glyphicon-chevron-down">&nbsp;</span></small>
            <?php endif; ?>
        </div>
    </td>
    <?php if(!$drev->isNonRecoltant()): ?>
    <td class="text-center"><?php echoFloat($produit->get('superficie_revendique'.$suffixe)) ?><?php if (!is_null($produit->get('superficie_revendique'.$suffixe))): ?> <small class="text-muted">ares</small><?php endif; ?></td>
    <?php endif; ?>
    <td class="text-center"><?php echoFloat($produit->get('volume_revendique'.$suffixe)) ?><?php if (!is_null($produit->get('volume_revendique'.$suffixe))): ?> <small class="text-muted">hl</small><?php endif; ?></td>
</tr>
<?php if (count($produit->getProduitsCepage()) > 0): ?>
    <tr>
        <td class="hiddenRow" colspan="3"  >
            <div id="<?php echo $key_for_tr_id; ?>" class="accordian-body collapse" >
                <div class="col-xs-12 revendication_recap_padding">
                    <table class="table table-striped-alt">
                        <tbody>
                            <?php
                            foreach ($produit->getProduitsCepage() as $cepage_key => $produit_cepage) :
                                ?>
                                <?php if ($produit_cepage->volume_revendique && !$vtsgn): ?>
                                <tr>
                                    <td class="col-md-6 text-muted revendication_recap_td_libelle"><small><?php echo $produit_cepage->getLibelle() ?></small></td>
                                    <?php if(!$drev->isNonRecoltant()): ?>
                                    <td class="text-center text-muted col-md-3">
                                        <?php if ($produit_cepage->superficie_revendique): ?>
                                            <small><?php echoFloat($produit_cepage->superficie_revendique) ?><?php if (!is_null($produit_cepage->superficie_revendique)): ?> <small class="text-muted">ares</small><?php endif; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
                                    <td class="text-center text-muted col-md-3"><small><?php echoFloat($produit_cepage->volume_revendique) ?><?php if (!is_null($produit_cepage->volume_revendique)): ?> <small class="text-muted">hl</small><?php endif; ?></small></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($produit_cepage->volume_revendique_vt && $vtsgn): ?>
                                    <tr>
                                        <td class="col-md-6 text-muted revendication_recap_td_libelle"><small><?php echo $produit_cepage->getLibelle() ?> - VT</small></td>
                                        <?php if(!$drev->isNonRecoltant()): ?>
                                        <td class="text-center text-muted col-md-3">
                                            <?php if ($produit_cepage->superficie_revendique_vt): ?>
                                                <small><?php echoFloat($produit_cepage->superficie_revendique_vt) ?><?php if (!is_null($produit_cepage->superficie_revendique_vt)): ?> <small class="text-muted">ares</small><?php endif; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                        <td class="text-center text-muted col-md-3"><small><?php echoFloat($produit_cepage->volume_revendique_vt) ?><?php if (!is_null($produit_cepage->volume_revendique_vt)): ?> <small class="text-muted">hl</small><?php endif; ?></small></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($produit_cepage->volume_revendique_sgn && $vtsgn): ?>
                                    <tr>
                                        <td class="col-md-6 text-muted revendication_recap_td_libelle"><small><?php echo $produit_cepage->getLibelle() ?> - SGN</small></td>
                                        <?php if(!$drev->isNonRecoltant()): ?>
                                        <td class="text-center text-muted col-md-3">
                                            <?php if ($produit_cepage->superficie_revendique_sgn): ?>
                                                <small><?php echoFloat($produit_cepage->superficie_revendique_sgn) ?><?php if (!is_null($produit_cepage->superficie_revendique_sgn)): ?> <small class="text-muted">ares</small><?php endif; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                        <td class="text-center text-muted col-md-3"><small><?php echoFloat($produit_cepage->volume_revendique_sgn) ?><?php if (!is_null($produit_cepage->volume_revendique_sgn)): ?> <small class="text-muted">hl</small><?php endif; ?></small></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </td>
    </tr>
<?php endif; ?>
