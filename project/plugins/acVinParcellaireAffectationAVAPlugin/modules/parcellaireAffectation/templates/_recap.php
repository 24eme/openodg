<?php
use_helper("Date");
use_helper("Float");
$last = $parcellaire->getParcellaireLastCampagne();
$lastParcellesKeysByAppellations = null;
if ($last) {
    $lastParcellesKeysByAppellations = $last->getAllParcellesKeysByAppellations()->getRawValue();
}
?>

<?php if($parcellaire->isIntentionCremant()): ?>
    <p class="text-muted">En plus des vos éventuelles parcelles déclarées dans votre affectation crémant, vous avez décidé de produire cette année du crémant dans les parcelles suivantes :</p>
<?php endif; ?>

<?php if (count($parcellaire->declaration->getAppellationsOrderParcellaire()) > 0): ?>
    <div class="row">
        <div class="col-xs-12">
            <?php
            foreach ($parcellaire->declaration->getAppellationsOrderParcellaire() as $kappellation => $appellation):
                ?>
                <?php if(!isset($notitle) || !$notitle): ?>
                <h3><strong> <?php echo "Appellation " . preg_replace('/AOC Alsace blanc/', 'AOC Alsace blanc VT/SGN', $appellation->getLibelleComplet()); ?></strong> <span class="small right" style="text-align: right;"><?php echo $appellation->getSuperficieTotale() . ' ares'; ?></span></h3>
                <?php endif; ?>
                <?php
                if (!$appellation->getSuperficieTotale()) {
                    echo "<i class='text-muted'>Vous n'avez pas affecté de parcelles pour cette appellation</i>";
                    continue;
                }
                ?>
                <table class="table table-striped table-condensed">
                    <thead>
                        <tr>
                            <th class="col-xs-4 text-center">Appellation</th>
                            <th class="col-xs-2 text-center">Commune</th>
                            <th class="col-xs-1 text-center">Section / Numéro</th>
                            <th class="col-xs-2 text-center">Lieu-dit</th>
                            <th class="col-xs-2 text-center">Cépage</th>
                            <th class="col-xs-1 text-center">Superficie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $appellation_details = $appellation->getDetailsSortedByParcelle();
                        foreach ($appellation_details as $detail):
                            if ($detail->isCleanable()) {
                                continue;
                            } ?>
                            <tr>
                                <td>
                                    <?php echo $detail->getAppellationLibelle(); ?>
                                </td>
                                <td>
                                    <?php echo $detail->getCommune(); ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php echo $detail->getSection(); ?> <?php echo $detail->getNumeroParcelle(); ?>
                                </td>
                                <td>
                                    <?php echo $detail->getLieuLibelle(); ?>
                                </td>
                                <td>
                                    <?php echo $detail->getCepageLibelle();  ?>
                                </td>
                                <td class="<?php echo $classsuperficie ?>" style="text-align: right;">
                                    <?php echoFloat($detail->getSuperficie()) ?> <small class="text-muted">ares</small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
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
                Aucune parcelle n'a été déclarée pour cette année.
            </p>
        </div>
    </div>
<?php endif; ?>
