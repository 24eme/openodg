<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<style>
<?php echo style(); ?>
.bg-white{
  background-color:white;
}

</style>
    <div>
      <table>
          <tr>
            <td style="width:60%;">
              <p>Code Commission: <?= $degustation->_id ?></p>
            </td>
            <td style="width:30%;">
              <p>Responsable : _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _</p>
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

    <p style="margin-left:0;">Nombre total de lots : <?php echo count($degustation->getLots());?></p>

     <?php $affiche = 0; $reste = 0; $i = 10; $table_header = true;  foreach($lots as $operateur => $lots_operateur):  ?>
       <?php $firstDisplay = true; $reste = 0?>

        <?php foreach ($lots_operateur as $lot): ?>
          <?php if ($i % 27 == 0 ) : $table_header = true; $firstDisplay = true;?>
       </table>
            <br pagebreak="true" />
            <br/>
          <?php $i = 0; endif; ?>
          <?php if ($table_header): $table_header = false; ?>
            <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;">
              <tr style="line-height:20px;">
                 <th rowspan="2" class="bg-white" style="width:10%;"><?php echo tdStart() ?><strong>N° Dossier</strong></th>
                 <th rowspan="2" class="bg-white" style="width:22%; "><?php echo tdStart() ?><strong>Raison Sociale<br>N°CVI</strong></th>
                 <th class="bg-white" colspan="6"style="width:67%;"><?php echo tdStart() ?><strong>Liste des lots</strong> <small>(trié par n° déclarant et n° anonymat)</small></th>
              </tr>
              <tr style="line-height:13px;">
                <th class="bg-white" style="width:7%;"><?php echo tdStart() ?><strong><small>N°Lot ODG</small></strong></th>
                <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>N°Anon</small></strong></th>
                <th class="bg-white" style="width:11%;"><?php echo tdStart() ?><strong><small>Cuve</small></strong></th>
                <th class="bg-white" style="width:9%;"><?php echo tdStart() ?><strong><small>Vol (hl)</small></strong></th>
                <th class="bg-white" style="width:35%;"><?php echo tdStart() ?><strong><small>Produit millesime cépage</small></strong></th>
              </tr>
          <?php endif; ?>
          <tr>
            <?php if($firstDisplay == true):
              $affiche = count($lots_operateur) - $reste;
              if((30 - $i) > 0 && (30 - $i) < count($lots_operateur)){
                $reste = (30 - $i);
                $affiche = (30 - $i);
              }
              ?>
              <td rowspan="<?php echo $affiche; ?>" style="margin-top: 10em; vertical-align: middle;"><small><?php echo ($lot->numero_dossier) ? $lot->numero_dossier : "Leurre" ; ?></small></td>
              <td rowspan="<?php echo $affiche; ?>" style="vertical-align: middle;"><small><?php echo substr($lot->getRawValue()->declarant_nom, 0, 20)."<br>".$lot->declarant_identifiant;?></small></td>
            <?php $firstDisplay= false; endif; ?>
            <td><small><?php echo $lot->numero_archive ?></small></td>
            <td><small><?php echo $lot->numero_anonymat?></small></td>
            <td><small><?php echo $lot->numero_logement_operateur ?></small></td>
            <td style="float:right; text-align:right;"><small><?php echo number_format($lot->volume, 2) ?></small></td>
            <td style="height:25px;"><small><?php echo showProduitCepagesLot($lot); ?></small></td>
          </tr>
          <?php $i++; ?>
      <?php endforeach; ?>


    <?php endforeach; ?>
  </table>
