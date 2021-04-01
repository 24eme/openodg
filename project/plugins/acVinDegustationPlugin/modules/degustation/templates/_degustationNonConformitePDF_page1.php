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

<table><tr><td><strong>Objet :</strong> Résultats contrôles organoleptiques <strong>non conformes</strong></td></tr></table>
<br/><br/>


<table>
  <tr><td>Madame, Monsieur,</td></tr>
  <br/>
  <tr><td>Lors de la séance de dégustation du <strong><?php echo format_date($degustation->date, "P", "fr_FR"); ?></strong>, certains de vos vins dont la liste figure dans les fiches de non conformité ci-jointes ont été ajournés.</td></tr>
  <tr><td>Compte tenu de ce résultat, vous pouvez décider :</td></tr><br/>
  <tr><td>
    <ul>
      <li>Soit d’abandonner volontairement la dénomination correspondante en nous communiquant par fax, courrier ou mail, une déclaration de déclassement au moyen de la fiche jointe.</li><br/>
      <li>Soit d’exécuter l’action corrective recommandée dans la fiche de non-conformité ci jointe. Dans cette hypothèse, votre vin pourra être soumis à une deuxième dégustation après que nous ayons procédé à un nouveau prélèvement, et ce dans un délai minimum de 15 jours à compter de la réception de votre demande de nouvelle présentation. Notez qu’après travail le vin concerné par la deuxième présentation peut être relogé&nbsp;:
        <ul>
          <li>Dans la même cuve</li>
          <li>Dans une autre cuve d’un volume inférieur ou égal au volume initial,</li>
          <li>Dans d’autres cuves d’un volume total inférieur ou égal au volume initial.</li>
        </ul>
      </li>
    </ul>
  </td></tr>
</table><br/><br/>

<table>
  <tr><td>Dans tous les cas, il vous appartient de nous retourner, par fax ou par courrier, daté et signé chacun des documents joints avec la mention de votre décision :&nbsp;<strong>Demande de déclassement ou nouvelle présentation</strong>.<br/><br/>Dans cette dernière hypothèse, vous voudrez bien nous retourner la fiche de non-conformité accompagnée d’un nouveau bulletin d’analyse</td></tr><br/>
  <tr><td>Nous vous prions de croire, Madame, Monsieur, en l’expression de nos sentiments les meilleurs.</td></tr><br/>
  <tr><td>Pour toutes informations, merci de nous contacter.</td></tr><br/>
</table>

<br/><br/>
<br/><br/>
<br/><br/>
<table style="width:1100px;padding-left:400px;" >
  <tr><td><?php echo $courrierInfos['responsable'] ?></td></tr>
</table>
