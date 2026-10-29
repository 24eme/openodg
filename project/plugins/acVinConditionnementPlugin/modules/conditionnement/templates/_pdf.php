<?php use_helper('TemplatingPDF');
 use_helper('Float');
 use_helper('Compte');
 use_helper("Date"); ?>
<style>
<?php echo styleDRev(); ?>
</style>

<span class="h3Alt">&nbsp;Entreprise&nbsp;</span><br/>
<table class="tableAlt"><tr><td>
<table border="0" >
  <tr>
      <td style="width: 360px;">&nbsp;Nom : <i><?php echo $document->declarant->raison_sociale ?></i></td>
      <td style="width: 300px;">&nbsp;Téléphones : <i><?php echo $document->declarant->telephone_mobile ?> / <?php echo $document->declarant->telephone_bureau ?></i></td>
      <td>N° CVI : <i><?php echo $document->declarant->cvi ?></i></td>
  </tr>
  <tr>
        <td>&nbsp;Adresse : <i><?php echo str_replace('−', '-', $document->declarant->adresse); ?></i></td>
        <td>&nbsp;Email : <i><?php echo $document->declarant->email; ?></i></td>
        <td>SIRET : <i><?php echo formatSIRET($document->declarant->siret); ?></i></td>
  </tr>
  <tr>
        <td>&nbsp;Commune : <i><?php echo $document->declarant->code_postal; ?> <?php echo $document->declarant->commune; ?></i></td>
        <td></td>
        <td>&nbsp;N° Adhérent : <i><?php echo preg_replace('/..$/', '', $document->identifiant); ?></i></td>
  </tr>
</table>
</td></tr></table>
<?php
$lots = $document->getLotsByCouleur();
?>
<br />
<div><span class="h3">&nbsp;Déclaration des lots&nbsp;</span></div>
<?php if (count($lots)): ?>
<table border="1" class="table" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: left; width: 80px">&nbsp;N° Lot ODG</th>
        <th class="th" style="text-align: left; width: 80px">&nbsp;N° Lot Opérat.</th>
        <th class="th" style="text-align: left; width: 420px">&nbsp;Produit (millésime)</th>
        <th class="th" style="text-align: center; width: 120px">Volume</th>
        <th class="th" style="text-align: center; width: 245px">&nbsp;Centilisation (date)</th>
    </tr>
<?php foreach($lots as $lotsByCouleur): ?>
<?php foreach ($lotsByCouleur as $lot) : ?>
    <tr>
        <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $lot->numero_archive; ?></td>
        <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $lot->numero_logement_operateur; ?></td>
        <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $lot->produit_libelle ?> (<?php echo $lot->millesime ?>)<?php if(count($lot->cepages)): echo "&nbsp;<small>".$lot->getCepagesLibelle()."</small>"; endif; ?><?php if($lot->statut == Lot::STATUT_ELEVAGE): echo "&nbsp;<small>élevage</small>"; endif; ?></td>
        <td class="td" style="text-align: right;"><?php echo tdStart() ?><?php echo sprintFloatFr($lot->volume) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
        <td class="td" style="text-align: center;"><?php echo tdStart() ?><?php echo $lot->centilisation; echo ($lot->destination_date) ? " (".$lot->getDestinationDateFr().")" : ''; ?></td>
    </tr>
<?php endforeach; ?>
<?php endforeach; ?>
</table>
<?php else: ?>
<br />
<em>Aucun lot déclaré</em>
<?php endif; ?>
