<?php use_helper('TemplatingPDF'); ?>
<style>
<?php echo style(); ?>

</style>
      <table>
        <tr>
          <td><?php echo tdStart() ?><br>
              <strong>Date : <?php echo substr($degustation->date,0,10); ?></strong><br>
              <strong>Heure : <?php echo substr($degustation->date,11,16); ?></strong><br>
              <strong>Commission: <?php  ?></strong><br>
          </td>
          <td><?php echo tdStart() ?><br>
              <strong>Campagne : <?php echo $degustation->campagne ?></strong><br>
              <strong>Millesime :</strong><br>
          </td>
          <td><?php echo tdStart() ?><br>
            <strong>Lieu: <?php echo $degustation->lieu; ?></strong>
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
          <strong>Jury n° : &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
      </p>
    </table>
    <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;" scope="colgroup" >
      <tr style="line-height:10px;">
         <th style="width:6%; "><?php echo tdStart() ?><strong><br><br>N°Ano</strong></th>
         <th style="width:7%; "><?php echo tdStart() ?><strong><br><br>Couleur</strong></th>
         <th style="width:13%;"><?php echo tdStart() ?><strong><br><br>IGP</strong></th>
         <th style="width:10%;"><?php echo tdStart() ?><strong><br><br>Cépage</strong></th>
         <th style="width:29%;"><?php echo tdStart() ?>
           <table width="100%" border="1" class="table" cellspacing=0 cellpadding=0 scope="colgroup">
             <tr style="text-align: center;line-height:30px;"scope="colgroup">
               <td  colspan="4"><strong>NOTATION</strong></td>
              </tr>
             <tr style="text-align: center;line-height:15px;">
               <td style='width:25%;'><?php echo tdStart() ?><strong><small>Visuel <br>/12</small></strong></td>
               <td style='width:25%;'><?php echo tdStart() ?><strong><small>Oifactif <br>/12</small></strong></td>
               <td style='width:25%;'><?php echo tdStart() ?><strong><small>Gustatif  <br> /24</small></strong></td>
               <td style='width:25%;'><?php echo tdStart() ?><strong><small>NOTE TOTALE /48</small></strong></td>
             </tr>
           </table>
         </th>
         <th style="width:7%;"><?php echo tdStart2() ?>
           <table width="100%"border="1" class="table" cellspacing=0 cellpadding=0 scope="colgroup" >
             <tr style="text-align: center;line-height:30px;" scope="colgroup">
               <td colspan="2"><strong>Avis</strong></td>
             </tr>
             <tr style="text-align: center;line-height:27px;">
               <td style='width:50%;' ><?php echo tdStart() ?><strong>C</strong></td>
               <td style='width:50%;'><?php echo tdStart() ?><strong>NC</strong></td>
             </tr>
           </table>
         </th>
         <th style="width:7%;"><?php echo tdStart2() ?>
           <table width="100%" border="1" class="table" cellspacing=0 cellpadding=0 >
             <tr style="text-align: center;line-height:15px;" scope="colgroup">
               <td colspan="2"><strong>Typicité cépage</strong></td>
             </tr>
             <tr style="text-align: center;line-height:32px;">
               <td style='width:50%;'><strong>C</strong></td>
               <td style='width:50%;'><strong>NC</strong></td>
             </tr>
           </table>
         </th>
         <th style="width:30%;"><strong><br><br>Motifs (si non conforme)</strong></th>
      </tr>
    <?php $i=1;?>
     <?php  foreach($lots as $lotInfo): ?>
      <tr style="line-height:8px;">
        <td >
          <?php echo tdStart() ?>&nbsp;<strong><?php echo $i ?></strong>
        </td>

        <td >
          <?php echo tdStart() ?>&nbsp;<strong><?php echo $lotInfo->getConfig()->getCouleur()->getLibelle();  ?></strong>
        </td>

        <td>
          <?php echo tdStart() ?>&nbsp;<?php echo $lotInfo->getConfig()->getAppellation()->getLibelle(); ?>
        </td>
        <td>
          <?php echo tdStart() ?>&nbsp;<small><?php echo $lotInfo->details;?></small>
        </td>
          <td ><?php echo tdStart2() ?>
            <table  border="1" class="0" cellspacing="0" cellpadding=0 >
              <tr style="text-align: center;line-height:200%;">
                <td style='width:25%;'><?php echo tdStart() ?></td>
                <td style='width:25%;'><?php echo tdStart() ?></td>
                <td style='width:25%;'><?php echo tdStart() ?></td>
                <td style='width:25%;'><?php echo tdStart() ?></td>
              </tr>
            </table>
          </td>
        <td><?php echo tdStart2() ?>
          <table border="1" class="0" cellspacing="0" cellpadding=0 >
            <tr style="text-align: center;line-height:200%;">
              <td style='width:25%; height:20px;'><?php echo tdStart() ?><span class="zap">o</span></td>
              <td style='width:25%;height:20px'><?php echo tdStart() ?><span class="zap">o</span></td>
            </tr>
          </table>
        </td>

        <td><?php echo tdStart2() ?>
          <table border="1" class="0" cellspacing="0" cellpadding=0 >
            <tr style="text-align: center;line-height:200%;">
              <td style='width:25%; height:20px;'><?php echo tdStart() ?><span class="zap">o</span></td>
              <td style='width:25%;height:20px'><?php echo tdStart() ?><span class="zap">o</span></td>
            </tr>
          </table>
        </td>
        <td>
          <?php echo tdStart() ?>&nbsp;
        </td>
      </tr>

      <?php $i=$i+1 ?>
    <?php endforeach; ?>
  </table>
