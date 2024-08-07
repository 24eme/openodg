<?php include_partial('degustation/headerCourrier', ['courrier' => $courrier, "objet" => "Levée de manquement controle vin"]) ?>

<p>Le lot <strong><?php echo showProduitCepagesLot($lot, false, null) ?> de <?php if ($lot->exist('quantite') && $lot->quantite) : ?><?php echo $lot->exist('quantite') ? $lot->quantite : 0 ?> cols<?php else: ?><?php echoFloatFr($lot->volume*1) ?> hl<?php endif; ?> (échantillon n°<?php echo $lot->numero_archive ?>)</strong> a été prélevé pour un nouvel examen analytique et organoleptique. Celui-ci n'a relevé aucun manquement au cahier des charges de l'Appellation revendiquée.</p>

<p>En conséquence, la circulation du lot concerné est autorisée à réception du présent courrier.</p>

<p>Vous trouverez ci-joint une copie de la fiche de manquement correspondante.</p>

<?php include_partial('degustation/footerCourrier', ['courrier' => $courrier]) ?>
