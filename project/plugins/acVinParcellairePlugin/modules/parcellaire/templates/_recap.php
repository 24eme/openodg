<?php
use_helper("Date");
$last = $parcellaire->getParcellaireLastCampagne();
$lastParcellesKeysByAppellations = null;
if ($last) {
    $lastParcellesKeysByAppellations = $last->getAllParcellesKeysByAppellations()->getRawValue();
}
?>
<?php if (count($parcellaire->declaration) > 0): ?>
    <div class="row">
        <div class="col-xs-12">
            <?php foreach ($parcellaire->declaration->getParcellesByCommune() as $commune => $parcelles): ?>
            	<h3><?php echo $commune ?></h3>

                <table class="table table-bordered table-condensed table-striped">
                  <thead>
		        	<tr>
		                <th class="col-xs-4">Parcelle</th>
		                <th class="col-xs-4">Cépage</th>
                        <th class="col-xs-1">Écart Pieds</th>
                        <th class="col-xs-1">Écart Rang</th>
                        <th class="col-xs-2" >Faire-valoir</th>
		            </tr>
                  </thead>
                    <tbody>



                        <?php
                        foreach ($parcelles as $detail):
                            $classline = '';
                            $styleline = '';
                            $styleproduit = '';
                            $styleparcelle = '';
                            $classparcelle = '';
                            $classsuperficie = '';
                            $stylesuperficie = '';
                            if (isset($diff) && $diff) {
                                if ($last && !$last->exist($detail->getHash())) {
                                    $styleline = 'border-style: solid; border-width: 1px; border-color: darkgreen;';
                                } else {
                                    if ($last && $detail->getParcelleIdentifiant() != $last->get($detail->getHash())->getParcelleIdentifiant()) {
                                        $styleparcelle = 'border-style: solid; border-width: 1px; border-color: darkorange;';
                                    }
                                    if ($last && $detail->getSuperficie() != $last->get($detail->getHash())->getSuperficie()) {
                                        $styleline = (!$detail->superficie) ? 'text-decoration: line-through; border-style: solid; border-width: 1px; border-color: darkred' : '';
                                        $classline = (!$detail->superficie) ? 'danger' : '';
                                        $stylesuperficie = (!$detail->superficie) ? 'border-style: solid; border-width: 1px; border-color: darkgreen' : 'border-style: solid; border-width: 1px; border-color: darkgreen';
                                    }
                                }
                                if (!$detail->getSuperficie()) {
                                    $stylesuperficie = 'border-style: solid; border-width: 1px; border-color: darkred';
                                }

                                if (!$detail->isAffectee()) {
                                    $styleline="opacity: 0.4;";
                                    $styleproduit="text-decoration: line-through;";
                                    $styleparcelle="text-decoration: line-through;";
                                    $stylesuperficie="text-decoration: line-through;";
                                    $classline="";
                                    $classsuperficie="";
                                    $classparcelle="";
                                }
                            }
                            $classecart = '';
                            $classcepage = '';
                            if ($detail->hasProblemExpirationCepage()) {
                              $classline .=  ' warning';
                              $classcepage .= ' text-warning';
                            }
                            if ($detail->hasProblemEcartPieds()) {
                              $classline .=  ' danger';
                              $classecart .= ' text-danger';
                            }
                            ?>
                            <tr class="<?php echo $classline ?>" style="<?php echo $styleline; ?>">
                                <td style="<?php echo $styleproduit; ?>">
                                    <?php echo $detail->getIdentificationParcelleLibelle(ESC_RAW); ?>
                                </td>
                                <td class="<?php echo $classcepage; ?>" style="<?php echo $styleproduit; ?>">
                                    <?php echo $detail->getIdentificationCepageLibelle(ESC_RAW);  ?>
                                </td>
                                <td class="<?php echo $classecart; ?>" style="text-align: center;"><?php echo ($detail->exist('ecart_pieds'))? $detail->get('ecart_pieds') : '&nbsp;'; ?> </td>
                                <td class="<?php echo $classecart; ?>" style="text-align: center;"><?php echo ($detail->exist('ecart_rang'))? $detail->get('ecart_rang') : '&nbsp;'; ?> </td>
                                <td class="<?php echo $classparcelle ?>" style="<?php echo $styleparcelle; ?>"><?php echo ($detail->exist('mode_savoirfaire') && array_key_exists($detail->get('mode_savoirfaire'),ParcellaireClient::$modes_savoirfaire))? ParcellaireClient::$modes_savoirfaire[$detail->get('mode_savoirfaire')] : '&nbsp;'; ?></td>
                            </tr>
                            <?php
                        endforeach;

                        ?>
                    </tbody>
                </table>
    <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-xs-12">
            <p class="text-muted">
                Aucune parcelle n'a été déclarée pour cette année en Côtes de Provence.
            </p>
        </div>
    </div>
<?php endif; ?>
