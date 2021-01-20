<?php use_helper('TemplatingPDF'); ?>
<style>
</style>
    <table border="" class="" cellspacing=0 cellpadding=0 style="text-align: right;">
    <?php foreach($plancheLots as $lotInfo): ?>
        <tr>
          <?php for($i=0; $i <3 ; $i++): ?>
            <td style="text-align: left;">
                <table cellspacing=0 cellpadding=0 style="font-size:10px;">
                  <tr style="line-height:4px;">
                    <td colspan="2" style="overflow-wrap:break-word;text-align:left;line-height:8px;" >
                      <?php echo tdStart() ?>&nbsp;<strong><?php echo ($lotInfo->lot->declarant_nom)? $lotInfo->lot->declarant_nom : "Leurre";  ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:7px;">
                    <td colspan="2" style="text-align: left;">
                      <?php echo tdStart() ?>&nbsp;Ville&nbsp;:&nbsp;<strong><?php echo ($lotInfo->etablissement)? $lotInfo->etablissement->commune : '';  ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:7px;">
                    <td style="overflow-wrap:break-word;">
                      <?php echo tdStart() ?>&nbsp;N°&nbsp;Lot&nbsp;ODG&nbsp;:&nbsp;<strong><?php echo (int)$lotInfo->lot->numero_archive;  ?></strong>
                    </td>
                    <td style="overflow-wrap:break-word;">
                      <?php echo tdStart() ?>&nbsp;N°&nbsp;Dos&nbsp;:&nbsp;<strong><?php echo (int)$lotInfo->lot->numero_dossier;  ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:7px;">
                    <td colspan="2" style="overflow-wrap:break-word;text-align: left;">
                      <?php echo tdStart() ?>&nbsp;IGP&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->produit_libelle; ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:7px;">
                    <td style="overflow-wrap:break-word;text-align: left;">
                      <?php echo tdStart() ?>&nbsp;Millésime&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->millesime;;  ?></strong>
                    </td>
                    <td style="overflow-wrap:break-word;text-align: left;">
                    <?php echo tdStart() ?>&nbsp;<strong><?php echo sprintf("%.2f", $lotInfo->lot->volume);  ?> hl</strong>
                    </td>

                  </tr>
                  <tr style="line-height:7px;">
                    <td colspan="2" style="overflow-wrap:break-word;">
                      <?php echo tdStart() ?>&nbsp;Lgt&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->numero_cuve;  ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:7px;">
                    <td colspan="2" style="overflow-wrap:break-word;text-align:left;line-height:6px;">
                      <?php echo tdStart() ?>&nbsp;Cépage&nbsp;:&nbsp; <strong style='text-overflow: ellipsis;'><?php if ($lotInfo->lot->details): echo $lotInfo->lot->details; endif;  ?></strong>
                    </td>
                  </tr>
                </table>
            </td>
          <?php endfor; ?>
        </tr>
        <tr style="line-height:30px;"><td></td></tr>
    <?php endforeach; ?>
    </table>
