<?php use_helper('TemplatingPDF'); ?>
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
            <p>Date : <?php $date = explode("-", substr($degustation->date, 0, 10));echo "$date[2]/$date[1]/$date[0]"; ?></p>
            <p>Heure : <?php echo substr($degustation->date, -5); ?></p>
            <p>Code Commission: <?php echo $degustation->_id; ?></p>
          </td>
          <td style="width:33%;">
            <p>Campagne: <?php echo $degustation->campagne .'/'.($degustation->campagne+1); ?></p>
            <p>Millésime: <?php echo $degustation->campagne; ?></p>

          </td>
          <td style="width:33%">
            <p>Lieu : <?php echo $degustation->getLieuNom(); ?> </p>
          </td>
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
                <td style="width:35%; text-align:left; margin-left: 1em;"></td>
                <td style="width:35%; text-align:left; margin-left: 1em;"></td>
                <td style="width:30%">
                </td>
              </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>

    <div>
      <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
        <tr style="line-height:20px;">
           <th class="topempty bg-white"style="width:7%; "><?php echo tdStart() ?><strong>Anon</strong></th>
           <th class="topempty bg-white" style="width:10%;"><?php echo tdStart() ?><strong>Lgmt</strong></th>
           <th class="topempty bg-white" style="width:10%; "><?php echo tdStart() ?><strong>Couleur</strong></th>
           <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>IGP</strong></th>
           <th class="topempty bg-white"style="width:13%;"><?php echo tdStart() ?><strong>Cépage</strong></th>
           <th class="bg-white" colspan="2"style="width:10%;"><?php echo tdStart() ?><strong>Avis</strong></th>
           <th class="bg-white"  colspan="2"style="width:10%;"><?php echo tdStart() ?><strong>Typicité cépage</strong></th>
           <th class="topempty bg-white" style="width:8%;"><?php echo tdStart() ?><strong>Note</strong></th>
           <th class="topempty bg-white" style="width:22%;"><strong>Motifs (si non conforme)</strong></th>
        </tr>
        <tr style="line-height:13px;">
          <th class="empty bg-white"></th>
          <th class="empty bg-white"></th>
          <th class="empty bg-white"></th>
          <th class="empty bg-white"></th>
          <th class="empty bg-white"></th>
          <th class="bg-white" style="width:5%;" ><?php echo tdStart() ?><strong><small>C</small></strong></th>
          <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>NC</small></strong></th>
          <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>C</small></strong></th>
          <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>NC</small></strong></th>
          <th class="empty bg-white"></th>
          <th class="empty bg-white"></th>
        </tr>
        <?php  $i=0;
        foreach($lots as $numAnonyme => $lotInfo): ?>
        <?php if($i == 11 || ($i - 11) % 20 > 18): ?>
     </table>
          <br pagebreak="true" />
          <p>Suite des lots table <?php echo $lotInfo->getNumeroTableStr(); ?><p/>
          <br/>
          <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
            <tr style="line-height:20px;">
               <th class="topempty bg-white"style="width:7%; "><?php echo tdStart() ?><strong>Anon</strong></th>
               <th class="topempty bg-white" style="width:10%;"><?php echo tdStart() ?><strong>Lgmt</small></th>
               <th class="topempty bg-white" style="width:10%; "><?php echo tdStart() ?><strong>Couleur</strong></th>
               <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>IGP</strong></th>
               <th class="topempty bg-white"style="width:13%;"><?php echo tdStart() ?><strong>Cépage</strong></th>
               <th class="bg-white" colspan="2"style="width:10%;"><?php echo tdStart() ?><strong>Avis</strong></th>
               <th class="bg-white"  colspan="2"style="width:10%;"><?php echo tdStart() ?><strong>Typicité cépage</strong></th>
               <th class="topempty bg-white" style="width:8%;"><?php echo tdStart() ?><strong>Note</strong></th>
               <th class="topempty bg-white" style="width:22%;"><strong>Motifs (si non conforme)</strong></th>
            </tr>
            <tr style="line-height:13px;">
              <th class="empty bg-white"></th>
              <th class="empty bg-white"></th>
              <th class="empty bg-white"></th>
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
         <tr style="line-height:17px;">
           <td><?php echo tdStart() ?><strong><small><?php echo $lotInfo->getNumeroAnonymat() ?></small></strong></td>
           <td><?php echo tdStart() ?><strong><small><?php echo $lotInfo->numero_logement_operateur ?></small></strong></td>
           <td><?php echo tdStart() ?><strong><small><?php echo $lotInfo->getConfig()->getCouleur()->getLibelle();  ?></small></strong></td>
           <td><?php echo tdStart() ?>
             <small>&nbsp;<?php echo $lotInfo->getConfig()->getAppellation()->getLibelle(); ?></small>
             <?php if(DegustationConfiguration::getInstance()->hasSpecificiteLotPdf() && DrevConfiguration::getInstance()->hasSpecificiteLot() && $lotInfo->specificite): ?>
             <br/><small style="color: #777777;font-size :14px"><?php echo " ($lotInfo->specificite)";?></small>
           <?php endif ?>
           </td>
           <td><?php echo tdStart() ?><small><?php echo $lotInfo->details;?></small></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?><span class="zap">o</span></td>
           <td><?php echo tdStart() ?>&nbsp;</td>
           <td><?php echo tdStart() ?>&nbsp;</td>
         </tr>
         <?php $i++; ?>
       <?php endforeach; ?>
      </table>
    </div>
