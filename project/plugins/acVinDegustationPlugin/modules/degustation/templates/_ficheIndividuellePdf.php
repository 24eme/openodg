<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<style>
<?php echo style(); ?>

</style>
      <table>
        <tr>
          <td><?php echo tdStart() ?><br>
              <strong>Date : <?php $date = date_create($degustation->date); echo $date->format("d/m/Y"); ?></strong><br>
              <strong>Heure : <?php echo $date->format("H:i"); ?></strong>
          </td>
          <td><?php echo tdStart() ?><br>
              <strong>Campagne : <?php echo $degustation->campagne ?></strong><br>
              <strong>Commission: <?php echo $degustation->_id; ?></strong>
          </td>
          <td><?php echo tdStart() ?><br>
            <strong>Lieu : <?php echo $degustation->getLieuNom(); ?></strong>
          </td>
        </tr>
      </table>
      <p> <strong> Nom : &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
          <strong>Prénom : &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
          <strong>Signature : &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
          <strong>Table : &nbsp;<?php echo $lots[0]->getNumeroTableStr(); ?></strong>
      </p>
    </table>

<?php $i = 18; $table_header = true;
 foreach($lots as $lotInfo): ?>
   <?php if ($i % 30 == 0 ) : $table_header = true; ?>
</table>
     <br pagebreak="true" />
     <p>Suite des lots table <?php echo $lotInfo->getNumeroTableStr(); ?><p/>
     <br/>
   <?php endif; ?>
   <?php if ($table_header): $table_header = false; ?>
     <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
       <tr style="line-height:20px;">
          <th class="topempty bg-white"style="width:7%; "><?php echo tdStart() ?><strong>Anon</strong></th>
          <th class="topempty bg-white" style="width:30%; "><?php echo tdStart() ?><strong>Produit millesime cépage</strong></th>
         <th colspan="4"style="width:20%;"><?php echo tdStart() ?><strong>NOTATION</strong></th>
          <th class="bg-white" colspan="2"style="width:8%;"><?php echo tdStart() ?><strong>Avis</strong></th>
          <th class="bg-white"  colspan="2"style="width:8%;"><?php echo tdStart() ?><strong>Typicité cépage</strong></th>
          <th class="topempty bg-white" style="width:30%;"><strong>Motifs (si non conforme)</strong></th>
       </tr>
       <tr style="line-height:13px;">
         <th class="empty bg-white"></th>
         <th class="empty bg-white"></th>
         <th style="width:5%;"><?php echo tdStart() ?><strong><small>Visuel<br>/12</small></strong></th>
         <th style="width:5%;"><?php echo tdStart() ?><strong><small>Olfactif<br>/12</small></strong></th>
         <th style="width:5%;"><?php echo tdStart() ?><strong><small>Gustatif <br> /24</small></strong></th>
         <th style="width:5%;"><?php echo tdStart() ?><strong><small>TOTAL<br/>/48</small></strong></th>
         <th class="bg-white" style="width:4%;" ><?php echo tdStart() ?><strong><small>C</small></strong></th>
         <th class="bg-white" style="width:4%;"><?php echo tdStart() ?><strong><small>NC</small></strong></th>
         <th class="bg-white" style="width:4%;"><?php echo tdStart() ?><strong><small>C</small></strong></th>
         <th class="bg-white" style="width:4%;"><?php echo tdStart() ?><strong><small>NC</small></strong></th>
         <th class="empty bg-white"></th>
       </tr>
   <?php endif;?>

    <tr style="line-height:25px;">
      <td><?php echo tdStart() ?>&nbsp;<strong><?php echo $lotInfo->getNumeroAnonymat() ?></strong></td>
      <td><?php echo tdStart() ?><?php echo showProduitLot($lotInfo) ?></td>
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
