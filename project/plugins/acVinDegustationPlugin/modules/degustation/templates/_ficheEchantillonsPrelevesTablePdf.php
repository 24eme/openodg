<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
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
              <p>Code Commission: <?= $degustation->_id ?></p>
            </td>
            <td style="width:60%;">
              <p>Responsable : _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _</p>
            </td>
            <td style="width:2%">
            </td>
          </tr>

          <tr>
            <td style="width:33%;">
              <p>Date : <?php $date = date_create($degustation->date); echo $date->format("d/m/Y"); ?></p>
            </td>
            <td style="width:33%;">
              <p>Heure : <?php echo $date->format("H:i"); ?></p>
            </td>
            <td style="width:33%">
              <p>Lieu : <?php echo $degustation->getLieuNom(); ?> </p>
            </td>
          </tr>

          <tr>
            <td style="width:33%;">
              <p>Campagne: <?php echo $degustation->campagne; ?></p>
            </td>
            <td style="width:33%">
            </td>
          </tr>
      </table>
    </div>

    <p>Nombre total de lots : <?php echo $nbLots;?></p>

     <?php $affiche = 0; $reste = 0; $i = 8; $table_header = true;
      foreach($lots as $numTab => $lotTable):  ?>
       <?php $firstDisplay = true; $reste = 0; $class = "bg-tab$numTab"?>
        <?php foreach ($lotTable as $numero_dossier => $lotInfo): ?>
          <?php $firstDisplayTab = true; $firstDisplayOp = true; $reste = 0; ?>
          <?php foreach ($lotInfo as $uniqueId => $lot): ?>
            <?php if ($i % 20 == 0 ) : $table_header = true; $firstDisplayTab = $firstDisplayOp = true;?>
             </table>
              <br pagebreak="true" />
              <p>Suite des lots<p/>
              <br/>
            <?php $i = 0; endif; ?>
            <?php if ($table_header): $table_header = false; ?>
              <table border="0.5px" class="table"style="text-align: center;">
                <tr style="line-height:20px;">
                  <th rowspan="2" class="topempty bg-white"style="width:5%;"><?php echo tdStart() ?><strong>Table</strong></th>
                   <th rowspan="2" class="topempty bg-white"style="width:10%;"><?php echo tdStart() ?><strong>N° Dossier</strong></th>
                   <th rowspan="2" class="topempty bg-white" style="width:23%; "><?php echo tdStart() ?><strong>Raison Sociale<br>N°CVI</strong></th>
                   <th class="bg-white" colspan="6"style="width:62%;"><?php echo tdStart() ?><strong>Liste des lots</strong></th>
                </tr>
                <tr style="line-height:13px;">
                  <th class="bg-white" style="width:7%;"><?php echo tdStart() ?><strong><small>N°Lot ODG</small></strong></th>
                  <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>N°Anon</small></strong></th>
                  <th class="bg-white" style="width:11%;"><?php echo tdStart() ?><strong><small>Cuve</small></strong></th>
                  <th class="bg-white" style="width:9%;"><?php echo tdStart() ?><strong><small>Vol (hl)</small></strong></th>
                  <th class="bg-white" style="width:30%;"><?php echo tdStart() ?><strong><small>Produit millesime cépage</small></strong></th>
                </tr>
            <?php endif; ?>
            <tr class="<?php echo $class; ?>" >
              <?php if($firstDisplayTab == true):
                $affiche = count($lotInfo) - $reste;
                if((20 - $i) > 0 && (20 - $i) < count($lotInfo)){
                  $reste = (20 - $i);
                  $affiche = (20 - $i);
                }
                ?>
                <td rowspan="<?php echo $affiche; ?>" ><small><?php echo DegustationClient::getNumeroTableStr($numTab) ?></small></td>
              <?php $firstDisplayTab= false; endif; ?>

                <td ><small><?php echo ($lot->numero_dossier) ? $lot->numero_dossier : "Leurre" ; ?></small></td>

              <?php if($firstDisplayOp == true ): ?>
                <td rowspan="<?php echo $affiche; ?>" ><small><?php echo $lot->declarant_nom."<br>".$lot->declarant_identifiant;?></small></td>
              <?php $firstDisplayOp= false; endif; ?>

              <td><small><?php echo $lot->numero_archive ?></small></td>
              <td><small><?php echo $lot->numero_anonymat ?></small></td>
              <td><small><?php echo $lot->numero_logement_operateur ?></small></td>
              <td style="text-align:center;"><small><?php echo number_format($lot->volume, 2) ?></small></td>
              <td><small><?php echo showProduitLot($lot); ?></small></td>
            </tr>
          <?php $i++; endforeach; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
  </table>
