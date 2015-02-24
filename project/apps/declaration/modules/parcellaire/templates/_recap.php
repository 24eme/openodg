<?php
use_helper("Date");
$last = $parcellaire->getParcellaireLastCampagne();
$lastParcellesKeysByAppellations = null;
if ($last) {
    $lastParcellesKeysByAppellations = $last->getAllParcellesKeysByAppellations()->getRawValue();
}
?>
<?php if(count($parcellaire->declaration->getAppellationsOrderParcellaire()) > 0): ?>
<div class="row">
    <div class="col-xs-12">
        <?php
        foreach ($parcellaire->declaration->getAppellationsOrderParcellaire() as $kappellation => $appellation):
            ?><h3><strong> <?php echo "Appellation " . $appellation->getLibelleComplet(); ?></strong> <span class="small right" style="text-align: right;"><?php echo $appellation->getSuperficieTotale() . ' (ares)'; ?></span></h3>
            <?php
            if (!$appellation->getSuperficieTotale()) {
                echo "<i class='text-muted'>Vous n'avez pas affecté de parcelles pour cette appellation</i>";
                continue;
            }
            ?>
            <table class="table table-striped table-condensed">
                <tbody>
                    <?php
                    $appellation_details = $appellation->getDetailsSortedByParcelle();
                    $detailsHashes = array();
                    foreach ($appellation_details as $detail):
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
                                $classline = 'success';
                                $styleline = 'border-style: solid; border-width: 1px; border-color: darkgreen;';
                            } else {
                                if ($last && $detail->getParcelleIdentifiant() != $last->get($detail->getHash())->getParcelleIdentifiant()) {
                                    $styleparcelle = 'border-style: solid; border-width: 1px; border-color: darkorange;';
                                    $classparcelle = 'warning';
                                }
                                if ($last && $detail->getSuperficie() != $last->get($detail->getHash())->getSuperficie()) {
                                    $styleline = (!$detail->superficie) ? 'text-decoration: line-through; border-style: solid; border-width: 1px; border-color: darkred' : '';
                                    $classline = (!$detail->superficie) ? 'danger' : '';
                                    $stylesuperficie = (!$detail->superficie) ? 'border-style: solid; border-width: 1px; border-color: darkred' : 'border-style: solid; border-width: 1px; border-color: darkorange';
                                    $classsuperficie = (!$detail->superficie) ? 'danger' : 'warning';
                                }
                            }
                            if (!$detail->getSuperficie()) {
                                $stylesuperficie = 'border-style: solid; border-width: 1px; border-color: darkred';
                                $classsuperficie = 'danger';
                            }
                        }
                        ?>
                        <tr class="<?php echo $classline ?>" style="<?php echo $styleline; ?>">
                            <td class="col-xs-3" style="<?php echo $styleproduit; ?>">
                                <?php echo $detail->getLieuLibelle(); ?>
                            </td>   
                            <td class="col-xs-3" style="<?php echo $styleproduit; ?>">
                                <?php
                                echo $detail->getCepageLibelle();
                                ;
                                ?>
                            </td>   
                            <td class="col-xs-4 <?php echo $classparcelle ?>" style="text-align: right; <?php echo $styleparcelle; ?>">
                                <?php echo $detail->getParcelleIdentifiant(); ?>
                            </td>   
                            <td class="col-xs-2 <?php echo $classsuperficie ?>" style="text-align: right; <?php echo $stylesuperficie; ?>">
                                <?php echo $detail->superficie . '&nbsp;ares'; ?>
                            </td>   
                        </tr> 
                        <?php
                    endforeach;

                    if($lastParcellesKeysByAppellations && array_key_exists($appellation->gethash(), $lastParcellesKeysByAppellations)):
                    foreach ($lastParcellesKeysByAppellations[$appellation->gethash()] as $hashDetail => $detail):
                        if (!array_key_exists($hashDetail, $detailsHashes)):
                            ?>
                        <tr class="" style="opacity: 0.5">
                            <td class="col-xs-3" style="text-decoration: line-through;">
            <?php echo $detail->getLieuLibelle(); ?>
                                </td>   
                                <td class="col-xs-3" style="text-decoration: line-through;">
                                    <?php echo $detail->getCepageLibelle(); ?>
                                </td>   
                                <td class="col-xs-4" style="text-align: right;">
            <?php echo $detail->getParcelleIdentifiant().' (supprimé)'; ?>
                                </td>   
                                <td class="col-xs-2" style="text-align: right; text-decoration: line-through;">
<?php printf("%0.2f", $detail->superficie) . '&nbsp;ares'; ?>
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
                $acheteurs = $appellation->getAcheteursNode();
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
                        foreach ($acheteurs as $cvi => $a) {
                            if ($y)
                                echo ", ";
                            print $a->nom;
                            $y = 1;
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