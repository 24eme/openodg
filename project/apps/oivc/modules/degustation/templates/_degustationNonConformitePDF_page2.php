<?php use_helper("Date"); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('TemplatingPDF'); ?>

<style>
    <?php echo style(); ?>
    table {
        font-size: 12px;
    }

    th {
        font-weight: bold;
    }
</style>
<br/>
<br/>

<table>
    <tr>
        <td style="width: 70%">Date de rédaction : <?php echo format_date($degustation->date, "P", "fr_FR"); ?></td>
        <td style="width: 30%">N° : <?php echo $lot->unique_id; ?></td>
    </tr>
</table>

<br/><br/>

<table style="text-align: center"><tr><td><strong>Fiche De Manquement Contrôle Produit</strong></td></tr></table>

<br/><br/>

<table><tr><td>Nom opérateur : <?php echo $etablissement->raison_sociale ?></td></tr></table>

<br/><br/>

<table><tr><td><strong>N° de rapport d'inspection correspondant : </strong></td></tr></table>

<br/>
<br/>

<table border="1">
    <tr><td>À REMPLIR PAR L'OPÉRATEUR</td></tr>
    <tr><td>Éventuelles observations :<br/><br/><br/><br/><br/><br/><br/></td></tr>
    <tr><td>Mesure(s) de correction proposée(s) :<br/><br/><br/><br/><br/><br/><br/></td></tr>
    <tr><td>Je souhaite que le lot soit reprélevé au mois de :</td></tr>
    <tr><td>Je souhaite exercer un recours : OUI <?php echo echoCheck(null, false) ?></td></tr>
    <tr><td>Nom du responsable de l'entreprise :<br/><br/>Date et signature :<br/><br/></td></tr>
</table>

<br/>
<br/>

<table border="1">
    <tr><td colspan="5">PARTIE RÉSERVÉE À L'OIVC</td></tr>
    <tr><td colspan="5">RÉSULTAT DU CONTRÔLE</td></tr>
    <tr style="text-align: center">
        <td></td> <td>Date</td> <td>Conforme</td> <td>Non conforme</td> <td>Libellé manquement / Code manquement</td>
    </tr>
    <tr>
        <td>Examen analytique<br/>(sous traitance)</td> <td></td> <td><?php echo echoCheck(null, false); ?></td> <td><?php echo echoCheck(null, true); ?></td> <td></td>
    </tr>
    <tr>
        <td>Examen organoleptique<br/></td> <td><?php echo $degustation->getDate(); ?></td> <td><?php echo echoCheck(null, true); ?></td> <td><?php echo echoCheck(null, false); ?></td> <td></td>
    </tr>
    <tr><td colspan="5">Date transmission INAO :</td></tr>
</table>

<br/><br/>

<table>
<tr>
  <td style="width: 50%"><strong>Nom du responsable d'inspection :</strong></td>
  <td style="width: 25%"><strong>Date :</strong></td>
  <td style="width: 25%"><strong>Signature :</strong></td>
</tr>
</table>

<br/><br/>
<br/><br/>

<table><tr><td>Fiche à retourner à l'OIVC par courrier au <?php echo Organisme::getInstance()->getAdresse() ?></td></tr></table>
