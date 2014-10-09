<?php use_helper('Float') ?>
<h3>Revendication</h2>
<table class="table">
    <thead>
        <tr>
            <th class="col-md-6">Appellation</th>
            <?php if(!$drev->isNonRecoltant()): ?>
            <th class="text-center col-md-3">Superficie totale</th>
        	<?php endif; ?>
            <th class="text-center col-md-3">Vol. revendiqué</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $cpt = 0;
        foreach ($drev->declaration->getProduits(true) as $key => $produit) :
            $produit = $drev->get($key);
            $key_for_tr_id = str_replace("/", '_', $key);
            ?>
            <tr data-toggle="collapse" data-target="#<?php echo $key_for_tr_id ?>" class="accordion-toggle <?php echo ($cpt % 2) ? "" : "table_td_zebra"; ?>" >
                <td>
                    <div class="float-left col-xs-10">
                        <?php echo $produit->getLibelleComplet() ?> 
                    </div>
                    <div class="float-right text-right col-xs-2">
                        <?php if (count($produit->getProduitsCepage()) > 0): ?>
                            <small style="cursor: pointer;"><span class="glyphicon glyphicon-chevron-down">&nbsp;</span></small><?php endif; ?>
                    </div>
                </td>
                <?php if(!$drev->isNonRecoltant()): ?>
                <td class="text-center"><?php echoFloat($produit->superficie_revendique) ?> <small class="text-muted">ares</small></td>
            	<?php endif; ?>
                <td class="text-center"><?php echoFloat($produit->volume_revendique) ?> <small class="text-muted">hl</small></td>
            </tr>
            <?php if (count($produit->getProduitsCepage()) > 0): ?>
                <tr>
                    <td class="hiddenRow" colspan="3"  >
                        <div id="<?php echo $key_for_tr_id; ?>" class="accordian-body collapse" >
                            <div class="col-xs-12 revendication_recap_padding">
                                <table class="table table-condensed">
                                    <tbody>
                                        <?php
                                        foreach ($produit->getProduitsCepage() as $cepage_key => $produit_cepage) :
                                            ?>
                                            <tr>
                                                <td class="col-md-6 text-muted revendication_recap_td_libelle"><small><?php echo $produit_cepage->getLibelle(); ?></small></td>
                                                <?php if(!$drev->isNonRecoltant()): ?>
                                                <td class="text-center text-muted col-md-3" >
                                                    <?php if ($produit_cepage->superficie_revendique): ?>
                                                        <small><?php echoFloat($produit_cepage->superficie_revendique) ?> <small class="text-muted">ares</small></small>
                                                    <?php endif; ?>
                                                </td>
                                            	<?php endif; ?>
                                                <td class="text-center text-muted col-md-3"><small><?php echoFloat($produit_cepage->volume_revendique) ?> <small class="text-muted">hl</small></small></td>                                           
                                            </tr>
                                            <?php if ($produit_cepage->volume_revendique_vt): ?>
                                                <tr>
                                                    <td class="col-md-6 text-muted revendication_recap_td_libelle"><small><?php echo $produit_cepage->getLibelle() . ' VT'; ?></small></td>
                                                    <?php if(!$drev->isNonRecoltant()): ?>
                                                    <td>
                                                        <?php if ($produit_cepage->superficie_revendique_vt): ?>
                                                            <small><?php echoFloat($produit_cepage->superficie_revendique_vt) ?> <small class="text-muted">ares</small></small>
                                                        <?php endif; ?>
                                                    </td>
                                                	<?php endif; ?>
                                                    <td class="text-center text-muted col-md-3"><small><?php echoFloat($produit_cepage->volume_revendique_vt) ?> <small class="text-muted">hl</small></small></td>                                           
                                                </tr>
                                            <?php endif; ?>
                                            <?php if ($produit_cepage->volume_revendique_sgn): ?>
                                                <tr>
                                                    <td class="col-md-6 text-muted revendication_recap_td_libelle"><small><?php echo $produit_cepage->getLibelle() . ' SGN'; ?></small></td>
                                                    <?php if(!$drev->isNonRecoltant()): ?>
                                                    <td>
                                                        <?php if ($produit_cepage->superficie_revendique_sgn): ?>
                                                            <small><?php echoFloat($produit_cepage->superficie_revendique_sgn) ?> <small class="text-muted">ares</small></small>
                                                        <?php endif; ?>
                                                    </td>
                                                	<?php endif; ?>
                                                    <td class="text-center text-muted col-md-3"><small><?php echoFloat($produit_cepage->volume_revendique_sgn) ?> <small class="text-muted">hl</small></small></td>                                           
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
            <?php $cpt++; ?> 
        <?php endforeach; ?>
    </tbody>
</table>
