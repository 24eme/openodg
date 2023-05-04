<?php
use_helper('TemplatingPDF');
use_helper('Float');
use_helper('Compte');
use_helper("Date");

$lots = $document->getLotsByCouleur();
?>
<style>
    <?php echo styleDRev(); ?>
</style>

<span class="h3Alt"> Opérateur </span><br/>
<table class="tableAlt">
  <tr><td>
    <table border="0" >
      <tr>
          <td>&nbsp;Nom : <i><?php echo $document->declarant->raison_sociale ?></i></td>
          <td>&nbsp;Téléphones : <i><?php echo $document->declarant->telephone_mobile ?> / <?php echo $document->declarant->telephone_bureau ?></i></td>
      </tr>
      <tr>
            <td>&nbsp;Adresse : <i><?php echo str_replace('−', '-', $document->declarant->adresse); ?></i></td>
            <td>&nbsp;Email : <i><?php echo $document->declarant->email; ?></i></td>
      </tr>
      <tr>
            <td>&nbsp;Commune : <i><?php echo $document->declarant->code_postal; ?> <?php echo $document->declarant->commune; ?></i></td>
            <td>&nbsp;N° Adhérent : <i><?php echo preg_replace('/..$/', '', $document->identifiant); ?></i></td>
      </tr>
      <tr>
        <td>&nbsp;N° CVI : <i><?php echo $document->declarant->cvi ?></i></td>
        <td>&nbsp;SIRET : <i><?php echo formatSIRET($document->declarant->siret); ?></i></td>
      </tr>
    </table>
  </td></tr>
</table>

<span class="h3Alt"> Site de stockage </span><br/>
<table class="tableAlt">
  <tr>
    <?php if ($document->isAdresseLogementDifferente() === false): ?>
    <td style="text-align: center">&nbsp;Même adresse que l'établissement</td>
    <?php else: ?>
        <table border="0">
          <tr>
              <td>&nbsp;Nom : <i><?php echo $document->chais->nom ?></i></td>
              <td></td>
          </tr>
          <tr>
            <td>&nbsp;Adresse : <i><?php echo $document->chais->adresse ?></i></td>
            <td>&nbsp;Commune : <i><?php echo $document->chais->code_postal; ?> <?php echo $document->chais->commune; ?></i></td>
          </tr>
        </table>
    <?php endif ?>
  </tr>
</table>

<br/>
<br/>

<u><strong>Option choisie</strong></u>

<p>
Présentation des vins lot par lot. Un lot est un volume de vin présentant les mêmes caractéristiques analytiques. Dans ce
cas, faire une demande de mise en circulation pour chaque lot.
</p>

<div><span class="h3"> Déclaration des lots </span></div>
<?php if (count($lots)): ?>
  <table border="1" class="table" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: left; width: 10%">&nbsp;N° Lot ODG</th>
        <th class="th" style="text-align: left; width: 10%">&nbsp;N° Lot Opérat.</th>
        <th class="th" style="text-align: left; width: 50%">&nbsp;Produit (millésime)</th>
        <th class="th" style="text-align: center; width: 15%">Volume</th>
        <th class="th" style="text-align: center; width: 15%">Date de dégust° souhaitée</th>
    </tr>
    <?php foreach($lots as $lotsByCouleur): ?>
      <?php foreach ($lotsByCouleur as $lot) : ?>
      <tr>
        <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $lot->numero_archive; ?></td>
        <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $lot->numero_logement_operateur; ?></td>
        <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $lot->produit_libelle ?>
            (<?php echo $lot->millesime ?>)
            <?php if (count($lot->cepages)): ?>&nbsp;<small><?php echo $lot->getCepagesLibelle(); ?></small><?php endif; ?>
        </td>
        <td class="td" style="text-align: right;"><?php echo tdStart() ?><?php echo sprintFloatFr($lot->volume) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
        <td class="td" style="text-align: right;"><?php echo tdStart() ?>&nbsp;<?php echo ($lot->exist('date_degustation_voulue')) ? $lot->date_degustation_voulue : "" ?></td>
      </tr>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </table>
<?php else: ?>
    <br />
    <em>Aucun lot déclaré</em>
<?php endif; ?>

<p>
Les vins seront prélevés, dégustés et analysés selon les modalités prévues au plan d'inspection et au réglement intérieur de l'AOC concernée.
</p>

<p>
Le délai entre la date de dégustation et la date de 1ère mise en bouteille ou 1ère vente ne doit pas excéder 3 mois. Dans le cas contraire, prévenir l'ODG ou l'OIVC.
</p>

<u><strong>Contrôle externe</strong></u>

<p>
À compter de la date choisie, les vins sur l'ensemble de l'appelation seront contrôlés de façon aléatoire par l'Organisme d'Inspection des Vins du Centre selon la fréquence suivante:
</p>

<table border="1" class="table" cellspacing=0 cellpadding=0>
    <tr style="text-align: center">
      <th colspan="2">AOC blanc et rosé</th>
      <th colspan="2">AOC rouge</th>
    </tr>
    <tr>
      <td> Décembre</td><td style="text-align: center">15%</td> <td> Mars</td><td style="text-align: center">15%</td>
    </tr>
    <tr>
      <td> Janvier</td><td style="text-align: center">10%</td> <td> Avril</td><td style="text-align: center">10%</td>
    </tr>
    <tr>
      <td> Février et au delà</td><td style="text-align: center">5%</td> <td> Mai et au delà</td><td style="text-align: center">5%</td>
    </tr>
</table>
