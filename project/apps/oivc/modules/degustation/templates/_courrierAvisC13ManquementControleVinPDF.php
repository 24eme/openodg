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
    <tr><td><?php echo $etablissement->adresse_complementaire ?></td></tr>
    <tr><td><?php echo $etablissement->code_postal .' '.$etablissement->commune ?></td></tr>
</table>
<br/>

<br/>
<br/>
<table><tr><td style="width: 324px;"><?php echo 'Le ' . format_date(date('Y-m-d'), "P", "fr_FR"); ?></td></tr></table>
<br/><br/>

<table style="text-align: center"><tr><td><strong>Objet : Avis de manquement controle vin</strong></td></tr></table>
<br/><br/>


<table>
    <tr><td>Madame, Monsieur,</td></tr>
    <br/>
    <tr><td>Suite à l'examen analytique et/ou organoleptique d'un lot de votre cave :</td></tr>
    <tr><td><strong>AOC <?php echo showProduitCepagesLot($lot, false) ?></strong></td></tr>
    <tr><td></td></tr>
    <tr><td>Volume : <?php echo $lot->volume ?> hl</td></tr>
    <tr><td>Cols : 0</td></tr>
    <tr><td></td></tr>
    <tr><td>un manquement a été détecté : défaut <strong><?php echo $lot->getShortLibelleConformite() ?></strong></td></tr>
    <tr><td>avec pour motif : <?php echo $lot->getMotif() ?></td></tr>
</table>

<br/>
<br/>

<table>
    <tr><td>Ce lot doit donc rester bloqué.</td></tr>
</table>

<br/>
<br/>

<table>
    <tr><td>Vous trouverez ci joint le rapport d'inspection et la fiche de manquement correspondante à compléter en indiquant :</td></tr>
    <tr><td>
            <ul>
                <li>Vos éventuelles observations</li>
                <li>Vos propositions de mesures de correction ainsi que votre souhait de délais pour le prochain prélèvement qui seront soumis à l'approbation de l'INAO</li>
                <li>Votre demande éventuelle de recours</li>
            </ul>
        </td></tr>
</table>

<br/>
<br/>

<table>
    <tr><td>Vous avez également la possibilité de déclasser ce lot en adressant à votre ODG et à l'OIVC une déclaration de déclassement</td></tr>
</table>

<br/>
<br/>

<table>
    <tr><td>Merci de nous retourner la fiche de manquement dans un délai de 10 jours maximum à partir de la date d'envoi.</td></tr>
</table>

<br/>
<br/>

<table>
    <tr><td>Nous restons à votre disposition et vous adressons, Madame, Monsieur, nos sincères salutations.</td></tr>
</table>

<br/><br/>
<br/><br/>
<br/><br/>
<table style="width:1100px;padding-left:400px;" >
    <tr><td><?php echo nl2br(Organisme::getInstance(null, 'degustation')->getResponsable()) ?></td></tr>
    <tr><td><?php if(file_exists(Organisme::getInstance(null, 'degustation')->getImageSignaturePath())): ?><img src="<?php echo Organisme::getInstance(null, 'degustation')->getImageSignaturePath() ?>"/><?php endif; ?></td></tr>
</table>
