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

<table style="width:1100px;" >
  <tr style="text-decoration: underline;"><td>Vos coordonnées :</td></tr>
  <tr><td>Email : <?php echo $etablissement->email ?></td></tr>
  <?php echo ($etablissement->telephone_bureau) ? '<tr><td>Bureau : '. $etablissement->telephone_bureau .'</td></tr>' : '' ?>
  <?php echo ($etablissement->telephone_mobile) ? '<tr><td>Mobile : '. $etablissement->telephone_mobile .'</td></tr>' : '' ?>
</table>

<br/>
<br/>
<table><tr><td style="width: 324px;"><?php echo 'Le ' . format_date(date('Y-m-d'), "P", "fr_FR"); ?></td></tr></table>
<br/><br/>

<table><tr><td><strong>Objet :</strong> Résultats contrôles organoleptiques <?php echo $lot->getTextPassage() ?> <strong>non conformes</strong> NC<?php echo $lot->unique_id ?></td></tr></table>
<br/><br/>


<table>
  <tr><td>Madame, Monsieur,</td></tr>
  <br/>
  <tr><td>Lors de la séance de dégustation du <strong><?php echo format_date($degustation->date, "P", "fr_FR"); ?></strong>, le lot dont les informations figurent dans la fiche de non conformité ci-jointe a été ajourné<?php echo ($lot->isSecondPassage()) ? ' pour la 2ème fois' : '' ?>.<br/></td></tr>
  <tr><td>Compte tenu de ce résultat, vous pouvez décider :</td></tr><br/>
  <tr><td>
    <ul>
      <li><strong>Soit d’abandonner volontairement la dénomination correspondante</strong> en nous communiquant par fax, courrier ou mail, une déclaration de déclassement au moyen de la fiche jointe.</li><br/>
      <?php if ($lot->isSecondPassage() || $lot->conformite == Lot::CONFORMITE_NONCONFORME_MAJEUR): ?>
      <li>Soit de vous opposer aux conclusions de ce contrôle. Dans ce cas, ainsi que le prévoit le plan de contrôle de l'IGP, vous êtes dans l'obligation de transmettre le dossier à l'organisme de contrôle/d'inspection qui diligentera un nouveau contrôle entièrement à votre charge. <?php if (Organisme::getInstance()->getOi()): ?><strong>(<?php echo Organisme::getInstance()->getOi() ?>)</strong><?php endif ?></li>
      <?php else: ?>
      <li>Soit d’exécuter l’action corrective recommandée dans la fiche de non-conformité ci jointe. Dans cette hypothèse, votre vin pourra être soumis à une deuxième dégustation après que nous ayons procédé à un nouveau prélèvement, et ce dans un délai minimum de 15 jours à compter de la réception de votre demande de nouvelle présentation. Notez qu’après travail le vin concerné par la deuxième présentation peut être relogé&nbsp;:
        <ul>
          <li>Dans la même cuve</li>
          <li>Dans une autre cuve d’un volume inférieur ou égal au volume initial,</li>
          <li>Dans d’autres cuves d’un volume total inférieur ou égal au volume initial.</li>
        </ul>
      </li>
      <?php endif ?>
    </ul>
  </td></tr>
</table><br/><br/>

<table>
<tr><td>Dans tous les cas, il vous appartient de nous retourner, par fax ou par courrier, daté et signé chacun des documents joints avec la mention de votre décision :&nbsp;<strong>Demande de déclassement ou <?php echo ($lot->isSecondPassage() || $lot->conformite == Lot::CONFORMITE_NONCONFORME_MAJEUR) ? 'nouveau contrôle OC/OI' : 'nouvelle présentation' ?></strong>.<br/><br/></td></tr><br/>
  <tr><td>Nous vous prions de croire, Madame, Monsieur, en l’expression de nos sentiments les meilleurs.</td></tr><br/>
  <tr><td>Pour toutes informations, merci de nous contacter.</td></tr><br/>
</table>

<br/><br/>
<br/><br/>
<br/><br/>
<table style="width:1100px;padding-left:400px;" >
  <tr><td><?php echo Organisme::getInstance()->getResponsable() ?></td></tr>
  <tr><td><?php if(file_exists(Organisme::getInstance()->getImageSignaturePath())): ?><img src="<?php echo Organisme::getInstance()->getImageSignaturePath() ?>"/><?php endif; ?></td></tr>
</table>
