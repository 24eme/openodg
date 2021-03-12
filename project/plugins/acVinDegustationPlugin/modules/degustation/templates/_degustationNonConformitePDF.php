<?php use_helper("Date"); ?>
<?php use_helper('TemplatingPDF'); ?>
<?php $adresse = sfConfig::get('app_degustation_courrier_adresse'); ?>
<style>
</style>

<br/><br/>
<table style="width:1100px;font-size:13px;padding-left:400px;" >
  <tr>
    <td><strong><?php echo $etablissement->raison_sociale ?></strong></td>
  </tr>
  <tr>
    <td><strong><?php echo $etablissement->adresse ?></strong></td>
  </tr>
  <tr>
    <td><strong><?php echo $etablissement->code_postal .' '.$etablissement->commune ?></strong></td>
  </tr>
</table>
<br/>

<br/>
<br/>

<table style="font-size:12px;">
  <tr>
    <td>Fax : <?php echo $etablissement->fax ?> - Courriel : <?php echo $etablissement->email ?></td>
  </tr>
  <br/>
  <tr>
    <td>Réf : <?php echo $etablissement->cvi[0];echo $etablissement->cvi[1] .' '. $etablissement->cvi[2],$etablissement->cvi[3],$etablissement->cvi[4].' '.$etablissement->cvi[5],$etablissement->cvi[6],$etablissement->cvi[7],$etablissement->cvi[8],$etablissement->cvi[9] ?></td>
  </tr>
</table>
<br/>
<br/>
<table style="font-size:12px;"><tr><td style="width: 324px;"><?php echo 'Aix-en-Provence, le ' . format_date(date('Y-m-d'), "P", "fr_FR"); ?></td></tr></table>
<br/><br/>

<table style="font-size:12px;"><tr><td>Objet : Résultats contrôles organoleptiques<strong> non conformes </strong></td></tr></table>
<br/><br/>


<table style="font-size:12px;">
  <tr><td>Madame, Monsieur,</td></tr>
  <br/>
  <tr><td>Lors de la séance de dégustation du <strong><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR")); ?></strong>, certains de vos vins dont la liste figure dans les fiches de non conformité ci-jointes ont été ajournés.</td></tr>
  <tr><td>Compte tenu de ce résultat, vous pouvez décider :</td></tr><br/>
  <tr><td>
    <ul>
      <li>Soit d’abandonner volontairement la dénomination correspondante en nous communiquant par fax, courrier ou mail, une déclaration de déclassement au moyen de la fiche jointe.</li><br/>
      <li>Soit d’exécuter l’action corrective recommandée dans la fiche de non-conformité ci jointe. Dans cette hypothèse, votre vin pourra être soumis à une deuxième dégustation après que nous ayons procédé à un nouveau prélèvement, et ce dans un délai minimum de 15 jours à compter de la réception de votre demande de nouvelle présentation. Notez qu’après travail le vin concerné par la deuxième présentation peut être relogé :
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
  <tr><td>Nous vous prions de croire, Monsieur, Madame, en l’expression de nos sentiments les meilleurs.</td></tr><br/>
  <tr><td>Pour toutes informations, vous pouvez nous contacter :</td></tr><br/>
  <tr><td><?php echo $adresse['telephone'] ?></td></tr><br/>
</table>

<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>

<table style="text-align:center;font-size:10px;">
  <tr>
    <td><?php echo $adresse['raison_sociale'] ?></td>
  </tr>
  <tr>
    <td><?php echo $adresse['adresse'].' - '. $adresse['cp_ville'] ?> </td>
  </tr>
  <tr>
    <td><?php echo $adresse['telephone'] ?></td>
  </tr>
  <tr>
    <td></td>
  </tr>
</table>


<table border="1" style="text-align:center;padding:10px;">
  <tr><td>FICHE DE NON CONFORMIT&Eacute;</td></tr>
  <tr><td>Lot non conforme à la dégustation, <strong><?php echo $lot->getTextPassage(); ?></strong> passage</td></tr>
  <tr><td>N° : NC - Bouches du Rhône - AIX 1312</td></tr>
</table>

<table style="font-size:12px;">
  <tr><td>Identité opérateur : <?php echo $etablissement->raison_sociale ?></td></tr>
  <tr><td>Adresse : <?php echo $etablissement->adresse .' '. $etablissement->adresse_complementaire .' '. $etablissement->code_postal .' '.$etablissement->commune ?></td></tr>
  <tr><td>Tél : <?php echo $etablissement->telephone_bureau .' - Fax : '.$etablissement->fax .' - Courriel : '. $etablissement ->email ?></td></tr><br/>

  <tr><td>N°immatriculation CVI : <?php echo $etablissement->cvi .' - N°SIRET : '. $etablissement->siret  ?></td></tr>
  <tr><td>Commission de Dégustation réunie le : <?php echo date('d/m/Y',strtotime($degustation->date)) .' '. $degustation->lieu ?></td></tr>
</table>

<p><strong>Lot Concerné par la Non-Conformité : <?php echo (int)$lot->numero_logement_operateur ?></strong></p>

<table border="3" cellpadding=0 cellspacing=0 style="text-align: center;font-size:12px;">
  <tr>
    <th style="font-weight:bold">N°Dos.</th>
    <th style="font-weight:bold">N° de lot OPERATEUR</th>
    <th style="font-weight:bold">Logement<br/>(Cuve)</th>
    <th style="font-weight:bold">IGP<br/>Couleur</th>
    <th style="font-weight:bold">Cépage</th>
    <th style="font-weight:bold">Millés.</th>
    <th style="font-weight:bold">Volume<br/>(HI)</th>
    <th style="font-weight:bold">Décision/<br/>Observation</th>
  </tr>
  <tr>
    <td><?php echo $lot->numero_dossier ?></td>
    <td><?php echo $lot->numero_archive ?></td>
    <td><?php echo $lot->numero_logement_operateur ?></td>
    <td><?php echo $lot->produit_libelle ?></td>
    <td><?php echo $lot->details ?></td>
    <td><?php echo $lot->millesime ?></td>
    <td><?php echo sprintf("%.2f", $lot->volume) ?></td>
    <td><?php echo $lot->observation ?></td>
  </tr>
</table>

<table border="2">
  <tr>
    <td>
      <span>Description de l'anomalie (<?php echo $lot->getTextPassage(); ?> passage)</span><br/><br/>
      <strong><span>Gravité : <?php echo $lot->conformite ?></span></strong><br/>
      <strong><span>Vin <?php echo $lot->statut ?> en IGP. Défauts constatés : <?php $lot->observation ?></span></strong>
    </td>
  </tr>
  <tr>
    <td>
      <strong><span>Action corrective proposé</span></strong>
      <ul>
        <li>Mise en place d'une pratique oenologique permettant la disparition du défaut constaté</li>
        <li>Déclassement du lot concerné en vin sans indication géographique (Vin de France)</li>
      </ul>
    </td>
  </tr>
  <tr>
    <td style="font-weight:bold;">Date d'envoi fiche</td>
    <td style="font-weight:bold;">Date de Notification :</td>
    <td style="font-weight:bold;">Signature du responsable de l'ODG :</td>
  </tr>
</table>

<table border="1">
<tr>
  <td style="font-weight:bold;">Date d'envoi fiche<br/></td>
  <td style="font-weight:bold;">Date de Notification :<br/></td>
  <td style="font-weight:bold;">Signature du responsable de l'ODG :<br/></td>
</tr>
</table>
<p><strong>Décision de l'opérateur : à <i>Remplir</i> par l'opérateur et à retourner à l'ODG.</strong></p>

<table border="0.5">
  <tr>
    <td style="padding:10px;"><p>Déclassement du lot : <strong>Seconde</strong> présentation</p></td>
  </tr>
  <tr>
    <td>
      <table border="1">
        <tr>
          <td>Date de la décision : <br/></td>
          <td><br/></td>
        </tr>
      </table>
      <table border="1">
        <tr>
          <td style="height:100px;">Signature de l'opérateur :</td>
        </tr>
      </table>
  </td>

  </tr>
</table>
