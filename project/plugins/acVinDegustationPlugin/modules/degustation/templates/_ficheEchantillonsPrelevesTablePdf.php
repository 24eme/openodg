<?php use_helper('TemplatingPDF'); ?>
<style>
<?php echo style(); ?>
.bg-white{
  background-color:white;
}

.bg-tab1, .bg-tab3{
  background-color:white;
}

.bg-tab2, .bg-tab4{
  background-color:#F0F0F0;
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
              <p>Lieu : <?php echo $degustation->getLieuNom(); ?> </p>
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

    <p>Nombre total de lots : <?php echo $nbLots;?></p>
    <table border="0.5px" class="table"style="text-align: center;">
      <tr style="line-height:20px;">
        <th rowspan="2" class="topempty bg-white"style="width:5%;"><?php echo tdStart() ?><strong>Table</strong></th>
         <th rowspan="2" class="topempty bg-white"style="width:10%;"><?php echo tdStart() ?><strong>N° Dossier</strong></th>
         <th rowspan="2" class="topempty bg-white" style="width:20%; "><?php echo tdStart() ?><strong>Raison Sociale<br>N°CVI</strong></th>
         <th class="bg-white" colspan="6"style="width:65%;"><?php echo tdStart() ?><strong>Liste des lots</strong></th>
      </tr>
      <tr style="line-height:13px;">
        <th class="bg-white" style="width:7%;"><?php echo tdStart() ?><strong><small>N°Lot ODG</small></strong></th>
        <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>N°Anon</small></strong></th>
        <th class="bg-white" style="width:9%;"><?php echo tdStart() ?><strong><small>Cuve</small></strong></th>
        <th class="bg-white" style="width:9%;"><?php echo tdStart() ?><strong><small>Vol (hl)</small></strong></th>
        <th class="bg-white" style="width:20%;"><?php echo tdStart() ?><strong><small>IGP/Couleur</small></strong></th>
        <th class="bg-white" style="width:15%;"><?php echo tdStart() ?><strong><small>Cepage</small></strong></th>
      </tr>
     <?php  foreach($lots as $numTab => $lotTable):  ?>
       <?php $firstDisplay = true; $class = "bg-tab$numTab"?>
        <?php foreach ($lotTable as $numero_dossier => $lotInfo): ?>
          <?php $firstDisplayTab = true; $firstDisplayOp = true; ?>
          <?php foreach ($lotInfo as $uniqueId => $lot): ?>

            <tr class="<?php echo $class; ?>" >
              <?php if($firstDisplayTab == true ): ?>
                <td rowspan="<?php echo count($lotInfo); ?>" ><small><?php echo DegustationClient::getNumeroTableStr($numTab) ?></small></td>
              <?php $firstDisplayTab= false; endif; ?>
                <td ><small><?php echo ($lot->numero_dossier) ? $lot->numero_dossier : "Leurre" ; ?></small></td>
              <?php if($firstDisplayOp == true ): ?>
                <td rowspan="<?php echo count($lotInfo); ?>" ><small><?php echo $lot->declarant_nom."<br>".$lot->declarant_identifiant;?></small></td>
              <?php $firstDisplayOp= false; endif; ?>

              <td><small><?php echo $lot->numero_archive ?></small></td>
              <td><small><?php echo $lot->numero_anonymat ?></small></td>
              <td><small><?php echo $lot->numero_logement_operateur ?></small></td>
              <td style="text-align:right;"><small><?php echo number_format($lot->volume, 2) ?></small></td>
              <td><small><?php echo $lot->produit_libelle ?></small></td>
              <td><small><?php echo $lot->details ?></small></td>
            </tr>
          <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
  </table>
