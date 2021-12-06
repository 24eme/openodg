<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<style>
<?php echo style(); ?>

</style>
      <table>
        <tr>
          <td><strong>Date : <?php $date = date_create($degustation->date); echo $date->format("d/m/Y"); ?></strong><br><strong>Heure : <?php echo $date->format("H:i"); ?></strong>
          </td>
          <td><strong>Campagne : <?php echo $degustation->campagne ?></strong><br><strong>Commission: <?php echo $degustation->_id; ?></strong>
          </td>
          <td><strong>Lieu : <?php echo $degustation->getLieuNom(); ?></strong></td>
        </tr>
      </table>
      <small><br /></small>
      <strong>Table : &nbsp;<?php echo $lots[0]->getNumeroTableStr(); ?></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong> Nom : &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
          <strong>Prénom : &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
          <strong>Signature : &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
<div></div>
<?php $i = 1; $table_header = true; $table_num = 1;
 foreach($lots as $lotInfo): ?>
   <?php if ($i % 11 == 0 ) : $table_header = true; $table_num++; ?>
</table>
     <br pagebreak="true" />
     <strong>Table : &nbsp;<?php echo $lots[0]->getNumeroTableStr(); ?> <small>(suite)</small></strong><small><br /><br /></small>
   <?php endif; ?>
   <?php if ($table_header): $table_header = false; ?>
     <table border="1px" class="table" id="table_fiche_<?php echo $table_num ?>" $cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
       <tr style="line-height:20px;">
          <th class="topempty bg-white"style="width:5%; "><?php echo tdStart() ?><strong>Anon</strong></th>
          <th class="topempty bg-white" style="width:34%; "><?php echo tdStart() ?><strong>Produit millesime cépage</strong></th>
         <th colspan="4"style="width:20%;"><?php echo tdStart() ?><strong>NOTATION</strong></th>
          <th class="bg-white" colspan="2"style="width:8%;"><?php echo tdStart() ?><strong>Avis</strong></th>
          <th class="bg-white"  colspan="2"style="width:8%;"><?php echo tdStart() ?><strong>Typicité cépage</strong></th>
          <th class="topempty bg-white" style="width:25%;"><strong>Motifs (si non conforme)</strong></th>
       </tr>
       <tr style="line-height:13px;">
         <th class="empty bg-white"></th>
         <th class="empty bg-white"></th>
         <th style="width:5%;"><?php echo tdStart() ?><strong><small>Visuel<br><?php if($notation): ?>/12<?php endif ?></small></strong></th>
         <th style="width:5%;"><?php echo tdStart() ?><strong><small>Olfactif<br><?php if($notation): ?>/12<?php endif ?></small></strong></th>
         <th style="width:5%;"><?php echo tdStart() ?><strong><small>Gustatif<br><?php if($notation): ?>/24<?php endif ?></small></strong></th>
         <th style="width:5%;"><?php echo tdStart() ?><strong><small>TOTAL<br><?php if($notation): ?>/48<?php endif ?></small></strong></th>
         <th class="bg-white" style="width:4%;"><?php echo tdStart() ?><strong><small>C</small></strong></th>
         <th class="bg-white" style="width:4%;"><?php echo tdStart() ?><strong><small>NC</small></strong></th>
         <th class="bg-white" style="width:4%;"><?php echo tdStart() ?><strong><small>C</small></strong></th>
         <th class="bg-white" style="width:4%;"><?php echo tdStart() ?><strong><small>NC</small></strong></th>
         <th class="empty bg-white"></th>
       </tr>
   <?php endif;?>

    <tr style="line-height:30px; height:32px">
      <td><?php echo tdStart() ?>&nbsp;<strong><?php echo $lotInfo->getNumeroAnonymat() ?></strong></td>
      <td style="text-align:left;"><?php echo tdStart() ?><span style="line-height: 16px;"> <?php echo showOnlyProduit($lotInfo, false, 'span') ?> <?php echo showOnlyCepages($lotInfo, false) ?></span></td>
      <td><?php echo tdStart() ?></td>
      <td><?php echo tdStart() ?></td>
      <td><?php echo tdStart() ?></td>
      <td><?php echo tdStart() ?></td>
      <td><?php echo tdStart() ?><span class="zap">o</span></td>
      <td><?php echo tdStart() ?><span class="zap">o</span></td>
      <td><?php echo tdStart() ?><span class="zap">o</span></td>
      <td><?php echo tdStart() ?><span class="zap">o</span></td>
      <td><?php echo tdStart() ?>&nbsp;</td>
    </tr>
    <?php $i++; ?>
  <?php endforeach; ?>
</table>
