<?php use_helper('TemplatingPDF'); ?>
<style>
</style>
    <table border="" class="" cellspacing=0 cellpadding=0 style="text-align: right;">
    <?php foreach($plancheLots as $lotInfo): ?>
        <tr>
          <?php for($i=0; $i <3 ; $i++): ?>
            <td style="text-align: left;width:245px;">
                <table cellspacing=0 cellpadding=0 style="font-size:8px;">
                  <tr style="line-height:8px;">
                    <td style="overflow-wrap:break-word;">
                      <?php echo tdStart() ?>&nbsp;&nbsp;<strong style="font-size:10px;"><?php echo (int)$lotInfo->lot->numero_archive;  ?></strong>
                    </td>
                    <td style="overflow-wrap:break-word;text-align: right;">
                      <?php echo tdStart() ?>&nbsp;N°Dos:<strong><?php echo (int)$lotInfo->lot->numero_dossier;  ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:12px;">
                    <td colspan="2" style="overflow-wrap:break-word;text-align:center;line-height:8px;" >
                      <?php echo tdStart() ?>&nbsp;<strong><?php echo ($lotInfo->lot->declarant_nom)? $lotInfo->lot->declarant_nom : "Leurre";  ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:12px;">
                    <td colspan="2" style="text-align: center;">
                      <?php echo tdStart() ?><strong><?php if($lotInfo->etablissement->cvi):echo ($lotInfo->etablissement->cvi);
                        elseif ($lotInfo->etablissement->siret):echo (substr($lotInfo->etablissement->siret,0,3)." ".substr($lotInfo->etablissement->siret,3,3)." ".substr($lotInfo->etablissement->siret,6,3)." ".substr($lotInfo->etablissement->siret,9,5));
                        endif;
                       ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:12px;">
                    <td colspan="2" style="overflow-wrap:break-word; text-align: center;">
                      <?php echo tdStart() ?><strong>&nbsp;IGP&nbsp;<?php echo $lotInfo->lot->produit_libelle .' '.  $lotInfo->lot->millesime;  ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:12px;">
                    <td colspan="2" style="overflow-wrap:break-word;text-align:center;line-height:7px;">
                      <?php echo tdStart() ?>
                      <?php if ($lotInfo->lot->details): ?>
                      <strong><?php echo $lotInfo->lot->details;  ?></strong>
                    <?php else: echo (""); ?>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td style="overflow-wrap:break-word;line-height:12px; width:75%;">
                      <?php $lot = $lotInfo->lot; $centilisation = $lot->centilisation ? " ($lot->centilisation)" : null; ?>
                      &nbsp;Lgt&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->numero_cuve.$centilisation;  ?></strong>
                    </td>
                    <td style="text-align: right; width:25%;">
                      <strong><?php echo sprintf("%.2f", $lotInfo->lot->volume);  ?> hl</strong>
                    </td>
                  </tr>
                </table>
            </td>
          <?php endfor; ?>
        </tr>
        <tr style="line-height:30px;"><td></td></tr>
    <?php endforeach; ?>
    </table>
