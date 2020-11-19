<?php use_helper('TemplatingPDF'); ?>
<style>
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

    <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;">
      <tr style="line-height:15px;">
         <td style="width:5%; "><strong>N°Ano</strong></td>
         <td style="width:7%; "><strong>Couleur</strong></td>
         <td style="width:13%;"><strong>IGP</strong></td>
         <td style="width:10%;"><strong>Cépage</strong></td>
         <td style="width:26%;">
           <table width="100%" border="1" class="0" cellspacing="0" cellpadding=0 >
             <tr style="text-align: center; width:100%;line-height:30px;">
               <td  colspan="4"><strong>NOTATION</strong></td>
              </tr>
             <tr style="text-align: center;line-height:10px;">
               <td style='width:25%;'><strong><small>Visuel <br>/12</small></strong></td>
               <td style='width:25%;'><strong><small>Oifactif <br>/12</small></strong></td>
               <td style='width:25%;'><strong><small>Gustatif  <br> /24</small></strong></td>
               <td style='width:25%;'><strong><small>NOTE TOTALE <br> /48</small></strong></td>
             </tr>
           </table>
         </td>
         <td style="width:7%;">
           <table width="100%" border="1" class="table" cellspacing=0 cellpadding=0 >
             <tr style="text-align: center;line-height:30px;">
               <td colspan="2"><strong>Avis</strong></td>
             </tr>
             <tr style="text-align: center;line-height:27px;">
               <td style='width:50%;' ><strong>C</strong></td>
               <td style='width:50%;'><strong>NC</strong></td>
             </tr>
           </table>
         </td>
         <td style="width:8%;">
           <table width="100%" border="1" class="table" cellspacing=0 cellpadding=0>
             <tr style="text-align: center;line-height:20px;">
               <td colspan="2"><strong>Typicité cépage</strong></td>
             </tr>
             <tr style="text-align: center;line-height:20px;">
               <td style='width:50%;'><strong>C</strong></td>
               <td style='width:50%;'><strong>NC</strong></td>
             </tr>
           </table>
         </td>
         <td style="width:25%;"><strong>Motifs (si non conforme)</strong></td>
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
          <td >
            <table width="100%" border="1" class="0" cellspacing="0" cellpadding=0 >
              <tr style="text-align: center;line-height:100%;">
                <td style='width:25%;'><?php echo ''?></td>
                <td style='width:25%;'><?php echo '';?></td>
                <td style='width:25%;'><?php echo '';?></td>
                <td style='width:25%;'><?php echo '';?></td>
              </tr>
            </table>
          </td>
        <td>
          <table width="100%" border="1" class="0" cellspacing="0" cellpadding=0 >
            <tr style="text-align: center;line-height:100%;">
              <td style='width:25%;'><span style="font-size: 20px"></span></td>
              <td style='width:25%;'><?php echo '□ ';?></td>
            </tr>
          </table>
        </td>

        <td>
          <?php echo tdStart() ?>&nbsp;
        </td>
        <td>
          <?php echo tdStart() ?>&nbsp;
        </td>
      </tr>

      <?php $i=$i+1 ?>
    <?php endforeach; ?>
  </table>
