<?php use_helper("Date"); ?>
<?php use_helper('TemplatingPDF'); ?>
<?php $adresse = sfConfig::get('app_degustation_courrier_adresse'); ?>
<style>
</style>

<br/><br/>
<table style="width:1100px;font-size:13px;padding-left:400px;" >
  <tr>
    <td><?php echo $etablissement->raison_sociale ?></td>
  </tr>
  <tr>
    <td><?php echo $etablissement->adresse ?></td>
  </tr>
  <tr>
    <td><?php echo $etablissement->code_postal .' '.$etablissement->commune ?></td>
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
<table style="font-size:12px;"><tr><td style="width: 324px;"><?php echo 'Aix-en-Provence, le ' . format_date($degustation->date, "P", "fr_FR"); ?></td></tr></table>
<br/><br/>

<table style="font-size:12px;"><tr><td>Objet : Résultats contrôle interne,<strong> lots <?php if($degustation->getNbLotsNonConformes() > 0): ?> non conformes <?php else: ?> conformes <?php endif; ?></strong></td></tr></table>
<br/><br/>

<table style="font-size:12px;">
  <tr><td>Madame, Monsieur,</td></tr>
  <br/>
  <tr><td>Nous  vous  prions  de  bien  vouloir  trouver  ci-dessous  extrait  du  procès  verbal  de  la  séance  de  dégustation  du  :<br/><strong><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR")); ?></strong></td></tr><br/>
  <tr><td>Au vu des documents fournis, et des résultats du contrôle documentaire, analytique et organoleptique, nous vous confirmons que <strong>LES LOTS</strong> listés dans le tableau suivant <strong><?php if($degustation->getNbLotsNonConformes() > 0): ?>SONT NON CONFORMES<?php else: ?>SONT CONFORMES <?php endif; ?></strong> et aptes à la commercialisation</td></tr>
</table><br/><br/>

<table border="0.5" class="" cellspacing=0 cellpadding=0 style="text-align:center;font-size:12px;">
  <tr>
    <th style="font-weight:bold;"><?php echo tdStart() ?>N° Dos</th>
    <th style="font-weight:bold;"><?php echo tdStart() ?>&nbsp;N°&nbsp;Lot ODG&nbsp;</th>
    <th style="font-weight:bold;"><?php echo tdStart() ?>&nbsp;N°&nbsp;Lot OP&nbsp;</th>
    <th style="font-weight:bold;"><?php echo tdStart() ?>&nbsp;Logement<br/>(Cuve)&nbsp;</th>
    <th style="font-weight:bold;"><?php echo tdStart() ?>&nbsp;Produit&nbsp;</th>
    <th style="font-weight:bold;"><?php echo tdStart() ?>&nbsp;Volume<br/>(HI)&nbsp;</th>
    <th style="font-weight:bold;"><?php echo tdStart() ?>&nbsp;Observation&nbsp;</th>
  </tr>
    <?php $totalVolume = 0 ?>
    <?php foreach($degustation->getLots() as $lot): ?>
    <tr>
      <td><?php echo (int)$lot->numero_dossier ?></td>
      <td><?php echo (int)$lot->numero_archive ?></td>
      <td><?php echo $lot->declarant_identifiant?></td>
      <td><?php echo $lot->numero_cuve ?></td>
      <td><?php echo $lot->produit_libelle ."<br>". $lot->millesime ?></td>
      <td style="text-align:right;"><?php echo sprintf("%.2f", $lot->volume); ?></td>
      <?php if(isset($lot->observation)): ?>
      <td><?php echo $lot->observation ?></td>
      <?php else: ?>
      <td><?php echo '' ?></td>
      <?php endif; ?>
    </tr>
    <?php $totalVolume = $totalVolume + $lot->volume; ?>
    <?php endforeach; ?>
</table>

<br/>
<br/>

<table class="" border="0.5" cellspacing=1 cellpadding=0 style="">
  <tr>
    <td>&nbsp;Nombre de lots Total : <?php echo count($degustation->lots) ?> </td>
    <td>&nbsp;Volume Total (HI) : <?php echo $totalVolume ?> </td>
  </tr>
</table>

<br/>
<br/>

<table style="font-size:13px;">
  <tr>
    <td>Nous vous invitons à nous signaler toute modification ultérieure dans la constitution ou la destination finale de ces lots.</td>
  </tr>
  <br/>
  <tr>
    <td>Veuillez accepter, Madame, Monsieur, nos plus sincères et cordiales salutations.</td>
  </tr>
</table>

<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
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
</table>
