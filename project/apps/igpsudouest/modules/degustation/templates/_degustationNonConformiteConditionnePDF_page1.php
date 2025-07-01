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

<table><tr><td><strong>Objet :</strong> Résultats contrôle organoleptique <?php echo $lot->getTextPassage() ?> <strong>non conforme</strong> NC<?php echo $lot->unique_id ?></td></tr></table>
<br/><br/>


<table>
  <tr><td>Madame, Monsieur,</td></tr>
  <br/>
  <tr><td>Lors de la séance de dégustation du <strong><?php echo format_date($degustation->date, "P", "fr_FR"); ?></strong>, nous avons relevé le manquement suivant :<br/></td></tr>
  <tr>
    <td><strong>Description de l'anomalie (<?php echo $lot->getTextPassage(); ?>)</strong><br/><br/>
      <strong>Gravité :</strong> <?php echo $lot->getLibelleConformite() ?><br/>
      <strong>Motif :</strong> <?php echo $lot->motif ?><br/>
      <strong>Observations constatées :</strong> <?php echo $lot->observation ?>
      <br/>
    </td>
  </tr>
  <br/>

</table><br/><br/>

<table>
<tr><td>Il en résulte que <strong>ce lot est non conforme en IGP Comté Tolosan mais ce lot étant déjà conditionné, il fait seulement l'objet d'un <u>avertissement</u>.</strong><br/></td></tr>
<tr><td><strong>Il est donc possible de commercialiser votre lot en IGP Comté Tolosan mais une pression de contrôle supplémentaire sera appliquée sur les prochains lots qui seront revendiqués.</strong></td></tr><br/>
<tr><td>Restant à votre disposition pour toutes demandes de renseignements complémentaires,</td></tr><br/>
<tr><td>Veuillez accepter, Madame, Monsieur, nos plus sincères et cordiales salutations.</td></tr><br/>
</table>

<br/><br/>
<br/><br/>
<table style="width:1100px;padding-left:400px;" >
  <tr><td><?php echo Organisme::getInstance(null, 'degustation')->getResponsable() ?></td></tr>
  <tr><td><?php if(file_exists(Organisme::getInstance(null, 'degustation')->getImageSignaturePath())): ?><img src="<?php echo Organisme::getInstance(null, 'degustation')->getImageSignaturePath() ?>"/><?php endif; ?></td></tr>
</table>
