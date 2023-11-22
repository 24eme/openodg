<?php include_partial('degustation/headerCourrier', ['courrier' => $courrier, "objet" => "Avis de manquement suite a recours"]) ?>

<p>Suite au nouvel examen analytique et/ou organoleptique pratiqué pour recours sur l'échantillon témoin d'un lot de votre cave :</p>

<p><strong><?php echo showProduitCepagesLot($lot, false, null) ?> de <?php if ($lot->exist('quantite') && $lot->quantite) : ?><?php echo $lot->exist('quantite') ? $lot->quantite : 0 ?> cols<?php else: ?><?php echoFloatFr($lot->volume*1) ?> hl<?php endif; ?> (échantillon n°<?php echo $lot->numero_archive ?>)</strong></p>

<p>Un manquement a été détecté : <strong>Défaut <?php echo $lot->getShortLibelleConformite() ?></strong></p>

<p>Ce lot doit donc rester bloqué.</p>

<p>Vous trouverez ci-joint le rapport d'inspection et la fiche de manquement correspondante à compléter en indiquant :<br />
<br />
<ul>
    <li>Vos éventuelles observations</li>
    <li>Vos propositions de mesures de correction ainsi que votre souhait de délais pour le prochain prélèvement qui sera soumis à l'approbation de l'INAO.</li>
</ul>
</p>

<p>Vous avez également la possibilité de déclasser ce lot en adressant à votre ODG et à l'OIVC une déclaration de déclassement.<p>

<p>Merci de nous retourner la fiche de manquement dans un délai de 10 jours maximum à partir de la date d'envoi.</p>

<p>Nous restons à votre disposition.</p>

<?php include_partial('degustation/footerCourrier', ['courrier' => $courrier]) ?>
