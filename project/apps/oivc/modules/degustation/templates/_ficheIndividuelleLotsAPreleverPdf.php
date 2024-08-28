<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<?php use_helper('Float') ?>
<?php use_helper('Text') ?>
<style>
<?php echo style(); ?>
.bg-white{
  background-color:white;
}
th {
  background-color:white;
  font-weight: bold;
}

p, div {
 line-height: 0.5;
}

.border{
  border-style: solid;
}

</style>
<table class="table" cellspacing=0 cellpadding=0 style="border-collapse:collapse;" scope="colgroup">
  <?php echo tdStart() ?>
  <tr>
      <th border="1px" class="border" style="width:50%; text-align: center;"><strong>Opérateur</strong></th>
      <th border="1px" class="border" style="width:50%; text-align: center;"><strong>Lieu entreposage et prélèvement</strong></th>
  </tr>
  <tr>
    <td border="1px" class="border">
        <p><span>&nbsp;&nbsp;<strong>Raison sociale :</strong> <?php echo truncate_text($etablissement->getRawValue()->raison_sociale, 43, '...') ?></span></p>
        <p>
          <span><strong>Adresse :</strong> <?php echo $etablissement->adresse ?></span>
        </p>
        <p>
          <span><strong>Code postal :</strong> <?php echo $etablissement->code_postal ?></span>
          <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Ville :</strong> <?php echo $etablissement->commune ?></span>
        </p>
        <p>
          <span><strong>Téléphone :</strong> <?php echo $etablissement->telephone_bureau ?></span>
          <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Portable :</strong> <?php echo $etablissement->telephone_mobile ?></span>
        </p>
        <p>
          <span><strong>Siret :</strong> <?php echo $etablissement->siret ?></span>
          <span><?php for($i=strlen($etablissement->siret); $i <= 25; $i++): ?>&nbsp;<?php endfor; ?><strong>N° CVI :</strong> <?php echo $etablissement->cvi ?></span>
          <br />
        </p>
    </td>
    <td border="1px" class="border">
<?php
    $lot = array_values($lots->getRawValue())[0];
?>
        <p>
          <span><strong>Nom :</strong> <?php echo truncate_text($lot->getLogementNom(), 43, '...') ?></span>
        </p>
        <p>
          <span><strong>Adresse :</strong> <?php echo $lot->getLogementAdresse(); ?></span>
        </p>
        <p>
          <span><strong>Code postal : </strong><?php echo $lot->getLogementCodePostal(); ?></span>
          <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Ville</strong> : <?php echo $lot->getLogementCommune(); ?></span>
        </p>
        <p>
          <span><strong>Téléphone :</strong> <?php echo $lot->getLogementTelephone() ?> <?php echo ($lot->getLogementPortable()) ? ' / '.$lot->getLogementPortable() : ''; ?></span>
        </p>
    </td>
  </tr>
</table>
<br />
<p><small>Le représentant de l'entreprise ci-dessus-désigné reconnait avoir assisté au prélevement ce jour de un ou plusieurs échantillon(s) en vue de leur présentation aux examens analyptiques et/ou organoleptiques et que le prélevement <br /><br /> a été effectué conformément au plan de contrôle de l'AOC concernée.</small></p>
<table class="table" cellspacing=0 cellpadding=0 style="border-collapse:collapse;" scope="colgroup">
  <tr>
    <th border="1px" class="border" style="width: 50%; text-align: center;"><strong>Nom et signature préleveur</strong></th>
    <th border="1px" class="border" style="width: 50%; text-align: center;"><strong>Nom et signature personne présente</strong></th>
  </tr>
  <tr>
    <td border="1px" class="border"><br /><br /></td>
    <td border="1px" class="border"><br /><br /></td>
  </tr>
</table>
<br />
<p><strong><?php echo count($lots) ?> lot(s) à prélever</strong> :</p>
  <?php $i = 10; $table_header = true; foreach($lots as $key => $lot): ?>
   <?php if($i % 20 == 0 ): $table_header = true; ?>
    </table>
     <br pagebreak="true" />
     <p><strong>Suite des lots :</strong><p/>
     <br/>
     <?php endif; ?>
    <?php if ($table_header): $table_header = false; ?>
     <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
       <tr style="line-height:20px;">
         <th style="width: 9%"><?php echo tdStart() ?><small>N° Dossier / Lot</small></th>
         <th style="width: 18%"><?php echo tdStart() ?><small>Produit / Millésime</small></th>
         <th style="width: 18%"><?php echo tdStart() ?><small>N°lot / logement</small></th>
         <th style="width: 8%"><?php echo tdStart() ?><small>Type contrôle</small></th>
         <th style="width: 11%"><?php echo tdStart() ?><small>Type de prélevement*</small></th>
         <th style="width: 8%"><?php echo tdStart() ?><small>Volume (hl)</small></th>
         <th style="width: 8%"><?php echo tdStart() ?><small>Nb de cols</small></th>
         <th style="width: 20%"><?php echo tdStart() ?><small>Observations</small></th>
       </tr>
   <?php endif;?>
    <tr style="line-height:17px; font-size: 11px;">
     <td><?php echo tdStart() ?><?php if($lot->numero_archive): ?><?php echo $lot->numero_dossier.'&nbsp;/&nbsp;'.$lot->numero_archive ?><?php endif; ?></td>
     <td style="text-align:left;"><?php echo tdStart() ?><?php echo showProduitCepagesLot($lot, false, 'span'); ?></td>
     <td style="text-align:left;"><?php echo tdStart() ?><?php echo $lot->numero_logement_operateur ?></td>
     <td><?php echo tdStart() ?><?php echo $lot->initial_type ?></td>
     <td><?php echo tdStart() ?>&nbsp;</td>
     <td><?php echo tdStart() ?><?php echoFloat($lot->volume); ?></td>
    <?php if ($lot->exist('quantite')): ?>
     <td><?php echo tdStart() ?><?php echo($lot->quantite); ?></td>
    <?php else: ?>
     <td><?php echo tdStart() ?>&nbsp;<br /><br /></td>
    <?php endif; ?>
     <td><?php echo tdStart() ?>&nbsp;<br /><br /></td>
    </tr>
  <?php endforeach; ?>
  </table>
  <p><small>(*) Type de Prélevement : C (conditionné), V (vrac), Cons (conditionné sur conservatoire), T (témoin)</small></p>
