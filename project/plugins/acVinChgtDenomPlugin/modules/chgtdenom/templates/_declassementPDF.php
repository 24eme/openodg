<?php use_helper("Date"); ?>
<?php use_helper('Lot'); ?>

<?php $adresse = sfConfig::get('app_degustation_courrier_adresse'); ?>
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
<table><tr><td><strong>Objet :</strong> Déclassement d'un lot</td></tr></table>
<br/><br/>

<p>Madame, Monsieur,</p>

<p>Nous vous prions de bien vouloir trouver ci-dessous la confirmation du déclassement de votre lot :</p>

<p style="text-align: center; font-weight: bold"><?php showProduitLot($chgtDenom->lots[0]) ?></p>

<table border="0.5" class="" cellspacing=0 cellpadding=0 style="width:100%;text-align:center;">
  <tr>
    <th style="width: 15%">N° Dos / N° Lot ODG</th>
    <th style="width: 15%">N°Lot OP</th>
    <th style="width: 40%">Produit</th>
    <th style="width: 10%">Volume<br/>(hl)</th>
    <th style="width: 20%">Observation</th>
  </tr>
    <?php $totalVolume = 0 ?>
    <?php foreach($chgtDenom->lots as $lot): ?>
    <tr>
      <td><?php echo $lot->numero_dossier ?> / <?php echo $lot->numero_archive ?></td>
      <td><?php echo $lot->numero_logement_operateur?></td>
      <td><?php echo showProduitLot($lot) ?></td>
      <td style="text-align:right;"><?php echo sprintf("%.2f", $lot->volume); ?></td>
      <td><?php echo $lot->observation ?></td>
    </tr>
    <?php $totalVolume = $totalVolume + $lot->volume; ?>
    <?php endforeach; ?>
</table>

<br/>
<br/>

<table class="" border="0.5" cellspacing=1 cellpadding=0 style="">
  <tr>
    <td>&nbsp;Nombre de lots Total : <?php echo count($lots) ?> </td>
    <td>&nbsp;Volume Total (hl) : <?php echo sprintf("%.2f", $totalVolume) ?> </td>
  </tr>
</table>

<br/>
<br/>

<table>
  <tr>
    <td>Nous vous invitons à nous signaler toute modification ultérieure dans la constitution ou la destination finale de ces lots.</td>
  </tr>
  <br/>
  <tr>
    <td>Veuillez accepter, Madame, Monsieur, nos plus sincères et cordiales salutations.</td>
  </tr>
</table>
<br/><br/>
<br/><br/>
<br/><br/>
<table style="width:1100px;padding-left:400px;" >
  <tr><td><?php echo $responsable ?>,</td></tr>
  <tr><td>SIGNATURE</td></tr>
</table>
