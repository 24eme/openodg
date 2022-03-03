<?php $suffixe = ($vtsgn) ? "_vtsgn" : null; ?>
<?php $key_for_tr_id = str_replace("/", '_', $produit->getHash()).$suffixe; ?>
<tr data-toggle="collapse" data-target="#<?php echo $key_for_tr_id ?>" class="accordion-toggle
    <?php echo ($cpt % 2) ? "" : "table_td_zebra"; ?>
    <?php echo (count($produit->getProduitsCepage()) > 0)? 'trAccordion' : ''; ?>" >
    <td>
        <div class="float-left col-xs-10">
            <?php echo $produit->getLibelleComplet() ?> <?php if(!$vtsgn && $produit->canHaveVtsgn()): ?><small class="text-muted">(hors VT/SGN)</small><?php elseif($vtsgn): ?>VT/SGN<?php endif; ?>
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
    <?php if ($drev->canHaveSuperficieVinifiee()): ?>
    <td class="text-center"><?php if ($produit->exist('superficie_vinifiee'.$suffixe)): ?><?php echoFloat($produit->get('superficie_vinifiee'.$suffixe)) ?><?php if (!is_null($produit->get('superficie_vinifiee'.$suffixe))): ?> <small class="text-muted">ares</small><?php endif; ?><?php endif; ?></td>
    <?php endif; ?>
    <?php if($drev->hasProduitsVCI() && !$suffixe): ?>
    <td class="text-center"><?php if ($produit->exist('volume_revendique_vci') && !is_null($produit->get('volume_revendique_vci'))): ?> <?php echoFloat($produit->get('volume_revendique_vci')) ?><small class="text-muted">hl</small><?php endif; ?></td>
    <?php elseif($drev->hasProduitsVCI()): ?>
    <td>&nbsp;</td>
    <?php endif; ?>
    <td class="text-center"><?php echoFloat($produit->get('volume_revendique'.$suffixe)) ?><?php if (!is_null($produit->get('volume_revendique'.$suffixe))): ?> <small class="text-muted">hl</small><?php endif; ?></td>
</tr>
<?php if (count($produit->getProduitsCepage()) > 0): ?>
    <tr>
        <td class="hiddenRow" colspan="<?php if($drev->hasProduitsVCI()): ?>5<?php else: ?>4<?php endif; ?>"  >
            <div id="<?php echo $key_for_tr_id; ?>" class="accordian-body collapse" >
                <div class="col-xs-12 revendication_recap_padding">
                    <table class="table table-striped-alt">
                        <tbody>
                            <?php
                            foreach ($produit->getProduitsCepage() as $cepage_key => $produit_cepage) :
                            	$print = false;
                                ?>
                                <?php if (($produit_cepage->volume_revendique || $produit_cepage->superficie_revendique) && !$vtsgn): ?>
                                <tr>
                                    <td class="col-md-<?php if($drev->hasProduitsVCI()): ?>4<?php else: ?>6<?php endif; ?> text-muted revendication_recap_td_libelle"><small><?php echo $produit_cepage->getLibelle() ?></small></td>
                                    <?php if(!$drev->isNonRecoltant()): ?>
                                    <td class="text-center text-muted col-md-2">
                                        <?php if ($produit_cepage->superficie_revendique): ?>
                                            <small><?php echoFloat($produit_cepage->superficie_revendique) ?><?php if (!is_null($produit_cepage->superficie_revendique)): ?> <small class="text-muted">ares</small><?php endif; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
                                    <?php if ($drev->canHaveSuperficieVinifiee()): ?>
                                    <td class="text-center text-muted col-md-<?php if(!$drev->isNonRecoltant()): ?>2<?php else: ?>3<?php endif; ?>"><?php if ($produit_cepage->superficie_vinifiee): ?>
                                        <small><?php echoFloat($produit_cepage->superficie_vinifiee) ?><?php if (!is_null($produit_cepage->superficie_vinifiee)): ?> <small class="text-muted">ares</small><?php endif; ?></small>
                                    <?php endif; ?></td>
                                    <?php endif; ?>
                                    <?php if($drev->hasProduitsVCI()): ?>
								    <td class="text-center text-muted col-md-2"><?php if($produit_cepage->getVolumeRevendiqueVci() !== null): ?><small><?php echoFloat($produit_cepage->getVolumeRevendiqueVci()) ?><?php if (!is_null($produit_cepage->getVolumeRevendiqueVci())): ?> <small class="text-muted">hl</small><?php endif; ?></small><?php else: ?>&nbsp;<?php endif; ?></td>
								    <?php endif; ?>
                                    <td class="text-center text-muted col-md-<?php if(!$drev->isNonRecoltant()): ?>2<?php else: ?>3<?php endif; ?>"><small><?php echoFloat($produit_cepage->volume_revendique) ?><?php if (!is_null($produit_cepage->volume_revendique + $produit_cepage->getVolumeRevendiqueVci())): ?> <small class="text-muted">hl</small><?php endif; ?></small></td>
                                </tr>
                                <?php $print = true; endif; ?>
                                <?php if ($produit_cepage->volume_revendique_vt && $vtsgn): ?>
                                    <tr>
                                        <td class="col-md-<?php if($drev->hasProduitsVCI()): ?>4<?php else: ?>6<?php endif; ?> text-muted revendication_recap_td_libelle"><small><?php echo $produit_cepage->getLibelle() ?> - VT</small></td>
                                        <?php if(!$drev->isNonRecoltant()): ?>
                                        <td class="text-center text-muted col-md-2">
                                            <?php if ($produit_cepage->superficie_revendique_vt): ?>
                                                <small><?php echoFloat($produit_cepage->superficie_revendique_vt) ?><?php if (!is_null($produit_cepage->superficie_revendique_vt)): ?> <small class="text-muted">ares</small><?php endif; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                        <?php if ($drev->canHaveSuperficieVinifiee()): ?>
                                   	 	<td class="text-center text-muted col-md-<?php if(!$drev->isNonRecoltant()): ?>2<?php else: ?>3<?php endif; ?>"></td>
                                   	 	<?php endif; ?>
	                                    <?php if($drev->hasProduitsVCI()): ?>
									    <td class="text-center text-muted col-md-2">&nbsp;</td>
									    <?php endif; ?>
                                        <td class="text-center text-muted col-md-<?php if(!$drev->isNonRecoltant()): ?>2<?php else: ?>3<?php endif; ?>"><small><?php echoFloat($produit_cepage->volume_revendique_vt) ?><?php if (!is_null($produit_cepage->volume_revendique_vt)): ?> <small class="text-muted">hl</small><?php endif; ?></small></td>
                                    </tr>
                                <?php $print = true; endif; ?>
                                <?php if ($produit_cepage->volume_revendique_sgn && $vtsgn): ?>
                                    <tr>
                                        <td class="col-md-<?php if($drev->hasProduitsVCI()): ?>4<?php else: ?>6<?php endif; ?> text-muted revendication_recap_td_libelle"><small><?php echo $produit_cepage->getLibelle() ?> - SGN</small></td>
                                        <?php if(!$drev->isNonRecoltant()): ?>
                                        <td class="text-center text-muted col-md-2">
                                            <?php if ($produit_cepage->superficie_revendique_sgn): ?>
                                                <small><?php echoFloat($produit_cepage->superficie_revendique_sgn) ?><?php if (!is_null($produit_cepage->superficie_revendique_sgn)): ?> <small class="text-muted">ares</small><?php endif; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                        <?php if ($drev->canHaveSuperficieVinifiee()): ?>
                                    	<td class="text-center text-muted col-md-<?php if(!$drev->isNonRecoltant()): ?>2<?php else: ?>3<?php endif; ?>"></td>
                                    	<?php endif; ?>
                                        <?php if($drev->hasProduitsVCI()): ?>
									    <td class="text-center text-muted col-md-2">&nbsp;</td>
									    <?php endif; ?>
                                        <td class="text-center text-muted col-md-<?php if(!$drev->isNonRecoltant()): ?>2<?php else: ?>3<?php endif; ?>"><small><?php echoFloat($produit_cepage->volume_revendique_sgn) ?><?php if (!is_null($produit_cepage->volume_revendique_sgn)): ?> <small class="text-muted">hl</small><?php endif; ?></small></td>
                                    </tr>
                                <?php $print = true; endif; ?>
                                <?php if (!$print): ?>
                                    <tr>
                                        <td class="col-md-<?php if($drev->hasProduitsVCI()): ?>4<?php else: ?>6<?php endif; ?> text-muted revendication_recap_td_libelle"><small><?php echo $produit_cepage->getLibelle() ?></small></td>
                                        <?php if(!$drev->isNonRecoltant()): ?>
                                        <td class="text-center text-muted col-md-2"></td>
                                        <?php endif; ?>
                                        <?php if ($drev->canHaveSuperficieVinifiee()): ?>
                                    	<td class="text-center text-muted col-md-<?php if(!$drev->isNonRecoltant()): ?>2<?php else: ?>3<?php endif; ?>"></td>
                                    	<?php endif; ?>
                                        <?php if($drev->hasProduitsVCI()): ?>
    								    <td class="text-center text-muted col-md-2"><?php if($produit_cepage->getVolumeRevendiqueVci() !== null): ?><small><?php echoFloat($produit_cepage->getVolumeRevendiqueVci()) ?><?php if (!is_null($produit_cepage->getVolumeRevendiqueVci())): ?> <small class="text-muted">hl</small><?php endif; ?></small><?php else: ?>&nbsp;<?php endif; ?></td>
    								    <?php endif; ?>
                                        <td class="text-center text-muted col-md-<?php if(!$drev->isNonRecoltant()): ?>2<?php else: ?>3<?php endif; ?>">&nbsp;</td>
                                    </tr>
                                <?php $print = true; endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </td>
    </tr>
<?php endif; ?>
