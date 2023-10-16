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

<table style="text-align: center"><tr><td><strong>Objet : Avis de manquement controle vin</strong></td></tr></table>
<br/><br/>


<table>
    <tr><td>Madame, Monsieur,</td></tr>
    <br/>
    <tr><td>Le lot : </td></tr>
    <br/>
    <tr><td>AOC, couleur, millésime : <strong><?php echo showProduitCepagesLot($lot, false) ?></strong></td></tr>
    <br/>
    <tr><td>Volume : <?php echo $lot->volume ?> hl</td></tr>
    <tr><td>Cols : 0</td></tr>
    <br/>
    <tr><td>Représenté par l'échantillon témoin pour lequel vous avez demandé un recours, n'a relevé aucun manquement qu cahier des charges de l'Appellation revendiquée.</td></tr>
    <br/>
    <tr><td>En conséquence, la circulation du lot concerné est autorisée à réception du présent courrier.</td></tr>
    <br/>
    <tr><td>Vous trouverez ci-joint une copie du rapport d'inspection corresponsant.</td></tr>
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
