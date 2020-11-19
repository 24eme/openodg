<?php use_helper('TemplatingPDF'); ?>
<style>
</style>
    <table border="" class="" cellspacing=0 cellpadding=0 style="text-align: right;">
    <?php foreach($plancheLots as $lotInfo): ?>
        <tr>
          <?php for($i=0; $i <3 ; $i++): ?>
            <td style="text-align: left;">
                <table cellspacing=0 cellpadding=0 >
                  <tr style="line-height:7px;">
                    <td colspan="2"  >
                      <?php echo tdStart() ?>&nbsp;<strong><?php echo ($lotInfo->lot->declarant_nom)? $lotInfo->lot->declarant_nom : "Leurre";  ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:7px;">
                    <td colspan="2">
                      <?php echo tdStart() ?>&nbsp;Ville&nbsp;:&nbsp;<strong><?php echo ($lotInfo->etablissement)? $lotInfo->etablissement->commune : '';  ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:7px;">
                    <td>
                      <?php echo tdStart() ?>&nbsp;N°&nbsp;Dos&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->numero_dossier;  ?></strong>
                    </td>
                    <td>
                      <?php echo tdStart() ?>&nbsp;N° Lot ODG&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->numero_archive;  ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:7px;">
                    <td>
                      <?php echo tdStart() ?>&nbsp;IGP&nbsp;:&nbsp;<strong><?php echo sfConfig::get('sf_app');  ?></strong>
                    </td>
                    <td>
                      <?php echo tdStart() ?>&nbsp;Vol. (hl)&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->volume;  ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:7px;">
                    <td>
                      <?php echo tdStart() ?>&nbsp;N° Lot Op&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->numero_cuve;  ?></strong>
                    </td>
                    <td>
                      <?php echo tdStart() ?>&nbsp;Lgt&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->numero_cuve;  ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:7px;">
                    <td>
                      <?php echo tdStart() ?>&nbsp;Millesime&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->millesime;  ?></strong>
                    </td>
                    <td>
                      <?php echo tdStart() ?>&nbsp;Couleur&nbsp;:&nbsp;<strong><?php echo $lotInfo->couleur;  ?></strong>
                    </td>
                  </tr>
                  <tr style="line-height:7px;">
                    <td colspan="2">
                      <?php echo tdStart() ?>&nbsp;Cépages&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->details;  ?></strong>
                    </td>
                  </tr>
                </table>
            </td>
          <?php endfor; ?>
        </tr>
        <tr style="line-height:30px;"><td></td></tr>
    <?php endforeach; ?>
    </table>
