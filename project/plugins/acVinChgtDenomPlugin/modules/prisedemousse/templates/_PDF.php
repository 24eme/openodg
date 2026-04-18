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
    <table><tr><td><strong>Objet : </strong>Déclaration de prise de mousse</td></tr></table>
<br/><br/>

<table><tr><td>Madame, Monsieur,</td></tr></table>

<?php $lotOrigine = $prisedemousse->getLotOrigine(); ?>

<table><tr><td>Nous vous prions de bien vouloir trouver ci-dessous la  <?php if ( $prisedemousse->getDegustationVMQ() !== null && !$prisedemousse->getDegustationVMQ()->isAffecte() && !$prisedemousse->getDegustationVMQ()->isAffectable() && $prisedemousse->getDegustationVMQ()->isConforme()): ?> confirmation <?php else: ?> demande <?php endif ?> de prise de mousse de votre lot originalement déclaré en vin de base :</td></tr></table>

<br/><br/>


<table border="1" cellspacing=0 cellpadding=0 style="width:100%;text-align:center;">
  <tr>
    <th style="width: 15%">N° Dos / N° Lot ODG</th>
    <th style="width: 15%">N°Lot OP</th>
    <th style="width: 40%">Produit</th>
    <th style="width: 10%">Volume<br/>(hl)</th>
    <th style="width: 20%">Observation</th>
  </tr>
    <?php if($lotOrigine): ?>
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
            <?php echo $prisedemousse->getOrigineProduitLibelleAndCepages() ?>
            <small><?php echo $prisedemousse->getOrigineMillesime() ?></small>
            <small><?php echo $prisedemousse->getOrigineSpecificite() ?></small>
          </td>
          <td style="text-align:right;"><?php echo sprintf("%.2f", $prisedemousse->getOrigineVolume()); ?></td>
          <td></td>
        </tr>
    <?php endif ?>
</table>

<br/>

<table style="padding:20px auto;font-weight:bold;">
    <tr>
        <?php if ($prisedemousse->getDegustationVMQ() !== null && !$prisedemousse->getDegustationVMQ()->isAffecte() && !$prisedemousse->getDegustationVMQ()->isAffectable() && $prisedemousse->getDegustationVMQ()->isConforme()): ?>
            <td style="text-align:center">qui devient le lot commercialisable suivant :  </td>
        <?php else: ?>
            <td style="text-align:left">qui après le contrôle organisé conformément au cahier des charges/plan de contrôle deviendra le lot ci-dessous : </td>
        <?php endif;?>
    </tr>
</table>
<br/>

<table border="1">
    <tr>
        <th style="font-size: 14px">Lot n°: <?php echo $prisedemousse->lots[1]->numero_dossier.' / '.$prisedemousse->lots[1]->numero_archive ?></th>
    </tr>
    <tr>
        <td>N° Lot OP : <?php echo $prisedemousse->lots[1]->numero_logement_operateur; ?><br/>
<?php if ($prisedemousse->lots[1]->adresse_logement): ?><br/>
Adresse du site : <div style="width:5px;"><?php echo $prisedemousse->lots[1]->adresse_logement; ?></div><br/>
<?php endif; ?>
            Produit : <?php echo showProduitCepagesLot($prisedemousse->lots[1]); ?><br/>
            Volume : <?php echo sprintf("%.2f", $prisedemousse->lots[1]->volume) ?> hl
        </td>
    </tr>
</table>

<table style="padding:10px 0;">
    <tr>
        <?php if (!$prisedemousse->lots[1]->isAffecte() && !$prisedemousse->lots[1]->isAffectable() && $prisedemousse->getDegustationVMQ()->isConforme()): ?>
            <td style="text-align:center">Vin dégusté et contrôlé conforme le <?php echo $prisedemousse->lots[1]->getDateCommissionFormat(); ?></td>
        <?php endif;?>
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
  <tr><td><?php echo Organisme::getInstance(null, 'degustation')->getResponsable() ?></td></tr>
  <tr><td><?php if(file_exists(Organisme::getInstance(null, 'degustation')->getImageSignaturePath())): ?><img src="<?php echo Organisme::getInstance(null, 'degustation')->getImageSignaturePath() ?>"/><?php endif; ?></td></tr>
</table>
