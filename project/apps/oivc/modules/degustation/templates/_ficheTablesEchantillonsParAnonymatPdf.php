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
      <div></div>
      <table style="line-height:15px;">
          <tr>
            <td style="width:33%;">
              Code Commission: <?= $degustation->_id ?><br/>
            </td>
            <td style="width:60%;">
              Responsable : <br/>
            </td>
            <td style="width:2%"><br/>
            </td>
          </tr>

          <tr>
            <td style="width:33%;">
              Date : <?php $date = date_create($degustation->date); echo $date->format("d/m/Y"); ?><br/>
            </td>
            <td style="width:33%;">
              Heure : <?php echo $date->format("H:i"); ?><br/>
            </td>
            <td style="width:33%">
              Lieu : <?php echo $degustation->getLieuNom(); ?><br/>
            </td>
          </tr>

          <tr>
            <td style="width:33%;">
              Campagne: <?php echo $degustation->campagne; ?><br/>
            </td>
            <td style="width:33%"><br/>
            </td>
          </tr>
          <tr>
            <td style="width:33%;">
               Nombre total d'échantillon : <?php echo count($degustation->getLotsDegustables()) ;?><br/>
            </td>
            <td style="width:33%"><br/>
            </td>
          </tr>
      </table>

    <?php $class = 'odd'; $i = 3; $first = true; $table_header = true; foreach ($lots as $table => $lots_table): ?>
        <?php $class = ($class === "even") ? 'odd' : 'even'; ?>
        <?php foreach ($lots_table as $lot): ?>
          <?php if ($i % 16 === 0): ?>
            <?php $table_header = true; $first = false; $i = 0; ?>
             </table>
              <br pagebreak="true" />
              Suite des échantillons<br/><br/>
          <?php endif; ?>

          <?php if ($table_header): $table_header = false; ?>
            <table border="1" cellspacing="0" class="table"style="text-align: center;">
              <tr style="line-height:20px;">
                 <th rowspan="2" class="bg-white" style="width:10%;"><?php echo tdStart() ?><strong>N° Anonymat</strong></th>
                 <th rowspan="2" class="bg-white" style="width:10%;"><?php echo tdStart() ?><strong>Table</strong></th>
                 <th rowspan="2" class="bg-white" style="width:24%; "><?php echo tdStart() ?><strong>Raison Sociale</strong></th>
                 <th colspan="6" class="bg-white" style="width:56%;"><?php echo tdStart() ?><strong>Liste des échantillons</strong> <small>(Trié par table et n° anonymat)</small></th>
              </tr>
              <tr style="line-height:13px;">
                <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>N° Dos</small></strong></th>
                <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>N° Ech</small></strong></th>
                <th class="bg-white" style="width:8%;"><?php echo tdStart() ?><strong><small>Id Lot Opérateur</small></strong></th>
                <th class="bg-white" style="width:8%;"><?php echo tdStart() ?><strong><small>Quantité</small></strong></th>
                <th class="bg-white" style="width:30%;"><?php echo tdStart() ?><strong><small>Produit millésime</small></strong></th>
              </tr>
          <?php endif; ?>
          <tr class="<?php echo $class; ?>" >
            <td><small><?php echo $lot->numero_anonymat ?></small></td>
            <td><small><?php echo DegustationClient::getNumeroTableStr($table) ?></small></td>
            <td style="text-align: left;"><small><?php echo substrUtf8($lot->declarant_nom, 0, 33) ?><br /><span style="color:grey;"><?php echo $lot->declarant_identifiant;?></span></small></td>

            <td><small><?php echo ($lot->numero_dossier) ? $lot->numero_dossier : "Leurre" ; ?></small></td>
            <td><small><?php echo $lot->numero_archive ?></small></td>
            <td><small><?php echo $lot->numero_logement_operateur ?></small></td>
            <td style="text-align: right;"><small><?php if($lot->exist('quantite') && $lot->quantite): ?><?php echo $lot->quantite ?>&nbsp;cols<?php elseif($lot->volume): ?><?php echo number_format($lot->volume, 2) ?>&nbsp;hl<?php endif; ?> &nbsp;</small></td>
            <td style="text-align: left;"><small><?php echo showOnlyProduit($lot, false) ?></small></td>
          </tr>
          <?php $i++; endforeach; ?>
        <?php endforeach; ?>
  </table>
