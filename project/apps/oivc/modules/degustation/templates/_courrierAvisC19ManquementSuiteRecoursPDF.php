<?php use_helper("Date"); ?>
<?php use_helper('Lot'); ?>

<style>
table {
    font-size: 12px;
}

th {
    font-weight: bold;
}
</style>

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

<table style="text-align: center"><tr><td><strong>Objet : Avis de manquement suite a recours INAO</strong></td></tr></table>
<br/><br/>


<table>
    <tr><td>Madame, Monsieur,</td></tr>
    <br/>
    <tr><td>Suite au nouvel examen analytique et/ou organoleptique pratiqué pour recours sur l'échantillon témoin d'un lot de votre cave :</td></tr>
    <br/>
    <tr><td>AOC, couleur, millésime : <strong><?php echo showProduitCepagesLot($lot, false) ?></strong></td></tr>
    <br/>
    <tr><td>Volume : <?php echo $lot->volume ?> hl</td></tr>
    <tr><td>Cols : 0</td></tr>
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
    <tr><td>Vous trouverez ci-joint le rapport d'inspection correspondant.</td></tr>
<br/>
<br/>
<tr><td>Vous pouvez nous faire parvenir sous 10 jours maximum à partir de la date d'envoi vos éventuelles observations.</td></tr>
<br/>
<br/>
<tr><td>Conformément au Plan d'Inspection de l'Appellation, le dossier est transmis à l'INAO.</td></tr>
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
