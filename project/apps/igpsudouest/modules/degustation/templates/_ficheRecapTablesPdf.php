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
        </tr>
      </table>
      <div></div>
      <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
        <thead>
          <tr>
            <th style="width:35%; text-align:left; margin-left: 1em;">Nom</th>
            <th style="width:35%; text-align:left; margin-left: 1em;">Prénom</th>
            <th style="width:30%">Signature</th>
          </tr>
        </thead>
        <tbody>
        <?php if (DegustationConfiguration::getInstance()->hasDegustateursPrerempli()): ?>
            <?php foreach ($degustation->getDegustateursConfirmesTableOrFreeTable($numTab) as $id_compte => $degustateur): ?>
                <?php $compte = CompteClient::getInstance()->find($id_compte); ?>
                  <tr>
                    <td class="text-left" style="width:35%; margin-left: 1em; text-align:left;"><?php echo $compte->getNom() ?></td>
                    <td class="text-left" style="width:35%; margin-left: 1em; text-align:left;"><?php echo $compte->getPrenom() ?></td>
                    <td style="width:30%"><small style="font-size: 4pt;"><br /><br /></small></td>
                  </tr>
              <?php endforeach; ?>
            <?php $t = count($degustation->getDegustateursConfirmesTableOrFreeTable($numTab)); ?>
            <?php for ($i = $t; $i<6; $i++): ?>
            <tr>
                <td class="text-center" style="width:35%; margin-left: 1em;"></td>
                <td class="text-center" style="width:35%; margin-left: 1em;"></td>
                <td style="width:30%"><small style="font-size: 4pt;"><br /><br /></small></td>
            </tr>
            <?php endfor; ?>
        <?php else : ?>
            <?php for ($i = 0; $i<6; $i++): ?>
            <tr>
                <td class="text-center" style="width:35%; margin-left: 1em;"></td>
                <td class="text-center" style="width:35%; margin-left: 1em;"></td>
                <td style="width:30%"><small style="font-size: 4pt;"><br /><br /></small></td>
            </tr>
            <?php endfor; ?>
        <?php endif ?>
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
               <th class="topempty bg-white"style="width:5%; "></th>
               <th class="topempty bg-white" style="width:30%; "><?php echo tdStart() ?><strong>Produit millesime cépage</strong></th>
               <th class="bg-white" colspan="4"style="width:24%;"><?php echo tdStart() ?><strong>Avis</strong></th>
               <th class="topempty bg-white" colspan="1"style="width:10%;"><?php echo tdStart() ?><strong><small>Agrément cépage</small></strong></th>
               <th class="topempty bg-white" style="width:10%;"><?php echo tdStart() ?><strong><small>Agrément primeur</small></strong></th>
               <th class="topempty bg-white" style="width:21%;"><strong>Observations</strong></th>
            </tr>
            <tr style="line-height:13px;">
              <th class="empty bg-white"></th>
              <th class="empty bg-white"></th>
              <th class="bg-white" style="width:6%;" ><?php echo tdStart() ?><strong><small>C</small></strong></th>
              <th class="bg-white" style="width:6%;"><?php echo tdStart() ?><strong><small>NCmi</small></strong></th>
              <th class="bg-white" style="width:6%;"><?php echo tdStart() ?><strong><small>NCma</small></strong></th>
              <th class="bg-white" style="width:6%;"><?php echo tdStart() ?><strong><small>NCg</small></strong></th>
              <th class="empty bg-white"></th>
              <th class="empty bg-white"></th>
            </tr>
      <?php endif; ?>
         <tr style="line-height:15px;">
           <td><?php echo tdStart() ?><strong><small><?php echo $lotInfo->getNumeroAnonymat() ?></small></strong></td>
           <td><?php echo tdStart() ?><small><?php echo substrUtf8(strip_tags(showOnlyProduit($lotInfo, false)), 0, 35);; ?></small><br/><small><?php echo showOnlyCepages($lotInfo, 45);?></small></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?>&nbsp;</td>
         </tr>
         <?php $ligne++; ?>
       <?php endforeach; ?>
      </table>
