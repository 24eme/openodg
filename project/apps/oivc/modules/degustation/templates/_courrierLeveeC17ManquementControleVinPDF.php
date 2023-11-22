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

<table style="text-align: center"><tr><td><strong>Objet : Levée de manquement controle vin</strong></td></tr></table>
<br/><br/>


<table>
    <tr><td>Madame, Monsieur,</td></tr>
    <br/>
    <tr><td>Le lot : </td></tr>
    <br/>
    <tr><td>AOC, couleur, millésime : <strong><?php echo showProduitCepagesLot($lot, false) ?></strong></td></tr>
    <br/>
    <?php if ($lot->exist('quantite') && $lot->quantite) : ?>
        <tr><td>Cols : <?php echo $lot->exist('quantite') ? $lot->quantite : 0 ?></td></tr>
    <?php else: ?>
        <tr><td>Volume : <?php echoFloat($lot->volume) ?> hl</td></tr>
    <?php endif; ?>
    <br/>
    <tr><td>a été prélevé pour un nouvel examen analytique et organoleptique. Celui-ci n'a relevé aucun manquement au cahier des charges de l'Appellation revendiquée.</td></tr>
    <br/>
    <tr><td>En conséquence, la circulation du lot concerné est autorisée à réception du présent courrier.</td></tr>
    <br/>
    <tr><td>Vous trouverez ci-joint une copie de la fiche de manquement correspondante.</td></tr>
    <br/>
    <br/>
    <tr><td>Nous vous adressons, Madame, Monsieur, nos sincères salutations.</td></tr>
</table>

<br/><br/>
<br/><br/>
<br/><br/>
<table style="width:1100px;padding-left:400px;" >
    <tr><td><?php echo nl2br(Organisme::getInstance(null, 'degustation')->getResponsable()) ?></td></tr>
    <tr><td><?php if(file_exists(Organisme::getInstance(null, 'degustation')->getImageSignaturePath())): ?><img src="<?php echo Organisme::getInstance(null, 'degustation')->getImageSignaturePath() ?>"/><?php endif; ?></td></tr>
</table>
