<?php include_partial('degustation/headerCourrier', ['courrier' => $courrier, "objet" => "Avis de manquement suite a recours INAO"]) ?>

<p>Suite au nouvel examen analytique et/ou organoleptique pratiqué pour recours sur l'échantillon témoin d'un lot de votre cave :</p>

<p><strong><?php echo showProduitCepagesLot($lot, false, null) ?> de <?php if ($lot->exist('quantite') && $lot->quantite) : ?><?php echo $lot->exist('quantite') ? $lot->quantite : 0 ?> cols<?php else: ?><?php echoFloatFr($lot->volume*1) ?> hl<?php endif; ?> (échantillon n°<?php echo $lot->numero_archive ?>)</strong></p>

<p>Un manquement a été détecté : <strong>Défaut <?php echo $lot->getShortLibelleConformite() ?></strong></p>

<p>Ce lot doit donc rester bloqué.</p>

<p>Vous trouverez ci-joint le rapport d'inspection correspondant.</p>

<p>Vous pouvez nous faire parvenir sous 10 jours maximum à partir de la date d'envoi vos éventuelles observations.</p>

<p>Conformément au Plan d'Inspection de l'Appellation, le dossier est transmis à l'INAO.</p>

<?php include_partial('degustation/footerCourrier', ['courrier' => $courrier]) ?>
