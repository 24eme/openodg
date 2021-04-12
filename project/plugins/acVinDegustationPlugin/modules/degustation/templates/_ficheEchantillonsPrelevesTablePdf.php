<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<style>
<?php echo style(); ?>
.bg-white{
  background-color:white;
}

.odd {
  background-color:white;
}

.even {
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

    <p>Nombre total de lots : <?php echo (string)(count($lots, COUNT_RECURSIVE) - count($lots)) ;?></p>

    <?php $class = 'odd'; $i = 1; $first = true; $table_header = true; foreach ($lots as $table => $lots_table): ?>
        <?php $class = ($class === "even") ? 'odd' : 'even'; ?>
        <?php foreach ($lots_table as $lot): ?>
          <?php if (($first === true && $i % 12 === 0) || $i % 16 === 0): ?>
            <?php $table_header = true; $first = false; $i = 0; ?>
             </table>
              <br pagebreak="true" />
              <p>Suite des lots<p/>
              <br/>
          <?php endif; ?>

          <?php if ($table_header): $table_header = false; ?>
            <table border="0.5px" class="table"style="text-align: center;">
              <tr style="line-height:20px;">
                <th rowspan="2" class="topempty bg-white"style="width:10%;"><?php echo tdStart() ?><strong>N° Anonymat</strong></th>
                 <th rowspan="2" class="topempty bg-white"style="width:10%;"><?php echo tdStart() ?><strong>Table</strong></th>
                 <th rowspan="2" class="topempty bg-white" style="width:20%; "><?php echo tdStart() ?><strong>Raison Sociale<br>N°CVI</strong></th>
                 <th class="bg-white" colspan="6"style="width:60%;"><?php echo tdStart() ?><strong>Liste des lots</strong> <small>(Trié par table et n° anonymat)</small></th>
              </tr>
              <tr style="line-height:13px;">
                <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>N° Lot ODG</small></strong></th>
                <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>N° Dos</small></strong></th>
                <th class="bg-white" style="width:10%;"><?php echo tdStart() ?><strong><small>Cuve</small></strong></th>
                <th class="bg-white" style="width:10%;"><?php echo tdStart() ?><strong><small>Vol (hl)</small></strong></th>
                <th class="bg-white" style="width:30%;"><?php echo tdStart() ?><strong><small>Produit millesime cépage</small></strong></th>
              </tr>
          <?php endif; ?>
          <tr class="<?php echo $class; ?>" >
            <td><small><?php echo $lot->numero_anonymat ?></small></td>
            <td><small><?php echo DegustationClient::getNumeroTableStr($table) ?></small></td>
            <td><small><?php echo substr($lot->declarant_nom, 0, 33)."<br>".$lot->declarant_identifiant;?></small></td>

            <td><small><?php echo $lot->numero_archive ?></small></td>
            <td><small><?php echo ($lot->numero_dossier) ? $lot->numero_dossier : "Leurre" ; ?></small></td>
            <td><small><?php echo $lot->numero_logement_operateur ?></small></td>
            <td style="text-align: right;"><small><?php echo number_format($lot->volume, 2) ?></small></td>
            <td><small><?php echo showProduitCepagesLot($lot) ?></small></td>
          </tr>
          <?php $i++; endforeach; ?>
        <?php endforeach; ?>
  </table>
