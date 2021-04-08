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

<table><tr><td><strong>Objet :</strong> Résultats contrôles organoleptiques <strong>absence de typicité cépage</strong></td></tr></table>
<br/><br/>


<table>
  <tr><td>Madame, Monsieur,</td></tr>
  <br/>
  <tr><td>Lors de la séance de dégustation du <strong><?php echo format_date($degustation->date, "P", "fr_FR"); ?></strong>, le lot <?php echo $lot->getLibelle() ?> a fait l'objet d'un absence de typicité cépage revendiqué.</td></tr>
  <tr><td>Compte tenu de ce résultat, vous pouvez décider :</td></tr><br/>
  <tr><td>
    <ul>
      <li>Soit d'abandonner la mention cépage sur votre étiquette et commercialiser votre vin en IGP var générique (sans mention de cépage);</li><br/>
      <li>Soit de nous transmettre les éléments de traçabilité attestant que le vin dégusté provient bien du cépage revendiqué.</li>
    </ul>
  </td></tr>
</table><br/><br/>

<table>
  <tr><td>Dans l'attente,</td></tr><br/>
  <tr><td>Nous vous prions de croire, Madame, Monsieur, en l’expression de nos sentiments les meilleurs.</td></tr><br/>
</table>

<br/><br/>
<br/><br/>
<br/><br/>
<table style="width:1100px;padding-left:400px;" >
  <tr><td><?php echo $courrierInfos['responsable'] ?></td></tr>
</table>
