<?php
use_helper("Date");
use_helper("Float");
$last = $parcellaire->getAffectationLastCampagne();
$lastParcellesKeysByAppellations = null;
if ($last) {
    $lastParcellesKeysByAppellations = $last->getAllParcellesKeysByAppellations()->getRawValue();
}
?>

<?php if($parcellaire->isIntentionCremant()): ?>
    <p class="text-muted">En plus de vos éventuelles parcelles déclarées dans votre affectation crémant, vous avez décidé de produire cette année du crémant dans les parcelles suivantes :</p>
<?php endif; ?>

<?php if (count($parcellaire->declaration->getAppellationsOrderParcellaire()) > 0): ?>
            <?php foreach ($parcellaire->declaration->getAppellationsOrderParcellaire() as $kappellation => $appellation): ?>
                <?php if(!isset($notitle) || !$notitle): ?>
                <h3><strong> <?php echo "Appellation " . preg_replace('/AOC Alsace blanc/', 'AOC Alsace blanc VT/SGN', $appellation->getLibelleComplet()); ?></strong></h3>
                <?php endif; ?>
                <?php
                if (!$appellation->getSuperficieTotale(ParcellaireClient::PARCELLAIRE_SUPERFICIE_UNIT_ARE)) {
                    echo "<i class='text-muted'>Vous n'avez pas affecté de parcelles pour cette appellation</i>";
                    continue;
                }
                ?>
                <table style="margin-bottom: 0;" class="table table-striped table-condensed">
                    <thead>
                        <tr>
                            <th class="col-xs-4 text-center">Appellation</th>
                            <th class="col-xs-2 text-center">Commune</th>
                            <th class="col-xs-1 text-center">Section / Numéro</th>
                            <th class="col-xs-2 text-center">Lieu-dit revendiqué
                                <?php if(is_object($appellation) && (strpos($appellation->getHash(), 'CREMANT') === null || strpos($appellation->getHash(), 'CREMANT') === false)  && strpos($appellation->getHash(), 'LIEUDIT')): ?>
                                    <p class="small text-muted" style="margin:0;">Lieu-dit cadastral</p>
                                <?php endif; ?>
                            </th>

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
                                <td class="text-right">
                                    <?php echo $detail->getSection(); ?> <?php echo $detail->getNumeroParcelle(); ?>
                                </td>
                                <td>
                                    <?php echo $detail->getLieuLibelle() ? $detail->getLieuLibelle() : '<p style="margin:0;"> - </p>'; ?>
                                    <?php if($detail->getLieuDitCadastral() && strpos($detail->getProduitHash(), 'LIEUDIT')) : ?>
                                        <p class="small text-muted" style="margin:0;"><?php echo $detail->getLieuDitCadastral() ?></p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $detail->getCepageLibelle();  ?>
                                </td>
                                <td class="text-right">
                                    <?php echoFloat($detail->getSuperficie(ParcellaireClient::PARCELLAIRE_SUPERFICIE_UNIT_ARE)) ?> <small class="text-muted">ares</small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tfoot>
                                <tr>
                                    <th colspan="4">Superficie totale affectable de l'appellation</th>
                                    <th colspan="2" class="text-right"><?php echoFloat($appellation->getSuperficieTotale(ParcellaireClient::PARCELLAIRE_SUPERFICIE_UNIT_ARE)) ?> <small class="text-muted">ares</small></th>
                                </tr>
                            </tfoot>
                    </tbody>
                </table>
                <p class="text-muted" style="margin-top: 10px;">Ces raisins sont destinés à être vinifiés <?php
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
    <div class="row">
        <div class="col-sm-6">
            <?php include_component('parcellaire', 'syntheseParCepages', array('parcellaire' => $parcellaire)); ?>
        </div>
        <div class="col-sm-6">
            <?php $syntheseDestination = $parcellaire->getSyntheseDestination() ?>
            <?php if (count($syntheseDestination)): ?>
            <h3 class="mt-0">Synthèse par destinations</h3>

            <table class="table table-bordered table-condensed table-striped tableParcellaire">
              <thead>
                <tr>
                    <th class="col-xs-8">Destination</th>
                    <th class="col-xs-4 text-center" colspan="2">Superficie <span class="text-muted small"><?php echo (ParcellaireConfiguration::getInstance()->isAres()) ? "(a)" : "(ha)" ?></span></th>
                </tr>
              </thead>
              <tbody>
                  <?php       $libelledestination = array('SUR_PLACE' => 'Sur place', 'CAVE_COOPERATIVE' => 'Caves coopératives', 'NEGOCIANT' => 'Négociants'); ?>

              <?php foreach($syntheseDestination as $destination => $s): ?>

                <tr>
                        <td><?php echo $libelledestination[$destination] ; ?></td>
                        <td class="text-right"><?php echoSuperficie($s); ?></td>
                </tr>
              <?php endforeach;?>
              <tr>
                    <td><strong>Total</strong></td>
                    <td class="text-right"><strong><?php echoSuperficie(array_sum($syntheseDestination->getRawValue())); ?></strong></td>
                </tr>
              </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <p class="text-muted">
        Aucune parcelle n'a été déclarée pour cette année.
    </p>
<?php endif; ?>
