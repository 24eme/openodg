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
              <p>Code Commission</p>
            </td>
            <td style="width:33%;">
              <p>Responsable : CHAMPETIER Pierre</p>
            </td>
            <td style="width:33%">
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
              <p>Millésine: <?php echo $degustation->campagne; ?></p>
            </td>
            <td style="width:33%">
            </td>
          </tr>
      </table>
    </div>

    <p style="margin-left:0;">
      <?php echo "Nombre total de lots : ".count($degustation->lots)."  dont : ";?>
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


    <table border="0.5px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;" scope="colgroup" >
      <tr style="line-height:20px;">
         <th class="topempty bg-white"style="width:10%;"><?php echo tdStart() ?><strong>N° Dossier</strong></th>
         <th class="topempty bg-white" style="width:20%; "><?php echo tdStart() ?><strong>Raison Sociale<br>N°CVI</strong></th>
         <th class="bg-white" colspan="5"style="width:70%;"><?php echo tdStart() ?><strong>Liste des lots</strong></th>
      </tr>
      <tr style="line-height:13px;">
        <th class="empty bg-white"></th>
        <th class="empty bg-white"></th>
        <th class="bg-white" style="width:10%;"><?php echo tdStart() ?><strong><small>N°Lot ODG</small></strong></th>
        <th class="bg-white" style="width:10%;"><?php echo tdStart() ?><strong><small>N°Lot Opérateur</small></strong></th>
        <th class="bg-white" style="width:10%;"><?php echo tdStart() ?><strong><small>Volume</small></strong></th>
        <th class="bg-white" style="width:20%;"><?php echo tdStart() ?><strong><small>IGP/Couleur</small></strong></th>
        <th class="bg-white" style="width:20%;"><?php echo tdStart() ?><strong><small>Cepage</small></strong></th>
      </tr>
    <?php $i=1;?>
     <?php  foreach($lots as $numero_dossier => $lotInfo): ?>

        <?php foreach ($lotInfo as $lot): ?>
          <tr>
            <td><small><?php echo $lot->numero_dossier ?></small></td>
            <td><small><?php echo $lot->declarant_nom."<br>".$lot->declarant_identifiant;?></small></td>
            <td><small><?php echo $lot->numero_archive ?></small></td>
            <td><small><?php echo $lot->numero_cuve ?></small></td>
            <td><small><?php echo $lot->volume ?></small></td>
            <td><small><?php echo $lot->produit_libelle ?></small></td>
            <td><small><?php echo $lot->produit_libelle ?></small></td>
          </tr>
      <?php endforeach; ?>

      <?php $i=$i+1 ?>
    <?php endforeach; ?>
  </table>
