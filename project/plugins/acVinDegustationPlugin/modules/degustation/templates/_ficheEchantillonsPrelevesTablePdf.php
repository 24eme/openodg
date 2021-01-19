<?php use_helper('TemplatingPDF'); ?>
<style>
<?php echo style(); ?>
.bg-white{
  background-color:white;
}

</style>
    <div>
      <table style="line-height:15px;">
          <tr>
            <td style="width:33%;">
              <p>Code Commission: _ _ _ _ </p>
            </td>
            <td style="width:60%;">
              <p>Responsable : _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _</p>
            </td>
            <td style="width:2%">
            </td>
          </tr>

          <tr>
            <td style="width:33%;">
              <p>Date : <?php $date = explode("-", substr($degustation->date, 0, 10));echo "$date[2]/$date[1]/$date[0]"; ?></p>
            </td>
            <td style="width:33%;">
              <p>Heure : <?php echo substr($degustation->date, -5); ?></p>
            </td>
            <td style="width:33%">
              <p>Lieu : <?php echo $degustation->lieu; ?> </p>
            </td>
          </tr>

          <tr>
            <td style="width:33%;">
              <p>Campagne: <?php echo $degustation->campagne .'/'.($degustation->campagne+1); ?></p>
            </td>
            <td style="width:33%;">
              <p>Millésime: <?php echo $degustation->campagne; ?></p>
            </td>
            <td style="width:33%">
            </td>
          </tr>
      </table>
    </div>

    <?php $nbLots = 0; foreach($lots as $numero_dossier => $lotInfo):  ?>
      <?php foreach ($lotInfo as $numAnonyme => $lot): ?>
        <?php if($lot->numero_table == $table): ?>
          <?php $nbLots +=1; ?>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php endforeach; ?>
    <p>Nombre total de lots : <?php echo $nbLots;?>&nbsp;&nbsp;Table : <?php echo DegustationClient::getNumeroTableStr($table); ?></p>
    <table border="0.5px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;">
      <tr style="line-height:20px;">
         <th rowspan="2" class="topempty bg-white"style="width:10%;"><?php echo tdStart() ?><strong>N° Dossier</strong></th>
         <th rowspan="2" class="topempty bg-white" style="width:20%; "><?php echo tdStart() ?><strong>Raison Sociale<br>N°CVI</strong></th>
         <th class="bg-white" colspan="6"style="width:70%;"><?php echo tdStart() ?><strong>Liste des lots</strong></th>
      </tr>
      <tr style="line-height:13px;">
        <th class="bg-white" style="width:7%;"><?php echo tdStart() ?><strong><small>N°Lot ODG</small></strong></th>
        <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>N°Anon</small></strong></th>
        <th class="bg-white" style="width:9%;"><?php echo tdStart() ?><strong><small>Cuve</small></strong></th>
        <th class="bg-white" style="width:9%;"><?php echo tdStart() ?><strong><small>Vol (hl)</small></strong></th>
        <th class="bg-white" style="width:20%;"><?php echo tdStart() ?><strong><small>IGP/Couleur</small></strong></th>
        <th class="bg-white" style="width:20%;"><?php echo tdStart() ?><strong><small>Cepage</small></strong></th>
      </tr>
    <?php $i=1;?>
     <?php  foreach($lots as $numero_dossier => $lotInfo):  ?>
       <?php $firstDisplay = true; ?>

        <?php foreach ($lotInfo as $numAnonyme => $lot): ?>
          <?php if($lot->numero_table == $table): ?>
            <tr>
              <?php if($firstDisplay == true): ?>
                <td rowspan="<?php echo count($lotInfo); ?>" style="margin-top: 10em; vertical-align: middle;"><small><?php echo ($lot->numero_dossier) ? $lot->numero_dossier : "Leurre" ; ?></small></td>
                <td rowspan="<?php echo count($lotInfo); ?>" style="vertical-align: middle;"><small><?php echo $lot->declarant_nom."<br>".$lot->declarant_identifiant;?></small></td>
              <?php $firstDisplay= false; endif; ?>
              <td><small><?php echo $lot->numero_archive ?></small></td>
              <td><small><?php echo $numAnonyme ?></small></td>
              <td><small><?php echo $lot->numero_cuve ?></small></td>
              <td style="float:right; text-align:right;"><small><?php echo number_format($lot->volume, 2) ?></small></td>
              <td><small><?php echo $lot->produit_libelle ?></small></td>
              <td><small><?php echo $lot->details ?></small></td>
            </tr>
          <?php endif; ?>
      <?php endforeach; ?>

      <?php $i=$i+1 ?>
    <?php endforeach; ?>
  </table>
