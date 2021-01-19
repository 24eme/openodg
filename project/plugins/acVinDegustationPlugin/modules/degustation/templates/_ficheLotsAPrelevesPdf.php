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
          <td style="width:20%;"></td>
          <td style="width: 50%">
            <div border="1px" style="border-style: solid; background-color: #E0E0E0;">
              <p style="margin: 2em; padding-left: 2em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fiche de tournée (Liste des lots à prélever)</p>
            </div>
          </td>
          <td style="width:20%;"></td>
        </tr>
        <tr>
          <td style="width:20%;">
          </td>
          <td style="width:30%;">
            <p>Préleveur :</p>
          </td>
          <td style="width:30%">
            <p>Date d'édition : <?php echo $date_edition;?></p>
          </td>
          <td style="width:20%;">
          </td>
        </tr>
      </table>
    </div>
    <div>
      <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
        <tr style="line-height:20px;">
          <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>Raison sociale</strong></th>
          <th class="topempty bg-white"style="width:20%;"><?php echo tdStart() ?><strong>Adresse prélèvement</strong></th>
          <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>Tel / Fix / Port </strong></th>
          <th class="topempty bg-white"style="width:12%;"><?php echo tdStart() ?><strong>Dosssier /<br/> Nb Lots</strong></th>
          <th class="topempty bg-white"style="width:16%;"><?php echo tdStart() ?><strong>Laboratoire</strong></th>
          <th class="topempty bg-white"style="width:10%;"><?php echo tdStart() ?><strong>Date /<br/> Heure</strong></th>
          <th class="topempty bg-white"style="width:12%;"><?php echo tdStart() ?><strong>Date<br/> commission</strong></th>
        </tr>
        <?php
    $nbLotTotal = 0; $i=0;
    foreach($etablissements as $numDossier => $etablissement): ?>
    <?php if($i == 12 || ($i - 12) % 17 > 16): //display 12 Lots on the first page and below 17 Lots all others pages?>
      </table>
      <br pagebreak="true" />
      <p>Suite des lots<p/>
      <br/>
      <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;border-collapse:collapse;" scope="colgroup" >
        <tr style="line-height:20px;">
          <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>Raison sociale</strong></th>
          <th class="topempty bg-white"style="width:20%;"><?php echo tdStart() ?><strong>Adresse prélèvement</strong></th>
          <th class="topempty bg-white"style="width:15%;"><?php echo tdStart() ?><strong>Tel / Fix / Port </strong></th>
          <th class="topempty bg-white"style="width:12%;"><?php echo tdStart() ?><strong>Dosssier /<br/> Nb Lots</strong></th>
          <th class="topempty bg-white"style="width:16%;"><?php echo tdStart() ?><strong>Laboratoire</strong></th>
          <th class="topempty bg-white"style="width:10%;"><?php echo tdStart() ?><strong>Date /<br/> Heure</strong></th>
          <th class="topempty bg-white"style="width:12%;"><?php echo tdStart() ?><strong>Date<br/> commission</strong></th>
        </tr>
      <?php endif;?>
         <tr style="line-height:17px;">
           <td><?php echo tdStart() ?><strong><small><?php echo $etablissement->raison_sociale ?></small></strong></td>
           <td><?php echo tdStart() ?>
              <small><?php echo $etablissement->adresse ?></small>
              <br/>
              <strong><small><?php echo $etablissement->code_postal. ' '.$etablissement->commune; ?></small></strong>
           </td>
           <td><?php echo tdStart() ?>
             <small>
             <?php echo ($etablissement->telephone_bureau) ? 'Fix: '.$etablissement->telephone_bureau : '' ?><br/>
             <?php echo ($etablissement->telephone_perso) ? 'Port: '.$etablissement->telephone_perso : '' ?><br/>
            </small>
          </td>
          <td><?php echo tdStart() ?>
            <small>n°&nbsp;<?php echo $numDossier; ?></small><br/>
            <small><?php echo count($lots[$numDossier]); ?>&nbsp;lot(s)</small>
            <?php $nbLotTotal += count($lots[$numDossier]); ?>
          </td>
          <td><?php echo tdStart() ?>
            <small><?php //echo $degustation->laboratoire; ?></small>
          </td>
          <td><?php echo tdStart() ?>

          </td>
          <td><?php echo tdStart() ?>
            <small> <?php $date = explode("-", substr($degustation->date, 0, 10));echo "$date[2]/$date[1]/$date[0]"; ?></small>
          </td>
         </tr>
         <?php $i++; ?>
      <?php endforeach; ?>
      </table>
      <table>
        <tr style="line-height: 25em; height:25em;">
          <td style="width:20%;"></td>
          <td style="width:80%;"><?php echo "Nombre total d'opérateurs : ".count($etablissements)." - Nombre total de lots à Prélever : ".$nbLotTotal; ?></td>
        </tr>
      </table>
    </div>
