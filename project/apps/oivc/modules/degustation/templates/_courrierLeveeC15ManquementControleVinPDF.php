<?php include_partial('degustation/headerCourrier', ['courrier' => $courrier, "objet" => "Levée de manquement controle vin"]) ?>

<p>Le lot :</p>

<p><strong><?php echo showProduitCepagesLot($lot, false, null) ?> de <?php if ($lot->exist('quantite') && $lot->quantite) : ?><?php echo $lot->exist('quantite') ? $lot->quantite : 0 ?> cols<?php else: ?><?php echoFloatFr($lot->volume*1) ?> hl<?php endif; ?> (échantillon n°<?php echo $lot->numero_archive ?>)</strong></p>

<p>Représenté par l'échantillon témoin pour lequel vous avez demandé un recours, n'a relevé aucun manquement qu cahier des charges de l'Appellation revendiquée.</p>

<p>En conséquence, la circulation du lot concerné est autorisée à réception du présent courrier.</p>

<p>Vous trouverez ci-joint une copie du rapport d'inspection corresponsant.</p>

<?php include_partial('degustation/footerCourrier', ['courrier' => $courrier]) ?>
