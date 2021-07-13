<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<?php use_helper('Text') ?>
<style>
  .font-1-3em{
    font-size: 1.3em;
  }
</style>
    <table  class="" cellspacing="0" cellpadding="11" style="height: 1122.4px; width: 100%; margin: 0; padding: 0">
    <?php foreach($plancheLots as $lotInfo): ?>
        <tr style="height: <?php echo 1122.4/count($plancheLots) ?>px">
          <?php for($i=0; $i <3 ; $i++): ?>
            <td>
                <table cellspacing="0" cellpadding="0" style="font-size:8px;overflow: hidden;white-space: nowrap;">
                  <tr style="">
                    <td style="overflow-wrap:break-word;">
                      <?php echo tdStart() ?>&nbsp;N°ODG:<strong style="font-size:10px;"><?php echo (int)$lotInfo->lot->numero_archive;  ?></strong>
                    </td>
                    <td style="overflow-wrap:break-word;text-align: right;">
                      <?php echo tdStart() ?>&nbsp;N°Dos:<strong><?php echo (int)$lotInfo->lot->numero_dossier;  ?></strong>
                    </td>
                  </tr>
                  <tr style="">
                    <td colspan="2" style="overflow-wrap:break-word;text-align:center;line-height:8px;" >
                      <?php echo tdStart() ?><strong>&nbsp;
                      <?php if ($i != 2 || !$anonymat4labo): ?>
                      <?php echo ($lotInfo->lot->declarant_nom)? truncate_text($lotInfo->getRawValue()->lot->declarant_nom, 43, '...') : "Leurre";  ?>
                      <?php endif; ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:12px;">
                    <td colspan="2" style="text-align: center;">
                      <?php echo tdStart() ?>
                      <?php if ($i != 2 || !$anonymat4labo): ?>
                          <?php if($lotInfo->etablissement->cvi):echo ($lotInfo->etablissement->cvi);
                           elseif ($lotInfo->etablissement->siret):echo (substr($lotInfo->etablissement->siret,0,3)." ".substr($lotInfo->etablissement->siret,3,3)." ".substr($lotInfo->etablissement->siret,6,3)." ".substr($lotInfo->etablissement->siret,9,5));
                          endif; ?>
                      <?php else: ?>
                          <i>Lot destiné au laboratoire</i>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr style="line-height:12px;">
                    <td colspan="2" style="overflow-wrap:break-word; text-align: center;">
                      <?php echo tdStart() ?><strong><?php echo truncate_text("IGP ".$lotInfo->getRawValue()->lot->produit_libelle, 46, '...', 'middle') .' '.  $lotInfo->lot->millesime;  ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:12px;">
                    <td colspan="2" style="overflow-wrap:break-word;text-align:center;line-height:7px;">
                      <?php echo tdStart() ?>

                      <strong><?php echo showOnlyCepages($lotInfo->lot, 65) ?></strong>

                    </td>
                  </tr>
                  <tr style="overflow:hidden; text-overflow: ellipsis">
                    <td style="line-height:12px; width:50%;">
                      <?php $lot = $lotInfo->lot; $centilisation = $lot->centilisation ? " ($lot->centilisation)" : null; ?>
                      &nbsp;Lgt&nbsp;:&nbsp;<strong class="font-1-3em"><?php echo substr($lotInfo->lot->numero_logement_operateur.$centilisation, 0, 20)  ?></strong>
                    </td>
                    <td class="font-1-3em" style="text-align: right; width:50%;">
                      <strong><?php echo sprintf("%.2f", $lotInfo->lot->volume);  ?> hl</strong>
                    </td>
                  </tr>
                </table>
            </td>
          <?php endfor; ?>
        </tr>
    <?php endforeach; ?>
    </table>
