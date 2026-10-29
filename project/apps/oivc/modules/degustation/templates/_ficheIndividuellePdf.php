<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<style>
<?php echo style(); ?>

</style>
      <div></div>
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
          <th class="topempty bg-white" style="width:20%; "><?php echo tdStart() ?><strong>Produit millesime</strong></th>
          <th class="bg-white" colspan="2" style="width:10%;"><?php echo tdStart() ?><strong>Visuelles</strong></th>
          <th class="bg-white" colspan="2" style="width:10%;"><?php echo tdStart() ?><strong>Olfactives</strong></th>
          <th class="bg-white" colspan="2" style="width:10%;"><?php echo tdStart() ?><strong>Gustatives</strong></th>
          <th class="bg-white" colspan="4" style="width:45%;"><strong>Conclusions</strong></th>
       </tr>
       <tr style="line-height:13px;">
         <th class="empty bg-white" style="width:5%;"></th>
         <th class="empty bg-white" style="width:20%;"></th>
         <th style="width:5%;"><?php echo tdStart() ?><strong><small>S *</small></strong></th>
         <th style="width:5%;"><?php echo tdStart() ?><strong><small>NS *</small></strong></th>
         <th style="width:5%;"><?php echo tdStart() ?><strong><small>S *</small></strong></th>
         <th style="width:5%;"><?php echo tdStart() ?><strong><small>NS *</small></strong></th>
         <th style="width:5%;"><?php echo tdStart() ?><strong><small>S *</small></strong></th>
         <th style="width:5%;"><?php echo tdStart() ?><strong><small>NS *</small></strong></th>
         <th class="bg-white" style="width:10%;"><?php echo tdStart() ?><strong><small>Favorable</small></strong></th>
         <th class="bg-white" style="width:10%;"><?php echo tdStart() ?><strong><small>Defavorable</small></strong></th>
         <th class="empty bg-white"  style="width:25%;">Motifs</th>
       </tr>
   <?php endif;?>

    <tr style="line-height:30px; height:32px">
      <td><?php echo tdStart() ?>&nbsp;<strong><?php echo $lotInfo->getNumeroAnonymat() ?></strong></td>
      <td style="text-align:left;"><?php echo tdStart() ?><span style="line-height: 16px;"> <?php echo showOnlyProduit($lotInfo, false, 'span') ?></span></td>
      <td><?php echo tdStart() ?><span class="zap">o</span></td>
      <td><?php echo tdStart() ?><span class="zap">o</span></td>
      <td><?php echo tdStart() ?><span class="zap">o</span></td>
      <td><?php echo tdStart() ?><span class="zap">o</span></td>
      <td><?php echo tdStart() ?><span class="zap">o</span></td>
      <td><?php echo tdStart() ?><span class="zap">o</span></td>
      <td><?php echo tdStart() ?><span class="zap">o</span></td>
      <td><?php echo tdStart() ?><span class="zap">o</span></td>
      <td><?php echo tdStart() ?>&nbsp;</td>
    </tr>
    <?php $i++; ?>
  <?php endforeach; ?>
</table>
<p>L’avis favorable signifie que le lot représenté par l’échantillon dispose des caractéristiques du cahier des charges de l’AOC, ne présente pas de défaut, est acceptable au sein de son appellation.</p>
<p>* S = Satisfaisant - NS = Non Satisfaisant</p>
