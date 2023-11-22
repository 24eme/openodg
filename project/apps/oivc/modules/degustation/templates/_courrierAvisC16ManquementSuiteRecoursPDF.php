<?php use_helper("Date"); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('Float'); ?>

<style>
table {
    font-size: 12px;
}

th {
    font-weight: bold;
}
</style>
<?php include_partial('degustation/headerCourrier') ?>
<br/><br/>
<table style="width:1100px;padding-left:400px;" >
    <tr><td></td></tr>
    <tr><td><?php echo $etablissement->raison_sociale ?></td></tr>
    <tr><td><?php echo $etablissement->adresse ?></td></tr>
    <tr><td><?php echo $etablissement->code_postal .' '.$etablissement->commune ?></td></tr>
</table>
<br/>

<br/>
<br/>
<br/><br/>

<table style="text-align: center"><tr><td><strong>Objet : Avis de manquement suite a recours</strong></td></tr></table>
<br/><br/>


<table>
    <tr><td>Madame, Monsieur,</td></tr>
    <br/>
    <tr><td>Suite au nouvel examen analytique et/ou organoleptique pratiqué pour recours sur l'échantillon témoin d'un lot de votre cave :</td></tr>
    <br/>
    <tr><td>AOC, couleur, millésime : <strong><?php echo showProduitCepagesLot($lot, false) ?></strong></td></tr>
    <br/>
    <?php if ($lot->exist('quantite') && $lot->quantite) : ?>
        <tr><td>Cols : <?php echo $lot->exist('quantite') ? $lot->quantite : 0 ?></td></tr>
    <?php else: ?>
        <tr><td>Volume : <?php echo echoFloat($lot->volume) ?> hl</td></tr>
    <?php endif; ?>
    <br/>
    <tr><td>un manquement a été détecté : défaut <strong><?php echo $lot->getShortLibelleConformite() ?></strong></td></tr>
</table>

<br/>
<br/>

<table>
    <tr><td>Ce lot doit donc rester bloqué.</td></tr>
</table>
<br/>
<br/>
<table>
    <tr><td>Vous trouverez ci-joint le rapport d'inspection et la fiche de manquement correspondante à compléter en indiquant :</td></tr>
    <tr>
        <td>
            <ul>
                <li>Vos éventuelles observations</li>
                <li>Vos propositions de mesures de correction ainsi que votre souhait de délais pour le prochain prélèvement qui sera soumis à l'approbation de l'INAO.</li>
            </ul>
        </td>
</tr>
<br/>
<br/>
<tr><td>Vous avez également la possibilité de déclasser ce lot en adressant à votre ODG et à l'OIVC une déclaration de déclassement.</td></tr>
<br/>
<br/>
<tr><td>Merci de nous retourner la fiche de manquement dans un délai de 10 jours maximum à partir de la date d'envoi.</td></tr>
<br/>
<br/>
<tr><td>Veuillez recevoir, Madame, Monsieur, nos sincères salutations.</td></tr>
</table>

<br/><br/>
<br/><br/>
<br/><br/>
<table style="width:1100px;padding-left:400px;" >
    <tr><td><?php echo nl2br(Organisme::getInstance(null, 'degustation')->getResponsable()) ?></td></tr>
    <tr><td><?php if(file_exists(Organisme::getInstance(null, 'degustation')->getImageSignaturePath())): ?><img src="<?php echo Organisme::getInstance(null, 'degustation')->getImageSignaturePath() ?>"/><?php endif; ?></td></tr>
</table>
