<?php use_helper('TemplatingPDF'); ?>
<style>
</style>
    <div><span class="h3">&nbsp;Etiquettes des prélévements&nbsp;</span></div>
    <table border="" class="table" cellspacing=0 cellpadding=0 style="text-align: right;">
    <?php foreach($plancheLots as $lotInfo): ?>
        <tr>
          <?php for($i=0; $i <3 ; $i++): ?>
            <td class="td" style="text-align: left;">
                <table class="table" cellspacing=0 cellpadding=0 style="text-align: left;">
                  <tr>
                    <td colspan="2">
                      <?php echo tdStart() ?>&nbsp;<strong><?php echo $lotInfo->lot->declarant_nom;  ?></strong>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2">
                      <?php echo tdStart() ?>&nbsp;Ville&nbsp;:&nbsp;<strong><?php echo $lotInfo->etablissement->commune;  ?></strong>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php echo tdStart() ?>&nbsp;N°&nbsp;Dos&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->numero_dossier;  ?></strong>
                    </td>
                    <td>
                      <?php echo tdStart() ?>&nbsp;N° Lot ODG&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->numero_archive;  ?></strong>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php echo tdStart() ?>&nbsp;IGP&nbsp;:&nbsp;<strong><?php echo sfConfig::get('sf_app');  ?></strong>
                    </td>
                    <td>
                      <?php echo tdStart() ?>&nbsp;Vol. (hl)&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->volume;  ?></strong>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php echo tdStart() ?>&nbsp;N° Lot Op&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->numero_cuve;  ?></strong>
                    </td>
                    <td>
                      <?php echo tdStart() ?>&nbsp;Lgt&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->numero_cuve;  ?></strong>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <?php echo tdStart() ?>&nbsp;Millesime&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->millesime;  ?></strong>
                    </td>
                    <td>
                      <?php echo tdStart() ?>&nbsp;Couleur&nbsp;:&nbsp;<strong><?php echo $lotInfo->couleur;  ?></strong>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2">
                      <?php echo tdStart() ?>&nbsp;Cépages&nbsp;:&nbsp;<strong><?php echo $lotInfo->lot->details;  ?></strong>
                    </td>
                  </tr>
                </table>
            </td>
          <?php endfor; ?>
        </tr>
    <?php endforeach; ?>
    </table>
