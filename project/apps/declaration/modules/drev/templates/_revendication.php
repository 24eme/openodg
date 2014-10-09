<?php use_helper('Float') ?>
<h3>Revendication</h2>
<table class="table">
    <thead>
        <tr>
            <th class="col-md-6">Appellation</th>
            <th class="text-center col-md-3">Superficie totale</th>
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
                        <?php if (count($produit->getProduitsCepage()) > 1): ?>
                            <small style="cursor: pointer;"><span class="glyphicon glyphicon-chevron-down">&nbsp;</span></small><?php endif; ?>
                    </div>
                </td>
                <td class="text-center"><?php echoFloat($produit->superficie_revendique) ?> <small class="text-muted">ares</small></td>
                <td class="text-center"><?php echoFloat($produit->volume_revendique) ?> <small class="text-muted">hl</small></td>
            </tr>
            <?php if (count($produit->getProduitsCepage()) > 1): ?>
                <tr>
                    <td class="hiddenRow" colspan="3"  >
                        <div id="<?php echo $key_for_tr_id; ?>" class="accordian-body collapse" >
                            <div class="col-xs-12 no-padding">
                                <table class="table table-condensed">
                                    <tbody>
                                        <?php
                                        foreach ($produit->getProduitsCepage() as $cepage_key => $produit_cepage) :
                                            $rowspan = 1;
                                            if ($produit_cepage->volume_revendique_vt) {
                                                $rowspan++;
                                            }
                                            if ($produit_cepage->volume_revendique_sgn) {
                                                $rowspan++;
                                            }
                                            ?>
                                            <tr>
                                                <td class="col-md-6 text-muted"><small><?php echo $produit_cepage->getLibelle(); ?></small></td>
                                                <td class="text-center text-muted col-md-3" rowspan="<?php echo $rowspan; ?>" >
                                                    <?php if ($produit_cepage->superficie_revendique): ?>
                                                        <small><?php echoFloat($produit_cepage->superficie_revendique) ?> <small class="text-muted">ares</small></small>
            <?php endif; ?>
                                                </td>
                                                <td class="text-center text-muted col-md-3"><small><?php echoFloat($produit_cepage->volume_revendique) ?> <small class="text-muted">hl</small></small></td>                                           
                                            </tr>
            <?php if ($produit_cepage->volume_revendique_vt): ?>
                                                <tr>
                                                    <td class="col-md-6 text-muted"><small><?php echo $produit_cepage->getLibelle() . ' VT'; ?></small></td>

                                                    <td class="text-center text-muted col-md-3"><small><?php echoFloat($produit_cepage->volume_revendique_vt) ?> <small class="text-muted">hl</small></small></td>                                           
                                                </tr>
                                            <?php endif; ?>
            <?php if ($produit_cepage->volume_revendique_sgn): ?>
                                                <tr>
                                                    <td class="col-md-6 text-muted"><small><?php echo $produit_cepage->getLibelle() . ' SGN'; ?></small></td>

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
