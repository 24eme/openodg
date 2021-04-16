<?php use_helper("Date"); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('TemplatingPDF') ?>

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
<?php if ($changement === ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT): ?>
    <table><tr><td><strong>Objet :</strong> Déclassement d'un lot</td></tr></table>
<?php else : ?>
    <table><tr><td><strong>Objet :</strong> Changement de dénomination d'un lot</td></tr></table>
<?php endif ?>
<br/><br/>

<table><tr><td>Madame, Monsieur,</td></tr></table>

<table><tr><td>Nous vous prions de bien vouloir trouver ci-dessous la confirmation du <?php if ($changement === ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT): ?>déclassement<?php else: ?>changement de dénomination<?php endif ?> de votre lot :</td></tr></table>

<br/><br/>

<table border="1" cellspacing=0 cellpadding=0 style="width:100%;text-align:center;">
  <tr>
    <th style="width: 15%">N° Dos / N° Lot ODG</th>
    <th style="width: 15%">N°Lot OP</th>
    <th style="width: 40%">Produit</th>
    <th style="width: 10%">Volume<br/>(hl)</th>
    <th style="width: 20%">Observation</th>
  </tr>
    <?php if($lotOrigine = $chgtdenom->getLotOrigine()): ?>
    <tr>
      <td><?php echo $lotOrigine->numero_dossier ?> / <?php echo $lotOrigine->numero_archive ?></td>
      <td><?php echo $lotOrigine->numero_logement_operateur?></td>
      <td><?php echo showProduitCepagesLot($lotOrigine) ?></td>
      <td style="text-align:right;"><?php echo sprintf("%.2f", $lotOrigine->volume); ?></td>
      <td><?php echo ($lotOrigine->observation) ?? '' ?></td>
    </tr>
    <?php else: ?>
        <tr>
          <td></td>
          <td></td>
          <td>
            <?php echo $chgtdenom->getOrigineProduitLibelle() ?>
            <small><?php echo $chgtdenom->getOrigineMillesime() ?></small>
            <small><?php echo $chgtdenom->getOrigineSpecificite() ?></small>
          </td>
          <td style="text-align:right;"><?php echo sprintf("%.2f", $chgtdenom->getOrigineVolume()); ?></td>
          <td></td>
        </tr>
    <?php endif ?>
</table>

<br/>
<p style="text-align:center">qui devient</p>

<br/>

<?php $lot = $chgtdenom->lots[0]; ?>
<table border="1">
    <tr>
        <th style="font-size: 14px">Lot n°: <?php echo $lot->numero_dossier.' / '.$lot->numero_archive ?></th>
        <?php if ($total == false): ?>
          <?php $lot2 = $chgtdenom->lots[1]; ?>
          <th style="font-size: 14px">Lot n°: <?php echo $lot2->numero_dossier.' / '.$lot2->numero_archive ?></th>
        <?php endif ?>
    </tr>
    <tr>
        <td>
N° Lot OP : <?php echo $lot->numero_logement_operateur; ?><br/>
            Produit : <?php echo showProduitCepagesLot($chgtdenom->lots[0]); ?><br/>
            Volume : <?php echo sprintf("%.2f", $lot->volume) ?> hl
        </td>
        <?php if ($total == false): ?>
            <td>
N° Lot OP : <?php echo $lot2->numero_logement_operateur; ?><br/>
            Produit : <?php echo showProduitCepagesLot($lot2); ?><br/>
            Volume : <?php echo sprintf("%.2f", $lot2->volume) ?> hl
            </td>
        <?php endif; ?>
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
  <tr><td><?php echo $courrierInfos['responsable'] ?></td></tr>
  <tr><td><img src="<?php echo $courrierInfos['signature'] ?>"/></td></tr>
</table>
