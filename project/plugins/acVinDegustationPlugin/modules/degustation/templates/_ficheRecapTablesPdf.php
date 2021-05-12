<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<style>
<?php echo style(); ?>
.bg-white{
  background-color:white;
}
th {
  background-color:white;
}

</style>
    <div>
      <table>
        <tr>
          <td style="width:33%;">
            <p>Date : <?php $date = date_create($degustation->date); echo $date->format("d/m/Y"); ?></p>
            <p>Heure : <?php echo $date->format("H:i"); ?></p>
          </td>
          <td style="width:33%;">
            <p>Campagne: <?php echo $degustation->campagne;?></p>
          </td>
          <td style="width:33%">
            <p>Lieu : <?php echo $degustation->getLieuNom(); ?> </p>
          </td>
        </tr>
        <br/>
        <tr>
          <td colspan="3">&nbsp;&nbsp;Code Commission: <?php echo $degustation->_id; ?></td>
        </tr>
      </table>
    </div>
    <div style="margin-bottom: 2em;">
      <table>
        <tr style="line-height:20em;">
          <td style="width:80%"></td>
          <td style="width:10%">Table :</td>
          <td border="1px" style="width:10%; border-style: solid;text-align:right;">
              <?php echo DegustationClient::getNumeroTableStr($numTab); ?> &nbsp;  &nbsp;
          </td>
        </tr>
      </table>
    </div>
    <div>
      <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
        <thead>
          <tr>
            <th style="width:35%">Nom</th>
            <th style="width:35%">Prénom</th>
            <th style="width:30%">Signature</th>
          </tr>
        </thead>
        <tbody>
          <?php for( $i = 0; $i<5; $i++): ?>
              <tr>
                <td style="width:35%; text-align:left; margin-left: 1em;">&nbsp;<br/>&nbsp;</td>
                <td style="width:35%; text-align:left; margin-left: 1em;"><br/></td>
                <td style="width:30%"><br/></td>
              </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>

    <div>
      <?php  $ligne=12; $table_header = true;
      foreach($lots as $numAnonyme => $lotInfo): ?>
      <?php if($ligne % 20 == 0): $table_header = true; ?>
        </table>
          <br pagebreak="true" />
          <p>Suite des lots table <?php echo $lotInfo->getNumeroTableStr(); ?><p/>
          <br/>
      <?php endif; ?>
      <?php if ($table_header): $table_header = false; ?>
          <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
            <tr style="line-height:20px;">
               <th class="topempty bg-white"style="width:10%; "><?php echo tdStart() ?><strong>Anon</strong></th>
               <th class="topempty bg-white" style="width:30%; "><?php echo tdStart() ?><strong>Produit millesime cépage</strong></th>
               <th class="bg-white" colspan="2"style="width:10%;"><?php echo tdStart() ?><strong>Avis</strong></th>
               <th class="bg-white"  colspan="2"style="width:10%;"><?php echo tdStart() ?><strong>Typicité cépage</strong></th>
               <th class="topempty bg-white" style="width:10%;"><?php echo tdStart() ?><strong>Note</strong></th>
               <th class="topempty bg-white" style="width:30%;"><strong>Motifs (si non conforme)</strong></th>
            </tr>
            <tr style="line-height:13px;">
              <th class="empty bg-white"></th>
              <th class="empty bg-white"></th>
              <th class="bg-white" style="width:5%;" ><?php echo tdStart() ?><strong><small>C</small></strong></th>
              <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>NC</small></strong></th>
              <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>C</small></strong></th>
              <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>NC</small></strong></th>
              <th class="empty bg-white"></th>
              <th class="empty bg-white"></th>
            </tr>
      <?php endif; ?>
         <tr style="line-height:15px;">
           <td><?php echo tdStart() ?><strong><small><?php echo $lotInfo->getNumeroAnonymat() ?></small></strong></td>
           <td><?php echo tdStart() ?><small><?php echo mb_substr(strip_tags(showOnlyProduit($lotInfo)), 0, 40) ?></small><br/><small><?php echo showOnlyCepages($lotInfo, 45);?></small></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?>&nbsp;</td>
           <td><?php echo tdStart() ?>&nbsp;</td>
         </tr>
         <?php $ligne++; ?>
       <?php endforeach; ?>
      </table>
    </div>
