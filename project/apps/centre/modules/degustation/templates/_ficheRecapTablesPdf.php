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
      <table>
        <tr>
          <td style="width:25%;">Date : <i><?php $date = date_create($degustation->date); echo $date->format("d/m/Y"); ?></i></td>
          <td style="width:25%;">Heure : <i><?php echo $date->format("H:i"); ?></i></td>
          <td style="width:50%;">Commission: <i><?php echo $degustation->_id; ?></i></td>
        </tr>
        <tr>
            <td style="width:50%"><?php echo tdStart() ?>Lieu : <i><?php echo $degustation->getLieuNom(); ?></i></td>
            <td style="width:50%;"><?php echo tdStart() ?>Campagne: <i><?php echo $degustation->campagne;?></i></td>
        </tr>
      </table>
      <div></div>
      <table>
        <tr style="line-height:20em;">
          <td style="width:12%">Table :</td>
          <td border="1px" style="width:10%; border-style: solid;text-align:right;">
              <?php echo DegustationClient::getNumeroTableStr($numTab); ?> &nbsp;  &nbsp;
          </td>
          <td style="width:3%">&nbsp;</td>
          <td style="width:45%">Nom & Signature du responsable :</td>
        </tr>
      </table>
      <div></div>
      <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
        <thead>
          <tr>
            <th style="width:25%">Nom</th>
            <th style="width:25%">Prénom</th>
            <th style="width:25%">Collège</th>
            <th style="width:25%">Signature</th>
          </tr>
        </thead>
        <tbody>
          <?php for( $i = 0; $i<5; $i++): ?>
              <tr>
                <td style="width:25%; text-align:left; margin-left: 1em;">&nbsp;<br/>&nbsp;</td>
                <td style="width:25%; text-align:left; margin-left: 1em;"><br/></td>
                <td style="width:25%"><br/></td>
                <td style="width:25%"><br/></td>
              </tr>
          <?php endfor; ?>
        </tbody>
      </table>
      <div></div>
      <?php  $ligne=12; $table_header = true;
      foreach($lots as $numAnonyme => $lotInfo): ?>
      <?php if($ligne % 26 == 0): $table_header = true; ?>
        </table>
          <br pagebreak="true" />
          <table>
            <tr style="line-height:20em;">
              <td style="width:12%">Table <small>(suite)</small> :</td>
              <td border="1px" style="width:10%; border-style: solid;text-align:right;">
                  <?php echo DegustationClient::getNumeroTableStr($numTab); ?> &nbsp;  &nbsp;
              </td>
            </tr>
          </table>
          <div></div>
      <?php endif; ?>
      <?php if ($table_header): $table_header = false; ?>
          <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
            <tr style="line-height:20px;">
               <th class="topempty bg-white"style="width:10%; "><?php echo tdStart() ?><strong>Anon</strong></th>
               <th class="topempty bg-white" style="width:30%; "><?php echo tdStart() ?><strong>Produit millesime cépage</strong></th>
               <th class="bg-white" colspan="2"style="width:30%;"><?php echo tdStart() ?><strong>Avis</strong></th>
               <th class="topempty bg-white" style="width:30%;"><strong>Motifs (si non conforme)</strong></th>
            </tr>
            <tr style="line-height:13px;">
              <th class="empty bg-white"></th>
              <th class="empty bg-white"></th>
              <th class="bg-white" style="width:15%;" ><?php echo tdStart() ?><strong><small>Fav.</small></strong></th>
              <th class="bg-white" style="width:15%;"><?php echo tdStart() ?><strong><small>Def.</small></strong></th>
              <th class="empty bg-white"></th>
            </tr>
      <?php endif; ?>
         <tr style="line-height:15px;">
           <td><?php echo tdStart() ?><strong><small><?php echo $lotInfo->getNumeroAnonymat() ?></small></strong></td>
           <td><?php echo tdStart() ?><small><?php echo substrUtf8(strip_tags(showOnlyProduit($lotInfo, false)), 0, 35);; ?></small><br/><small><?php echo showOnlyCepages($lotInfo, 45);?></small></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?>&nbsp;</td>
         </tr>
         <?php $ligne++; ?>
       <?php endforeach; ?>
      </table>
