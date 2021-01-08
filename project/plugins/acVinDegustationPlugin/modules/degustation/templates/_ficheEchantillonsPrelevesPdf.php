<?php use_helper('TemplatingPDF'); ?>
<style>
<?php echo style(); ?>
.bg-white{
  background-color:white;
}

</style>
    <div>
      <table>
          <tr>
            <td style="width:33%;">
              <p>Code Commission: _ _ _ _ </p>
            </td>
            <td style="width:60%;">
              <p>Responsable : _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _</p>
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

    <p style="margin-left:0;">
      <?php echo "Nombre total de lots : ".count($lots)."  dont : ";?>
      <?php $i = 0; foreach ($lots as $key => $l): ?>
        <?php foreach ($l as $key => $value): ?>
          <?php echo count($l)." éch. ".$value->declarant_nom; break;?>
        <?php endforeach; ?>
        <?php $i++; if(count($lots)-1 == $i ): ?>
          <?php echo " et "; ?>
        <?php elseif(count($lots) == $i ): ?>
          <?php echo "."; ?>
        <?php else: ?>
          <?php echo ", "; ?>
        <?php endif ?>
      <?php endforeach; ?>
    </p>


    <table border="0.5px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;">
      <tr style="line-height:20px;">
         <th rowspan="2" class="topempty bg-white"style="width:10%;"><?php echo tdStart() ?><strong>N° Dossier</strong></th>
         <th rowspan="2" class="topempty bg-white" style="width:20%; "><?php echo tdStart() ?><strong>Raison Sociale<br>N°CVI</strong></th>
         <th class="bg-white" colspan="6"style="width:70%;"><?php echo tdStart() ?><strong>Liste des lots</strong></th>
      </tr>
      <tr style="line-height:13px;">
        <th class="bg-white" style="width:7%;"><?php echo tdStart() ?><strong><small>N°Lot ODG</small></strong></th>
        <th class="bg-white" style="width:8%;"><?php echo tdStart() ?><strong><small>N°Anonyme</small></strong></th>
        <th class="bg-white" style="width:10%;"><?php echo tdStart() ?><strong><small>N°Lot Opérateur</small></strong></th>
        <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>Volume (hl)</small></strong></th>
        <th class="bg-white" style="width:20%;"><?php echo tdStart() ?><strong><small>IGP/Couleur</small></strong></th>
        <th class="bg-white" style="width:20%;"><?php echo tdStart() ?><strong><small>Cepage</small></strong></th>
      </tr>
    <?php $i=1;?>
     <?php  foreach($lots as $numero_dossier => $lotInfo): ?>
       <?php $firstDisplay = true; ?>

        <?php foreach ($lotInfo as $numAnonyme => $lot): ?>
          <tr>
            <?php if($firstDisplay == true): ?>
              <td rowspan="<?php echo count($lotInfo); ?>" style="margin-top: 10em; vertical-align: middle;"><small><?php echo $lot->numero_dossier ?></small></td>
              <td rowspan="<?php echo count($lotInfo); ?>" style="vertical-align: middle;"><small><?php echo $lot->declarant_nom."<br>".$lot->declarant_identifiant;?></small></td>
            <?php $firstDisplay= false; endif; ?>
            <td><small><?php echo $lot->numero_archive ?></small></td>
            <td><small><?php echo $numAnonyme ?></small></td>
            <td><small><?php echo $lot->numero_cuve ?></small></td>
            <td style="float:right; text-align:right;"><small><?php echo number_format($lot->volume, 2) ?></small></td>
            <td><small><?php echo $lot->produit_libelle ?></small></td>
            <td><small><?php echo $lot->details ?></small></td>
          </tr>
      <?php endforeach; ?>

      <?php $i=$i+1 ?>
    <?php endforeach; ?>
  </table>
