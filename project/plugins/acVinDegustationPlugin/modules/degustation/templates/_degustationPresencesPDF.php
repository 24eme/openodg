<?php use_helper("Date"); ?>
<?php use_helper('TemplatingPDF'); ?>
<?php $adresse = sfConfig::get('app_degustation_courrier_adresse'); ?>

<table style="font-size:20px;margin:auto;" >
  <tr>
    <td style="text-align: center;text-decoration:underline;"><strong>FEUILLE DE PRESENCE</strong></td>
  </tr>
</table>
<br/><br/>

<table style="text-align: center;font-size:15px;">
  <tr>
    <td>Commission de dégustation : <?php echo $degustation->date ?></td>
  </tr>
</table>
<br/><br/>

<table style="font-size:15px;text-align:center;">
  <tr>
    <?php $date = strtotime($degustation->date); ?>
    <td>Date : <?php echo date('d/m/Y',$date) ?></td>
    <td>Heure : <?php echo date('H:i', $date); ?></td>
  </tr>
</table>

<table style="text-align: center;font-size:15px;margin:auto;" >
  <tr>
    <td>Lieu : <?php echo $degustation->lieu ?></td>
  </tr>
</table>
<br/><br/>


<table border="1" cellspacing=0 cellpadding=0 style="text-align:center;padding:10px;">
  <?php foreach ($degustation->degustateurs as $college => $degustateurs): ?>
  <?php foreach ($degustateurs as $id => $degustateur):?>
  <tr>
    <?php $degustateurNom = explode(' — ',$degustateur->get('libelle','')) ?>
    <?php $compte = CompteClient::getInstance()->find($id);?>
    <td style="height:50px;"><?php print_r($degustateurNom[0])?><br/><?php echo($compte->telephone_bureau.' - '.$compte->telephone_mobile) ?></td>
    <td style="height:50px;"><?php echo DegustationConfiguration::getInstance()->getLibelleCollege($college) ?></td>
    <td style="height:50px;"></td>
    <td style="height:50px;"><span style="font-size:10px;">Présent(e)</span></td>
  </tr>
  <?php endforeach; ?>
  <?php endforeach; ?>
</table>
<br/>
<br/>

<table>
  <tr>
    <td>NOM - Prénom et signature du responsable de l'ODG :</td>
  </tr>
</table>

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
</table>
