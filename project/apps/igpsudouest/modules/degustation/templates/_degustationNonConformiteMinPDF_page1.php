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
<br/>
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
<table><tr><td><strong>Objet :</strong> Résultats contrôle interne, notification d'avertissement relevé lors du contrôle interne sur le lot revendiqué de l'IGP Comté Tolosan</td></tr></table>
<br/><br/>


<table>
  <tr><td>Madame, Monsieur,</td></tr>
  <br/>
  <tr><td>Dans le cadre du contrôle interne organoleptique réalisé le :<br/><strong><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR")); ?></strong> sur le lot de vin que vous avez revendiqué en IGP Comté Tolosan, nous avons relevé le manquement suivant</td></tr>
</table><br/><br/>

<table border="0.5" class="" cellspacing=0 cellpadding=0 style="width:100%;text-align:center;">
  <tr>
    <th style="width: 15%">N° Dos / N° Lot ODG</th>
    <th style="width: 15%">N°Lot OP</th>
    <th style="width: 40%">Produit</th>
    <th style="width: 10%">Volume<br/>(hl)</th>
    <th style="width: 20%">Observation</th>
  </tr>
    <?php $totalVolume = 0; ?>
    <?php $nombreObs = 0; ?>
    <tr>
      <td><?php echo $lot->numero_dossier ?> / <?php echo $lot->numero_archive ?></td>
      <td><?php echo $lot->numero_logement_operateur?></td>
      <td><?php echo showProduitCepagesLot($lot, false) ?></td>
      <td style="text-align:right;"><?php echo sprintf("%.2f", $lot->volume); ?></td>
      <td>Manquement mineur : <br/><?php echo $lot->motif ?><br/><?php echo $lot->observation; ?></td>
    </tr>
</table>

<br/>
<br/>

<table>
    <tr><td>Il en résulte que <strong>ce lot fait l'objet d'un <u>avertissement</u></strong> car il à été jugé « acceptable ».</td></tr>
    <tr><td><strong>Ce lot peut être commercialisé en IGP Comté Tolosan, mais nous vous invitons à prendre en compte les remarques formulées avant toute transaction ou conditionnement.</strong></td></tr>
</table>
<br/>
<br/>
<br/>
<table>
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
