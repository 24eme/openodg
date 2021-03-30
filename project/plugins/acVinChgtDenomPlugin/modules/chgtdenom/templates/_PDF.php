<?php use_helper("Date"); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('TemplatingPDF') ?>

<?php $adresse = sfConfig::get('app_degustation_courrier_adresse'); ?>
<style>
    <?php echo style() ?>
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
<?php if ($changement === ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT): ?>
    <table><tr><td><strong>Objet :</strong> Déclassement d'un lot</td></tr></table>
<?php else : ?>
    <table><tr><td><strong>Objet :</strong> Changement de dénomination d'un lot</td></tr></table>
<?php endif ?>
<br/><br/>

<p>Madame, Monsieur,</p>

<p>Nous vous prions de bien vouloir trouver ci-dessous la confirmation du <?php if ($changement === ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT): ?>déclassement<?php else: ?>changement de dénomination<?php endif ?> de votre lot :</p>

<p style="text-align: center; font-weight: bold"><?php showProduitLot($chgtdenom->getLotOrigine()) ?></p>

<table border=1 cellspacing=0 cellpadding=0 style="width:100%;text-align:center;">
  <tr>
    <th style="width: 15%">N° Dos / N° Lot ODG</th>
    <th style="width: 15%">N°Lot OP</th>
    <th style="width: 40%">Produit</th>
    <th style="width: 10%">Volume<br/>(hl)</th>
    <th style="width: 20%">Observation</th>
  </tr>
  <tr>
    <?php $lotOrigine = $chgtdenom->getLotOrigine(); ?>
    <tr>
      <td><?php echo $lotOrigine->numero_dossier ?> / <?php echo $lotOrigine->numero_archive ?></td>
      <td><?php echo $lotOrigine->numero_logement_operateur?></td>
      <td><?php echo showProduitLot($lotOrigine) ?></td>
      <td style="text-align:right;"><?php echo sprintf("%.2f", $lotOrigine->volume); ?></td>
      <td><?php echo ($lotOrigine->observation) ?? '' ?></td>
    </tr>
</table>

<br/>
<p style="text-align:center">devient</p>

<br/>

<table>
    <tr>
        <td>
            <?php $lot = $chgtdenom->lots[0]; ?>
            Dossier : <?php echo $lot->campagne ?> Lot n°: <?php echo $lot->numero_dossier . '-' . $lot->numero_archive ?><br/>
            Produit : <?php echo showProduitLot($chgtdenom->lots[0]); ?><br/>
            Volume : <?php echo $lot->volume ?>hl<br/>
        </td>
        <?php if ($total == false): ?>
            <td>
                <?php $lot2 = $chgtdenom->lots[1]; ?>
                Dossier : <?php echo $lot2->campagne ?> Lot n°: <?php echo $lot2->numero_dossier . '-' . $lot2->numero_archive ?><br/>
                Produit : <?php echo showProduitLot($lot2); ?><br/>
                Volume : <?php echo $lot2->volume ?>hl<br/>
            </td>
        <?php endif; ?>
    </tr>
</table>

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
