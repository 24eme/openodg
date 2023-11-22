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
<br/><br/>
<br/><br/>
<strong>Objet : Avis de conformité controle vin</strong>
<br/><br/>
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
    <tr><td>a été prélevé pour un nouvel examen analytique et organoleptique. Celui-ci n'à relevé aucun manquement au cahier des charges de l'Appellation revendiquée.</td></tr>
    <br/>
    <tr><td>En conséquence la circulation du lot conccerné est autorisée à réception du présent courrier.</td></tr>
    <br/>
    <tr><td>Vous trouverez ci-joint une copie du rapport d'inspection correspondant.</td></tr>
    <br/>
    <br/>
    <tr><td>Nous vous adressons, Madame, Monsieur, nos sincères salutations.</td></tr>
</table>
