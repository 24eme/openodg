<?php use_helper('TemplatingPDF');
 use_helper('Float');
 use_helper('Compte');
 use_helper("Date");
 use_helper('Lot'); ?>

<style>
<?php echo styleDRev(); ?>
</style>

<span class="h3Alt">&nbsp;Entreprise&nbsp;</span><br/>
<table class="tableAlt"><tr><td>
<table border="0" >
  <tr>
      <td style="width: 360px;">&nbsp;Nom : <i><?php echo $drev->declarant->raison_sociale ?></i></td>
      <td style="width: 300px;">&nbsp;Téléphones : <i><?php echo $drev->declarant->telephone_mobile ?> / <?php echo $drev->declarant->telephone_bureau ?></i></td>
      <td>N° CVI : <i><?php echo $drev->declarant->cvi ?></i></td>
  </tr>
  <tr>
        <td>&nbsp;Adresse : <i><?php echo str_replace('−', '-', $drev->declarant->adresse); ?></i></td>
        <td>&nbsp;Email : <i><?php echo $drev->declarant->email; ?></i></td>
        <td>SIRET : <i><?php echo formatSIRET($drev->declarant->siret); ?></i></td>
  </tr>
  <tr>
        <td>&nbsp;Commune : <i><?php echo $drev->declarant->code_postal; ?> <?php echo $drev->declarant->commune; ?></i></td>
        <td></td>
        <td><?php if(DRevConfiguration::getInstance()->hasCgu()): ?>&nbsp;N° Interloire : <?php else : ?>&nbsp;N° Adhérent : <?php endif; ?><i><?php echo preg_replace('/..$/', '', $drev->identifiant); ?></i></td>
  </tr>
</table>
</td></tr></table>

<?php if(count($drev->declaration->getProduitsWithoutLots($region))): ?>
<br />
<div><span class="h3">&nbsp;Revendication&nbsp;</span></div>
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: left; width: 400px;">Produit</th>
        <th class="th" style="text-align: center; width: 137px;">Superficie revendiquée</th>
        <th class="th" style="text-align: center; width: 137px;">Volume millesime <?php echo $drev->periode-1 ?> issu du VCI</th>
        <th class="th" style="text-align: center; width: 137px;">Volume issu de la récolte <?php echo $drev->periode ?></th>
        <th class="th" style="text-align: center; width: 137px;">Volume revendiqué net total <?php if($drev->hasProduitWithMutageAlcoolique()): ?><small>(alcool compris)</small><?php endif; ?></th>
    </tr>
    <?php foreach($drev->declaration->getProduitsWithoutLots($region) as $produit): ?>
        <tr>
            <td class="td" style="text-align:left;"><?php echo tdStart() ?>&nbsp;<?php echo $produit->getLibelleComplet() ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if ($produit->superficie_revendique): ?><?php echo sprintFloatFr($produit->superficie_revendique) ?>&nbsp;<small>ha</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if ($produit->volume_revendique_issu_vci): ?><?php echo sprintFloatFr($produit->volume_revendique_issu_vci) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if ($produit->volume_revendique_issu_recolte): ?><?php echo sprintFloatFr($produit->volume_revendique_issu_recolte) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if ($produit->volume_revendique_total): ?><?php echo sprintFloatFr($produit->volume_revendique_total) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<br />
<?php if(count($drev->declaration->getProduitsVci($region))): ?>
<div><span class="h3 font-size-16-pt">&nbsp;Gestion du VCI&nbsp;</span></div>
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: left; width: 247px;">Produit</th>
        <th class="th" style="text-align: center; width: 100px;">Stock <?php echo ($drev->periode - 1) ?></th>
        <th class="th" style="text-align: center; width: 100px;">Rafraichi</th>
        <th class="th" style="text-align: center; width: 100px;">Complément</th>
        <th class="th" style="text-align: center; width: 100px;">A détruire</th>
        <th class="th" style="text-align: center; width: 100px;">Substitution</th>
        <th class="th" style="text-align: center; width: 100px;">Constitué <?php echo $drev->periode ?></th>
        <th class="th" style="text-align: center; width: 100px;">Stock <?php echo $drev->periode ?></th>
    </tr>
    <?php foreach($drev->declaration->getProduitsVci($region) as $produit): ?>
        <tr>
            <td class="td" style="text-align:left;"><?php echo tdStart() ?>&nbsp;<?php echo $produit->getLibelleComplet() ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->vci->stock_precedent): ?><?php echo sprintFloatFr($produit->vci->stock_precedent) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->vci->rafraichi): ?><?php echo sprintFloatFr($produit->vci->rafraichi) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->vci->complement): ?><?php echo sprintFloatFr($produit->vci->complement) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->vci->destruction): ?><?php echo sprintFloatFr($produit->vci->destruction) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->vci->substitution): ?><?php echo sprintFloatFr($produit->vci->substitution) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->vci->constitue): ?><?php echo sprintFloatFr($produit->vci->constitue) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?><?php if($produit->vci->stock_final): ?><?php echo sprintFloatFr($produit->vci->stock_final) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;<?php endif; ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<br /><br />
Les produits déclarés sont du millésime du VCI
<?php else: ?>
<br />
<em>Aucun stock VCI déclaré</em>
<?php endif; ?>
<?php endif; ?>

<?php if(count($drev->getLotsRevendiques())): ?>
<br />
<div><span class="h3">&nbsp;Déclaration des lots&nbsp;</span></div>
<?php if (count($drev->getLotsRevendiques())): ?>
<table border="1" class="table" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr style="line-height:20em;">
        <th class="th" style="text-align: left; width: 10%">&nbsp;Date</th>
        <th class="th" style="text-align: left; width: 20%">&nbsp;Lot</th>
        <th class="th" style="text-align: left; width: 40%">&nbsp;Produit (millésime)</th>
        <th class="th" style="text-align: center; width: 10%">Volume</th>
        <th class="th" style="text-align: center; width: 20%">&nbsp;Destination (date)</th>
    </tr>
<?php foreach($drev->getLotsByUniqueAndDate() as $lot): ?>
    <tr>
        <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $lot->getDateVersionfr() ?></td>
        <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $lot->numero_logement_operateur ?></td>
        <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo showProduitCepagesLot($lot) ?><?php if($lot->statut == Lot::STATUT_ELEVAGE): echo "&nbsp;<small>élevage</small>"; endif; ?></td>
        <td class="td" style="text-align: right;"><?php echo tdStart() ?><?php echo sprintFloatFr($lot->volume) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
        <td class="td" style="text-align: center; font-size: 8pt;"><?php echo tdStart() ?><?php echo $lot->destination_type; echo ($lot->destination_date) ? " (".$lot->getDestinationDateFr().")" : ''; ?></td>
    </tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<br />
<em>Aucun lot déclaré</em>
<?php endif; ?>
<?php endif; ?>

<?php if($drev->hasProduitsReserveInterpro($region)): ?>
<br />
<div><span class="h3">&nbsp;Réserve interprofessionnelle&nbsp;</span></div>
<table border="1" class="table" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: left;width: 400px;">&nbsp;Produit</th>
        <th class="th" style="text-align: center;width: 200px;">&nbsp;Volume mis en réserve</th>
        <th class="th" style="text-align: center;width: 200px;">&nbsp;Volume revendiqué commercialisable</th>
    </tr>
<?php foreach($drev->getProduitsWithReserveInterpro($region) as $p): ?>
    <tr>
        <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<?php echo $p->getLibelle() ?></td>
        <td class="td" style="text-align: right;"><?php echo tdStart() ?><?php echo sprintFloatFr($p->getVolumeReserveInterpro()) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
        <td class="td" style="text-align: right;"><?php echo tdStart() ?><?php echo sprintFloatFr($p->getVolumeRevendiqueCommecialisable()) ?>&nbsp;<small>hl</small>&nbsp;&nbsp;&nbsp;</td>
    </tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<?php if(!$drev->isAllDossiersHaveSameAddress()): ?>
    <br/><br/>
    <span class="h3">&nbsp;Logement du vin&nbsp;</span><br/>
    <table class="tableAlt" border="0" cellspacing=0 cellpadding=0 style="text-align: right;" >
      <thead>
        <tr style="line-height:20em;">
          <th class="th" style="text-align: center; width: 20%">Num. Dossier</th>
          <th class="th" style="text-align: center; width: 80%">Détails du chais</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($drev->getLotsByNumeroDossier() as $lot) : ?>
        <tr>
          <td class="td" style="text-align: center; width: 20%"><?php echo $lot->numero_dossier; ?></td>
          <td class="td" style="text-align: left; width: 80%">
            <?php echo $lot->adresse_logement;
            ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    </table>
<?php endif; ?>

<?php if($drev->exist('documents') && count($drev->documents->toArray(true, false)) && DRevConfiguration::getInstance()->hasEngagementsPdf()): ?>
    <br />
    <div><span class="h3">&nbsp;Engagement(s)&nbsp;</span></div>
    <table border="1" class="table" cellspacing=0 cellpadding=0 style="text-align: right;">
    <?php foreach($drev->documents as $docKey => $doc): ?>
        <tr>
            <td class="td" style="text-align: left;"><?php echo tdStart() ?>&nbsp;<span style="font-family: Dejavusans">☑</span> <?php echo ($doc->exist('libelle') && $doc->libelle) ? $doc->libelle : $drev->documents->getEngagementLibelle($docKey);  ?></td>
        </tr>
    <?php endforeach; ?>
    </table>
<?php endif; ?>
