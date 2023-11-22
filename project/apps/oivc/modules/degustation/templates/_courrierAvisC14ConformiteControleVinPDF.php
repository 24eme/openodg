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
    <tr><td></td></tr>
    <tr><td>Le <?php echo $courrier->getDateFormat('d/m/Y') ?></td></tr>
</table>
<br/>
<br/>
<br/>
<strong>Objet : Avis de conformité controle vin</strong>
<p></p>

<p>Madame, Monsieur,</p>

<p>Le lot <strong><?php echo showProduitCepagesLot($lot, false, null) ?> de <?php if ($lot->exist('quantite') && $lot->quantite) : ?><?php echo $lot->exist('quantite') ? $lot->quantite : 0 ?><?php else: ?><?php echoFloat($lot->volume*1) ?> hl<?php endif; ?> (échantillon n°<?php echo $lot->numero_archive ?>)</strong> a été prélevé pour un examen analytique et organoleptique. Celui-ci n'à relevé aucun manquement au cahier des charges de l'Appellation revendiquée.</p>

<p>En conséquence la circulation du lot conccerné est autorisée à réception du présent courrier.</p>

<p>Vous trouverez ci-joint une copie du rapport d'inspection correspondant.</p>

<p>Nous vous adressons, Madame, Monsieur, nos sincères salutations.</p>

<p></p>
<p></p>

<table style="width:1100px; padding-left:400px;" ><tr><td><?php echo nl2br(str_replace(",", "&nbsp;&nbsp;&nbsp;", Organisme::getInstance(null, 'degustation')->getResponsable())) ?></td></tr></table>
