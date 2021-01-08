<?php use_helper('TemplatingPDF'); ?>
<style>
<?php echo style(); ?>

</style>
      <table>
        <tr>
          <td><?php echo tdStart() ?><br>
              <strong>Date : <?php echo substr($degustation->date,0,10); ?></strong><br>
              <strong>Heure : <?php echo substr($degustation->date,11,16); ?></strong><br>
              <strong>Commission: <?php echo $lots[0]->getNumeroTableStr(); ?></strong><br>
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
          <strong>Jury : &nbsp;<?php echo $lots[0]->getNumeroTableStr(); ?></strong>
      </p>
    </table>

    <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
      <tr style="line-height:20px;">
         <th class="topempty"style="width:50px; "><?php echo tdStart() ?><strong>N°Ano</strong></th>
         <th class="topempty" style="width:70px; "><?php echo tdStart() ?><strong>Couleur</strong></th>
         <th class="topempty"style="width:150px;"><?php echo tdStart() ?><strong>IGP</strong></th>
         <th class="topempty"style="width:100px;"><?php echo tdStart() ?><strong>Cépage</strong></th>
         <th colspan="4"style="width:200px;"><?php echo tdStart() ?><strong>NOTATION</strong></th>
         <th colspan="2"style="width:60px;"><?php echo tdStart() ?><strong>Avis</strong></th>
         <th colspan="2"style="width:60px;"><?php echo tdStart() ?><strong>Typicité cépage</strong></th>
         <th class="topempty" style="width:300px;"><strong>Motifs (si non conforme)</strong></th>
      </tr>
      <tr style="line-height:13px;">
        <th class="empty"></th>
        <th class="empty"></th>
        <th class="empty"></th>
        <th class="empty"></th>
        <th style="width:50px;"><?php echo tdStart() ?><strong><small>Visuel <br>/12</small></strong></th>
        <th style="width:50px;"><?php echo tdStart() ?><strong><small>Oifactif <br>/12</small></strong></th>
        <th style="width:50px;"><?php echo tdStart() ?><strong><small>Gustatif  <br> /24</small></strong></th>
        <th style="width:50px;"><?php echo tdStart() ?><strong><small>NOTE TOTALE /48</small></strong></th>
        <th style="width:30px;" ><?php echo tdStart() ?><strong><small>C</small></strong></th>
        <th style="width:30px;"><?php echo tdStart() ?><strong><small>NC</small></strong></th>
        <th style="width:30px;"><?php echo tdStart() ?><strong><small>C</small></strong></th>
        <th style="width:30px;"><?php echo tdStart() ?><strong><small>NC</small></strong></th>
        <th class="empty"></th>
      </tr>
    <?php $i=1;?>
     <?php  foreach($lots as $lotInfo): ?>
      <tr style="line-height:17px;">
        <td><?php echo tdStart() ?>&nbsp;<strong><?php echo $lotInfo->getNumeroAnonymise() ?></strong></td>
        <td><?php echo tdStart() ?>&nbsp;<strong><?php echo $lotInfo->getConfig()->getCouleur()->getLibelle();  ?></strong></td>
        <td><?php echo tdStart() ?>
          &nbsp;<?php echo $lotInfo->getConfig()->getAppellation()->getLibelle(); ?>
          <?php if(DegustationConfiguration::getInstance()->hasSpecificiteLotPdf() && DrevConfiguration::getInstance()->hasSpecificiteLot()): ?>
          <br/><small style="color: #777777;font-size :14px"><?php echo " ($lotInfo->specificite)";?></small>
        <?php endif ?>
        </td>
        <td><?php echo tdStart() ?>&nbsp;<small><?php echo $lotInfo->details;?></small></td>
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

      <?php $i=$i+1 ?>
    <?php endforeach; ?>
  </table>
