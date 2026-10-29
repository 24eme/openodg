<?php use_helper("Date"); ?>
<?php use_helper('Float'); ?>

<?php include_partial('degustation/headerCourrier', ['courrier' => $courrier, "objet" => "Avis de manquement suite a nouveau controle vin"]) ?>

<p>Suite au nouvel examen analytique et/ou organoleptique d'un lot de votre cave :</p>

<p><strong><?php echo showProduitCepagesLot($lot, false, null) ?> de <?php if ($lot->exist('quantite') && $lot->quantite) : ?><?php echo $lot->exist('quantite') ? $lot->quantite : 0 ?> cols<?php else: ?><?php echoFloatFr($lot->volume*1) ?> hl<?php endif; ?> (échantillon n°<?php echo $lot->numero_archive ?>)</strong></p>

<p>Un manquement a été détecté : <strong>Défaut <?php echo $lot->getShortLibelleConformite() ?></strong></p>

<p>Ce lot doit donc rester bloqué.</p>

<p>Vous trouverez ci-joint le rapport d'inspection et la fiche de manquement correspondante.</p>

<p>Vous pouvez nous faire parvenir sous 10 jours maximum à partir de la date d'envoi vos éventuelles observations et demande de recours sur papier libre.</p>

<p>Conformément au Plan d'Inspection de l'Appellation, le dossier est transmis à l'INAO.</p>

<?php include_partial('degustation/footerCourrier', ['courrier' => $courrier]) ?>
