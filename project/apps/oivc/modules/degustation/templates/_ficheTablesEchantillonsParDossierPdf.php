<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<style>
<?php echo style(); ?>
.bg-white{
  background-color:white;
}

</style>
      <div></div>
      <table>
          <tr>
            <td style="width:60%;">
              Code Commission: <?= $degustation->_id ?><br/>
            </td>
            <td style="width:40%;">
              Responsable : <br/>
            </td>
          </tr>

          <tr>
            <td style="width:30%;">
              Date : <?php $date = date_create($degustation->date); echo $date->format("d/m/Y"); ?><br/>
            </td>
            <td style="width:30%;">
              Heure : <?php echo $date->format("H:i"); ?><br/>
            </td>
            <td style="width:40%">
              Lieu : <?php echo $degustation->getLieuNom(); ?> <br/>
            </td>
          </tr>

          <tr>
            <td style="width:33%;">
              Campagne: <?php echo $degustation->campagne; ?><br/>
            </td>
          </tr>
          <tr>
            <td style="width:33%;">
              Nombre total d'échantillon : <?php echo count($degustation->getLotsDegustables());?><br/>
            </td>
          </tr>
      </table>

     <?php $affiche = 0; $reste = 0; $i = 6; $table_header = true;  foreach($lots as $operateur => $lots_operateur):  ?>
     <?php $firstDisplay = true; $reste = 0?>
     <?php $nb_lots_par_page = 27; ?>
        <?php foreach ($lots_operateur as $lot): ?>
          <?php if ($i % $nb_lots_par_page == 0 ) : $table_header = true; $firstDisplay = true;?>
       </table>
            <br pagebreak="true" />
            <br/>
          <?php $i = 0; endif; ?>
          <?php if ($table_header): $table_header = false; ?>
            <table border="1px" class="table" cellspacing=0 cellpadding=0 style="text-align: center;">
              <tr style="line-height:20px;">
                 <th rowspan="2" class="bg-white" style="width:10%;"><?php echo tdStart() ?><strong>N° Dossier</strong></th>
                 <th rowspan="2" class="bg-white" style="width:22%; "><?php echo tdStart() ?><strong>Raison Sociale</strong></th>
                 <th class="bg-white" colspan="6"style="width:67%;"><?php echo tdStart() ?><strong>Liste des échantillons</strong> <small>(trié par n° de dossier et n° d'anonymat)</small></th>
              </tr>
              <tr style="line-height:13px;">
                <th class="bg-white" style="width:7%;"><?php echo tdStart() ?><strong><small>N°Ech</small></strong></th>
                <th class="bg-white" style="width:5%;"><?php echo tdStart() ?><strong><small>N°Anon</small></strong></th>
                <th class="bg-white" style="width:11%;"><?php echo tdStart() ?><strong><small>Cuve</small></strong></th>
                <th class="bg-white" style="width:9%;"><?php echo tdStart() ?><strong><small>Volume</small></strong></th>
                <th class="bg-white" style="width:35%;"><?php echo tdStart() ?><strong><small>Produit millesime</small></strong></th>
              </tr>
          <?php endif; ?>
          <tr>
            <?php if($firstDisplay == true):
              $affiche = count($lots_operateur) - $reste;
              if(($nb_lots_par_page - $i) > 0 && ($nb_lots_par_page - $i) < count($lots_operateur) && !$reste){
                $reste = ($nb_lots_par_page - $i);
                $affiche = ($nb_lots_par_page - $i);
              }
              ?>
              <td rowspan="<?php echo $affiche; ?>" style="margin-top: 10em; vertical-align: middle;"><small><?php echo ($lot->numero_dossier) ? $lot->numero_dossier : "Leurre" ; ?><br/><span style="color:grey;"><?php echo $lot->campagne; ?></span></small></td>
              <td rowspan="<?php echo $affiche; ?>" style="vertical-align: middle; text-align: left;"><small><?php echo $lot->getRawValue()->declarant_nom ?><br><span style="color:grey;"><?php echo $lot->declarant_identifiant;?></span></small></td>
            <?php $firstDisplay= false; endif; ?>
            <td><small><?php echo $lot->numero_archive ?></small></td>
            <td><small><?php echo $lot->numero_anonymat?></small></td>
            <td><small><?php echo $lot->numero_logement_operateur ?></small></td>
            <td style="text-align: right;"><small><?php if($lot->exist('quantite') && $lot->quantite): ?><?php echo $lot->quantite ?>&nbsp;cols<?php elseif($lot->volume): ?><?php echo number_format($lot->volume, 2) ?>&nbsp;hl<?php endif; ?> &nbsp;</small></td>
            <td style="height:25px; text-align: left;"><small><?php echo showOnlyProduit($lot, false) ?></small></td>
          </tr>
          <?php $i++; ?>
      <?php endforeach; ?>


    <?php endforeach; ?>
  </table>
