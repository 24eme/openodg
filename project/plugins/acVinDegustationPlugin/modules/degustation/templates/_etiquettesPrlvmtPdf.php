<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<?php use_helper('Text') ?>
<style>
  .font-1-3em{
    /*font-size: 1.3em;*/
  }

  table.table-etiquette tr td {
       /*border: 0px solid #fff;*/
       font-family: monospace;
       font-size: 11px;
  }
</style>
    <table cellspacing="0" cellpadding="0" style="height: 1122.4px; margin: 0; padding: 0">
    <?php foreach($plancheLots as $lotInfo): ?>
        <tr style="height: <?php echo 1122.4/count($plancheLots) ?>px; margin: 0; padding: 0;">
          <?php for($i=0; $i <3 ; $i++): ?>
            <td cellspacing="0" cellpadding="0" style="margin: 0; padding: 0;">
                <table class="table-etiquette" cellspacing="0" cellpadding="0" style="font-size:8px;overflow: hidden;white-space: nowrap; top: 0; left: 0; padding: 0; margin: 0; width: 226px">
		  <tr>
                        <td style="overflow-wrap:break-word;text-align: left; height: 6px; line-height: 6px; overflow: hidden;" colspan="2">&nbsp;</td>
                  </tr>
                  <tr>
                    <td style="overflow-wrap:break-word;text-align: left; height: 15px; line-height: 15px; overflow: hidden;">&nbsp;N°ODG <strong><?php echo (int)$lotInfo->lot->numero_archive;  ?></strong></td>
                    <td style="overflow-wrap:break-word;text-align: right; height: 15px; line-height: 15px; overflow: hidden; padding-right">N°DOSSIER <strong><?php echo (int)$lotInfo->lot->numero_dossier;  ?></strong>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" style="overflow-wrap:break-word;text-align: center; height: 14.5px; line-height: 14px; overflow: hidden;" ><strong><?php if ($i != 2 || !$anonymat4labo): ?><?php echo ($lotInfo->lot->declarant_nom)? truncate_text($lotInfo->lot->declarant_nom, 33, '…') : "Leurre";  ?><?php endif; ?></strong></td>
                  </tr>
                  <tr>
                    <td colspan="2" style="overflow-wrap:break-word;text-align: center; height: 14px; line-height: 14px; overflow: hidden;">
                      <?php if ($i != 2 || !$anonymat4labo): ?>
                          <?php if($lotInfo->etablissement->cvi):echo ($lotInfo->etablissement->cvi);
                           elseif ($lotInfo->etablissement->siret):echo (substr($lotInfo->etablissement->siret,0,3)." ".substr($lotInfo->etablissement->siret,3,3)." ".substr($lotInfo->etablissement->siret,6,3)." ".substr($lotInfo->etablissement->siret,9,5));
                          endif; ?>
                      <?php else: ?>
                          <i>Lot destiné au laboratoire</i>
                      <?php endif; ?>
                    </td>
                  </tr>
		  <tr>
                        <td style="overflow-wrap:break-word;text-align: left; height: 4px; line-height: 1px; overflow: hidden;" colspan="2">&nbsp;</td>
                  </tr>
                  <tr>
                    <td colspan="2" style="overflow-wrap:break-word;text-align: center; height: 29px; line-height: 14px; overflow: hidden; vertical-align: middle;"><strong><?php echo truncate_text(strtoupper(KeyInflector::unaccent("IGP ".$lotInfo->getRawValue()->lot->produit_libelle)), 66, '…', 'middle') .' '.  $lotInfo->lot->millesime;  ?></strong>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" style="overflow-wrap:break-word;text-align: center; height: 24px; line-height: 12px; overflow: hidden;"><?php echo showOnlyCepages($lotInfo->lot, 66, 'span') ?></td>
                  </tr>
                  <tr>
                    <td style="overflow-wrap:break-word;text-align: left; height: 15px; line-height: 15px; overflow: hidden; width: 65%;">&nbsp;<?php $lot = $lotInfo->lot; $centilisation = $lot->centilisation ? " ($lot->centilisation)" : null; ?>LGT <strong><?php echo truncate_text($lotInfo->lot->numero_logement_operateur.$centilisation, 17, '…');  ?></strong>
                    </td>
                    <td style="overflow-wrap:break-word;text-align: right; height: 15px; line-height: 15px; overflow: hidden; width: 35%;"><strong><?php echo sprintf("%.2f", $lotInfo->lot->volume);  ?></strong> HL</td>
                  </tr>
		  <tr>
                        <td style="overflow-wrap:break-word;text-align: left; height: 6px; line-height: 6px; overflow: hidden;" colspan="2">&nbsp;</td>
                  </tr>
                </table>
            </td>
          <?php endfor; ?>
        </tr>
    <?php endforeach; ?>
    </table>
