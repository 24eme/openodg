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
            <?php
            foreach ($parcellaire->declaration as $parcellesProduit):
                ?><h3><strong> <?php echo "Appellation " . preg_replace('/AOC Alsace blanc/', 'AOC Alsace blanc VT/SGN', $parcellesProduit->getLibelle()); ?></strong> <span class="small right" style="text-align: right;"><?php echo $parcellesProduit->getSuperficieTotale() . ' ares'; ?></span></h3>
                <?php
                if (!$parcellesProduit->getSuperficieTotale()) {
                    echo "<i class='text-muted'>Vous n'avez pas affecté de parcelles pour cette appellation</i>";
                    continue;
                }
                ?>
                <table class="table table-striped table-condensed">
                  <thead>
                    <tr>
                        <th class="col-xs-3" >Commune/Identifiant</th>
                        <th class="col-xs-2">Cépage</th>
                        <th class="col-xs-1">Superficie</th>
                        <th class="col-xs-2">Campagne Plantation</th>
                        <th class="col-xs-1">Écart Pieds</th>
                        <th class="col-xs-1">Écart Rang</th>
                        <th class="col-xs-2"style="text-align: right;" >Mode Savoir-faire</th>
                    </tr>
                  </thead>
                    <tbody>



                        <?php

                        $detailsHashes = array();
                        foreach ($parcellesProduit->detail as $detail):
                            if ($detail->isCleanable()) {
                                continue;
                            }
                            $detailsHashes[$detail->getHash()] = $detail->getHash();
                            $classline = '';
                            $styleline = '';
                            $styleproduit = '';
                            $styleparcelle = '';
                            $classparcelle = '';
                            $classsuperficie = '';
                            $stylesuperficie = '';
                            if (isset($diff) && $diff) {
                                if ($last && !$last->exist($detail->getHash())) {
                                    #$classline = 'success';
                                    $styleline = 'border-style: solid; border-width: 1px; border-color: darkgreen;';
                                } else {
                                    if ($last && $detail->getParcelleIdentifiant() != $last->get($detail->getHash())->getParcelleIdentifiant()) {
                                        $styleparcelle = 'border-style: solid; border-width: 1px; border-color: darkorange;';
                                        #$classparcelle = 'warning';
                                    }
                                    if ($last && $detail->getSuperficie() != $last->get($detail->getHash())->getSuperficie()) {
                                        $styleline = (!$detail->superficie) ? 'text-decoration: line-through; border-style: solid; border-width: 1px; border-color: darkred' : '';
                                        $classline = (!$detail->superficie) ? 'danger' : '';
                                        $stylesuperficie = (!$detail->superficie) ? 'border-style: solid; border-width: 1px; border-color: darkgreen' : 'border-style: solid; border-width: 1px; border-color: darkgreen';
                                        #$classsuperficie = (!$detail->superficie) ? 'danger' : 'warning';
                                    }
                                }
                                if (!$detail->getSuperficie()) {
                                    $stylesuperficie = 'border-style: solid; border-width: 1px; border-color: darkred';
                                    #$classsuperficie = 'danger';
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
                            ?>
                            <tr class="<?php echo $classline ?>" style="<?php echo $styleline; ?>">
                                <td class="col-xs-3" style="<?php echo $styleproduit; ?>">
                                    <?php echo $detail->getParcelleIdentifiant(); ?>
                                </td>
                                <td class="col-xs-2" style="<?php echo $styleproduit; ?>">
                                    <?php echo $detail->getCepageLibelle();  ?>
                                </td>
                                <td class="col-xs-1 <?php echo $classsuperficie ?>" style="text-align: right; <?php echo $stylesuperficie; ?>">
                                  <?php printf("%0.2f&nbsp;ares", $detail->superficie); ?>
                                </td>
                                <td class="col-xs-2" style="text-align: center;"><?php echo ($detail->exist('campagne_plantation'))? $detail->get('campagne_plantation') : '&nbsp;'; ?> </td>
                                <td class="col-xs-1" style="text-align: center;"><?php echo ($detail->exist('ecart_pieds'))? $detail->get('ecart_pieds') : '&nbsp;'; ?> </td>
                                <td class="col-xs-1" style="text-align: center;"><?php echo ($detail->exist('ecart_rang'))? $detail->get('ecart_rang') : '&nbsp;'; ?> </td>
                                <td class="col-xs-2 <?php echo $classparcelle ?>" style="text-align: right; <?php echo $styleparcelle; ?>"><?php echo ($detail->exist('mode_savoirfaire') && array_key_exists($detail->get('mode_savoirfaire'),ParcellaireClient::$modes_savoirfaire))? ParcellaireClient::$modes_savoirfaire[$detail->get('mode_savoirfaire')] : '&nbsp;'; ?></td>
                            </tr>
                            <?php
                        endforeach;

                        if ($lastParcellesKeysByAppellations && array_key_exists($parcellesProduit->gethash(), $lastParcellesKeysByAppellations)):
                            foreach ($lastParcellesKeysByAppellations[$parcellesProduit->gethash()] as $hashDetail => $detail):
                                if (!array_key_exists($hashDetail, $detailsHashes)):

                                    ?>
                                    <tr class="" style="opacity: 0.4">
                                        <td class="col-xs-3" style="text-decoration: line-through;">
                                            <?php echo $detail->getLieuLibelle(); ?>
                                        </td>
                                        <td class="col-xs-3" style="text-decoration: line-through;">
                                            <?php echo $detail->getCepage(); ?>
                                        </td>
                                        <td class="col-xs-1" style="text-align: center;"><?php echo ($detail->getVtsgn()) ? 'VT/SGN' : '&nbsp;'; ?> </td>
                                        <td class="col-xs-3" style="text-align: right; text-decoration: line-through;">
                                            <?php echo $detail->getParcelleIdentifiant(); ?>
                                        </td>
                                        <td class="col-xs-1" style="text-align: right; text-decoration: line-through;">
                                            <?php printf("%0.2f&nbsp;ares", $detail->superficie); ?>
                                        </td>
                                    </tr>
                                    <?php
                                endif;
                            endforeach;
                        endif;
                        ?>
                    </tbody>
                </table>
                <p class="text-muted">Ces raisins sont destinés à être vinifiés <?php
                    $libelledestination = array('SUR_PLACE' => 'sur place', 'CAVE_COOPERATIVE' => 'en caves coopératives', 'NEGOCIANT' => 'par des négociants');
                    $acheteurs = $parcellesProduit->getAcheteursNode();
                    $i = 0;
                    foreach ($acheteurs as $type => $acheteurs) {
                        if ($i > 0)
                            if ($i == count($acheteurs))
                                echo ' et ';
                            else
                                echo ', ';
                        $i++;
                        echo $libelledestination[$type] . " ";
                        if ($type != 'SUR_PLACE') {
                            echo "(";
                            $y = 0;
                            $nomAcheteurs = array();
                            foreach ($acheteurs as $cvi => $a) {
                                if (!array_key_exists($a->nom, $nomAcheteurs)) {
                                if ($y)
                                    echo ", ";
                                    print preg_replace('/ *\([^\)]*\) */', '', $a->nom);
                                    $y = 1;
                                    $nomAcheteurs[$a->nom] = $a->nom;
                                }
                            }
                            echo ")";
                        }
                    }
                    ?>.</p>
    <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-xs-12">
            <p class="text-muted">
                Aucune parcelle n'a été déclarée pour cette année en AOC Alsace Grand Cru, AOC Alsace Lieu-dit et AOC Alsace Communale.
            </p>
        </div>
    </div>
<?php endif; ?>
